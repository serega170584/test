<?php

declare(strict_types=1);

namespace App\Tests\Feature\Console;

use App\Entity\AddressEntity;
use App\Entity\AddressLevelEntity;
use App\Entity\AddressToAddressEntity;
use App\Entity\ConsumerEntity;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Entity\ShopEntity;
use App\Entity\ShopGroupEntity;
use App\Enum\ConsumerEnum;
use App\Repository\ConsumerRepository;
use App\Repository\ShopGroupRepository;
use App\Service\FeatureToggleService;
use App\ValueObject\ConsumerCode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use JsonException;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class URLVirtualGroupsImportCommandTest extends BaseTestClass
{
    use RefreshDatabase;

    /**
     * Тестирование создания ВГ без дистров
     *
     * @throws JsonException
     * @throws Exception
     */
    public function testURLImportShopGroups(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $this->initFixtures();
        $em->clear();
        $this->initURLClientMock();

        $featureToggleService = $this->initFeatureManagerMock();
        $featureToggleService
            ->method('isEnabledGenerateVirtualGroupForDistr')
            ->willReturn(false);

        $application = new Application(self::$kernel);

        $command = $application->find('app:url-virtual-groups-import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $em->clear();

        $commandTester->assertCommandIsSuccessful();

        /** @var ConsumerRepository $consumerRepo */
        $consumerRepo = $em->getRepository(ConsumerEntity::class);
        $existedConsumer = $consumerRepo->findBy(['code' => 'existed_consumer']);
        self::assertNotEmpty($existedConsumer);

        $shopGroupRepo = $em->getRepository(ShopGroupEntity::class);
        $shopGroup = $shopGroupRepo->findBy(['code' => 'shop_group_location_test_old']);
        self::assertNotEmpty($shopGroup);

        self::assertFalse($shopGroup[0]?->getConsumers()->isEmpty());

        $shopGroupRelationsRepo = $em->getRepository(RelationshipOfStoreGroupsToShopEntity::class);
        $shopGroupRelations = $shopGroupRelationsRepo->findBy(['ufXmlId' => '901185']);
        self::assertCount(1, $shopGroupRelations);

        $addressRepo = $em->getRepository(AddressEntity::class);
        $address = $addressRepo->findBy(['externalId' => 1]);
        self::assertNotEmpty($address);

        $level = $address[0]?->getLevel();
        self::assertNotEmpty($level);
        self::assertEquals(7, $level?->getExternalId());

        $parent = $address[0]?->getParent();
        self::assertNotEmpty($parent);
        self::assertEquals(2, $parent?->getExternalId());

        self::assertFalse(
            $parent?->getParentAddressToAddressEntities()
                   ->filter(fn(AddressToAddressEntity $listAddress) => $listAddress->getAddress()->getExternalId() === 1)
                   ->isEmpty()
        );

        $addressLevelRepo = $em->getRepository(AddressLevelEntity::class);
        $addressLevel = $addressLevelRepo->findBy(['externalId' => 4]);
        self::assertNotEmpty($addressLevel);

        $shopRepo = $em->getRepository(ShopEntity::class);
        $shop = $shopRepo->findBy(['externalId' => 1]);
        self::assertNotEmpty($shop);
        self::assertFalse(
            $shop[0]?->getAddresses()
                    ->filter(fn(AddressEntity $listAddress) => $listAddress->getExternalId() === $address[0]?->getExternalId())
                    ->isEmpty()
        );

        $shopGroupRepo = $em->getRepository(ShopGroupEntity::class);
        $shopGroup = $shopGroupRepo->findBy(['code' => 'shop_group_location_1', 'fiasId' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5']);
        self::assertNotEmpty($shopGroup);
        self::assertEquals(
            count($consumerRepo->findAll()),
            $shopGroup[0]?->getConsumers()->count()
        );

        $groupShopRelationShipsRepo = $em->getRepository(RelationshipOfStoreGroupsToShopEntity::class);
        $groupShopRelationShips = $groupShopRelationShipsRepo->findBy(['shopGroup' => $shopGroup, 'ufXmlId' => '901185']);
        self::assertNotEmpty($groupShopRelationShips);
    }

    /**
     * Тестирование создания ВГ с дистрами
     *
     * @throws JsonException
     * @throws Exception
     */
    public function testURLImportShopGroupsForDistributor(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $this->initFixtures();
        $this->initDistrFixtures();
        $em->clear();
        $this->initURLClientMock();

        $featureToggleService = $this->initFeatureManagerMock();
        $featureToggleService
            ->method('isEnabledGenerateVirtualGroupForDistr')
            ->willReturn(true);


        $application = new Application(self::$kernel);

        $command = $application->find('app:url-virtual-groups-import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $em->clear();

        $commandTester->assertCommandIsSuccessful();

        /** @var ConsumerRepository $consumerRepo */
        $consumerRepo = $em->getRepository(ConsumerEntity::class);
        $distrConsumer = $consumerRepo->findBy(['code' => ConsumerEnum::DISTRIBUTOR->value]);
        self::assertNotEmpty($distrConsumer);

        self::assertEquals(
            count($consumerRepo->findAll()),
            3
        );

        $shopGroupRepo = $em->getRepository(ShopGroupEntity::class);
        $oldShopGroup = $shopGroupRepo->findBy(['code' => 'shop_group_location_test_old']);
        self::assertNotEmpty($oldShopGroup);

        self::assertFalse($oldShopGroup[0]?->getConsumers()->isEmpty());

        $addressRepo = $em->getRepository(AddressEntity::class);
        $address = $addressRepo->findBy(['externalId' => 1]);
        self::assertNotEmpty($address);

        $level = $address[0]?->getLevel();
        self::assertNotEmpty($level);
        self::assertEquals(7, $level?->getExternalId());

        $parent = $address[0]?->getParent();
        self::assertNotEmpty($parent);
        self::assertEquals(2, $parent?->getExternalId());

        self::assertFalse(
            $parent?->getParentAddressToAddressEntities()
                ->filter(fn(AddressToAddressEntity $listAddress) => $listAddress->getAddress()->getExternalId() === 1)
                ->isEmpty()
        );

        $addressLevelRepo = $em->getRepository(AddressLevelEntity::class);
        $addressLevel = $addressLevelRepo->findBy(['externalId' => 4]);
        self::assertNotEmpty($addressLevel);

        $shopRepo = $em->getRepository(ShopEntity::class);
        $shop = $shopRepo->findBy(['externalId' => 1]);
        self::assertNotEmpty($shop);
        self::assertFalse(
            $shop[0]?->getAddresses()
                ->filter(fn(AddressEntity $listAddress) => $listAddress->getExternalId() === $address[0]?->getExternalId())
                ->isEmpty()
        );

        $shopGroupRepo = $em->getRepository(ShopGroupEntity::class);
        $shopGroup = $shopGroupRepo->findBy(['code' => 'shop_group_location_1', 'fiasId' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5']);
        self::assertNotEmpty($shopGroup);

        $groupShopRelationShipsRepo = $em->getRepository(RelationshipOfStoreGroupsToShopEntity::class);
        $groupShopRelationShips = $groupShopRelationShipsRepo->findBy(['shopGroup' => $shopGroup, 'ufXmlId' => '901185']);
        self::assertNotEmpty($groupShopRelationShips);

        $shopDistrGroup = $shopGroupRepo->findBy(['code' => 'shop_group_location_1_distr']);
        self::assertNotEmpty($shopDistrGroup);

        $groupShopDistrRelationShips = $groupShopRelationShipsRepo->findBy(['shopGroup' => $shopDistrGroup, 'ufXmlId' => '901185']);
        self::assertNotEmpty($groupShopDistrRelationShips);

        /** @var ShopGroupEntity $notUsedShopGroup */
        $notUsedShopGroup = $shopGroupRepo->findBy(['code' => 'shop_group_location_2_distr']);
        self::assertNotEmpty($notUsedShopGroup);

        self::assertEmpty($notUsedShopGroup[0]->getConsumers());

        $notUsedShopGroupToShopRelations = $groupShopRelationShipsRepo->findBy(['shopGroup' => $notUsedShopGroup]);
        self::assertEmpty($notUsedShopGroupToShopRelations);

    }

    /**
     * @throws Exception
     */
    private function initFixtures(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        /** @var ConsumerRepository $consumerRepo */
        $consumerRepo = $em->getRepository(ConsumerEntity::class);

        $consumer = new ConsumerEntity();
        $consumer->setCode(new ConsumerCode(ConsumerEnum::PHARMA_CLIENTS->value));
        $consumer->setName('Существующий консьюмер');
        $consumer->setDescription('Существующий консьюмер для теста');
        $consumerRepo->save($consumer);

        $consumerExist = new ConsumerEntity();
        $consumerExist->setCode(new ConsumerCode('existed_consumer'));
        $consumerExist->setName('Существующий консьюмер');
        $consumerExist->setDescription('Существующий консьюмер для теста');
        $consumerRepo->save($consumerExist);

        $em->flush();

        /** @var ShopGroupRepository $shopGroupRepo */
        $shopGroupRepo = $em->getRepository(ShopGroupEntity::class);
        $shopGroups = $shopGroupRepo->findBy(['code' => 'shop_group_location_test_old']);
        $shopGroup = $shopGroups[0] ?? null;

        if (null === $shopGroup) {
            $shopGroup = new ShopGroupEntity();
            $shopGroup->setCode('shop_group_location_test_old');
            $shopGroup->setTitle('Тест');
            $shopGroup->addConsumer($consumer);
            $shopGroupRepo->save($shopGroup);
            $em->flush();
        }

        $shopGroupRelationsRepo = $em->getRepository(RelationshipOfStoreGroupsToShopEntity::class);
        $shopGroupRelations = $shopGroupRelationsRepo->findBy(['shopGroup' => $shopGroup, 'ufXmlId' => '901185']);
        $shopGroupRelation = $shopGroupRelations[0] ?? null;
        if (null === $shopGroupRelation) {
            $shopGroupRelation = new RelationshipOfStoreGroupsToShopEntity();
            $shopGroupRelation->setShopGroup($shopGroup);
            $shopGroupRelation->setUfXmlId('901185');
            $shopGroupRelationsRepo->save($shopGroupRelation);
            $em->flush();
        }
    }

    private function  initDistrFixtures()
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        /** @var ConsumerRepository $consumerRepo */
        $consumerRepo = $em->getRepository(ConsumerEntity::class);

        $consumerDistr = new ConsumerEntity();
        $consumerDistr->setCode(new ConsumerCode(ConsumerEnum::DISTRIBUTOR->value));
        $consumerDistr->setName('Дистрибьютор для теста');
        $consumerDistr->setDescription('Дистрибьютор для теста');
        $consumerRepo->save($consumerDistr);

        /** @var ShopGroupRepository $shopGroupRepo */
        $shopGroupRepo = $em->getRepository(ShopGroupEntity::class);
        $shopGroups = $shopGroupRepo->findBy(['code' => 'shop_group_location_2_distr']);
        $shopGroup = $shopGroups[0] ?? null;

        if (null === $shopGroup) {
            $shopGroup = new ShopGroupEntity();
            $shopGroup->setCode('shop_group_location_2_distr');
            $shopGroup->setTitle('Тест');
            $shopGroup->addConsumer($consumerDistr);
            $shopGroupRepo->save($shopGroup);
        }

        $em->flush();
    }

    /**
     * @throws JsonException
     */
    private function initURLClientMock(): void
    {
        $client = $this->createMock(Client::class);
        $client
            ->method('request')
            ->willReturnMap([
                ['GET', 'addresses/levels_count', [], $this->createResponseAddressesLevelsCountMock()],
                [
                    'GET',
                    'addresses/all_address_levels',
                    [
                        'query' => [
                            'offset' => 0,
                            'limit' => 100,
                        ],
                    ],
                    $this->createResponseAllAddressLevelsMock(),
                ],
                ['GET', 'addresses/count', [], $this->createResponseAddressCountMock(),],
                [
                    'GET',
                    'addresses/all',
                    [
                        'query' => [
                            'offset' => 0,
                            'limit' => 100,
                        ],
                    ],
                    $this->createResponseAddressAllMock(),
                ],
                ['GET', 'catalog/all_stores_count', [], $this->createResponseAllStoresCountMock()],
                [
                    'GET',
                    'catalog/all_stores',
                    [
                        'query' => [
                            'offset' => 0,
                            'limit' => 100,
                        ],
                    ],
                    $this->createResponseAllStoresMock(),
                ],
                ['GET', 'addresses/addresses_link_count', [], $this->createResponseAddressesLinkCountMock()],
                [
                    'GET',
                    'addresses/all_addresses_link',
                    [
                        'query' => [
                            'offset' => 0,
                            'limit' => 100,
                        ],
                    ],
                    $this->createResponseAllAddressesLinkMock(),
                ],
                ['GET', 'catalog/all_stores_link_count', [], $this->createResponseAllStoresLinkCountMock()],
                [
                    'GET',
                    'catalog/all_stores_link',
                    [
                        'query' => [
                            'offset' => 0,
                            'limit' => 100,
                        ],
                    ],
                    $this->createResponseAllStoresLinkMock(),
                ],
            ]);

        self::getContainer()->set('test.url.client', $client);
    }

    /**
     * @throws JsonException
     */
    private function createResponseAddressesLevelsCountMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(json_encode(['count' => 2], JSON_THROW_ON_ERROR));
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAllAddressLevelsMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode([
                    'address_levels' => [
                        [
                            'id' => 4,
                            'name' => 'Область',
                            'short_name' => 'обл.',
                            'level' => 1,
                        ],
                        [
                            'id' => 7,
                            'name' => 'Город',
                            'short_name' => 'г.',
                            'level' => 2,
                        ],
                    ],
                ],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAddressCountMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode(['count' => 2],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAddressAllMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode([

                    'addresses' => [
                        [
                            'id' => 1,
                            'name' => 'Город Москва',
                            'guid' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                            'favorite' => false,
                            'addressLevel' => 2,
                            'levelId' => 7,
                            'fullName' => 'Город Москва',
                            'phone' => '',
                            'latitude' => 0,
                            'longitude' => 0,
                            'zoom' => 0,
                            'display' => true,
                            'mainStorage' => 'base',
                            'shopGroupCode' => 'shop_group_location_1',
                            'active' => true,
                            'first_level_parent' => 'custom_01',
                            'parent_full_name' => '',
                            'parent_id' => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Московская область',
                            'guid' => '29251dcf-00a1-4e34-98d4-5c47484a36d4',
                            'favorite' => false,
                            'addressLevel' => 1,
                            'levelId' => 4,
                            'fullName' => 'Московская область',
                            'phone' => '',
                            'latitude' => 0,
                            'longitude' => 0,
                            'zoom' => 0,
                            'display' => true,
                            'mainStorage' => 'base',
                            'shopGroupCode' => 'shop_group_location_2',
                            'active' => true,
                            'first_level_parent' => 'custom_01',
                            'parent_full_name' => '',
                            'parent_id' => null,
                        ],
                        [
                            'id' => 3,
                            'name' => 'Район Москва',
                            'guid' => '0c5b2444-70a0-4932-980c-b4dc0d3f0233',
                            'favorite' => false,
                            'addressLevel' => 2,
                            'levelId' => 7,
                            'fullName' => 'Район Москва',
                            'phone' => '',
                            'latitude' => 0,
                            'longitude' => 0,
                            'zoom' => 0,
                            'display' => true,
                            'mainStorage' => 'base',
                            'shopGroupCode' => 'shop_group_location_3',
                            'active' => true,
                            'first_level_parent' => 'custom_01',
                            'parent_full_name' => '',
                            'parent_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
                        ],
                        [
                            'id' => 4,
                            'name' => 'Деревня Москва',
                            'guid' => '0c5b2444-70a0-4932-980c-b4dc0d3f0255',
                            'favorite' => false,
                            'addressLevel' => 2,
                            'levelId' => 7,
                            'fullName' => 'Деревня Москва',
                            'phone' => '',
                            'latitude' => 0,
                            'longitude' => 0,
                            'zoom' => 0,
                            'display' => true,
                            'mainStorage' => 'base',
                            'shopGroupCode' => 'shop_group_location_4',
                            'active' => true,
                            'first_level_parent' => 'custom_01',
                            'parent_full_name' => '',
                            'parent_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f0233',
                        ],
                    ],

                ],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAllStoresCountMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode(['count' => 5],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAllStoresMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode([
                    // Указаны только используемые поля
                    'stores' => [
                        [
                            'id' => 1,
                            'xml_id' => '901185',
                        ],
                        [
                            'id' => 2,
                            'xml_id' => '901183',
                        ],
                        [
                            'id' => 3,
                            'xml_id' => '901514',
                        ],
                        [
                            'id' => 4,
                            'xml_id' => '901383',
                        ],

                    ],
                ],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAddressesLinkCountMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode(['count' => 1],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAllAddressesLinkMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode([
                    'addresses_link' => [
                        [
                            'id' => 1,
                            'address_id' => 1,
                            'parent_id' => 2,
                            'depth' => 1,
                        ],
                        [
                            'id' => 2,
                            'address_id' => 3,
                            'parent_id' => 1,
                            'depth' => 1,
                        ],
                        [
                            'id' => 3,
                            'address_id' => 4,
                            'parent_id' => 3,
                            'depth' => 1,
                        ],
                    ],
                ],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAllStoresLinkCountMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode(['count' => 4],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @throws JsonException
     */
    private function createResponseAllStoresLinkMock(): MockObject
    {
        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('getContents')
            ->willReturn(
                json_encode([
                    'stores' => [
                        [
                            'address_id' => 1,
                            'store_id' => 1,
                        ],
                        [
                            'address_id' => 1,
                            'store_id' => 2,
                        ],
                        [
                            'address_id' => 1,
                            'store_id' => 3,
                        ],
                        [
                            'address_id' => 1,
                            'store_id' => 4,
                        ],
                        [
                            'address_id' => 3,
                            'store_id' => 1,
                        ],
                        [
                            'address_id' => 4,
                            'store_id' => 1,
                        ],
                    ],
                ],
                    JSON_THROW_ON_ERROR)
            );
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($body);

        return $response;
    }

    private function initFeatureManagerMock(): MockObject
    {
        $featureToggleService = $this->createMock(FeatureToggleService::class);

        self::getContainer()->set('test.feature_toggle_service', $featureToggleService);

        return $featureToggleService;
    }
}
