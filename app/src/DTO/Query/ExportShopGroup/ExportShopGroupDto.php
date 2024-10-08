<?php

declare(strict_types=1);

namespace App\DTO\Query\ExportShopGroup;

class ExportShopGroupDto
{
    /** @var ShopGroupDto[] */
    public array $shopGroups;

    /** @var ConsumerDto[] */
    public array $consumers = [];

    /** @var ShopGroupToShopDto[] */
    public array $shopGroupToShop = [];

    public function getShopGroups(): array
    {
        return $this->shopGroups;
    }

    public function setShopGroups(array $shopGroups): void
    {
        $this->shopGroups = $shopGroups;
    }

    public function getConsumers(): array
    {
        return $this->consumers;
    }

    public function setConsumers(array $consumers): void
    {
        $this->consumers = $consumers;
    }

    public function getShopGroupToShop(): array
    {
        return $this->shopGroupToShop;
    }

    public function setShopGroupToShop(array $shopGroupToShop): void
    {
        $this->shopGroupToShop = $shopGroupToShop;
    }
}
