<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportShopGroups;

use App\DTO\Filter\ShopGroupFilter;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\Exception\ImportException;
use App\Repository\ConsumerRepository;
use App\Repository\ShopGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final class ImportShopGroupsHandler implements CommandHandlerBase
{
    /**
     * @var array<string, ConsumerEntity>
     */
    private array $cacheConsumerEntity = [];

    /**
     * @var array<string, array<string, ?ShopGroupEntity>>
     */
    private array $relationshipWithParent = [];

    public function __construct(
        private ShopGroupRepository $shopGroupRepository,
        private ConsumerRepository $consumerRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws ImportException
     */
    public function __invoke(ImportShopGroupsCommand $command): void
    {
        $this->logger->info('ShopGroups start import');

        try {
            $this->warmUpCacheConsumerEntity();
            $this->clearRelationshipWithParent();

            $importItems = $this->groupingItemsByCode($command);

            $this->em->beginTransaction();

            $newItems = $this->updateOrRemoveEntities($importItems);
            $this->insertNewItems($newItems);
            $this->linkParentsAndChildren();

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw new ImportException('Error when trying to import ShopGroups', $e->getCode(), $e);
        }

        $this->logger->info('ShopGroups import complete');
    }

    private function warmUpCacheConsumerEntity(): void
    {
        $this->cacheConsumerEntity = [];

        foreach ($this->consumerRepository->findAll() as $item) {
            $this->cacheConsumerEntity[$item->getCode()->value()] = $item;
        }
    }

    private function clearRelationshipWithParent(): void
    {
        $this->relationshipWithParent = [];
    }

    /**
     * @return array<string, ImportShopGroupItem>
     */
    private function groupingItemsByCode(ImportShopGroupsCommand $command): array
    {
        $result = [];

        foreach ($command->items as $item) {
            $result[$item->code] = $item;
            $this->createRelationshipWithParent($item->parentCode);
        }

        return $result;
    }

    private function createRelationshipWithParent(string $code): void
    {
        if ($code) {
            $this->relationshipWithParent[$code] = [
                'parent' => null,
            ];
        }
    }

    /**
     * @param array<string, ImportShopGroupItem> $importItems
     *
     * @return array<string, ImportShopGroupItem>
     */
    private function updateOrRemoveEntities(array $importItems): array
    {
        $entities = $this->shopGroupRepository->findByFilter(new ShopGroupFilter());

        foreach ($entities as $entity) {
            if ($importItem = $importItems[$entity->getCode()] ?? null) {
                /* @var ImportShopGroupItem $importItem */
                $entity->setTitle($importItem->title)
                       ->setDescription($importItem->description)
                       ->setActive($importItem->active)
                       ->setParent(null);

                $this->createRelationshipWithConsumer($importItem, $entity);
                $this->updateRelationshipWithParent($importItem, $entity);

                $this->shopGroupRepository->save($entity);

                unset($importItems[$entity->getCode()]);
            } else {
                $this->shopGroupRepository->remove($entity);
            }
        }

        return $importItems;
    }

    /**
     * @param array<string, ImportShopGroupItem> $importItems
     */
    private function insertNewItems(array $importItems): void
    {
        foreach ($importItems as $item) {
            $entity = (new ShopGroupEntity())->setCode($item->code)
                                             ->setTitle($item->title)
                                             ->setDescription($item->description)
                                             ->setActive($item->active);

            $this->createRelationshipWithConsumer($item, $entity);
            $this->updateRelationshipWithParent($item, $entity);

            $this->shopGroupRepository->save($entity);
        }
    }

    private function updateRelationshipWithParent(
        ImportShopGroupItem $importShopGroupItem,
        ShopGroupEntity $entity
    ): void {
        if (array_key_exists($entity->getCode(), $this->relationshipWithParent)) {
            $this->relationshipWithParent[$entity->getCode()]['parent'] = $entity;
        }

        if (array_key_exists($importShopGroupItem->parentCode, $this->relationshipWithParent)) {
            $this->relationshipWithParent[$importShopGroupItem->parentCode][] = $entity;
        }
    }

    private function createRelationshipWithConsumer(ImportShopGroupItem $importItem, ShopGroupEntity $entity): void
    {
        $consumerRelationship = [];

        foreach ($importItem->consumers as $consumerCode) {
            $consumerRelationship[$consumerCode->value()] = 0;
        }

        foreach ($entity->getConsumers() as $consumer) {
            if (array_key_exists($consumer->getCode()->value(), $consumerRelationship)) {
                unset($consumerRelationship[$consumer->getCode()->value()]);
            } else {
                $entity->removeConsumer($consumer);
            }
        }

        foreach ($consumerRelationship as $consumerCode => $index) {
            if (array_key_exists($consumerCode, $this->cacheConsumerEntity)) {
                $entity->addConsumer($this->cacheConsumerEntity[$consumerCode]);
            }
        }
    }

    private function linkParentsAndChildren(): void
    {
        foreach ($this->relationshipWithParent as $items) {
            $parent = $items['parent'] ?? null;
            unset($items['parent']);
            foreach ($items as $child) {
                if ($child) {
                    $child->setParent($parent);
                }
            }
        }
    }
}
