<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $amStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $amEnd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $pmStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $pmEnd = null;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column]
    private ?int $dayOfWeek = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmStart(): ?\DateTimeInterface
    {
        return $this->amStart;
    }

    public function setAmStart(?\DateTimeInterface $amStart): static
    {
        $this->amStart = $amStart;

        return $this;
    }

    public function getAmEnd(): ?\DateTimeInterface
    {
        return $this->amEnd;
    }

    public function setAmEnd(?\DateTimeInterface $amEnd): static
    {
        $this->amEnd = $amEnd;

        return $this;
    }

    public function getPmStart(): ?\DateTimeInterface
    {
        return $this->pmStart;
    }

    public function setPmStart(?\DateTimeInterface $pmStart): static
    {
        $this->pmStart = $pmStart;

        return $this;
    }

    public function getPmEnd(): ?\DateTimeInterface
    {
        return $this->pmEnd;
    }

    public function setPmEnd(?\DateTimeInterface $pmEnd): static
    {
        $this->pmEnd = $pmEnd;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }
}
