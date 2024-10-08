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

final class DistrImportVirtualGroupStrategy extends BaseImportShopGroupStrategy implements ImportShopGroupStrategy
{
    private const DISTR_POSTFIX = 'distr';

    private const RETRY_COUNT = 3;

    public function importShopGroups(): array
    {
        $shopGroups = $this->prepareShopGroups();

        foreach ($shopGroups as $shopGroup) {
            for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
                try {
                    $this->logger->info('Distributor import shop group save start', ['attempt' => $i, 'shop_group' => $shopGroup->getId()]);

                    $this->shopGroupRepository->save($shopGroup, true);

                    $this->logger->info('Distributor import shop group save successful finish', ['attempt' => $i, 'shop_group' => $shopGroup->getId()]);

                    break;
                } catch (\Exception $e) {
                    $this->logger->info('Distributor import shop group save failed finish', ['attempt' => $i, 'shop_group' => $shopGroup->getId()]);

                    $this->mr->resetManager();
                    $this->em->getConnection()->connect();

                    if ($i == self::RETRY_COUNT - 1) {
                        $this->logger->error('Distributor import shop group save error '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

                        throw new VirtualGroupException('Distributor import shop group save error', $e->getCode(), $e);
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
     */
    private function prepareShopGroups(): array
    {
        $shopGroups = [];

        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            try {
                $this->logger->info('Distributor import shop group getting start', ['attempt' => $i]);

                /** @var ConsumerEntity $consumer */
                $consumer = $this->consumerRepository->findOneBy(['code' => ConsumerEnum::DISTRIBUTOR->value]);
                $consumer->removeAllShopGroups();

                $shops = $this->shopRepository->getAllShops();

                foreach ($shops as $shop) {
                    try {
                        $shopAddress = $this->getShopAddress($shop);

                        if (null === $shopAddress) {
                            $this->logger->error('Address not found for shop', ['xml_id' => $shop->getXmlId()]);

                            continue;
                        }
                        $code = $this->getDistrCode($shopAddress);

                        if (array_key_exists($code, $shopGroups)) {
                            $shopGroups[$code]->addShop($shop->getXmlId());

                            continue;
                        }

                        $shopGroup = $this->shopGroupRepository->findOneByCode($code) ?? new ShopGroupEntity();

                        $shopGroup = $this->hydrateShopGroup($shopGroup, $code, $shopAddress);

                        $shopGroup->removeAllShops();
                        $shopGroup->addShop($shop->getXmlId());

                        $consumer->addShopGroup($shopGroup);

                        $shopGroups[$code] = $shopGroup;
                    } catch (\Exception $e) {
                        $this->logger->error('Distributor import shop group error: '.$e->getMessage(), ['xml_id' => $shop->getXmlId()]);
                    }
                }

                $this->logger->info('Distributor import shop group getting successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Distributor import shop group getting failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    $this->logger->error('Distributor import shop group save error '.$e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);

                    throw new VirtualGroupException('Distributor import shop group save error', $e->getCode(), $e);
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
        $shopGroup->setIsDistr(true);

        return $shopGroup;
    }

    private function getDistrCode(AddressEntity $shopAddress): string
    {
        return sprintf(
            '%s_%s',
            PrefixEnum::SHOP_GROUP_PREFIX->appendPrefix((string) $shopAddress->getExternalId()),
            self::DISTR_POSTFIX,
        );
    }
}
