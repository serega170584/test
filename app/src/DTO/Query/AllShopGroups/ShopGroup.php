<?php

declare(strict_types=1);

namespace App\DTO\Query\AllShopGroups;

class ShopGroup
{
    public function __construct(
        public string $code,
        public bool $isActive,
        public bool $isDistr,
        public array $shopCodes
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isDistr(): bool
    {
        return $this->isDistr;
    }

    /**
     * @return array<string>
     */
    public function getShopCodes(): array
    {
        return $this->shopCodes;
    }
}
