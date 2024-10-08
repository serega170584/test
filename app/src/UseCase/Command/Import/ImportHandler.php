<?php

declare(strict_types=1);

namespace App\UseCase\Command\Import;

use App\Entity\ConsumerEntity;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Entity\ShopGroupEntity;
use App\Enum\PrefixEnum;
use App\Repository\ConsumerRepository;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use App\Repository\ShopGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class ImportHandler implements CommandHandlerBase
{
    public function __construct(
        private EntityManagerInterface $em,
        private ConsumerRepository $consumerRepository,
        private ShopGroupRepository $shopGroupRepository,
        private RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ImportCommand $command): void
    {
        try {
            $consumers = $this->importConsumers($command->consumers);
            $shopGroups = $this->importShopGroups($command->shopGroups, $consumers);
            $this->importRelationshipOfStoreGroupsToShop($command->relationshipOfStoreGroupsToShopItems, $shopGroups);

            $this->em->flush();
            $this->em->clear();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param ImportConsumerItem[] $importConsumerItems
     *
     * @return array<string, ConsumerEntity>
     */
    private function importConsumers(array $importConsumerItems): array
    {
        $result = [];
        if (!$importConsumerItems) {
            throw new \Exception('No data for consumers import');
        }

        $this->logger->info('Start import consumers');
        foreach ($importConsumerItems as $item) {
            $entity = $this->consumerRepository->findByCode($item->code);

            $isNew = false;

            if (null === $entity) {
                $entity = new ConsumerEntity();
                $isNew = true;
            }

            $entity->setCode($item->code)
                   ->setName($item->title)
                   ->setDescription($item->description);

            if ($isNew) {
                $this->consumerRepository->save($entity);
            }

            $result[$item->code->value()] = $entity;
        }

        $this->logger->info('Finish import consumers');

        return $result;
    }

    /**
     * @param ImportShopGroupItem[]         $importShopGroupItems
     * @param array<string, ConsumerEntity> $consumers
     *
     * @return array<string, ShopGroupEntity>
     */
    private function importShopGroups(array $importShopGroupItems, array $consumers): array
    {
        $result = [];

        if (!$importShopGroupItems) {
            throw new \Exception('No data for shopGroups import');
        }

        $this->logger->info('Start import shopGroups');

        $relationshipWithParent = [];

        foreach ($importShopGroupItems as $item) {
            $entity = $this->shopGroupRepository->findOneByCode(PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist((string) $item->code));

            $isNew = false;

            if (null === $entity) {
                $entity = new ShopGroupEntity();
                $isNew = true;
            }

            $entity = $entity->setCode($item->code)
                 ->setTitle($item->title)
                 ->setDescription($item->description)
                 ->setActive($item->active)
                 ->setFiasId($item->fiasId);

            foreach ($item->consumers as $code) {
                if (array_key_exists($code, $consumers)) {
                    $entity->addConsumer($consumers[$code]);
                } else {
                    $this->logger->error(
                        sprintf(
                            'Для shopGroup [%s] не найден consumer [%s]',
                            $item->code,
                            $code
                        )
                    );
                }
            }

            if ($isNew) {
                $this->shopGroupRepository->save($entity);
            }
            $result[$item->code] = $entity;

            if ($item->parentCode) {
                if (array_key_exists($item->parentCode, $relationshipWithParent)) {
                    $relationshipWithParent[$item->parentCode][] = $entity;
                } else {
                    $relationshipWithParent[$item->parentCode] = [$entity];
                }
            }
        }

        foreach ($relationshipWithParent as $shopGroupCode => $parentEntities) {
            array_map([$result[$shopGroupCode], 'addChildren'], $parentEntities);
        }

        $this->logger->info('Finish import shopGroups');

        return $result;
    }

    /**
     * @param ImportRelationshipOfStoreGroupsToShopItem[] $importRelationshipOfStoreGroupsToShopItem
     * @param array<string, ShopGroupEntity>              $shopGroups
     */
    private function importRelationshipOfStoreGroupsToShop(
        array $importRelationshipOfStoreGroupsToShopItem,
        array $shopGroups
    ): void {
        if (empty($importRelationshipOfStoreGroupsToShopItem)) {
            $this->logger->info('Not found items for import relationshipOfStoreGroupsToShop');
            throw new \Exception('No data for relationshipOfStoreGroupsToShop import');
        }

        $this->logger->info('Start import relationshipOfStoreGroupsToShop');

        foreach ($importRelationshipOfStoreGroupsToShopItem as $key => $item) {
            if (array_key_exists($item->shopGroupCode, $shopGroups)) {
                $this->logger->info($key.' in progress');
                $entity = $this->relationshipOfStoreGroupsToShopRepository->findOneBy(['shopGroup' => $shopGroups[$item->shopGroupCode], 'ufXmlId' => $item->ufXmlId]);

                $isNew = false;
                if (null === $entity) {
                    $isNew = true;
                    $entity = new RelationshipOfStoreGroupsToShopEntity();
                }

                $entity
                    ->setShopGroup($shopGroups[$item->shopGroupCode])
                    ->setUfXmlId($item->ufXmlId);

                if ($isNew) {
                    $this->relationshipOfStoreGroupsToShopRepository->save($entity);
                }
            } else {
                throw new \Exception(sprintf('Не найден shopGroup [%s] для ТО [%s]', $item->shopGroupCode, $item->ufXmlId));
            }
        }

        $this->logger->info('Finish import relationshipOfStoreGroupsToShop');
    }
}
