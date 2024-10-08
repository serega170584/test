<?php

namespace App\Faker\FakerValues;

use Test\PhpServicesBundle\Faker\AbstractFactoryValue;
use Symfony\Component\Uid\UuidV7;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FakerGuid extends AbstractFactoryValue
{
    public function generate(array $modifierBag): string
    {
        return UuidV7::generate();
    }
}
