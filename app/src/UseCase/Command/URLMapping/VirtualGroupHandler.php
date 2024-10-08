<?php

declare(strict_types=1);

namespace App\UseCase\Command\URLMapping;

use App\Entity\AddressEntity;
use App\Entity\AddressToAddressEntity;
use App\Entity\ConsumerEntity;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Entity\ShopEntity;
use App\Entity\ShopGroupEntity;
use App\Enum\PrefixEnum;
use App\Exception\VirtualGroupException;
use App\Repository\AddressToAddressEntityRepository;
use App\Repository\ConsumerRepository;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use App\Repository\ShopGroupRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Test\PhpServicesBundle\Bus\CommandHandlerBase;
use Psr\Log\LoggerInterface;

final readonly class VirtualGroupHandler implements CommandHandlerBase
{
    private const DISTR_POSTFIX = 'distr';

    public function __construct(
        private EntityManagerInterface $em,
        private AddressToAddressEntityRepository $addressToAddressRepository,
        private ShopGroupRepository $shopGroupRepository,
        private LoggerInterface $logger,
        private ShopRepository $shopRepository,
        private ConsumerRepository $consumerRepository,
        private RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(VirtualGroupCommand $command): void
    {
        $shopGroups = $this->saveShopGroups();
        $this->saveShopGroupToShops();

        /** @var ConsumerEntity[] $consumers */
        $consumers = $this->consumerRepository->findAll();
        foreach ($consumers as $consumer) {
            $newShopGroups = $this->getConsumerNewShopGroups($consumer, $shopGroups);
            $this->consumerRepository->insertConsumerShopGroupRelations($consumer, $newShopGroups);
        }

        $this->em->clear();
    }

    /**
     * @param ShopGroupEntity[] $shopGroups
     */
    public function getConsumerNewShopGroups(ConsumerEntity $consumerEntity, array $shopGroups): array
    {
        $newShopGroups = [];
        foreach ($shopGroups as $shopGroup) {
            $existedFiasShopGroups = $this->shopGroupRepository->findAllActiveByFiasIdAndConsumerId($shopGroup->getFiasId(), $consumerEntity->getId());
            if (!$existedFiasShopGroups) {
                $newShopGroups[] = $shopGroup;
            }
        }

        return $newShopGroups;
    }

    private function saveShopGroups(): array
    {
        $shopGroups = [];

        try {
            $iterator = $this->shopRepository->createQueryBuilder('s')
                                             ->getQuery()
                                             ->toIterable();

            $this->logger->info('Shop group save start');

            /**
             * @var ShopEntity $shop
             */
            foreach ($iterator as $shop) {
                try {
                    $shopAddress = $this->getShopAddress($shop);

                    if (null === $shopAddress) {
                        $this->logger->error('Address not found for shop', ['xml_id' => $shop->getXmlId()]);
                        continue;
                    }

                    $shopGroup = $shopGroups[$shopAddress->getExternalId()] ?? null;

                    if (null !== $shopGroup) {
                        continue;
                    }

                    $shopGroup = $this->shopGroupRepository->findOneByCode(PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist((string) $shopAddress->getExternalId()));

                    $isNew = false;
                    if (null === $shopGroup) {
                        $shopGroup = new ShopGroupEntity();
                        $isNew = true;
                    }

                    $shopGroup->setCode((string) $shopAddress->getExternalId());
                    $shopGroup->setTitle($shopAddress->getName());
                    $shopGroup->setActive($shopAddress->isActive());
                    $shopGroup->setLevel(1);
                    $shopGroup->setFiasId($shopAddress->getGuid());

                    $this->processDistrShopGroup($shopAddress, $shopGroup);

                    if ($isNew) {
                        $this->em->persist($shopGroup);
                    }

                    $shopGroups[$shopAddress->getExternalId()] = $shopGroup;
                } catch (\Exception $e) {
                    $this->logger->error('Create shop group error: '.$e->getMessage(), ['xml_id' => $shop->getXmlId()]);
                } catch (\Throwable $e) {
                    $this->logger->error('Create shop group syntax error: '.$e->getMessage(), ['xml_id' => $shop->getXmlId()]);
                }
            }

            $this->em->flush();
            $this->em->clear();
        } catch (\Exception $e) {
            throw new VirtualGroupException('Shop group save error', $e->getCode(), $e);
        }

        return $shopGroups;
    }

    private function processDistrShopGroup(AddressEntity $shopAddress, ShopGroupEntity $shopGroup): void
    {
        $code = $this->getDistrCode($shopAddress);

        $distrShopGroup = $this->shopGroupRepository
            ->findOneByCode($code);

        $isNew = false;
        if (null === $distrShopGroup) {
            $distrShopGroup = clone $shopGroup;
            $isNew = true;
        }

        if ($isNew) {
            $this->em->persist($distrShopGroup);
        }

        $distrShopGroup->setCode($code);
        $distrShopGroup->setIsDistr(true);
    }

    private function getDistrCode(AddressEntity $shopAddress): string
    {
        return sprintf(
            '%s_%s',
            PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist((string) $shopAddress->getExternalId()),
            self::DISTR_POSTFIX,
        );
    }

    /**
     * @throws VirtualGroupException
     */
    private function saveShopGroupToShops(): void
    {
        $this->logger->info('Group to shop save start');
        try {
            $iterator = $this->shopRepository->createQueryBuilder('s')
                          ->getQuery()
                          ->toIterable();

            /**
             * @var ShopEntity $shop
             */
            foreach ($iterator as $shop) {
                try {
                    $this->relationshipOfStoreGroupsToShopRepository->removeByShopXmlId($shop->getXmlId());
                    $shopAddress = $this->getShopAddress($shop);

                    if (null === $shopAddress) {
                        continue;
                    }

                    /**
                     * @var ShopGroupEntity $shopGroup
                     */
                    $shopGroup = $this->shopGroupRepository->findOneByCode(PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist((string) $shopAddress->getExternalId()));

                    $shopGroupToShop = new RelationshipOfStoreGroupsToShopEntity();
                    $shopGroupToShop->setShopGroup($shopGroup);
                    $shopGroupToShop->setUfXmlId($shop->getXmlId());
                    $this->relationshipOfStoreGroupsToShopRepository->save($shopGroupToShop, true);
                } catch (\Throwable $e) {
                    $this->logger->error('Create shop relation error: '.$e->getMessage(), ['xml_id' => $shop->getXmlId()]);
                }
            }

            $this->em->clear();
        } catch (\Exception $e) {
            throw new VirtualGroupException('Group to shop save error', $e->getCode(), $e);
        }

        $this->logger->info('Group to shop save finish');
    }

    private function getShopAddress(ShopEntity $shop): ?AddressEntity
    {
        $addresses = $shop->getAddresses()->filter(function (AddressEntity $address) {
            return 2 === $address->getLevel()?->getLevel();
        });

        if ($addresses->isEmpty()) {
            return null;
        }

        if (1 === $addresses->count()) {
            return $addresses->current();
        }

        $addressGuids = [];
        /**
         * @var AddressEntity $address
         */
        foreach ($addresses as $address) {
            $addressGuids[] = $address->getGuid();
        }
        $this->logger->info(sprintf('Shop: %s related to %s', $shop->getXmlId(), implode(', ', $addressGuids)));

        $indexedAddressToAddresses = [];
        $addressToAddresses = $this->addressToAddressRepository->getAddressRelationships($addresses->toArray());
        foreach ($addressToAddresses as $addressToAddress) {
            $indexedAddressToAddresses[$addressToAddress->getAddress()?->getId()] = $addressToAddress;
        }

        if (!$indexedAddressToAddresses) {
            return $addresses->current();
        }

        /**
         * @var AddressToAddressEntity $el
         */
        $el = array_shift($indexedAddressToAddresses);
        while ($indexedAddressToAddresses[$el->getParent()?->getId()] ?? null) {
            $el = $indexedAddressToAddresses[$el->getParent()?->getId()];
        }

        return $el->getParent();
    }
}
