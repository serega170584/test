<?php

declare(strict_types=1);

namespace App\Enum;

enum PrefixEnum: string
{
    case SHOP_GROUP_PREFIX = 'shop_group_location_';

    public function appendPrefixIfNotExist(string $code): string
    {
        if (str_starts_with($code, $this->value)) {
            return $code;
        }

        return $this->appendPrefix($code);
    }

    public function appendPrefix(string $code): string
    {
        return $this->value.$code;
    }
}
