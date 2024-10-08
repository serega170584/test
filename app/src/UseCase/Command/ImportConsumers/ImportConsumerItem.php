<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportConsumers;

use App\ValueObject\ConsumerCode;

final readonly class ImportConsumerItem
{
    public function __construct(
        public ConsumerCode $code,
        public string $title,
        public string $description
    ) {
    }
}
