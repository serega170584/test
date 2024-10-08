<?php

declare(strict_types=1);

namespace App\ValueObject;

final class ConsumerCode
{
    public function __construct(private string $code)
    {
        $this->code = strtolower($code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function value(): string
    {
        return $this->code;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
