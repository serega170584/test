<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use Doctrine\ORM\Mapping as ORM;
use Test\PhpServicesBundle\Faker\FakerValues;

#[ORM\Entity(repositoryClass: RelationshipOfStoreGroupsToShopRepository::class)]
#[ORM\Table(name: 'relationship_of_store_groups_to_shop')]
class RelationshipOfStoreGroupsToShopEntity
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ShopGroupEntity::class, cascade: ['persist'], inversedBy: 'shops')]
    #[ORM\JoinColumn(name: 'shop_group_id', referencedColumnName: 'id')]
    private ShopGroupEntity $shopGroup;

    #[ORM\Id]
    #[ORM\Column(name: 'uf_xml_id', type: 'string', length: 100)]
    #[FakerValues\FakerNumerify('#######')]
    private string $ufXmlId;

    public function getShopGroup(): ShopGroupEntity
    {
        return $this->shopGroup;
    }

    public function setShopGroup(ShopGroupEntity $shopGroup): self
    {
        $this->shopGroup = $shopGroup;
        $shopGroup->getShops()->add($this);

        return $this;
    }

    public function getUfXmlId(): string
    {
        return $this->ufXmlId;
    }

    public function setUfXmlId(string $ufXmlId): self
    {
        $this->ufXmlId = $ufXmlId;

        return $this;
    }
}
