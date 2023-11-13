<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandRepository::class)]
class Command
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCommands','command:read'])]
    private ?int $id = null;

    #[Groups(['getCommands','command:read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $Date = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCommands','command:read'])]
    private ?string $Status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['getCommands','command:read'])]
    private ?string $Total_Price = null;

    #[ORM\ManyToOne(inversedBy: 'Command')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\OneToMany(mappedBy: 'Command', targetEntity: CommandLine::class, orphanRemoval: true)]
    private Collection $Command_Line;

    public function __construct()
    {
        $this->Command_Line = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): static
    {
        $this->Date = $Date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->Status;
    }

    public function setStatus(string $Status): static
    {
        $this->Status = $Status;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->Total_Price;
    }

    public function setTotalPrice(string $Total_Price): static
    {
        $this->Total_Price = $Total_Price;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

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
            $commandLine->setCommand($this);
        }

        return $this;
    }

    public function removeCommandLine(CommandLine $commandLine): static
    {
        if ($this->Command_Line->removeElement($commandLine)) {
            // set the owning side to null (unless already changed)
            if ($commandLine->getCommand() === $this) {
                $commandLine->setCommand(null);
            }
        }

        return $this;
    }
}
