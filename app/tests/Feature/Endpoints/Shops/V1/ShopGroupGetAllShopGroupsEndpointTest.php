<?php
declare(strict_types=1);

namespace App\Tests\Feature\Endpoints\Shops\V1;

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

class ShopGroupGetAllShopGroupsEndpointTest extends BaseTestClass
{
    use RefreshDatabase;

    private EntityManagerInterface $em;

    private ConsumerFactory $consumerFactory;

    private ShopGroupFactory $shopGroupFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->shopGroupFactory = new ShopGroupFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            $this->em
        );
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSuccessAll(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(3, 2);

        $response = $client->request(
            'GET',
            "/api/v1/all-shop-groups",
            [
                'query' => [
                    'isShopGroupActive' => true
                ]
            ]
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertCount(count($shopGroups), $response['items']);
    }

    public function testEmptyByActive(): void
    {
        $client = static::createClient();

        $this->shopGroupFactory->createShopGroupsWithShops(3, 2);

        $response = $client->request(
            'GET',
            "/api/v1/all-shop-groups",
        );
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertResponseIsSuccessful();
        self::assertEmpty($response);
    }
}