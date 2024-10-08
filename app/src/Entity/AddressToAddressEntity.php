<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AddressToAddressEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressToAddressEntityRepository::class)]
class AddressToAddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $depth = null;

    #[ORM\ManyToOne(inversedBy: 'addressToAddressEntities')]
    private ?AddressEntity $address = null;

    #[ORM\ManyToOne(inversedBy: 'parentAddressToAddressEntities')]
    private ?AddressEntity $parent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getAddress(): ?AddressEntity
    {
        return $this->address;
    }

    public function setAddress(?AddressEntity $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getParent(): ?AddressEntity
    {
        return $this->parent;
    }

    public function setParent(?AddressEntity $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
