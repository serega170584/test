<?php

declare(strict_types=1);

namespace App\Tests\Feature\Endpoints\Shops\V1;

use App\Tests\Data\ConsumerFactory;
use App\Tests\Data\ShopGroupFactory;
use App\ValueObject\ConsumerCode;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;
use Symfony\Component\Uid\UuidV7;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ShopGroupGetShopGroupByFiasIdEndpointTest extends BaseTestClass
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
    public function testWithoutConsumerVersion(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(1, 2);
        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups($shopGroups);

        $shopGroup = $shopGroups[0];

        $response = $client->request(
            'GET',
            "/api/v1/shop-group/by-fias-id/{$shopGroup->getFiasId()}",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode()],
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertEquals($shopGroup->getCode(), $response['ShopGroupCode'] ?? null);
    }

    public function testDistrConsumerVersion(): void
    {
        $client = static::createClient();

        $fiasId = UuidV7::generate();

        $shopGroup = $this->shopGroupFactory->createShopGroupsWithShops(
            1,
            2,
            null,
            true,
            ['isDistr' => false, 'fiasId' => $fiasId]
        )[0];
        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups(
            [$shopGroup],
            true,
            ['code' => new ConsumerCode('ios')]
        );

        $shopGroupWithDistr = $this->shopGroupFactory->createShopGroupsWithShops(
            1,
            2,
            null,
            true,
            ['isDistr' => true, 'fiasId' => $fiasId]
        )[0];
        $this->consumerFactory->createConsumerAndLinkShopGroups(
            [$shopGroupWithDistr],
            true,
            ['code' => new ConsumerCode('ios_distr')]
        );

        $response = $client->request(
            'GET',
            "/api/v1/shop-group/by-fias-id/{$shopGroup->getFiasId()}",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode(), 'X-App-Version' => '1.0.0'],
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertEquals($shopGroup->getCode(), $response['ShopGroupCode'] ?? null);

        $response = $client->request(
            'GET',
            "/api/v1/shop-group/by-fias-id/{$shopGroupWithDistr->getFiasId()}",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode(), 'X-App-Version' => '2.0.0'],
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertEquals($shopGroupWithDistr->getCode(), $response['ShopGroupCode'] ?? null);
    }

    public function testNotFoundActiveShopGroup(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(1, 2, null, false);
        $shopGroup = $shopGroups[0];
        $shopGroup->setActive(false);
        $this->em->flush();

        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups($shopGroups);

        $client->request(
            'GET',
            "/api/v1/shop-group/by-fias-id/{$shopGroup->getFiasId()}",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode()],
            ]
        );

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testNotFoundConsumer(): void
    {
        $client = static::createClient();

        $fiasId = UuidV7::generate();

        $client->request(
            'GET',
            "/api/v1/shop-group/by-fias-id/{$fiasId}",
            [
                'headers' => ['X-Device-Platform' => 'some-not-existed-consumer'],
            ]
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testNotFoundFiasId(): void
    {
        $client = static::createClient();

        $consumer = $this->consumerFactory->createConsumerAndLinkShopGroups([]);

        $fiasId = UuidV7::generate();

        $client->request(
            'GET',
            "/api/v1/shop-group/by-fias-id/{$fiasId}",
            [
                'headers' => ['X-Device-Platform' => $consumer->getCode()],
            ]
        );

        self::assertResponseStatusCodeSame(404);
    }
}