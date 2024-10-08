<?php

declare(strict_types=1);

namespace App\Repository;

interface FailedMessagesRepository
{
    /**
     * Подсчет количества сообщений в failed-очереди.
     */
    public function getMessagesCount(): int;
}
