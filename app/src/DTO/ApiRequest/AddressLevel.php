<?php

declare(strict_types=1);

namespace App\DTO\ApiRequest;

final readonly class AddressLevel
{
    public function __construct(
        public int $id,
        public string $name,
        public string $shortName,
        public int $level,
    ) {
    }
}
