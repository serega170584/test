<?php

declare(strict_types=1);

namespace App\DTO\Response\ExportShopGroup;

class ShopGroupToShopDto
{
    public function __construct(
        public string $uf_xml_id,
        public string $shop_group_code
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '"%s";"%s"',
            $this->uf_xml_id,
            $this->shop_group_code,
        );
    }
}
