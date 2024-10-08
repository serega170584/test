<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportConsumers;

use App\Entity\ConsumerEntity;
use App\Enum\PrefixEnum;
use App\Exception\ImportException;
use App\Repository\ConsumerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class ImportConsumersHandler implements CommandHandlerBase
{
    public function __construct(
        private ConsumerRepository $consumerRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws ImportException
     */
    public function __invoke(ImportConsumersCommand $command): void
    {
        $this->logger->info('Consumers start import');

        try {
            $importItems = $this->groupingItemsByCode($command);

            $this->em->beginTransaction();

            $newItems = $this->updateOrRemoveEntities($importItems);
            $this->insertNewItems($newItems);

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw new ImportException('Error when trying to import Consumers', $e->getCode(), $e);
        }

        $this->logger->info('Consumers import complete');
    }

    /**
     * @return array<string, ImportConsumerItem>
     */
    private function groupingItemsByCode(ImportConsumersCommand $command): array
    {
        $result = [];
        foreach ($command->items as $item) {
            $result[PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist($item->code->value())] = $item;
        }

        return $result;
    }

    /**
     * @param array<string, ImportConsumerItem> $importItems
     *
     * @return array<string, ImportConsumerItem>
     */
    private function updateOrRemoveEntities(array $importItems): array
    {
        $consumersForUpdated = $this->consumerRepository->findAll();

        foreach ($consumersForUpdated as $consumerEntity) {
            if ($importItem = $importItems[$consumerEntity->getCode()->value()] ?? null) {
                $consumerEntity->setName($importItem->title)
                               ->setDescription($importItem->description);

                $this->consumerRepository->save($consumerEntity);

                unset($importItems[$consumerEntity->getCode()->value()]);
            } else {
                $this->consumerRepository->remove($consumerEntity);
            }
        }

        return $importItems;
    }

    /**
     * @param array<string, ImportConsumerItem> $importItems
     */
    private function insertNewItems(array $importItems): void
    {
        foreach ($importItems as $item) {
            $this->consumerRepository->save(
                (new ConsumerEntity())->setCode($item->code)
                                      ->setName($item->title)
                                      ->setDescription($item->description)
            );
        }
    }
}
