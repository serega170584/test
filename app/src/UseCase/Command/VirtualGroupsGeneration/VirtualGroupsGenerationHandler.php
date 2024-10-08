<?php

declare(strict_types=1);

namespace App\UseCase\Command\VirtualGroupsGeneration;

use App\Bus\BusManager;
use App\Exception\ImportException;
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
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Cache\CacheItemPoolInterface;

class VirtualGroupsGenerationHandler implements CommandHandlerBase
{
    private BusManager $busManager;

    private CacheItemPoolInterface $cache;

    private FeatureToggleService $featureToggleService;

    public function __construct(
        BusManager $busManager,
        CacheItemPoolInterface $cache,
        FeatureToggleService $featureToggleService
    ) {
        $this->busManager = $busManager;
        $this->cache = $cache;
        $this->featureToggleService = $featureToggleService;
    }

    /**
     * @throws ImportException
     */
    public function __invoke(VirtualGroupsGenerationCommand $command): void
    {
        if (!$command->isImportOnlyVirtualGroups) {
            $this->busManager->executeAddressLevelMappingCommand(new AddressLevelMappingCommand());
            $this->busManager->executeAddressMappingCommand(new AddressMappingCommand());
            $this->busManager->executeShopMappingCommand(new ShopMappingCommand());
            $this->busManager->executeAddressToLevelMappingCommand(new AddressToLevelMappingCommand());
            $this->busManager->executeAddressToParentMappingCommand(new AddressToParentMappingCommand());
            $this->busManager->executeAddressToAddressMappingCommand(new AddressToAddressMappingCommand());
            $this->busManager->executeAddressToShopMappingCommand(new AddressToShopMappingCommand());
        }

        if ($this->featureToggleService->isEnabledGenerateVirtualGroupForDistr()) {
            $this->busManager->executeGenerateVirtualGroupCommand(new GenerateVirtualGroupCommand());
        } else {
            $this->busManager->executeVirtualGroupCommand(new VirtualGroupCommand());
        }

        $this->cache->clear();
    }
}
