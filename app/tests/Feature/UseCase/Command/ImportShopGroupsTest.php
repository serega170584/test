<?php
declare(strict_types=1);

namespace App\Tests\Feature\UseCase\Command;

use App\Bus\BusManager;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\Enum\PrefixEnum;
use App\Tests\Data\ConsumerFactory;
use App\UseCase\Command\ImportShopGroups\ImportShopGroupItem;
use App\UseCase\Command\ImportShopGroups\ImportShopGroupsCommand;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;

class ImportShopGroupsTest extends BaseTestClass
{
    use RefreshDatabase;

    private FakerGeneratorFactory $factory;

    private BusManager $busManager;

    private EntityManagerInterface $em;

    private ConsumerFactory $consumerFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->busManager = static::$kernel->getContainer()->get('test.busManager');
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->consumerFactory = new ConsumerFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            $this->em
        );
    }

    public function testImportShopGroups(): void
    {
        $consumers = $this->consumerFactory->createConsumersWithMultipleRelationshipsToShopGroups(3);
        $removedShopGroup = $this->removeFirstShopGroupWithParent(current($consumers));
        $command = $this->createImportShopGroupsCommand($consumers, $removedShopGroup->getCode());

        $this->busManager->executeImportShopGroupsCommand($command);

        /**
         * @var ArrayCollection<int,ShopGroupEntity> $currentShopGroups
         */
        $currentShopGroups = $this->em->getRepository(ShopGroupEntity::class)->findAll();
        foreach ($currentShopGroups as $shopGroup) {
            static::assertNotEquals($removedShopGroup->getCode(), $shopGroup->getCode());
            static::assertArrayHasKey($shopGroup->getCode(), $command->items);

            $item = $command->items[$shopGroup->getCode()];

            static::assertEquals($item->code, $shopGroup->getCode());
            static::assertEquals($item->title, $shopGroup->getTitle());
            static::assertEquals($item->description, $shopGroup->getDescription());
            static::assertEquals($item->active, $shopGroup->isActive());

            $commandConsumers = $item->consumers;
            $currentConsumers = array_map(static fn(ConsumerEntity $e) => $e->getCode(),
                $shopGroup->getConsumers()->toArray());

            sort($commandConsumers);
            sort($currentConsumers);

            static::assertArraySubset($commandConsumers, $currentConsumers);

            if ($item->parentCode === $removedShopGroup->getCode()) {
                static::assertEquals('', $shopGroup->getParent()?->getCode() ?? '');
            } else {
                static::assertEquals($item->parentCode, $shopGroup->getParent()?->getCode() ?? '');
            }

            $shopGroupParent = $shopGroup->getParent();
            $level = 1;

            while ($shopGroupParent) {
                $level++;
                $shopGroupParent = $shopGroupParent->getParent();
            }

            static::assertEquals($level, $shopGroup->getLevel());
        }
    }

    private function removeFirstShopGroupWithParent(ConsumerEntity $entity): ShopGroupEntity
    {
        foreach ($entity->getShopGroups() as $shopGroup) {
            if ($shopGroup->getParent()) {
                return $shopGroup;
            }
        }

        throw new LogicException('Не найден shopGroup с родителем');
    }

    /**
     * @param ConsumerEntity[] $consumers
     *
     * @return ImportShopGroupsCommand
     */
    private function createImportShopGroupsCommand(
        array $consumers,
        string $excludeShopGroupCode
    ): ImportShopGroupsCommand {
        $items = [];
        $removeActive = true;
        $removeParentForEntityWithParent = true;
        $changeParentForEntityWithParent = true;
        $previousParentCode = '';

        foreach ($consumers as $consumer) {
            foreach ($consumer->getShopGroups() as $shopGroup) {

                $shopGroupCode = $shopGroup->getCode();

                if ($excludeShopGroupCode === $shopGroupCode) {
                    continue;
                }

                if ($removeActive) {
                    $removeActive = $active = false;
                } else {
                    $active = $shopGroup->isActive();
                }

                if ($removeParentForEntityWithParent && $shopGroup->getParent()) {
                    $parentCode = '';
                    $removeParentForEntityWithParent = false;
                } elseif ($changeParentForEntityWithParent && $shopGroup->getParent()) {
                    $parentCode = $previousParentCode;
                    $changeParentForEntityWithParent = false;
                } else {
                    $parentCode = $shopGroup->getParent() ? $shopGroup->getParent()->getCode() : '';
                }

                $previousParentCode = $shopGroupCode;

                $items[$shopGroup->getCode()] = new ImportShopGroupItem(
                    $shopGroupCode,
                    $shopGroup->getTitle(),
                    $shopGroup->getDescription(),
                    $parentCode,
                    $active,
                    array_map(static fn(ConsumerEntity $e) => $e->getCode(), $shopGroup->getConsumers()->toArray())
                );
            }
        }

        $code = PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist('region_1');
        $items[$code] = new ImportShopGroupItem(
            $code,
            'MSK',
            '',
            '',
            true,
            array_map(static fn(ConsumerEntity $e) => $e->getCode(), $consumers)
        );

        return new ImportShopGroupsCommand($items);
    }
}
