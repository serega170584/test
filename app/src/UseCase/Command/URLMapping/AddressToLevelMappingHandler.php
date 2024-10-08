<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\ApiResource\URLProvider;
use App\DTO\ApiRequest\Address;
use App\Entity\AddressEntity;
use App\Entity\AddressLevelEntity;
use App\Exception\AddressToLevelMappingException;
use App\Repository\AddressLevelRepository;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class AddressToLevelMappingHandler implements CommandHandlerBase
{
    private const RETRY_COUNT = 3;
    private const BATCH_SIZE = 100;

    public function __construct(
        private EntityManagerInterface $em,
        private AddressLevelRepository $addressLevelRepository,
        private AddressRepository      $addressRepository,
        private LoggerInterface        $logger,
        private URLProvider            $provider,
        private ManagerRegistry        $mr,
    ) {
    }

    public function __invoke(AddressToLevelMappingCommand $addressToLevelMappingCommand): void
    {
        $this->logger->info('Address to level mapping start');

        try {
            $provider = $this->provider;
            $count = $provider->getAddressCount();
            $offset = 0;
            while ($offset < $count) {
                $addressToLevels = $provider->getAddresses($offset, self::BATCH_SIZE);
                $addressIds = [];
                $levelIds = [];
                foreach ($addressToLevels as $addressToLevel) {
                    $addressIds[] = $addressToLevel->id;
                    $levelIds[] = $addressToLevel->levelId;
                }

                try {
                    $this->saveAddressToLevels($addressToLevels, $addressIds, $levelIds);
                } catch (\Exception $e) {
                    $this->logger->error('Address to level save error:'.$e->getMessage(), ['offset' => $offset]);
                }

                $offset += self::BATCH_SIZE;
            }
        } catch (\Exception $e) {
            throw new AddressToLevelMappingException('Address to level mapping error', $e->getCode(), $e);
        }

        $this->logger->info('Address to level mapping finish');
    }

    /**
     * @param Address[] $addressToLevels
     *
     * @throws \Exception
     */
    private function saveAddressToLevels(array $addressToLevels, array $addressIds, array $levelIds): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Address to levels save start', ['attempt' => $i]);

            try {
                $addressIds = array_unique($addressIds);
                $levelIds = array_unique($levelIds);

                $addresses = $this->addressRepository->findBy(['externalId' => $addressIds]);
                $addresses = array_combine(array_map(static function (AddressEntity $address) {
                    return $address->getExternalId();
                }, $addresses), $addresses);

                $levels = $this->addressLevelRepository->findBy(['externalId' => $levelIds]);
                $levels = array_combine(array_map(static function (AddressLevelEntity $level) {
                    return $level->getExternalId();
                }, $levels), $levels);

                foreach ($addressToLevels as $addressToLevel) {
                    $address = $addresses[$addressToLevel->id];
                    $level = $levels[$addressToLevel->levelId];
                    $address->setLevel($level);
                }

                $this->em->flush();
                $this->em->clear();

                $this->logger->info('Address to levels save successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Address to levels save failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }
}
