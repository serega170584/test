<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopGroupByShopCode;

use App\Entity\ShopGroupEntity;
use App\Repository\ConsumerRepository;
use App\Repository\ShopGroupRepository;
use App\Service\ConsumerResolver;
use Doctrine\ORM\EntityNotFoundException;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;

class GetShopGroupByShopCodeHandler implements QueryHandlerBase
{
    public function __construct(
        private readonly ShopGroupRepository $shopGroupRepository,
        private readonly ConsumerRepository $consumerRepository,
        private readonly ConsumerResolver $consumerResolver
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function __invoke(GetShopGroupByShopCodeQuery $query): ?ShopGroupEntity
    {
        $consumerCode = $this->consumerResolver->resolveConsumerCodeByConsumerVersion($query->consumerCode, $query->consumerVersion);
        if ($consumer = $this->consumerRepository->findByCode($consumerCode)) {
            return $this->shopGroupRepository->findByShopIdAndConsumerId($query->ufXmlId, $consumer->getId());
        }

        throw new EntityNotFoundException('Consumer ['.$consumerCode.'] not found');
    }
}
