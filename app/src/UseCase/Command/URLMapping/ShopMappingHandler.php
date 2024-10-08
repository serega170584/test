<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\ApiResource\URLProvider;
use App\DTO\ApiRequest\Shop;
use App\Entity\ShopEntity;
use App\Exception\ShopMappingException;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class ShopMappingHandler implements CommandHandlerBase
{
    private const BATCH_SIZE = 100;
    private const RETRY_COUNT = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private ShopRepository         $shopRepository,
        private LoggerInterface        $logger,
        private URLProvider            $provider,
        private ManagerRegistry        $mr,
    ) {
    }

    public function __invoke(ShopMappingCommand $shopMappingCommand): void
    {
        $this->logger->info('Shop mapping start');

        try {
            $this->truncateShops();
            $provider = $this->provider;
            $count = $provider->getShopCount();
            $offset = 0;

            while ($offset < $count) {
                $shops = $provider->getShops($offset, self::BATCH_SIZE);

                try {
                    $this->saveShops($shops);
                } catch (\Exception $e) {
                    $this->logger->error('Shop save error:'.$e->getMessage(), ['offset' => $offset]);
                }

                $offset += self::BATCH_SIZE;
            }
        } catch (\Exception $e) {
            throw new ShopMappingException('Shop mapping error', $e->getCode(), $e);
        }

        $this->logger->info('Shop mapping finish');
    }

    /**
     * @throws \Exception
     */
    private function truncateShops(): void
    {
        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Shops truncate start', ['attempt' => $i]);

            try {
                $this->shopRepository->truncate();

                $this->logger->info('Shops truncate successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Shop truncate failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param Shop[] $shops
     *
     * @throws \Exception
     */
    private function saveShops(array $shops): void
    {
        $receivedShopIds = [];

        for ($i = 0; $i < self::RETRY_COUNT; ++$i) {
            $this->logger->info('Shops save start', ['attempt' => $i]);

            try {
                foreach ($shops as $shop) {
                    $dbShop = new ShopEntity();
                    $dbShop->setExternalId($shop->id);
                    $dbShop->setXmlId($shop->xmlId);
                    $dbShop->setIsDistr(false);

                    $receivedShopIds[] = $shop->id;

                    $this->em->persist($dbShop);
                }

                $this->em->flush();
                $this->em->clear();

                $this->logger->info(sprintf('Received shop ids: %s', implode(', ', $receivedShopIds)));

                $this->logger->info('Shops save successful finish', ['attempt' => $i]);

                break;
            } catch (\Exception $e) {
                $this->logger->info('Shops save failed finish', ['attempt' => $i]);

                $this->mr->resetManager();
                $this->em->getConnection()->connect();

                if ($i == self::RETRY_COUNT - 1) {
                    throw $e;
                }
            }
        }
    }
}
