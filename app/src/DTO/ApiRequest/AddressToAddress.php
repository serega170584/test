<?php

namespace App\DTO\ApiRequest;

final readonly class AddressToAddress
{
    public function __construct(
        public int $addressId,
        public int $parentId,
        public int $depth
    ) {
    }
}
