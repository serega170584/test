<?php

declare(strict_types=1);

namespace App\Metrics;

use Test\PhpServicesBundle\Metrics\MetricsManager;

final readonly class ShopGroupFailedMessageMetricsCollector
{
    public function __construct(private MetricsManager $metricsManager)
    {
    }

    public function setCounter(float $value): self
    {
        $this->metricsManager->getGauge(
            'shop_group_failed_message_counter',
            'shop group failed message counter'
        )->set($value);

        return $this;
    }
}
