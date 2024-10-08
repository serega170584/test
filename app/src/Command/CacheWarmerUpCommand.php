<?php

declare(strict_types=1);

namespace App\Command;

use App\Bus\BusManager;
use App\Repository\ConsumerRepository;
use App\UseCase\Query\GetShopsGroupedByShopGroupCode\GetShopsGroupedByShopGroupCodeQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CacheWarmerUpCommand extends Command
{
    public function __construct(
        private readonly ConsumerRepository $consumerRepository,
        private readonly BusManager $busManager
    ) {
        parent::__construct('app:cache:warmup');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumers = $this->consumerRepository->findAll();

        foreach ($consumers as $consumer) {
            $this->busManager->askGetShopsGroupedByShopGroupCodeQuery(
                new GetShopsGroupedByShopGroupCodeQuery($consumer->getCode())
            );

            foreach ($consumer->getShopGroups() as $shopGroup) {
                $this->busManager->askGetShopsGroupedByShopGroupCodeQuery(
                    new GetShopsGroupedByShopGroupCodeQuery($consumer->getCode(), $shopGroup->getCode(), true)
                );
            }
        }

        return Command::SUCCESS;
    }
}
