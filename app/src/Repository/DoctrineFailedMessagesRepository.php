<?php

declare(strict_types=1);

namespace App\Repository;

use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class DoctrineFailedMessagesRepository implements FailedMessagesRepository
{
    public function __construct(
        private readonly TransportInterface $failedTransport
    ) {
    }

    public function getMessagesCount(): int
    {
        if (!$this->failedTransport instanceof MessageCountAwareInterface) {
            throw new \RuntimeException(sprintf('%s does not implement MessageCountAwareInterface', get_class($this->failedTransport)));
        }

        return $this->failedTransport->getMessageCount();
    }
}
