<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\ConsumerEnum;
use App\ValueObject\ConsumerCode;
use Psr\Log\LoggerInterface;

// TODO Подумать над другим решением
readonly class ConsumerResolver
{
    public function __construct(
        private string $minDistrIosVersion,
        private string $minDistrAndroidVersion,
        private LoggerInterface $logger,
    ) {
    }

    public function resolveConsumerCodeByConsumerVersion(ConsumerCode $consumerCode, ?string $consumerVersion): ConsumerCode
    {
        $this->logger->info('[Consumer Resolver]', [
            'consumer_version' => $consumerVersion,
            'consumer_code' => $consumerCode->value(),
        ]);

        if (!$consumerVersion || !in_array($consumerCode->value(), [ConsumerEnum::ANDROID->value, ConsumerEnum::IOS->value], true)) {
            return $consumerCode;
        }

        $minDistrVersion = $this->minDistrAndroidVersion;
        if (ConsumerEnum::IOS->value === $consumerCode->value()) {
            $minDistrVersion = $this->minDistrIosVersion;
        }

        $this->logger->info('[Consumer Resolver]', [
            'min_distr_version' => $minDistrVersion,
            'is_new_version' => version_compare($consumerVersion, $minDistrVersion, '>=') ? 'yes' : 'no',
        ]);

        if (version_compare($consumerVersion, $minDistrVersion, '>=')) {
            return new ConsumerCode($consumerCode->value().'_distr');
        }

        return $consumerCode;
    }
}
