<?php
declare(strict_types=1);

namespace App\Tests\Feature\UseCase\Command;

use App\Bus\BusManager;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Tests\Data\ShopGroupFactory;
use App\UseCase\Command\ImportRelationshipOfStoreGroupsToShop\ImportRelationshipOfStoreGroupsToShopCommand;
use App\UseCase\Command\ImportRelationshipOfStoreGroupsToShop\ImportRelationshipOfStoreGroupsToShopItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;

class ImportRelationshipOfStoreGroupsToShopTest extends BaseTestClass
{
    use RefreshDatabase;

    private ShopGroupFactory $factory;

    private BusManager $busManager;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->busManager = static::$kernel->getContainer()->get('test.busManager');
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->factory = new ShopGroupFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            $this->em
        );
    }

    public function testImportRelationshipOfStoreGroupsToShop(): void
    {
        [$command, $removeItems] = $this->createTestData();

        /**
         * @var ImportRelationshipOfStoreGroupsToShopCommand $command
         */
        $this->busManager->executeImportRelationshipOfStoreGroupsToShopCommand($command);

        /**
         * @var ArrayCollection<int, RelationshipOfStoreGroupsToShopEntity> $entities
         */
        $entities = $this->em->getRepository(RelationshipOfStoreGroupsToShopEntity::class)->findAll();

        foreach ($entities as $entity) {

            static::assertArrayNotHasKey($this->getCompositeKey($entity), $removeItems);

            static::assertArrayHasKey($this->getCompositeKey($entity), $command->items);
        }
    }

    private function createTestData(): array
    {
        $items = [];
        $removeItems = [];

        $shopGroups = $this->factory->createShopGroupsWithShops(2, 3);

        foreach ($shopGroups as $shopGroup) {
            $changeShop = $removeItem = true;

            foreach ($shopGroup->getShops() as $shop) {
                if ($removeItem) {
                    $removeItem = false;
                    $removeItems[$this->getCompositeKey($shop)] = true;
                    continue;
                }

                if ($changeShop) {
                    $changeShop = false;
                    $items[$this->getCompositeKey($shop) . '-update'] = new ImportRelationshipOfStoreGroupsToShopItem(
                        $shopGroup->getCode(),
                        $shop->getUfXmlId() . '-update'
                    );
                } else {
                    $items[$this->getCompositeKey($shop)] = new ImportRelationshipOfStoreGroupsToShopItem(
                        $shopGroup->getCode(),
                        $shop->getUfXmlId()
                    );
                }

            }

            $items[$this->getCompositeKey($shop) . '-new'] = new ImportRelationshipOfStoreGroupsToShopItem(
                $shopGroup->getCode(),
                $shop->getUfXmlId() . '-new'
            );
        }

        return [new ImportRelationshipOfStoreGroupsToShopCommand($items), $removeItems];
    }

    private function getCompositeKey(RelationshipOfStoreGroupsToShopEntity $entity): string
    {
        return $entity->getShopGroup()->getCode() . '-' . $entity->getUfXmlId();
    }

}
