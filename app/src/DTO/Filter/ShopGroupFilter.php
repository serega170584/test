<?php

declare(strict_types=1);

namespace App\DTO\Filter;

final class ShopGroupFilter
{
    public ?int $consumerId = null;

    public array $codes = [];

    public array $fiasIds = [];

    public bool $onlyActive = false;
}
