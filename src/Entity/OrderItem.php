<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_STAFF')"),
        new Get(security: "is_granted('ROLE_STAFF')"),
        new Post(security: "is_granted('ROLE_STAFF')"),
        new Put(security: "is_granted('ROLE_STAFF')"),
        new Patch(security: "is_granted('ROLE_STAFF')"),
        new Delete(security: "is_granted('ROLE_STAFF')"),
    ],
)]
#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(inversedBy: 'items')]
	#[ORM\JoinColumn(nullable: false)]
	private ?Order $order = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: false)]
	private ?Product $product = null;

	#[ORM\Column]
	private int $quantity = 1;

	#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
	private ?string $unitPrice = '0.00';

	#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
	private ?string $subtotal = '0.00';

	public function getId(): ?int { return $this->id; }
	public function getOrder(): ?Order { return $this->order; }
	public function setOrder(?Order $order): static { $this->order = $order; return $this; }
	public function getProduct(): ?Product { return $this->product; }
	public function setProduct(?Product $product): static { $this->product = $product; return $this; }
	public function getQuantity(): int { return $this->quantity; }
	public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }
	public function getUnitPrice(): ?string { return $this->unitPrice; }
	public function setUnitPrice(string $unitPrice): static { $this->unitPrice = $unitPrice; return $this; }
	public function getSubtotal(): ?string { return $this->subtotal; }
	public function setSubtotal(string $subtotal): static { $this->subtotal = $subtotal; return $this; }
}



