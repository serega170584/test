<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\ApiResource\URLProvider;
use App\DTO\ApiRequest\Address;
use App\Entity\AddressEntity;
use App\Exception\AddressToParentMappingException;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class AddressToParentMappingHandler implements CommandHandlerBase
{
    private const BATCH_SIZE = 100;

    private const RETRY_COUNT = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private AddressRepository      $addressRepository,
        private LoggerInterface        $logger,
        private URLProvider            $provider,
        private ManagerRegistry        $mr,
    ) {
    }

    public function __invoke(AddressToParentMappingCommand $addressToParentCommand): void
    {
        $this->logger->info('Address to parent mapping start');

        try {
            $provider = $this->provider;
            $count = $provider->getAddressCount();
            $offset = 0;
            while ($offset < $count) {
                $addressToAddresses = $provider->getAddresses($offset, self::BATCH_SIZE);
                $addressIds = [];
                $parentAddressGuids = [];
                foreach ($addressToAddresses as $addressToAddress) {
                    if (null === $addressToAddress->parentId || '' === $addressToAddress->parentId) {
                        continue;
                    }

                    $addressIds[] = $addressToAddress->id;
                    $parentAddressGuids[] = $addressToAddress->parentId;
                }

                $addressIds = array_unique($addressIds);
                $parentAddressGuids = array_unique($parentAddressGuids);

                try {
                    $this->saveAddressToParents($addressToAddresses, $addressIds, $parentAddressGuids);
                } catch (\Exception $e) {
                    $this->logger->error('Address to parent save error:'.$e->getMessage(), ['offset' => $offset]);
                }

                $offset += self::BATCH_SIZE;
            }
        } catch (\Exception $e) {
            throw new AddressToParentMappingException('Address to parent mapping error', $e->getCode(), $e);
        }

        $this->logger->info('Address to parent mapping finish');
    }

    /**
     * @param Address[] $addressToAddresses
     *
     * @throws \Exception
     */
    private function saveAddressToParents(array $addressToAddresses, array $addressIds, array $parentAddressGuids): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Address to parents save start', ['attempt' => $i]);

            try {
                $addresses = $this->addressRepository->findBy(['externalId' => $addressIds]);
                $addresses = array_combine(array_map(static function (AddressEntity $address) {
                    return $address->getExternalId();
                }, $addresses), $addresses);

                $parentAddresses = $this->addressRepository->findBy(['guid' => $parentAddressGuids]);
                $parentAddresses = array_combine(array_map(static function (AddressEntity $address) {
                    return $address->getGuid();
                }, $parentAddresses), $parentAddresses);

                foreach ($addressToAddresses as $addressToAddress) {
                    if (null === $addressToAddress->parentId || '' === $addressToAddress->parentId) {
                        continue;
                    }

                    $address = $addresses[$addressToAddress->id];
                    $parentAddress = $parentAddresses[$addressToAddress->parentId];
                    $address->setParent($parentAddress);
                }

                $this->em->flush();
                $this->em->clear();

                $this->logger->info('Address to parents save successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Address to parents save failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }
}
