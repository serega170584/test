<?php

declare(strict_types=1);

namespace App\Service\ImportVirtualGroupStrategy;

use App\Entity\AddressEntity;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\Enum\ConsumerEnum;
use App\Enum\PrefixEnum;
use App\Enum\ShopGroupLevel;
use App\Exception\VirtualGroupException;
use Doctrine\DBAL\Exception;

final class OtherConsumerImportVirtualGroupStrategy extends BaseImportShopGroupStrategy implements ImportShopGroupStrategy
{
    private const RETRY_COUNT = 3;

    public function importShopGroups(): array
    {
        $shopGroups = $this->prepareShopGroups();

        foreach ($shopGroups as $shopGroup) {
            $this->logger->info('Import shop group save start');
            for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
                try {
                    $this->shopGroupRepository->save($shopGroup, true);

                    $this->logger->info('Import shop group save successful finish', ['attempt' => $i]);

                    break;
                } catch (\Exception $e) {
                    $this->logger->info('Import shop group save failed finish', ['attempt' => $i]);

                    $this->mr->resetManager();
                    $this->em->getConnection()->connect();

                    if ($i == self::RETRY_COUNT - 1) {
                        $this->logger->error('Import shop group save error '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

                        throw new VirtualGroupException('Import shop group save error', $e->getCode(), $e);
                    }
                }
            }
        }

        return array_map(static function (ShopGroupEntity $shopGroupEntity): int {
            return $shopGroupEntity->getId();
        }, $shopGroups);
    }

    /**
     * @return array<string,ShopGroupEntity>
     *
     * @throws VirtualGroupException
     * @throws Exception
     */
    private function prepareShopGroups(): array
    {
        /** @var array<string, ShopGroupEntity> $shopGroups */
        $shopGroups = [];

        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            try {
                $this->logger->info('Shop group getting start', ['attempt' => $i]);

                $consumers = $this->consumerRepository->findByCodesNotIn([ConsumerEnum::DISTRIBUTOR->value]);

                $shops = $this->shopRepository->getNonDistributorShops();

                foreach ($shops as $shop) {
                    try {
                        $shopAddress = $this->getShopAddress($shop);

                        if (null === $shopAddress) {
                            $this->logger->error('Address not found for shop', ['xml_id' => $shop->getXmlId()]);

                            continue;
                        }
                        $code = PrefixEnum::SHOP_GROUP_PREFIX->appendPrefix((string) $shopAddress->getExternalId());

                        if (array_key_exists($code, $shopGroups)) {
                            $shopGroups[$code]->addShop($shop->getXmlId());

                            continue;
                        }

                        $shopGroup = $this->shopGroupRepository->findOneByCode($code) ?? new ShopGroupEntity();
                        $shopGroup = $this->hydrateShopGroup($shopGroup, $code, $shopAddress);

                        $shopGroup->removeAllShops();
                        $shopGroup->addShop($shop->getXmlId());

                        if (!$shopGroup->getId()) {
                            array_map(
                                static function (ConsumerEntity $consumerEntity) use ($shopGroup) {
                                    $shopGroup->addConsumer($consumerEntity);
                                },
                                $consumers
                            );
                        }

                        $shopGroups[$code] = $shopGroup;
                    } catch (\Exception $e) {
                        $this->logger->error('Create shop group error: '.$e->getMessage(), ['xml_id' => $shop->getXmlId()]);
                    }
                }

                $this->logger->info('Shop group getting successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Shop group getting failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    $this->logger->error('Shop group getting error '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

                    throw new VirtualGroupException('Shop group getting error', $e->getCode(), $e);
                }
            }
        }

        return $shopGroups;
    }

    private function hydrateShopGroup(ShopGroupEntity $shopGroup, string $code, AddressEntity $shopAddress): ShopGroupEntity
    {
        $shopGroup->setCode($code);
        $shopGroup->setTitle($shopAddress->getName());
        $shopGroup->setActive($shopAddress->isActive());
        $shopGroup->setLevel(ShopGroupLevel::PARENT_LEVEL->value);
        $shopGroup->setFiasId($shopAddress->getGuid());

        return $shopGroup;
    }
}
