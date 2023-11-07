<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $Price = null;

    #[ORM\Column]
    private ?int $Inventory = null;

    #[ORM\OneToMany(mappedBy: 'Products', targetEntity: CommandLine::class, orphanRemoval: true)]
    private Collection $Command_Line;

    public function __construct()
    {
        $this->Command_Line = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->Price;
    }

    public function setPrice(string $Price): static
    {
        $this->Price = $Price;

        return $this;
    }

    public function getInventory(): ?int
    {
        return $this->Inventory;
    }

    public function setInventory(int $Inventory): static
    {
        $this->Inventory = $Inventory;

        return $this;
    }

    /**
     * @return Collection<int, CommandLine>
     */
    public function getCommandLine(): Collection
    {
        return $this->Command_Line;
    }

    public function addCommandLine(CommandLine $commandLine): static
    {
        if (!$this->Command_Line->contains($commandLine)) {
            $this->Command_Line->add($commandLine);
            $commandLine->setProducts($this);
        }

        return $this;
    }

    public function removeCommandLine(CommandLine $commandLine): static
    {
        if ($this->Command_Line->removeElement($commandLine)) {
            // set the owning side to null (unless already changed)
            if ($commandLine->getProducts() === $this) {
                $commandLine->setProducts(null);
            }
        }

        return $this;
    }
}
