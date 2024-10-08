<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PrefixEnum;
use App\Faker\FakerValues\FakerGuid;
use App\Repository\ShopGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Test\PhpServicesBundle\Faker\FakerValues;

#[ORM\Entity(repositoryClass: ShopGroupRepository::class)]
#[ORM\Table(name: 'shop_group')]
#[ORM\Index(fields: ['fiasId'], name: 'idx_shop_group_fias_id')]
class ShopGroupEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'code', type: 'string', length: 100, unique: true)]
    #[FakerValues\FakerNumerify('#######')]
    private string $code;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[FakerValues\FakerShortTitle]
    private string $title;

    #[ORM\Column(name: 'active', type: 'boolean')]
    private bool $active = true;

    #[ORM\Column(name: 'is_distr', type: 'boolean')]
    private bool $isDistr = false;

    #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
    #[FakerValues\FakerDescription]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: ConsumerEntity::class, mappedBy: 'shopGroups', cascade: ['persist'])]
    private Collection $consumers;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: ShopGroupEntity::class, cascade: ['persist'])]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: ShopGroupEntity::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'set null')]
    private ?ShopGroupEntity $parent = null;

    #[ORM\Column(name: 'level', type: 'integer')]
    private int $level = 1;

    #[ORM\Column(name: 'fias_id', type: 'guid', length: 36, nullable: true)]
    #[FakerGuid]
    private ?string $fiasId;

    #[ORM\OneToMany(
        mappedBy: 'shopGroup',
        targetEntity: RelationshipOfStoreGroupsToShopEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $shops;

    public function __construct()
    {
        $this->consumers = new ArrayCollection();
        $this->shops = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist($code);

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, ConsumerEntity>
     */
    public function getConsumers(): Collection
    {
        return $this->consumers;
    }

    public function addConsumer(ConsumerEntity $entity): self
    {
        if (!$this->consumers->contains($entity)) {
            $this->consumers->add($entity);
            $entity->addShopGroup($this);
        }

        return $this;
    }

    public function removeConsumer(ConsumerEntity $entity): self
    {
        if ($this->consumers->contains($entity)) {
            $this->consumers->removeElement($entity);
            $entity->removeShopGroup($this);
        }

        return $this;
    }

    public function removeAllShops(): self
    {
        $this->shops = new ArrayCollection();

        return $this;
    }

    /**
     * @return Collection<int, RelationshipOfStoreGroupsToShopEntity>
     */
    public function getShops(): Collection
    {
        return $this->shops;
    }

    public function addShop(string $ufXmlId): self
    {
        if (null === $this->getShopByCode($ufXmlId)) {
            $this->shops->add(
                (new RelationshipOfStoreGroupsToShopEntity())
                    ->setShopGroup($this)
                    ->setUfXmlId($ufXmlId)
            );
        }

        return $this;
    }

    public function getShopByCode(string $ufXmlId): ?RelationshipOfStoreGroupsToShopEntity
    {
        foreach ($this->getShops() as $shop) {
            if ($shop->getUfXmlId() === $ufXmlId) {
                return $shop;
            }
        }

        return null;
    }

    public function removeShop(string $ufXmlId): self
    {
        foreach ($this->getShops() as $index => $shop) {
            if ($shop->getUfXmlId() === $ufXmlId) {
                $this->shops->remove($index);
            }
        }

        return $this;
    }

    public function getParent(): ?ShopGroupEntity
    {
        return $this->parent;
    }

    public function setParent(?ShopGroupEntity $parent): self
    {
        $this->setLevel(($parent?->getLevel() ?? 0) + 1);

        if ($parent) {
            $parent->addChildren($this);
        } elseif ($this->parent) {
            $this->parent->removeChildren($this);
        }

        $this->parent = $parent;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, ShopGroupEntity>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChildren(ShopGroupEntity $entity): self
    {
        if (!$this->children->contains($entity)) {
            $this->children->add($entity);
            $entity->setParent($this);
        }

        return $this;
    }

    public function removeChildren(ShopGroupEntity $entity): self
    {
        if ($this->children->contains($entity)) {
            $this->children->removeElement($entity);
            $entity->setParent(null);
        }

        return $this;
    }

    public function getFiasId(): ?string
    {
        return $this->fiasId;
    }

    public function setFiasId(?string $fiasId): self
    {
        $this->fiasId = $fiasId;

        return $this;
    }

    public function isDistr(): bool
    {
        return $this->isDistr;
    }

    public function setIsDistr(bool $isDistr): self
    {
        $this->isDistr = $isDistr;

        return $this;
    }
}
