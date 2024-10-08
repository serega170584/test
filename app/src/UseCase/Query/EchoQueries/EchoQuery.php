<?php

namespace App\UseCase\Query\EchoQueries;

use Test\PhpServicesBundle\Bus\QueryBase;

class EchoQuery implements QueryBase
{
    public function __construct(public readonly string $message)
    {
    }
}
