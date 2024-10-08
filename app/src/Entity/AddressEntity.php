<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class AddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(length: 255)]
    private ?string $guid = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'addresses')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $addresses;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    private ?string $parentFullName = null;

    #[ORM\Column(length: 255)]
    private ?string $firstLevelParent = null;

    #[ORM\Column]
    private ?bool $display = null;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    private ?AddressLevelEntity $level = null;

    #[ORM\ManyToMany(targetEntity: ShopEntity::class, inversedBy: 'addresses')]
    private Collection $shop;

    #[ORM\Column]
    private ?int $externalId = null;

    #[ORM\OneToMany(mappedBy: 'address', targetEntity: AddressToAddressEntity::class)]
    private Collection $addressToAddressEntities;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: AddressToAddressEntity::class)]
    private Collection $parentAddressToAddressEntities;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->shop = new ArrayCollection();
        $this->addressToAddressEntities = new ArrayCollection();
        $this->parentAddressToAddressEntities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(self $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setParent($this);
        }

        return $this;
    }

    public function removeAddress(self $address): self
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getParent() === $this) {
                $address->setParent(null);
            }
        }

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getParentFullName(): ?string
    {
        return $this->parentFullName;
    }

    public function setParentFullName(string $parentFullName): self
    {
        $this->parentFullName = $parentFullName;

        return $this;
    }

    public function getFirstLevelParent(): ?string
    {
        return $this->firstLevelParent;
    }

    public function setFirstLevelParent(string $firstLevelParent): self
    {
        $this->firstLevelParent = $firstLevelParent;

        return $this;
    }

    public function isDisplay(): ?bool
    {
        return $this->display;
    }

    public function setDisplay(bool $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function getLevel(): ?AddressLevelEntity
    {
        return $this->level;
    }

    public function setLevel(?AddressLevelEntity $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, ShopEntity>
     */
    public function getShop(): Collection
    {
        return $this->shop;
    }

    public function addShop(ShopEntity $shop): self
    {
        if (!$this->shop->contains($shop)) {
            $this->shop->add($shop);
        }

        return $this;
    }

    public function removeShop(ShopEntity $shop): self
    {
        $this->shop->removeElement($shop);

        return $this;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(int $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return Collection<int, AddressToAddressEntity>
     */
    public function getAddressToAddressEntities(): Collection
    {
        return $this->addressToAddressEntities;
    }

    public function addAddressToAddressEntity(AddressToAddressEntity $addressToAddressEntity): self
    {
        if (!$this->addressToAddressEntities->contains($addressToAddressEntity)) {
            $this->addressToAddressEntities->add($addressToAddressEntity);
            $addressToAddressEntity->setAddress($this);
        }

        return $this;
    }

    public function removeAddressToAddressEntity(AddressToAddressEntity $addressToAddressEntity): self
    {
        if ($this->addressToAddressEntities->removeElement($addressToAddressEntity)) {
            // set the owning side to null (unless already changed)
            if ($addressToAddressEntity->getAddress() === $this) {
                $addressToAddressEntity->setAddress(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AddressToAddressEntity>
     */
    public function getParentAddressToAddressEntities(): Collection
    {
        return $this->parentAddressToAddressEntities;
    }

    public function addParentAddressToAddressEntity(AddressToAddressEntity $parentAddressToAddressEntity): self
    {
        if (!$this->parentAddressToAddressEntities->contains($parentAddressToAddressEntity)) {
            $this->parentAddressToAddressEntities->add($parentAddressToAddressEntity);
            $parentAddressToAddressEntity->setParent($this);
        }

        return $this;
    }

    public function removeParentAddressToAddressEntity(AddressToAddressEntity $parentAddressToAddressEntity): self
    {
        if ($this->parentAddressToAddressEntities->removeElement($parentAddressToAddressEntity)) {
            // set the owning side to null (unless already changed)
            if ($parentAddressToAddressEntity->getParent() === $this) {
                $parentAddressToAddressEntity->setParent(null);
            }
        }

        return $this;
    }
}
