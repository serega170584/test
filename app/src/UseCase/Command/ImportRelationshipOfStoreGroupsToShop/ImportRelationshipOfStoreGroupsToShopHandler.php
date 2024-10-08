<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportRelationshipOfStoreGroupsToShop;

use App\DTO\Filter\ShopGroupFilter;
use App\Entity\ShopGroupEntity;
use App\Exception\ImportException;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use App\Repository\ShopGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class ImportRelationshipOfStoreGroupsToShopHandler implements CommandHandlerBase
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository,
        private ShopGroupRepository $shopGroupRepository
    ) {
    }

    /**
     * @throws ImportException
     */
    public function __invoke(ImportRelationshipOfStoreGroupsToShopCommand $command): void
    {
        $this->logger->info('RelationshipOfStoreGroupsToShop start import');

        try {
            $importItems = $this->groupingItemsByCode($command);

            $this->em->beginTransaction();

            $newItems = $this->removeEntities($importItems);
            $this->insertNewItems($newItems);

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw new ImportException('Error when trying to import RelationshipOfStoreGroupsToShop', $e->getCode(), $e);
        }

        $this->logger->info('RelationshipOfStoreGroupsToShop import complete');
    }

    /**
     * @return array<string, string[]>
     */
    private function groupingItemsByCode(ImportRelationshipOfStoreGroupsToShopCommand $command): array
    {
        $result = [];

        foreach ($command->items as $item) {
            if (!array_key_exists($item->shopGroupCode, $result)) {
                $result[$item->shopGroupCode] = [];
            }

            $result[$item->shopGroupCode][$item->ufXmlId] = $item->ufXmlId;
        }

        return $result;
    }

    /**
     * @param array<string, array<string, string>> $importItems
     *
     * @return array<string, array<string, string>>
     */
    private function removeEntities(array $importItems): array
    {
        $shopGroups = $this->findShopGroupsByImportItems($importItems);

        foreach ($shopGroups as $shopGroup) {
            foreach ($shopGroup->getShops() as $shop) {
                $shopGroupCode = $shopGroup->getCode();
                $shopCode = $shop->getUfXmlId();

                if (array_key_exists($shopGroupCode, $importItems)
                    && array_key_exists($shopCode, $importItems[$shopGroupCode])) {
                    unset($importItems[$shopGroupCode][$shopCode]);
                } else {
                    $this->relationshipOfStoreGroupsToShopRepository->remove($shop);
                }
            }
        }

        return $importItems;
    }

    /**
     * @param array<string, array<string, string>> $importItems
     */
    private function insertNewItems(array $importItems): void
    {
        $shopGroups = $this->findShopGroupsByImportItems($importItems);

        foreach ($shopGroups as $shopGroupItem) {
            foreach ($importItems[$shopGroupItem->getCode()] as $shopCode) {
                $shopGroupItem->addShop($shopCode);
            }
            $this->shopGroupRepository->save($shopGroupItem);
        }
    }

    /**
     * @param array<string, array<string, string>> $importItems
     *
     * @return iterable<int, ShopGroupEntity>
     */
    private function findShopGroupsByImportItems(array $importItems): iterable
    {
        $filter = new ShopGroupFilter();
        $filter->codes = array_keys($importItems);

        return $this->shopGroupRepository->findByFilter($filter);
    }
}
