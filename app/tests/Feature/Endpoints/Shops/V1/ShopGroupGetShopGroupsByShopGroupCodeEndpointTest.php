<?php
declare(strict_types=1);

namespace App\Tests\Feature\Endpoints\Shops\V1;

use App\Tests\Data\ShopGroupFactory;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

class ShopGroupGetShopGroupsByShopGroupCodeEndpointTest extends BaseTestClass
{
    use RefreshDatabase;

    private ShopGroupFactory $shopGroupFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->shopGroupFactory = new ShopGroupFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            $em
        );
    }

    public function testGetShopGroupsByShopGroupCode(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(1, 1);

        $client->request(
            'GET',
            "/api/v1/shop-group-by-shop-group-code",
            [
                'query' => [
                    'shopGroupCode' => $shopGroups[0]->getCode()
                ]
            ]
        );

        self::assertResponseIsSuccessful();
    }

    public function testGetShopGroupsByShopGroupCode404(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            "/api/v1/shop-group-by-shop-group-code",
            [
                'query' => [
                    'shopGroupCode' => 123
                ]
            ]
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}