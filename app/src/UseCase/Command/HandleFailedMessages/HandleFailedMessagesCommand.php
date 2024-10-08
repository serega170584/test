<?php

declare(strict_types=1);

namespace App\UseCase\Command\HandleFailedMessages;

use Test\PhpServicesBundle\Bus\CommandBase;

final readonly class HandleFailedMessagesCommand implements CommandBase
{
    public function __construct(
        /** Количество сообщений для обработки */
        public int $limitCount,
        /** Время на обработку сообщений */
        public int $limitTime,
    ) {
    }
}
