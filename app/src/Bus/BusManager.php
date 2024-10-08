<?php

/**
 * This file is generated by architect.
 */

namespace App\Bus;

class BusManager implements \Test\PhpServicesBundle\Bus\BusManagerInterface
{
	private \Test\PhpServicesBundle\Bus\MessengerBus $bus;


	public function __construct(\Test\PhpServicesBundle\Bus\MessengerBus $bus)
	{
		$this->bus = $bus;
	}


	public function askGetShopGroupByShopGroupCodeQuery(
		\App\UseCase\Query\GetShopGroupsByShopGroupCode\GetShopGroupByShopGroupCodeQuery $dto,
	): iterable
	{
		return $this->bus->ask($dto);
	}


	public function askGetShopsGroupedByShopGroupCodeQuery(
		\App\UseCase\Query\GetShopsGroupedByShopGroupCode\GetShopsGroupedByShopGroupCodeQuery $dto,
	): \App\DTO\Response\ShopsByShopGroupResponse
	{
		return $this->bus->ask($dto);
	}


	public function askExportShopGroupQuery(
		\App\UseCase\Query\Export\ExportShopGroupQuery $dto,
	): \App\DTO\Response\ExportShopGroup\ExportShopGroupDto
	{
		return $this->bus->ask($dto);
	}


	public function askGetShopGroupByShopCodeQuery(
		\App\UseCase\Query\GetShopGroupByShopCode\GetShopGroupByShopCodeQuery $dto,
	): \App\Entity\ShopGroupEntity
	{
		return $this->bus->ask($dto);
	}


	public function askEchoQuery(\App\UseCase\Query\EchoQueries\EchoQuery $dto): string
	{
		return $this->bus->ask($dto);
	}


	public function askGetActiveShopGroupByFiasIdQuery(
		\App\UseCase\Query\GetActiveShopGroupByFiasId\GetActiveShopGroupByFiasIdQuery $dto,
	): \App\Entity\ShopGroupEntity
	{
		return $this->bus->ask($dto);
	}


	public function askGetShopGroupsQuery(\App\UseCase\Query\GetShopGroups\GetShopGroupsQuery $dto): iterable
	{
		return $this->bus->ask($dto);
	}


	public function askGetShopsQuery(\App\UseCase\Query\GetShops\GetShopsQuery $dto): iterable
	{
		return $this->bus->ask($dto);
	}


	public function askGetAllShopGroupsQuery(\App\UseCase\Query\GetAllShopGroups\GetAllShopGroupsQuery $dto): iterable
	{
		return $this->bus->ask($dto);
	}


	public function askGetFailedMessagesCountQuery(
		\App\UseCase\Query\GetFailedMessagesCount\GetFailedMessagesCountQuery $dto,
	): \App\DTO\Response\GetFailedMessages\GetFailedMessagesDTO
	{
		return $this->bus->ask($dto);
	}


	public function executeImportShopGroupsCommand(
		\App\UseCase\Command\ImportShopGroups\ImportShopGroupsCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeVirtualGroupsGenerationCommand(
		\App\UseCase\Command\VirtualGroupsGeneration\VirtualGroupsGenerationCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeImportRelationshipOfStoreGroupsToShopCommand(
		\App\UseCase\Command\ImportRelationshipOfStoreGroupsToShop\ImportRelationshipOfStoreGroupsToShopCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeShopMappingCommand(\App\UseCase\Command\URLMapping\ShopMappingCommand $dto): void
	{
		$this->bus->execute($dto);
	}


	public function executeAddressToAddressMappingCommand(
		\App\UseCase\Command\URLMapping\AddressToAddressMappingCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeGenerateVirtualGroupCommand(
		\App\UseCase\Command\URLMapping\GenerateVirtualGroupCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeAddressToShopMappingCommand(
		\App\UseCase\Command\URLMapping\AddressToShopMappingCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeAddressLevelMappingCommand(
		\App\UseCase\Command\URLMapping\AddressLevelMappingCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeAddressToParentMappingCommand(
		\App\UseCase\Command\URLMapping\AddressToParentMappingCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeAddressMappingCommand(\App\UseCase\Command\URLMapping\AddressMappingCommand $dto): void
	{
		$this->bus->execute($dto);
	}


	public function executeVirtualGroupCommand(\App\UseCase\Command\URLMapping\VirtualGroupCommand $dto): void
	{
		$this->bus->execute($dto);
	}


	public function executeAddressToLevelMappingCommand(
		\App\UseCase\Command\URLMapping\AddressToLevelMappingCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeHandleFailedMessagesCommand(
		\App\UseCase\Command\HandleFailedMessages\HandleFailedMessagesCommand $dto,
	): void
	{
		$this->bus->execute($dto);
	}


	public function executeImportConsumersCommand(\App\UseCase\Command\ImportConsumers\ImportConsumersCommand $dto): void
	{
		$this->bus->execute($dto);
	}


	public function executeImportCommand(\App\UseCase\Command\Import\ImportCommand $dto): void
	{
		$this->bus->execute($dto);
	}
}
