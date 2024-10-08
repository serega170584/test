<?php

declare(strict_types=1);

namespace App\DTO\ApiRequest;

final readonly class Address
{
    public function __construct(
        public int $id,
        public string $name,
        public string $fullName,
        public string $parentFullName,
        public string $firstLevelParent,
        public bool $active,
        public bool $display,
        public ?string $parentId,
        public int $levelId,
        public string $guid,
    ) {
    }
}
