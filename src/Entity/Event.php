<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_start = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_limit_registration = null;

    #[ORM\Column]
    private ?int $max_registration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $event_info = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTimeInterface $date_start): static
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDateLimitRegistration(): ?\DateTimeInterface
    {
        return $this->date_limit_registration;
    }

    public function setDateLimitRegistration(\DateTimeInterface $date_limit_registration): static
    {
        $this->date_limit_registration = $date_limit_registration;

        return $this;
    }

    public function getMaxRegistration(): ?int
    {
        return $this->max_registration;
    }

    public function setMaxRegistration(int $max_registration): static
    {
        $this->max_registration = $max_registration;

        return $this;
    }

    public function getEventInfo(): ?string
    {
        return $this->event_info;
    }

    public function setEventInfo(?string $event_info): static
    {
        $this->event_info = $event_info;

        return $this;
    }

}
