<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\ApiResource\URLProvider;
use App\DTO\ApiRequest\AddressLevel;
use App\Entity\AddressLevelEntity;
use App\Exception\AddressLevelMappingException;
use App\Repository\AddressLevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class AddressLevelMappingHandler implements CommandHandlerBase
{
    private const BATCH_SIZE = 100;

    private const RETRY_COUNT = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private AddressLevelRepository $addressLevelRepository,
        private LoggerInterface        $logger,
        private URLProvider            $provider,
        private ManagerRegistry        $mr,
    ) {
    }

    public function __invoke(AddressLevelMappingCommand $addressLevelMappingCommand): void
    {
        $this->logger->info('Address level mapping start');

        try {
            $this->truncateAddressLevels();
            $provider = $this->provider;
            $count = $provider->getAddressLevelCount();
            $offset = 0;
            while ($offset < $count) {
                $addressLevels = $provider->getAddressLevels($offset, self::BATCH_SIZE);
                try {
                    $this->saveAddressLevels($addressLevels);
                } catch (\Exception $e) {
                    $this->logger->error('Address level save error:'.$e->getMessage(), ['offset' => $offset]);
                }
                $offset += self::BATCH_SIZE;
            }
        } catch (\Exception $e) {
            throw new AddressLevelMappingException('Address level mapping error', $e->getCode(), $e);
        }

        $this->logger->info('Address level mapping finish');
    }

    /**
     * @throws \Exception
     */
    private function truncateAddressLevels(): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Address levels truncate start', ['attempt' => $i]);

            try {
                $this->addressLevelRepository->truncate();

                $this->logger->info('Address levels truncate successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Address levels truncate failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param AddressLevel[] $addressLevels
     *
     * @throws \Exception
     */
    private function saveAddressLevels(array $addressLevels): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Address levels save start', ['attempt' => $i]);

            try {
                foreach ($addressLevels as $addressLevel) {
                    $dbAddressLevel = new AddressLevelEntity();
                    $dbAddressLevel->setName($addressLevel->name);
                    $dbAddressLevel->setShortName($addressLevel->shortName);
                    $dbAddressLevel->setLevel($addressLevel->level);
                    $dbAddressLevel->setExternalId($addressLevel->id);
                    $this->em->persist($dbAddressLevel);
                }

                $this->em->flush();
                $this->em->clear();

                $this->logger->info('Address levels save successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->error('Address levels save failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }
}
