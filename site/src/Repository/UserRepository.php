<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Users can log in with their username or e-mail address (case insensitive), as in the legacy system.
     */
    public function loadUserByIdentifier(string $identifier): ?User
    {
        $canonical = mb_strtolower($identifier);

        return $this->createQueryBuilder('u')
            ->where('u.usernameCanonical = :identifier OR u.emailCanonical = :identifier')
            ->setParameter('identifier', $canonical)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Called after a successful login whose password hash is outdated (legacy sha512): stores the modern
     * hash and clears the legacy salt.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword)->setSalt(null);
        $this->getEntityManager()->flush();
    }
}
