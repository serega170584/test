<?php

declare(strict_types=1);

namespace App\Tests\Feature\UseCase\Query;

use App\Bus\BusManager;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\UseCase\Query\GetShopGroupByShopCode\GetShopGroupByShopCodeQuery;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;

class GetShopGroupTest extends BaseTestClass
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

    public function testGetShopGroupByShopCode(): void
    {
        /** @var ConsumerEntity $consumer */
        $consumer = $this->factory->init()->createAndSave(ConsumerEntity::class);

        /** @var ShopGroupEntity $shopGroup */
        $shopGroup = $this->factory->init()->create(ShopGroupEntity::class);
        $shopCode = (string) random_int(1, 10);
        $shopGroup->addShop($shopCode);
        $shopGroup->addConsumer($consumer);

        $this->em->persist($shopGroup);
        $this->em->flush();

        $shopGroupResult = $this->busManager->askGetShopGroupByShopCodeQuery(
            new GetShopGroupByShopCodeQuery($shopCode, $consumer->getCode())
        );

        self::assertEquals($shopGroupResult->getId(), $shopGroup->getId());
    }
}
