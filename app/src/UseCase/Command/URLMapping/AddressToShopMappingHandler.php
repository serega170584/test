<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\ApiResource\URLProvider;
use App\DTO\ApiRequest\AddressShop;
use App\Entity\AddressEntity;
use App\Entity\ShopEntity;
use App\Exception\AddressToShopMappingException;
use App\Repository\AddressRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class AddressToShopMappingHandler implements CommandHandlerBase
{
    private const BATCH_SIZE = 100;

    private const RETRY_COUNT = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private AddressRepository      $addressRepository,
        private ShopRepository         $shopRepository,
        private LoggerInterface        $logger,
        private URLProvider            $provider,
        private ManagerRegistry        $mr,
    ) {
    }

    public function __invoke(AddressToShopMappingCommand $addressToShopMappingCommand): void
    {
        $this->logger->info('Address to shop mapping start');

        try {
            $this->em->getConnection()->executeQuery('TRUNCATE address_entity_shop_entity;');
            $provider = $this->provider;
            $count = $provider->getAddressToShopCount();
            $offset = 0;
            while ($offset < $count) {
                try {
                    $addressToShops = $provider->getAddressShops($offset, self::BATCH_SIZE);
                    $addressIds = [];
                    $shopIds = [];
                    foreach ($addressToShops as $addressToShop) {
                        $addressIds[] = $addressToShop->addressId;
                        $shopIds[] = $addressToShop->shopId;
                    }

                    $addressIds = array_unique($addressIds);
                    $shopIds = array_unique($shopIds);

                    $this->saveAddressToShops($addressToShops, $addressIds, $shopIds);
                } catch (\Exception $e) {
                    $this->logger->error('Address to shop save error:'.$e->getMessage(), ['offset' => $offset]);
                }

                $offset += self::BATCH_SIZE;
            }
        } catch (\Exception $e) {
            throw new AddressToShopMappingException('Address to shop mapping error', $e->getCode(), $e);
        }

        $this->logger->info('Address to shop mapping finish');
    }

    /**
     * @param AddressShop[] $addressToShops
     *
     * @throws \Exception
     */
    private function saveAddressToShops(array $addressToShops, array $addressIds, array $shopIds): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Address to shops save start', ['attempt' => $i]);

            try {
                $addresses = $this->addressRepository->findBy(['externalId' => $addressIds]);
                $addresses = array_combine(array_map(static function (AddressEntity $address) {
                    return $address->getExternalId();
                }, $addresses), $addresses);

                $shops = $this->shopRepository->findBy(['externalId' => $shopIds]);
                $shops = array_combine(array_map(static function (ShopEntity $shop) {
                    return $shop->getExternalId();
                }, $shops), $shops);

                foreach ($addressToShops as $addressToShop) {
                    try {
                        $address = $addresses[$addressToShop->addressId] ?? null;
                        if (null === $address) {
                            throw new \Exception(sprintf('Address with external id %d is not mapped', $addressToShop->addressId));
                        }
                        $shop = $shops[$addressToShop->shopId] ?? null;
                        if (null === $shop) {
                            throw new \Exception(sprintf('Shop with external id %d is not mapped', $addressToShop->shopId));
                        }
                        $address->addShop($shop);
                    } catch (\Exception $e) {
                        $this->logger->error('Address to shop mapping error:'.$e->getMessage());
                    }
                }

                $this->em->flush();
                $this->em->clear();

                $this->logger->info('Address to addresses save successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Address to addresses save failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }
}
