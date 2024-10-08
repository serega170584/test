<?php

namespace App\UseCase\Query\EchoQueries;

use Test\PhpServicesBundle\Bus\QueryHandlerBase;

class EchoQueryHandler implements QueryHandlerBase
{
    public function __invoke(EchoQuery $query): string
    {
        return 'Echoed!!!!!  :::  '.$query->message;
    }
}
