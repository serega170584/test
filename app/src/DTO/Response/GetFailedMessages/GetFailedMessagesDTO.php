<?php

namespace App\DTO\Response\GetFailedMessages;

final readonly class GetFailedMessagesDTO
{
    public function __construct(
        public readonly int $count,
    ) {
    }
}
