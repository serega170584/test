<?php

declare(strict_types=1);

namespace App\Repository;

use Symfony\Component\Messenger\Transport\InMemoryTransport;

class InMemoryFailedMessagesRepository implements FailedMessagesRepository
{
    public function __construct(
        private readonly InMemoryTransport $failedTransport
    ) {
    }

    public function getMessagesCount(): int
    {
        return count($this->failedTransport->getSent());
    }
}
