<?php

declare(strict_types=1);

namespace App\Entity;

use App\Faker\FakerValues\FakerCustomerCode;
use App\Repository\ConsumerRepository;
use App\ValueObject\ConsumerCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Test\PhpServicesBundle\Faker;

// Группы ТО используются пока только аптеками, если понадобится использовать другие сервисы, то нужна будет доработка для учета типа сервиса

#[ORM\Entity(repositoryClass: ConsumerRepository::class)]
#[ORM\Table(name: 'consumer')]
class ConsumerEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'code', type: 'consumer_code', length: 20)]
    #[FakerCustomerCode(maxSymbols: 20)]
    private ConsumerCode $code;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    #[Faker\FakerValues\FakerShortTitle()]
    private string $name;

    #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
    #[Faker\FakerValues\FakerDescription]
    private ?string $description;

    #[ORM\ManyToMany(targetEntity: ShopGroupEntity::class, inversedBy: 'consumers', cascade: ['persist'])]
    private ?Collection $shopGroups;

    public function __construct()
    {
        $this->shopGroups = new ArrayCollection();
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

    public function getCode(): ConsumerCode
    {
        return $this->code;
    }

    public function setCode(ConsumerCode $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
     * @return Collection<int, ShopGroupEntity>
     */
    public function getShopGroups(): Collection
    {
        return $this->shopGroups;
    }

    public function addShopGroup(ShopGroupEntity $entity): self
    {
        if (!$this->shopGroups->contains($entity)) {
            $this->shopGroups->add($entity);
            $entity->addConsumer($this);
        }

        return $this;
    }

    public function removeShopGroup(ShopGroupEntity $entity): self
    {
        if ($this->shopGroups->contains($entity)) {
            $this->shopGroups->removeElement($entity);
            $entity->removeConsumer($this);
        }

        return $this;
    }

    public function removeAllShopGroups(): self
    {
        $this->shopGroups = new ArrayCollection();

        return $this;
    }
}
