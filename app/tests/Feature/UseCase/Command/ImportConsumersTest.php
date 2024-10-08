<?php
declare(strict_types=1);

namespace App\Tests\Feature\UseCase\Command;

use App\Bus\BusManager;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\Tests\Data\ConsumerFactory;
use App\UseCase\Command\ImportConsumers\ImportConsumerItem;
use App\UseCase\Command\ImportConsumers\ImportConsumersCommand;
use App\ValueObject\ConsumerCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;

class ImportConsumersTest extends BaseTestClass
{
    use RefreshDatabase;

    private BusManager $busManager;

    private EntityManagerInterface $em;

    private ConsumerFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->busManager = static::$kernel->getContainer()->get('test.busManager');
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->factory = new ConsumerFactory(
            static::$kernel->getContainer()->get(FakerGeneratorFactory::class),
            $this->em
        );
    }

    public function testImportConsumers(): void
    {
        $consumers = $this->factory->createConsumersWithMultipleRelationshipsToShopGroups(3);
        $removeConsumer = array_shift($consumers);
        $command = $this->createImportConsumersCommand($consumers);

        $this->busManager->executeImportConsumersCommand($command);

        /**
         * @var ArrayCollection<int, ConsumerEntity> $currentConsumers
         */
        $currentConsumers = $this->em->getRepository(ConsumerEntity::class)->findAll();
        foreach ($currentConsumers as $consumer) {
            static::assertNotEquals($removeConsumer->getCode(), $consumer->getCode());
            static::assertArrayHasKey($consumer->getCode()->value(), $command->items);

            $item = $command->items[$consumer->getCode()->value()];

            static::assertEquals($item->code->value(), $consumer->getCode()->value());
            static::assertEquals($item->title, $consumer->getName());
            static::assertEquals($item->description, $consumer->getDescription());
        }

        $shopGroupsCodes = $removeConsumer->getShopGroups()->map(
            static fn(ShopGroupEntity $entity) => $entity->getCode()
        )->toArray();

        $qtyShopGroups = $this->em->getRepository(ShopGroupEntity::class)->count(['code' => $shopGroupsCodes]);

        static::assertEquals(count($shopGroupsCodes), $qtyShopGroups);
    }

    /**
     * @param ConsumerEntity[] $consumers
     *
     * @return ImportConsumersCommand
     */
    private function createImportConsumersCommand(array $consumers): ImportConsumersCommand
    {
        $updateName = true;
        $items = [];

        foreach ($consumers as $entity) {

            if ($updateName) {
                $updateName = false;
                $items[$entity->getCode()->value()] = new ImportConsumerItem(
                    $entity->getCode(),
                    'new amazing name',
                    $entity->getDescription()
                );
            } else {
                $items[$entity->getCode()->value()] = new ImportConsumerItem(
                    $entity->getCode(),
                    $entity->getName(),
                    $entity->getDescription()
                );
            }
        }

        $consumeCode = new ConsumerCode('iOS');
        $items[$consumeCode->value()] = new ImportConsumerItem($consumeCode, 'аптеки', '');

        return new ImportConsumersCommand($items);
    }
}
