<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'projects')]
class Project
{
    public const STATUS_OPEN = 'open';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        'Ouvert' => self::STATUS_OPEN,
        'En pause' => self::STATUS_PAUSED,
        'Terminé' => self::STATUS_COMPLETED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Saisissez le titre du projet.')]
    #[Assert\Length(max: 180)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Décrivez le projet.')]
    #[Assert\Length(max: 10000)]
    private string $description = '';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    #[Assert\NotNull(message: 'Saisissez le budget du projet.')]
    #[Assert\Range(min: 0.01, max: 9999999999.99, notInRangeMessage: 'Le budget doit être supérieur à zéro.')]
    private ?string $budget = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: 'Choisissez une date limite.')]
    #[Assert\GreaterThan('today', message: 'La date limite doit être ultérieure à aujourd’hui.')]
    private ?\DateTimeImmutable $deadline = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Indiquez une catégorie.')]
    #[Assert\Length(max: 100)]
    private string $category = '';

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_OPEN, self::STATUS_PAUSED, self::STATUS_COMPLETED])]
    private string $status = self::STATUS_OPEN;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'client_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $client = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = trim($description);

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(?string $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getDeadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeImmutable $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = trim($category);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Statut de projet invalide.');
        }

        $this->status = $status;

        return $this;
    }

    public function getStatusLabel(): string
    {
        return array_search($this->status, self::STATUSES, true) ?: 'Ouvert';
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(User $client): self
    {
        $this->client = $client;

        return $this;
    }
}
