<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse courriel est déjà utilisée.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_CLIENT = 'ROLE_CLIENT';
    public const ROLE_FREELANCER = 'ROLE_FREELANCER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Saisissez votre adresse courriel.')]
    #[Assert\Email(message: 'Saisissez une adresse courriel valide.')]
    #[Assert\Length(max: 180, maxMessage: 'L’adresse courriel ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [self::ROLE_FREELANCER];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Saisissez votre nom complet.')]
    #[Assert\Length(max: 180, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private string $fullName = '';

    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\Length(max: 120, maxMessage: 'Le nom de l’entreprise ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $company = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'La présentation ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $bio = null;

    /** @var Collection<int, Skill> */
    #[ORM\ManyToMany(targetEntity: Skill::class)]
    #[ORM\JoinTable(name: 'user_skills')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'skill_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $skills;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email === null ? null : (new UnicodeString(trim($email)))->lower()->toString();

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function getRoles(): array
    {
        return array_values(array_unique([...$this->roles, 'ROLE_USER']));
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    public function getAccountType(): string
    {
        return in_array(self::ROLE_CLIENT, $this->roles, true)
            ? self::ROLE_CLIENT
            : self::ROLE_FREELANCER;
    }

    public function setAccountType(string $accountType): self
    {
        if (!in_array($accountType, [self::ROLE_CLIENT, self::ROLE_FREELANCER], true)) {
            throw new \InvalidArgumentException('Type de compte invalide.');
        }

        $this->roles = [$accountType];

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = trim($fullName);

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company === null || trim($company) === '' ? null : trim($company);

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio === null || trim($bio) === '' ? null : trim($bio);

        return $this;
    }

    /** @return Collection<int, Skill> */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): self
    {
        $this->skills->removeElement($skill);

        return $this;
    }

    public function clearSkills(): self
    {
        $this->skills->clear();

        return $this;
    }
}
