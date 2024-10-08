<?php

namespace App\EventSubscriber;

use App\Bus\BusManager;
use App\Metrics\ShopGroupFailedMessageMetricsCollector;
use App\UseCase\Query\GetFailedMessagesCount\GetFailedMessagesCountQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

final readonly class FailedMessagesMetricSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BusManager $busManager,
        private ShopGroupFailedMessageMetricsCollector $shopGroupFailedMessageMetric
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageHandledEvent::class => 'onMessageHandledEvent',
            WorkerMessageFailedEvent::class => ['onMessageFailedEvent', -200],
        ];
    }

    /**
     * @throws \Exception
     */
    public function onMessageHandledEvent(WorkerMessageHandledEvent $event): void
    {
        if (!$this->isFailedMessageProcessed($event->getReceiverName())) {
            return;
        }

        $this->calculateMetric();
    }

    /**
     * @throws \Exception
     */
    public function onMessageFailedEvent(WorkerMessageFailedEvent $event): void
    {
        $this->calculateMetric();
    }

    /**
     * @throws \Exception
     */
    private function calculateMetric(): void
    {
        $dto = $this->busManager->askGetFailedMessagesCountQuery(new GetFailedMessagesCountQuery());

        $this->shopGroupFailedMessageMetric->setCounter($dto->count);
    }

    private function isFailedMessageProcessed(string $receiverName): bool
    {
        return 'failed' === $receiverName;
    }
}
