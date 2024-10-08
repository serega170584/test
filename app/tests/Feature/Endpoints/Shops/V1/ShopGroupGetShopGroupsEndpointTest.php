<?php

declare(strict_types=1);

namespace App\Tests\Feature\Endpoints\Shops\V1;

use App\Entity\ShopGroupEntity;
use App\Tests\Data\ConsumerFactory;
use App\Tests\Data\ShopGroupFactory;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ShopGroupGetShopGroupsEndpointTest extends BaseTestClass
{
    use RefreshDatabase;

    private EntityManagerInterface $em;

    private ConsumerFactory $consumerFactory;

    private ShopGroupFactory $shopGroupFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->consumerFactory = new ConsumerFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            $this->em
        );

        $this->shopGroupFactory = new ShopGroupFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            $this->em
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws JsonException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @return void
     */
    public function testSuccessAll(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(3, 2);
        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups($shopGroups);

        $response = $client->request(
            'GET',
            "/api/v1/shop-groups",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode()],
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertCount(count($shopGroups), $response['items']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function testSuccessByCodes(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(3, 2);
        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups($shopGroups);

        $slice = array_slice($shopGroups, 0, 2);
        $filterShopGroupCodes = array_map(static fn(ShopGroupEntity $e) => $e->getCode(), $slice);

        $query = http_build_query([
            'shopGroupCodes' => $filterShopGroupCodes,
        ]);

        $response = $client->request(
            'GET',
            "/api/v1/shop-groups?{$query}",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode()],
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertCount(count($slice), $response['items']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function testSuccessByFiasIds(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(4, 2);
        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups($shopGroups);

        $slice = array_slice($shopGroups, 0, 3);
        $filterFiasIds = array_map(static fn(ShopGroupEntity $e) => $e->getFiasId(), $slice);

        $query = http_build_query([
            'fiasIds' => $filterFiasIds,
        ]);

        $response = $client->request(
            'GET',
            "/api/v1/shop-groups?{$query}",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode()],
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertCount(count($slice), $response['items']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function testSuccessEmpty(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(4, 2);
        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups($shopGroups);

        $response = $client->request(
            'GET',
            "/api/v1/shop-groups?shopGroupCodes[]=some-not-existed-group",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode()],
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertEmpty($response['items'] ?? []);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testNotFoundConsumer(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            "/api/v1/shop-groups",
            [
                'headers' => ['X-Device-Platform' => 'some-not-existed-consumer'],
            ]
        );

        self::assertResponseStatusCodeSame(404);
    }
}