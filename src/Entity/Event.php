<?php

namespace App\Entity;

use App\Enum\State;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual('today', message: 'La date ne doit pas être antérieur à la date du jour')]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(
        min: 60,
        max: 43200,
        notInRangeMessage: 'La durée doit être comprise entre 60 minutes et 4300 minutes (30 jours).',
    )]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Expression(
        expression: "this.getDateLimitRegistration() < this.getDateStart()",
        message: "La date de limite d'inscription doit se terminer avant le début de la sortie et après la date du jour."
    )]
    #[Assert\GreaterThanOrEqual('today', message: 'La date ne doit pas être antérieur à la date du jour')]
    private ?\DateTimeInterface $dateLimitRegistration = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(
        min: 2,
        max: 2800,
        notInRangeMessage: 'Le nombre de place doit être compris entre 2 et 2800 participants.',
    )]
    private ?int $maxRegistration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eventInfo = null;

    #[ORM\ManyToOne(inversedBy: 'event')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Location $location = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $eventOrganizer = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'participationEvents')]
    private Collection $participate;

    #[ORM\Column(length: 255, enumType: State::class)]
    private ?State $state = null;


    public function __construct()
    {
        $this->participate = new ArrayCollection();
    }


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
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

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
        return $this->dateLimitRegistration;
    }

    public function setDateLimitRegistration(\DateTimeInterface $dateLimitRegistration): static
    {
        $this->dateLimitRegistration = $dateLimitRegistration;

        return $this;
    }

    public function getMaxRegistration(): ?int
    {
        return $this->maxRegistration;
    }

    public function setMaxRegistration(int $maxRegistration): static
    {
        $this->maxRegistration = $maxRegistration;

        return $this;
    }

    public function getEventInfo(): ?string
    {
        return $this->eventInfo;
    }

    public function setEventInfo(?string $eventInfo): static
    {
        $this->eventInfo = $eventInfo;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getEventOrganizer(): ?User
    {
        return $this->eventOrganizer;
    }

    public function setEventOrganizer(?User $eventOrganizer): static
    {
        $this->eventOrganizer = $eventOrganizer;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipate(): Collection
    {
        return $this->participate;
    }

    public function addParticipate(User $participate): static
    {
        if (!$this->participate->contains($participate)) {
            $this->participate->add($participate);
        }

        return $this;
    }

    public function removeParticipate(User $participate): static
    {
        $this->participate->removeElement($participate);

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(State $state): static
    {
        $this->state = $state;

        return $this;
    }


}
