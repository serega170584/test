<?php

declare(strict_types=1);

namespace App\DTO\Query\ExportShopGroup;

class ConsumerDto
{
    public function __construct(
        public string $code,
        public string $title,
        public ?string $description
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '"%s";"%s";"%s"',
            $this->code,
            $this->title,
            $this->description
        );
    }
}
