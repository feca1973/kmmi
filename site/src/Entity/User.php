<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Deprecated;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin user, stored in the legacy FOSUserBundle `user` table (sha512 passwords with salt, serialized roles).
 *
 * Legacy passwords are verified with the legacy hasher (see security.yaml) and are replaced with a modern hash,
 * and the salt is cleared, on the first successful login. Once no user has a salt any more, the
 * LegacyPasswordAuthenticatedUserInterface and the `legacy_sha512` hasher can be removed.
 *
 * @SuppressWarnings("PHPMD.UnusedPrivateField")
 */
#[ORM\Table(name: 'user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['emailCanonical'], message: 'Ez az e-mail cím már használatban van.', errorPath: 'email')]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'username', type: Types::STRING, length: 180)]
    private string $username = '';

    #[ORM\Column(name: 'username_canonical', type: Types::STRING, length: 180, unique: true)]
    private string $usernameCanonical = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(name: 'email', type: Types::STRING, length: 255)]
    private string $email = '';

    #[ORM\Column(name: 'email_canonical', type: Types::STRING, length: 180, unique: true)]
    private string $emailCanonical = '';

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN)]
    private bool $enabled = false;

    #[ORM\Column(name: 'salt', type: Types::STRING, length: 255, nullable: true)]
    private ?string $salt = null;

    #[ORM\Column(name: 'password', type: Types::STRING, length: 255)]
    private string $password = '';

    #[ORM\Column(name: 'last_login', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $lastLogin = null;

    #[ORM\Column(name: 'confirmation_token', type: Types::STRING, length: 180, nullable: true, unique: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(name: 'password_requested_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $passwordRequestedAt = null;

    /**
     * @var array<int|string, mixed>
     */
    // The column comment is a legacy (Doctrine 2 type hint) leftover; kept so the schema diff stays empty.
    #[ORM\Column(name: 'roles', type: 'array', options: ['comment' => '(DC2Type:array)'])]
    private array $roles = [];

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    private string $name = '';

    /**
     * Not stored: set by the admin form, hashed before saving.
     */
    private ?string $plainPassword = null;

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Keeps the plain password and the legacy salt out of the session, and logs the user out
     * when the password hash changes (the session stores a checksum of it).
     *
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        unset($data["\0" . self::class . "\0plainPassword"]);

        return $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        assert($this->username !== '');

        return $this->username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * The e-mail address is also the username (as in the legacy system); both are stored in canonical
     * (lowercase) form too, which is what the login looks up.
     */
    public function setEmail(?string $email): self
    {
        $email = $email ?? '';
        $this->email = $email;
        $this->username = $email;
        $this->emailCanonical = mb_strtolower($email);
        $this->usernameCanonical = mb_strtolower($email);

        return $this;
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

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTime $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = array_filter($this->roles, is_string(...));
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param array<int|string, mixed> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    #[Deprecated('Credentials are kept out of the session by __serialize().')]
    public function eraseCredentials(): void
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name ?? '';

        return $this;
    }
}
