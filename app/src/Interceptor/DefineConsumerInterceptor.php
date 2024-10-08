<?php

namespace App\Interceptor;

use Google\Protobuf\Internal\Message;
use Test\PhpServicesBundle\PreInterceptorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[\Attribute(\Attribute::TARGET_METHOD)]
class DefineConsumerInterceptor implements PreInterceptorInterface
{
    public function intercept(array &$ctx, Message &$in): void
    {
        if (!method_exists($in, 'getConsumerCode') || !method_exists($in, 'setConsumerCode')) {
            throw new \RuntimeException('Message '.get_class($in).' without ConsumerCode');
        }
        if (!method_exists($in, 'getConsumerVersion') || !method_exists($in, 'setConsumerVersion')) {
            throw new \RuntimeException('Message '.get_class($in).' without ConsumerVersion');
        }

        $consumerCode = $ctx['x-device-platform'][0] ?? $ctx['test-app-os'][0] ?? null;
        if (!$consumerCode) {
            $consumerCode = $in->getConsumerCode();
        }

        if (!$consumerCode) {
            throw new BadRequestHttpException('Consumer code not specified');
        }

        $in->setConsumerCode(strtolower($consumerCode));

        $consumerVersion = $ctx['x-app-version'][0] ?? null;
        if ($consumerVersion) {
            $in->setConsumerVersion(strtolower($consumerVersion));
        }
    }
}
