<?php

namespace App\Entity;

use DateType;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 50)]
    private ?string $lastname = null;

    #[ORM\Column(length: 50)]
    private ?string $firstname = null;

    // #[ORM\Column(nullable: true)]
    // private ?\DateTimeImmutable $loginAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $newMail = null;

    // je crois qu'à cause de çà il supprime le user au moment de supprimer sa confirmation
    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist'])]
    private ?ConfirmationEmail $confirmationEmail = null;

    /**
     * @var Collection<int, ListRequest>
     */
    // #[ORM\OneToMany(targetEntity: ListRequest::class, mappedBy: 'user', orphanRemoval: true)]
    // private Collection $listRequests;

    public function __construct()
    {
        if ($this->createdAt === null) {
            $this->setCreatedAt(new DateTimeImmutable);
        }
        // $this->listRequests = new ArrayCollection();
    }

    // public function PrepUpdate()
    // {
    //     $this->loginAt = new DateTimeImmutable;
    // }

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
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }
    // public function getLoginAt(): ?\DateTimeImmutable
    // {
    //     return $this->loginAt;
    // }

    // public function setLoginAt(?\DateTimeImmutable $loginAt): static
    // {
    //     $this->loginAt = $loginAt;

    //     return $this;
    // }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBirthAt(): ?\DateTimeInterface
    {
        return $this->birthAt;
    }

    public function setBirthAt(?\DateTimeInterface $birthAt): static
    {
        $this->birthAt = $birthAt;

        return $this;
    }

    public function getNewMail(): ?string
    {
        return $this->newMail;
    }

    public function setNewMail(?string $newMail): static
    {
        $this->newMail = $newMail;

        return $this;
    }

    // /**
    //  * @return Collection<int, ListRequest>
    //  */
    // public function getListRequests(): Collection
    // {
    //     return $this->listRequests;
    // }

    // public function addListRequest(ListRequest $listRequest): static
    // {
    //     if (!$this->listRequests->contains($listRequest)) {
    //         $this->listRequests->add($listRequest);
    //         $listRequest->setUser($this);
    //     }

    //     return $this;
    // }

    // public function removeListRequest(ListRequest $listRequest): static
    // {
    //     if ($this->listRequests->removeElement($listRequest)) {
    //         // set the owning side to null (unless already changed)
    //         if ($listRequest->getUser() === $this) {
    //             $listRequest->setUser(null);
    //         }
    //     }

    //     return $this;
    // }

    public function getConfirmationEmail(): ?ConfirmationEmail
    {
        return $this->confirmationEmail;
    }

    public function setConfirmationEmail(ConfirmationEmail $confirmationEmail): static
    {
        // set the owning side of the relation if necessary
        if ($confirmationEmail->getUser() !== $this) {
            $confirmationEmail->setUser($this);
        }

        $this->confirmationEmail = $confirmationEmail;

        return $this;
    }
}
