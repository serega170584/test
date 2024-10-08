<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportShopGroups;

use App\ValueObject\ConsumerCode;

final readonly class ImportShopGroupItem
{
    /**
     * @var array<int, ConsumerCode>
     */
    public array $consumers;

    public function __construct(
        public string $code,
        public string $title,
        public string $description,
        public string $parentCode,
        public bool $active,
        array $consumers,
    ) {
        $this->consumers = $consumers;
    }
}
