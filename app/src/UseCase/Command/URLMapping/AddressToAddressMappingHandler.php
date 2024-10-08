<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\ApiResource\URLProvider;
use App\DTO\ApiRequest\AddressToAddress;
use App\Entity\AddressEntity;
use App\Entity\AddressToAddressEntity;
use App\Exception\AddressToAddressMappingException;
use App\Repository\AddressRepository;
use App\Repository\AddressToAddressEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class AddressToAddressMappingHandler implements CommandHandlerBase
{
    private const BATCH_SIZE = 100;

    private const RETRY_COUNT = 3;

    public function __construct(
        private EntityManagerInterface           $em,
        private AddressRepository                $addressRepository,
        private AddressToAddressEntityRepository $addressToAddressEntityRepository,
        private LoggerInterface                  $logger,
        private URLProvider                      $provider,
        private ManagerRegistry                  $mr,
    ) {
    }

    public function __invoke(AddressToAddressMappingCommand $addressToAddressMappingCommand
    ): void {
        $this->logger->info('Address to address mapping start');

        try {
            $this->addressToAddressEntityRepository->truncate();

            $provider = $this->provider;
            $count = $provider->getAddressToAddressCount();
            $offset = 0;

            while ($offset < $count) {
                $addressToAddresses = $provider->getAddressToAddresses($offset, self::BATCH_SIZE);
                $addressIds = [];
                $parentIds = [];
                foreach ($addressToAddresses as $addressToAddress) {
                    $addressIds[] = $addressToAddress->addressId;
                    $parentIds[] = $addressToAddress->parentId;
                }

                $addressIds = array_unique($addressIds);
                $parentIds = array_unique($parentIds);

                try {
                    $this->saveAddressToAddresses($addressToAddresses, $addressIds, $parentIds);
                } catch (\Exception $e) {
                    $this->logger->error('Address to address save error:'.$e->getMessage(), ['offset' => $offset]);
                }

                $offset += self::BATCH_SIZE;
            }
        } catch (\Exception $e) {
            throw new AddressToAddressMappingException('Address to address mapping error', $e->getCode(), $e);
        }

        $this->logger->info('Address to address mapping finish');
    }

    /**
     * @param AddressToAddress[] $addressToAddresses
     *
     * @throws \Exception
     */
    private function saveAddressToAddresses(array $addressToAddresses, array $addressIds, array $parentIds): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Address to addresses save start', ['attempt' => $i]);

            try {
                $addresses = $this->addressRepository->findBy(['externalId' => $addressIds]);
                $addresses = array_combine(
                    array_map(static function (AddressEntity $address) {
                        return $address->getExternalId();
                    }, $addresses),
                    $addresses
                );

                $parents = $this->addressRepository->findBy(['externalId' => $parentIds]);
                $parents = array_combine(
                    array_map(static function (AddressEntity $address) {
                        return $address->getExternalId();
                    }, $parents),
                    $parents
                );

                foreach ($addressToAddresses as $addressToAddress) {
                    $address = $addresses[$addressToAddress->addressId];
                    $parent = $parents[$addressToAddress->parentId];
                    $addressToAddressEntity = new AddressToAddressEntity();
                    $addressToAddressEntity->setAddress($address);
                    $addressToAddressEntity->setParent($parent);
                    $addressToAddressEntity->setDepth($addressToAddress->depth);
                    $this->em->persist($addressToAddressEntity);
                }

                $this->em->flush();
                $this->em->clear();

                $this->logger->info('Address to addresses save successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Address to addresses save failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }
}
