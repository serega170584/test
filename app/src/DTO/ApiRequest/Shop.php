<?php

declare(strict_types=1);

namespace App\DTO\ApiRequest;

final readonly class Shop
{
    public function __construct(
        public int $id,
        public string $xmlId,
    ) {
    }
}
