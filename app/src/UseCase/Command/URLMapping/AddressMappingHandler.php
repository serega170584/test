<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\ApiResource\URLProvider;
use App\DTO\ApiRequest\Address;
use App\Entity\AddressEntity;
use App\Exception\AddressMappingException;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class AddressMappingHandler implements CommandHandlerBase
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

    public function __invoke(AddressMappingCommand $addressMappingCommand): void
    {
        $this->logger->info('Address mapping start');

        try {
            $this->truncateAddresses();
            $provider = $this->provider;
            $count = $provider->getAddressCount();
            $offset = 0;
            while ($offset < $count) {
                $addresses = $provider->getAddresses($offset, self::BATCH_SIZE);
                try {
                    $this->saveAddresses($addresses);
                } catch (\Exception $e) {
                    $this->logger->error('Address save error:'.$e->getMessage(), ['offset' => $offset]);
                }

                $offset += self::BATCH_SIZE;
            }
        } catch (\Exception $e) {
            throw new AddressMappingException('Address mapping error', $e->getCode(), $e);
        }

        $this->logger->info('Address mapping finish');
    }

    /**
     * @throws \Exception
     */
    private function truncateAddresses(): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Addresses truncate start', ['attempt' => $i]);

            try {
                $this->addressRepository->truncate();

                $this->logger->info('Addresses truncate successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Addresses truncate failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param Address[] $addresses
     *
     * @throws \Exception
     */
    private function saveAddresses(array $addresses): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Addresses save start', ['attempt' => $i]);

            try {
                foreach ($addresses as $address) {
                    $dbAddress = new AddressEntity();
                    $dbAddress->setName($address->name);
                    $dbAddress->setActive($address->active);
                    $dbAddress->setExternalId($address->id);
                    $dbAddress->setDisplay($address->display);
                    $dbAddress->setGuid($address->guid);
                    $dbAddress->setFirstLevelParent($address->firstLevelParent);
                    $dbAddress->setFullName($address->fullName);
                    $dbAddress->setParentFullName($address->parentFullName);
                    $this->em->persist($dbAddress);
                }

                $this->em->flush();
                $this->em->clear();

                $this->logger->info('Addresses save successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Addresses save failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }
}
