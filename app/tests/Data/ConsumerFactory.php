<?php
declare(strict_types=1);

namespace App\Tests\Data;

use App\Entity\ConsumerEntity;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Entity\ShopGroupEntity;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;

final readonly class ConsumerFactory
{
    public function __construct(
        private FakerGeneratorFactory $factory,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Создаем консьюмеры у которых первым и второй элемент имеют общие группы
     *
     * @param int  $count
     * @param bool $flush
     *
     * @return ConsumerEntity[]
     */
    public function createConsumersWithMultipleRelationshipsToShopGroups(int $count, bool $flush = true): array
    {
        if ($count < 2) {
            throw new LogicException('Нужно минимум 2 элемента');
        }
        $shopGroupFactory = new ShopGroupFactory($this->factory, $this->em);

        /**
         * @var ConsumerEntity[] $consumers
         */
        $consumers = $this->factory->init()->createFew(ConsumerEntity::class, $count);
        foreach ($consumers as $index => $consumer) {

            array_map(static function (ShopGroupEntity $shopGroup) use ($index, $consumers) {

                if (!$index) {
                    $shopGroup->addConsumer($consumers[1]);
                }

                $shopGroup->addConsumer($consumers[$index]);

            }, $shopGroupFactory->createLinkedShopGroups(2, false));

        }

        if ($flush) {
            array_map([$this->em, 'persist'], $consumers);
            $this->em->flush();
        }

        return $consumers;
    }

    /**
     * @param ShopGroupEntity[] $shopGroups
     * @param bool              $flush
     *
     * @return ConsumerEntity
     */
    public function createConsumerAndLinkShopGroups(array $shopGroups, bool $flush = true, array $overridesFields = []): ConsumerEntity
    {
        /**
         * @var ConsumerEntity $consumer
         */
        $consumer = $this->factory->init()->create(ConsumerEntity::class, $overridesFields);
        array_map(static fn(ShopGroupEntity $e) => $e->addConsumer($consumer), $shopGroups);

        if ($flush) {
            $this->em->persist($consumer);
            $this->em->flush();
        }

        return $consumer;
    }
}
