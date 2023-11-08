<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUser'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getUser'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['getUser'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['getUser'])]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUser'])]
    private ?string $Address = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['getUser'])]
    private ?string $Wallet = null;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: Command::class, orphanRemoval: true)]
    private Collection $Command;

    public function __construct()
    {
        $this->Command = new ArrayCollection();
        $this->Wallet = 0.00; // Valeur par défaut pour $Wallet
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAddress(): ?string
    {
        return $this->Address;
    }

    public function setAddress(string $Address): static
    {
        $this->Address = $Address;

        return $this;
    }

    public function getWallet(): ?string
    {
        return $this->Wallet;
    }

    public function setWallet(string $Wallet): static
    {
        $this->Wallet = $Wallet;

        return $this;
    }

    /**
     * @return Collection<int, Command>
     */
    public function getCommand(): Collection
    {
        return $this->Command;
    }

    public function addCommand(Command $command): static
    {
        if (!$this->Command->contains($command)) {
            $this->Command->add($command);
            $command->setUser($this);
        }

        return $this;
    }

    public function removeCommand(Command $command): static
    {
        if ($this->Command->removeElement($command)) {
            // set the owning side to null (unless already changed)
            if ($command->getUser() === $this) {
                $command->setUser(null);
            }
        }

        return $this;
    }
}
