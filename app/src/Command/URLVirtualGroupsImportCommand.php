<?php

declare(strict_types=1);

namespace App\Command;

use App\Bus\BusManager;
use App\Service\FeatureToggleService;
use App\UseCase\Command\URLMapping\AddressLevelMappingCommand;
use App\UseCase\Command\URLMapping\AddressMappingCommand;
use App\UseCase\Command\URLMapping\AddressToAddressMappingCommand;
use App\UseCase\Command\URLMapping\AddressToLevelMappingCommand;
use App\UseCase\Command\URLMapping\AddressToParentMappingCommand;
use App\UseCase\Command\URLMapping\AddressToShopMappingCommand;
use App\UseCase\Command\URLMapping\GenerateVirtualGroupCommand;
use App\UseCase\Command\URLMapping\ShopMappingCommand;
use App\UseCase\Command\URLMapping\VirtualGroupCommand;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:url-virtual-groups-import',
    description: 'Add a short description for your command',
)]
class URLVirtualGroupsImportCommand extends Command
{
    private BusManager $busManager;

    private CacheItemPoolInterface $cache;

    private FeatureToggleService $featureToggleService;

    public function __construct(
        BusManager $busManager,
        CacheItemPoolInterface $cache,
        FeatureToggleService $featureToggleService,
        string $name = null,
    ) {
        parent::__construct($name);
        $this->busManager = $busManager;
        $this->cache = $cache;
        $this->featureToggleService = $featureToggleService;
    }

    protected function configure(): void
    {
        $this->addOption('import_only_virtual_groups', null, InputOption::VALUE_NONE, 'Do you import virtual groups without tables?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isOnlyVirtualGroupsImport = $input->getOption('import_only_virtual_groups');
        if (!$isOnlyVirtualGroupsImport) {
            $this->busManager->executeAddressLevelMappingCommand(new AddressLevelMappingCommand());
            $this->busManager->executeAddressMappingCommand(new AddressMappingCommand());
            $this->busManager->executeAddressToLevelMappingCommand(new AddressToLevelMappingCommand());
            $this->busManager->executeAddressToParentMappingCommand(new AddressToParentMappingCommand());
            $this->busManager->executeAddressToAddressMappingCommand(new AddressToAddressMappingCommand());
        }

        $this->busManager->executeShopMappingCommand(new ShopMappingCommand());
        $this->busManager->executeAddressToShopMappingCommand(new AddressToShopMappingCommand());

        if ($this->featureToggleService->isEnabledGenerateVirtualGroupForDistr()) {
            $this->busManager->executeGenerateVirtualGroupCommand(new GenerateVirtualGroupCommand());
        } else {
            $this->busManager->executeVirtualGroupCommand(new VirtualGroupCommand());
        }

        $this->cache->clear();

        return Command::SUCCESS;
    }
}
