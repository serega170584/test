<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
class ShopEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $xmlId = null;

    #[ORM\ManyToMany(targetEntity: AddressEntity::class, mappedBy: 'shop')]
    private Collection $addresses;

    #[ORM\Column]
    private ?int $externalId = null;

    #[ORM\Column]
    private bool $isDistr = false;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getXmlId(): ?string
    {
        return $this->xmlId;
    }

    public function setXmlId(string $xmlId): self
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return Collection<int, AddressEntity>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(AddressEntity $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->addShop($this);
        }

        return $this;
    }

    public function removeAddress(AddressEntity $address): self
    {
        if ($this->addresses->removeElement($address)) {
            $address->removeShop($this);
        }

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
