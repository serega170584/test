<?php

declare(strict_types=1);

namespace App\Tests\Feature\Endpoints\Shops\V1;

use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ShopGroupGetFailedMessagesEndpointTest extends BaseTestClass
{
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

        $this->addMessageToFailedTransport();

        $response = $client->request('GET', '/api/v1/messenger/get-failed', ['json' => ['LimitTime' => 1]]);

        self::assertResponseIsSuccessful();
        self::assertJsonEquals(["CountRemaining" => 1], $response->getContent());
    }

    private function addMessageToFailedTransport(): void
    {
        /** @var InMemoryTransport $failedTransport */
        $failedTransport = self::getContainer()->get('messenger.transport.failed');
        $failedTransport->reset();
        $failedTransport->send(Envelope::wrap(new \stdClass()));
    }
}