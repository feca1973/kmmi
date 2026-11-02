<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Doctrine\Type\LegacyRolesType;
use App\Repository\UserRepository;
use App\State\CurrentUserProvider;
use App\State\UserPasswordHasher;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['emailCanonical'], message: 'This email is already in use.')]
#[UniqueEntity(fields: ['usernameCanonical'], message: 'This username is already in use.')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:read', 'admin:read']],
            security: 'is_granted("ROLE_ADMIN")'
        ),
        new Get(
            requirements: ['id' => '\\d+'],
            normalizationContext: ['groups' => ['user:read', 'admin:read']],
            security: 'is_granted("ROLE_ADMIN") or object == user'
        ),
        new Post(
            normalizationContext: ['groups' => ['user:read', 'admin:read']],
            denormalizationContext: ['groups' => ['user:write', 'admin:write']],
            security: 'is_granted("ROLE_ADMIN")',
            validationContext: ['groups' => ['Default', 'user:create', 'admin:create']],
            processor: UserPasswordHasher::class
        ),
        new Post(
            uriTemplate: '/users/register',
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['Default', 'user:create']],
            name: 'user_register',
            processor: UserPasswordHasher::class
        ),
        new Patch(
            requirements: ['id' => '\\d+'],
            normalizationContext: ['groups' => ['user:read', 'admin:read']],
            denormalizationContext: ['groups' => ['user:write', 'admin:write']],
            security: 'is_granted("ROLE_ADMIN") or object == user',
            securityPostDenormalize: 'is_granted("ROLE_ADMIN") or object.getRoles() == previous_object.getRoles()',
            validationContext: ['groups' => ['Default', 'user:update', 'admin:update']],
            processor: UserPasswordHasher::class
        ),
        new Delete(
            requirements: ['id' => '\\d+'],
            security: 'is_granted("ROLE_ADMIN")'
        ),
        new Get(
            uriTemplate: '/users/me',
            normalizationContext: ['groups' => ['user:read', 'admin:read']],
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            name: 'user_me',
            provider: CurrentUserProvider::class
        ),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, LegacyPasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:read', 'admin:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180)]
    #[Groups(['admin:read'])]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 180)]
    private ?string $usernameCanonical = null;

    #[ORM\Column(type: 'string', length: 180)]
    private ?string $emailCanonical = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['admin:read', 'admin:write'])]
    private bool $enabled = true;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['admin:read', 'admin:write'])]
    private ?string $salt = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password = '';

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['admin:read'])]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    #[Groups(['admin:read', 'admin:write'])]
    private ?string $confirmationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['admin:read', 'admin:write'])]
    private ?\DateTimeInterface $passwordRequestedAt = null;

    #[ORM\Column(type: LegacyRolesType::NAME)]
    #[Groups(['admin:read', 'admin:write'])]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:read', 'user:write', 'admin:read', 'admin:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write', 'admin:read', 'admin:write'])]
    private ?string $email = null;

    #[Assert\NotBlank(groups: ['user:create', 'admin:create'])]
    #[Assert\Length(min: 8, max: 255, groups: ['user:create', 'user:update', 'admin:create', 'admin:update'])]
    #[Groups(['user:write', 'admin:write'])]
    private ?string $plainPassword = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        $this->usernameCanonical = $this->canonicalize($username);

        return $this;
    }

    public function getUsernameCanonical(): ?string
    {
        return $this->usernameCanonical;
    }

    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $passwordRequestedAt): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->emailCanonical = $this->canonicalize($email);

        if ($this->username === null) {
            $this->setUsername($email);
        }

        if ($this->name === null) {
            $this->name = $email;
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        if ($this->email !== null) {
            return $this->email;
        }

        return (string) $this->username;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->email !== null) {
            $this->emailCanonical = $this->canonicalize($this->email);
        }

        if ($this->username !== null) {
            $this->usernameCanonical = $this->canonicalize($this->username);
        }

        if ($this->username === null && $this->email !== null) {
            $this->setUsername($this->email);
        }

        if ($this->name === null && $this->email !== null) {
            $this->name = $this->email;
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        if ($this->email !== null) {
            $this->emailCanonical = $this->canonicalize($this->email);
        }

        if ($this->username !== null) {
            $this->usernameCanonical = $this->canonicalize($this->username);
        }

        if ($this->name === null && $this->email !== null) {
            $this->name = $this->email;
        }
    }

    private function canonicalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtolower(trim($value));
    }
}
