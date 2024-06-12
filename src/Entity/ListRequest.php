<?php

namespace App\Entity;

use App\Repository\ListRequestRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListRequestRepository::class)]
class ListRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $param = null;

    // #[ORM\OneToOne(cascade: ['persist'])]
    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn()]
    private ?User $initiator = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $requestAt = null;

    public function __construct()
    {
        if (!$this->requestAt) {
            $this->setRequestAt(new DateTimeImmutable());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParam(): ?string
    {
        return $this->param;
    }

    public function setParam(string $param): static
    {
        $this->param = $param;

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

    public function getInitiator(): ?User
    {
        return $this->initiator;
    }

    public function setInitiator(?User $initiator): static
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function getRequestAt(): ?\DateTimeImmutable
    {
        return $this->requestAt;
    }

    public function setRequestAt(\DateTimeImmutable $requestAt): static
    {
        $this->requestAt = $requestAt;

        return $this;
    }
}
