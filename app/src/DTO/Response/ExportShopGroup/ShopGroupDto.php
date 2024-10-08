<?php

declare(strict_types=1);

namespace App\DTO\Response\ExportShopGroup;

class ShopGroupDto
{
    public function __construct(
        public string $code,
        public string $title,
        public ?string $description,
        public ?string $parent_code,
        public bool $active,
        public ?string $consumers,
        public ?string $fias_id,
    ) {
    }

    public function getConsumers(): ?string
    {
        return $this->consumers;
    }

    public function setConsumers(?string $consumers): void
    {
        if (empty($this->consumers)) {
            $this->consumers = $consumers;
        } else {
            // для csv-формата клеим строки
            $this->consumers .= ','.$consumers;
        }
    }

    public function __toString(): string
    {
        return sprintf(
            '"%s";"%s";"%s";"%s";%s;"%s";"%s"',
            $this->code,
            $this->title,
            $this->description,
            $this->parent_code,
            $this->active,
            $this->consumers,
            $this->fias_id,
        );
    }
}
