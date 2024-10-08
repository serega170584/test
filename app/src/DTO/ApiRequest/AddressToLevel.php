<?php

namespace App\DTO\ApiRequest;

final readonly class AddressToLevel
{
    public function __construct(
        public int $addressId,
        public int $levelId,
    ) {
    }
}
