<?php

declare(strict_types=1);

namespace App\Enum;

enum CacheKeyTemplateEnum: string
{
    case CONSUMER_FOUND = 'consumerFound.%s';

    case CONSUMER_SHOPS_GROUPS = 'consumer.%s.shopsGroups.%s';

    case CONSUMER_ALL_SHOPS_GROUPS = 'consumer.%s.allShopsGroups';

    case CONSUMER_RELATED_SHOPS_GROUPS = 'consumer.%s.related.shopGroup.%s';

    case CONSUMER_LINK_TO_RELATED_SHOPS_GROUPS = 'consumer.%s.link_to_related.shopGroup.%s';

    case GET_SHOPS = 'get_shops.shop_group.%s';

    public function createKey(mixed ...$values): string
    {
        return vsprintf($this->value, func_get_args());
    }
}
