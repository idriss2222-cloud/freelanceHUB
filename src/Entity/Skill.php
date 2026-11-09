<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: 'skills')]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom d’une compétence ne peut pas être vide.')]
    #[Assert\Length(max: 100)]
    private string $name;

    #[ORM\Column(length: 100, unique: true)]
    private string $normalizedName;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNormalizedName(): string
    {
        return $this->normalizedName;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);
        $this->normalizedName = (new UnicodeString($this->name))->lower()->toString();

        return $this;
    }
}
