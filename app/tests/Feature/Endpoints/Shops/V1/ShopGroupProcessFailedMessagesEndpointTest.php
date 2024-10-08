<?php

declare(strict_types=1);

namespace App\Tests\Feature\Endpoints\Shops\V1;

use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ShopGroupProcessFailedMessagesEndpointTest extends BaseTestClass
{
    use RefreshDatabase;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/messenger/process-failed', ['json' => ['LimitTime' => 1]]);

        self::assertResponseIsSuccessful();
    }
}