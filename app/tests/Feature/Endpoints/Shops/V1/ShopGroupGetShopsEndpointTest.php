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
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ShopGroupGetShopsEndpointTest extends BaseTestClass
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
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testSuccess(): void
    {
        $client = static::createClient();

        $shopGroups = $this->shopGroupFactory->createShopGroupsWithShops(2, 2);

        foreach ($shopGroups as $shopGroup) {
            $response = $this->sendRequest($client, $shopGroup);

            self::assertResponseIsSuccessful();

            $shopsByCode = [];
            foreach ($shopGroup->getShops() as $shop) {
                $shopsByCode[$shop->getUfXmlId()] = $shop;
            }
            foreach ($response['items'] as $item) {
                self::assertArrayHasKey($item['code'], $shopsByCode);
            }
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testInvalid(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/shops');

        self::assertResponseIsUnprocessable();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function testInactiveShopGroup(): void
    {
        $client = static::createClient();
        $shopGroup = $this->shopGroupFactory->createShopGroupsWithShops(
            1,
            2,
            null,
            true,
            ['active' => false]
        )[0];

        $response = $this->sendRequest($client, $shopGroup);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $response);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function sendRequest(HttpClientInterface $client, ShopGroupEntity $shopGroup): array
    {
        $response = $client->request(
            'GET',
            '/api/v1/shops',
            [
                'query' => [
                    'shopGroupCode' => $shopGroup->getCode(),
                ],
            ]
        );

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}