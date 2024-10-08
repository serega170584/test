<?php

declare(strict_types=1);

namespace App\Doctrine\Types;

use App\ValueObject\ConsumerCode;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ConsumerCodeType extends StringType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?ConsumerCode
    {
        return null === $value ? null : new ConsumerCode($value);
    }
}
