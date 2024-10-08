<?php

declare(strict_types=1);

namespace App\Service\ImportVirtualGroupStrategy;

use App\Entity\AddressEntity;
use App\Entity\AddressToAddressEntity;
use App\Entity\ShopEntity;
use App\Repository\AddressToAddressEntityRepository;
use App\Repository\ConsumerRepository;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use App\Repository\ShopGroupRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class BaseImportShopGroupStrategy
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected AddressToAddressEntityRepository $addressToAddressRepository,
        protected ShopGroupRepository $shopGroupRepository,
        protected LoggerInterface $logger,
        protected ShopRepository $shopRepository,
        protected ConsumerRepository $consumerRepository,
        protected RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository,
        protected ManagerRegistry $mr,
    ) {
    }

    protected function getShopAddress(ShopEntity $shop): ?AddressEntity
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
