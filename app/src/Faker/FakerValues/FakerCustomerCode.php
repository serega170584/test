<?php

declare(strict_types=1);

namespace App\Faker\FakerValues;

use App\ValueObject\ConsumerCode;
use Test\PhpServicesBundle\Faker\AbstractFactoryValue;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FakerCustomerCode extends AbstractFactoryValue
{
    public function __construct(private int $maxSymbols)
    {
    }

    public function generate(array $modifierBag): ConsumerCode
    {
        return new ConsumerCode(substr($this->modifyFaker($modifierBag)->slug(20), 0, $this->maxSymbols));
    }
}
