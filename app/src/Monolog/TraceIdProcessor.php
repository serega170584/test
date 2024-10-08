<?php

declare(strict_types=1);

namespace App\Monolog;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsMonologProcessor]
final class TraceIdProcessor
{
    private const HEADER_NAME = 'trace_id';

    private ?string $uid = null;

    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->requestStack->getMainRequest();

        if ($request && $requestId = $request->headers->get(self::HEADER_NAME, null)) {
            $record->extra['trace_id'] = $requestId;
        } else {
            $record->extra['trace_id'] = $this->getUid();
        }

        return $record;
    }

    private function getUid(): string
    {
        if (null === $this->uid) {
            $this->uid = hash('md5', uniqid('', true));
        }

        return $this->uid;
    }
}
