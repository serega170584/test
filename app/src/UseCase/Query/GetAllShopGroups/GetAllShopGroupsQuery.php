<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetAllShopGroups;

use Test\PhpServicesBundle\Bus\QueryBase;

final readonly class GetAllShopGroupsQuery implements QueryBase
{
    public function __construct(
        public bool $isShopGroupActive,
        public ?string $lastSgCode,
        public ?int $limit
    ) {
    }
}
