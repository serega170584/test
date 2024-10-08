<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\Exception\VirtualGroupException;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use App\Service\ImportVirtualGroupStrategy\DistrImportVirtualGroupStrategy;
use App\Service\ImportVirtualGroupStrategy\ImportShopGroupStrategy;
use App\Service\ImportVirtualGroupStrategy\OtherConsumerImportVirtualGroupStrategy;
use Doctrine\DBAL\Exception;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;

final class GenerateVirtualGroupHandler implements CommandHandlerBase
{
    /** @var ImportShopGroupStrategy[] */
    private array $importShopGroupStrategies;

    private RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository;

    public function __construct(
        RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository,
        OtherConsumerImportVirtualGroupStrategy $otherConsumerImportVirtualGroupStrategy,
        DistrImportVirtualGroupStrategy $distrImportVirtualGroupStrategy
    ) {
        $this->relationshipOfStoreGroupsToShopRepository = $relationshipOfStoreGroupsToShopRepository;

        $this->addImportStrategy($otherConsumerImportVirtualGroupStrategy);
        $this->addImportStrategy($distrImportVirtualGroupStrategy);
    }

    /**
     * @throws VirtualGroupException
     * @throws Exception
     */
    public function __invoke(GenerateVirtualGroupCommand $command): void
    {
        /** @var array<int> $validShopGroupIDs */
        $validShopGroupIDs = [];

        foreach ($this->importShopGroupStrategies as $importShopGroupStrategy) {
            $validShopGroupIDs[] = $importShopGroupStrategy->importShopGroups();
        }

        // TODO подумать о разделении  удаления невалидных связок ВГ и ТО  в рамках одной стратегии, а не в одном запросе
        if (!empty($validShopGroupIDs)) {
            $this->relationshipOfStoreGroupsToShopRepository->deleteNotIn(array_merge(...$validShopGroupIDs));
        }
    }

    public function addImportStrategy(ImportShopGroupStrategy $groupStrategy): self
    {
        $this->importShopGroupStrategies[] = $groupStrategy;

        return $this;
    }
}
