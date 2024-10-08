<?php

declare(strict_types=1);

namespace App\UseCase\Command\Import;

final readonly class ImportShopGroupItem
{
    public function __construct(
        public string $code,
        public string $title,
        public string $description,
        public string $parentCode,
        public bool $active,
        public ?string $fiasId,
        public array $consumers,
    ) {
    }
}
