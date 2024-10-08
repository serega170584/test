<?php
declare(strict_types=1);

namespace App\Tests\Data;

use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Entity\ShopGroupEntity;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;

final readonly class ShopGroupFactory
{
    public function __construct(
        private FakerGeneratorFactory $factory,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Создать связанные между собой ShopGroups у которых
     * 1) последняя группа элементов ShopGroups имеет 3 наследника на последнем уровне
     * 2) остальные группы элементов ShopGroups имеет только одного наследника
     *
     * @param int  $count
     * @param bool $flush
     *
     * @return ShopGroupEntity[]
     */
    public function createLinkedShopGroups(int $count, bool $flush = true): array
    {
        if ($count < 2) {
            throw new LogicException('Нужно минимум 2 элемента');
        }

        $result = [];

        for ($i = 0; $i < $count; $i++) {

            $previousShopGroup = null;
            $shopGroups = $this->createShopGroupsWithShops(5, 3, null, false);

            foreach ($shopGroups as $shopGroup) {

                if ($previousShopGroup) {
                    $shopGroup->setParent($previousShopGroup);
                }

                $previousShopGroup = $shopGroup;

                $result[] = $shopGroup;
            }
        }

        $result = array_merge(
            $result,
            $this->createShopGroupsWithShops(3, 3, $previousShopGroup ?? null, false)
        );

        if ($flush) {
            array_map([$this->em, 'persist'], $result);
            $this->em->flush();
        }

        return $result;
    }

    /**
     * @param int              $qtyShopsGroups
     * @param ?ShopGroupEntity $previousShopGroup
     * @param bool             $flush
     *
     * @return ShopGroupEntity[]
     */
    public function createShopGroupsWithShops(
        int $qtyShopsGroups,
        int $qtyShops,
        ?ShopGroupEntity $previousShopGroup = null,
        bool $flush = true,
        array $overridesFields = []
    ): array {
        $result = [];

        $shopGroups = $this->factory->init()->createFew(ShopGroupEntity::class, $qtyShopsGroups, $overridesFields);

        foreach ($shopGroups as $shopGroup) {

            if ($previousShopGroup) {
                $shopGroup->setParent($previousShopGroup);
            }

            $this->factory->init()->createFew(
                RelationshipOfStoreGroupsToShopEntity::class,
                $qtyShops,
                [
                    'shopGroup' => $shopGroup,
                ]
            );

            $result[] = $shopGroup;
        }

        if ($flush) {
            array_map([$this->em, 'persist'], $result);
            $this->em->flush();
        }

        return $result;
    }
}
