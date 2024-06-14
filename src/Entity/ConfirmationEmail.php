<?php

namespace App\Entity;

use App\Repository\ConfirmationEmailRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfirmationEmailRepository::class)]
class ConfirmationEmail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $signature = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $at = null;

    #[ORM\OneToOne(inversedBy: 'confirmationEmail')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] // Utilisation de SET NULL au lieu de CASCADE
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $newMail = null;


    public function __construct()
    {
        $this->setAt(new DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): static
    {
        $this->signature = $signature;

        return $this;
    }

    public function getAt(): ?\DateTimeInterface
    {
        return $this->at;
    }

    private function setAt(\DateTimeInterface $at): static
    {
        $this->at = $at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

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
}
