<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_STAFF')"),
        new Get(security: "is_granted('ROLE_STAFF') or (object.getUser() != null and object.getUser() == user)"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_STAFF')"),
        new Patch(security: "is_granted('ROLE_STAFF')"),
        new Delete(security: "is_granted('ROLE_STAFF')"),
    ],
    order: ['AppointmentDate' => 'ASC'],
)]
#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[Assert\NotBlank(message: 'Pet name is required.')]
    #[Assert\Length(min: 2, max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[Assert\NotNull(message: 'Appointment date is required.')]
    #[ORM\Column]
    private ?\DateTime $AppointmentDate = null;

    #[Assert\NotBlank(message: 'Pet type is required.')]
    #[Assert\Choice(choices: ['dog', 'cat'], message: 'Choose dog or cat.')]
    #[ORM\Column(length: 50)]
    private ?string $PetType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
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

    public function getAppointmentDate(): ?\DateTime
    {
        return $this->AppointmentDate;
    }

    public function setAppointmentDate(\DateTime $AppointmentDate): static
    {
        $this->AppointmentDate = $AppointmentDate;

        return $this;
    }

    public function getPetType(): ?string
    {
        return $this->PetType;
    }

    public function setPetType(string $PetType): static
    {
        $this->PetType = $PetType;

        return $this;
    }
}
