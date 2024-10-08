<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetActiveShopGroupByFiasId;

use App\Entity\ShopGroupEntity;
use App\Exception\NotFoundException;
use App\Repository\ConsumerRepository;
use App\Repository\ShopGroupRepository;
use App\Service\ConsumerResolver;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;

readonly class GetActiveShopGroupByFiasIdHandler implements QueryHandlerBase
{
    public function __construct(
        private ShopGroupRepository $shopGroupRepository,
        private ConsumerRepository $consumerRepository,
        private ConsumerResolver $consumerResolver
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function __invoke(GetActiveShopGroupByFiasIdQuery $query): ShopGroupEntity
    {
        $consumerCode = $this->consumerResolver->resolveConsumerCodeByConsumerVersion($query->consumerCode, $query->consumerVersion);

        $consumer = $this->consumerRepository->findByCode($consumerCode);
        if (!$consumer) {
            throw new NotFoundException("Not found consumer with code [{$consumerCode}]");
        }
        $shopGroups = $this->shopGroupRepository->findAllActiveByFiasIdAndConsumerId($query->fiasId, $consumer->getId());
        $countShopGroups = count($shopGroups);
        if (!$countShopGroups) {
            throw new NotFoundException("Not found active shop group with consumer code [{$consumerCode}] and fias id [{$query->fiasId}]");
        }
        if ($countShopGroups > 1) {
            throw new \RuntimeException("More than one shop group with consumer code {$consumerCode}] and fias id {$query->fiasId}");
        }

        return $shopGroups[0];
    }
}
