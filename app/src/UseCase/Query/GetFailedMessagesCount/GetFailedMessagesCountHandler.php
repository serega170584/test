<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetFailedMessagesCount;

use App\DTO\Response\GetFailedMessages\GetFailedMessagesDTO;
use App\Repository\FailedMessagesRepository;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;

final class GetFailedMessagesCountHandler implements QueryHandlerBase
{
    public function __construct(
        private readonly FailedMessagesRepository $failedMessagesRepository
    ) {
    }

    public function __invoke(GetFailedMessagesCountQuery $query): GetFailedMessagesDTO
    {
        return new GetFailedMessagesDTO($this->getCountMessages());
    }

    /**
     * Возвращает количество сообщений в транспорте failed.
     */
    private function getCountMessages(): int
    {
        return $this->failedMessagesRepository->getMessagesCount();
    }
}
