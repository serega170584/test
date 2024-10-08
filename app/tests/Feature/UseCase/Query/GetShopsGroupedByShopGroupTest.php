<?php

declare(strict_types=1);

namespace Feature\UseCase\Query;


use App\Tests\Data\ConsumerFactory;
use App\Tests\Data\ShopGroupFactory;
use App\Entity\ConsumerEntity;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Entity\ShopGroupEntity;
use App\UseCase\Query\GetShopsGroupedByShopGroupCode\GetShopsGroupedByShopGroupCodeQuery;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;
use App\Bus\BusManager;

class GetShopsGroupedByShopGroupTest extends BaseTestClass
{
    use RefreshDatabase;

    private BusManager $busManager;

    private ShopGroupFactory $shopGroupFactory;

    private ConsumerFactory $consumerFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->busManager = static::$kernel->getContainer()->get('test.busManager');

        $this->consumerFactory = new ConsumerFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            static::$kernel->getContainer()->get('doctrine.orm.entity_manager')
        );

        $this->shopGroupFactory = new ShopGroupFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            static::$kernel->getContainer()->get('doctrine.orm.entity_manager')
        );
    }

    public function testGetShopsByConsumerCode(): void
    {
        $this->createConsumerWithShops();
        $consumer = $this->createConsumerWithShops();
        $expectedData = [];

        foreach ($consumer->getShopGroups() as $item) {
            $expectedData = $this->prepareResponseFromFakeData($item, false, $expectedData);
        }

        $result = $this->busManager->askGetShopsGroupedByShopGroupCodeQuery(
            new GetShopsGroupedByShopGroupCodeQuery($consumer->getCode())
        );

        static::assertNotEmpty($result->items);

        foreach ($result->items as $shopGroupItem) {
            static::assertArrayHasKey($shopGroupItem->shopGroupCode, $expectedData);
            static::assertArraySubset($expectedData[$shopGroupItem->shopGroupCode], $shopGroupItem->shopsCodes);
            unset($expectedData[$shopGroupItem->shopGroupCode]);
        }

        static::assertEmpty($expectedData);
    }

    /**
     * @dataProvider dataProviderTestGetShopsByShopGroupId
     */
    public function testGetShopsByShopGroupCode(bool $recursive): void
    {
        $this->createConsumerWithShops();
        $consumer = $this->createConsumerWithShops();
        $selectedShopGroup = $consumer->getShopGroups()->get(2);
        $expectedData = $this->prepareResponseFromFakeData($selectedShopGroup, $recursive);

        $result = $this->busManager->askGetShopsGroupedByShopGroupCodeQuery(
            new GetShopsGroupedByShopGroupCodeQuery(
                $consumer->getCode(),
                $selectedShopGroup->getCode(),
                $recursive
            )
        );

        static::assertNotEmpty($result->items);

        foreach ($result->items as $shopGroupItem) {
            static::assertArrayHasKey($shopGroupItem->shopGroupCode, $expectedData);
            static::assertArraySubset($expectedData[$shopGroupItem->shopGroupCode], $shopGroupItem->shopsCodes);
            unset($expectedData[$shopGroupItem->shopGroupCode]);
        }

        static::assertEmpty($expectedData);
    }

    public function dataProviderTestGetShopsByShopGroupId(): iterable
    {
        yield 'Информация по одной группе магазинов' => [false];
        yield 'Информация по всем связанным группам магазинов' => [true];
    }

    private function createConsumerWithShops(): ConsumerEntity
    {
        return $this->consumerFactory->createConsumerAndLinkShopGroups(
            $this->shopGroupFactory->createLinkedShopGroups(2, false)
        );
    }

    private function prepareResponseFromFakeData(
        ShopGroupEntity $shopGroupEntity,
        bool $recursive,
        array $previousResult = []
    ): array {
        if (array_key_exists($shopGroupEntity->getCode(), $previousResult)) {
            return $previousResult;
        }

        $shops = $shopGroupEntity->getShops()
            ->map(static function (RelationshipOfStoreGroupsToShopEntity $item) {
                return $item->getUfXmlId();
            })
            ->toArray();
        sort($shops);
        $result = $previousResult;
        $result[$shopGroupEntity->getCode()] = $shops;

        if ($recursive) {
            if ($shopGroupEntity->getParent()) {
                $result = $this->prepareResponseFromFakeData($shopGroupEntity->getParent(), true, $result);
            }

            if (!$shopGroupEntity->getChildren()->isEmpty()) {
                foreach ($shopGroupEntity->getChildren() as $child) {
                    $result = $this->prepareResponseFromFakeData($child, true, $result);
                }
            }
        }

        return $result;
    }
}
