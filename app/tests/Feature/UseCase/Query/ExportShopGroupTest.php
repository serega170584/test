<?php

declare(strict_types=1);

namespace App\Tests\Feature\UseCase\Query;

use App\Bus\BusManager;
use App\DTO\Response\ExportShopGroup\ConsumerDto;
use App\DTO\Response\ExportShopGroup\ShopGroupDto;
use App\DTO\Response\ExportShopGroup\ShopGroupToShopDto;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\UseCase\Query\Export\ExportShopGroupQuery;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;

class ExportShopGroupTest extends BaseTestClass
{
    use RefreshDatabase;

    private FakerGeneratorFactory $factory;

    private EntityManagerInterface $em;

    private BusManager $busManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = static::$kernel->getContainer()->get(FakerGeneratorFactory::class);
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->busManager = static::$kernel->getContainer()->get('test.busManager');
    }

    public function testExportShopGroupQuery(): void
    {
        /**@var ConsumerEntity $consumerCreated */
        $consumerCreated = $this->factory->init()->createAndSave(ConsumerEntity::class);

        /**@var ShopGroupEntity $shopGroupCreated */
        $shopGroupCreated = $this->factory->init()->create(ShopGroupEntity::class);
        $shopId = (string)random_int(1, 10);
        $shopGroupCreated->addShop($shopId);
        $shopGroupCreated->addConsumer($consumerCreated);

        $this->em->persist($shopGroupCreated);
        $this->em->flush();

        $exportResult = $this->busManager->askExportShopGroupQuery(new ExportShopGroupQuery());

        $shopGroups = $exportResult->getShopGroups();
        /** @var ShopGroupDto $shopGroup */
        $shopGroup = reset($shopGroups);
        self::assertEquals(
            [
                $shopGroup->code,
                $shopGroup->title,
                $shopGroup->description,
                $shopGroup->code,
                $shopGroup->parent_code,
                (int) $shopGroup->active,
            ],
            [
                $shopGroupCreated->getCode(),
                $shopGroupCreated->getTitle(),
                $shopGroupCreated->getDescription(),
                $shopGroupCreated->getCode(),
                !empty($shopGroupCreated->getParent()) ? $shopGroupCreated->getParent()->getCode() : null,
                (int) $shopGroupCreated->isActive(),
            ]
        );

        $consumers = $exportResult->getConsumers();
        /** @var ConsumerDto $consumer */
        $consumer = reset($consumers);
        self::assertEquals(
            [
                $consumer->code,
                $consumer->title,
                $consumer->description,
            ],
            [
                $consumerCreated->getCode(),
                $consumerCreated->getName(),
                $consumerCreated->getDescription(),
            ]
        );

        $shopGroupToShops = $exportResult->getShopGroupToShop();
        /** @var ShopGroupToShopDto $shopGroupToShop */
        $shopGroupToShop = reset($shopGroupToShops);
        self::assertEquals($shopGroupToShop->uf_xml_id, $shopId);
    }
}