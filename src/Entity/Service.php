<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ServiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(security: 'true'),
        new Get(security: 'true'),
        new Post(security: "is_granted('ROLE_STAFF')"),
        new Put(security: "is_granted('ROLE_STAFF')"),
        new Patch(security: "is_granted('ROLE_STAFF')"),
        new Delete(security: "is_granted('ROLE_STAFF')"),
    ],
    order: ['createdAt' => 'DESC'],
)]
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $description = null;

	#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
	private ?string $price = null;

	#[ORM\Column(length: 50)]
	private ?string $petType = null;

	#[ORM\Column]
	private ?bool $isActive = true;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	private ?\DateTimeInterface $createdAt = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	private ?\DateTimeInterface $updatedAt = null;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
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

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): static
	{
		$this->description = $description;

		return $this;
	}

	public function getPrice(): ?string
	{
		return $this->price;
	}

	public function setPrice(string $price): static
	{
		$this->price = $price;

		return $this;
	}

	public function getPetType(): ?string
	{
		return $this->petType;
	}

	public function setPetType(string $petType): static
	{
		$this->petType = $petType;

		return $this;
	}

	public function isIsActive(): ?bool
	{
		return $this->isActive;
	}

	public function setIsActive(bool $isActive): static
	{
		$this->isActive = $isActive;

		return $this;
	}

	public function getCreatedAt(): ?\DateTimeInterface
	{
		return $this->createdAt;
	}

	public function setCreatedAt(\DateTimeInterface $createdAt): static
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getUpdatedAt(): ?\DateTimeInterface
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}
}


