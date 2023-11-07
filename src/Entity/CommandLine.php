<?php

namespace App\Entity;

use App\Repository\CommandLineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandLineRepository::class)]
class CommandLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $Quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $Price = null;

    #[ORM\ManyToOne(inversedBy: 'Command_Line')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Command $Command = null;

    #[ORM\ManyToOne(inversedBy: 'Command_Line')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Products $Products = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    public function setQuantity(int $Quantity): static
    {
        $this->Quantity = $Quantity;

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

    public function getCommand(): ?Command
    {
        return $this->Command;
    }

    public function setCommand(?Command $Command): static
    {
        $this->Command = $Command;

        return $this;
    }

    public function getProducts(): ?Products
    {
        return $this->Products;
    }

    public function setProducts(?Products $Products): static
    {
        $this->Products = $Products;

        return $this;
    }
}
