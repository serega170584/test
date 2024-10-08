<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportConsumers;

use Test\PhpServicesBundle\Bus\CommandBase;

final readonly class ImportConsumersCommand implements CommandBase
{
    /**
     * @param ImportConsumerItem[] $items
     */
    public function __construct(public array $items)
    {
    }
}
