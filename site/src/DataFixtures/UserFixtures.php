<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'dev';

        if ($appEnv === 'test') {
            $this->ensureUser(
                $manager,
                $_ENV['APP_TEST_ADMIN_EMAIL'] ?? getenv('APP_TEST_ADMIN_EMAIL') ?: 'admin@test.local',
                $_ENV['APP_TEST_ADMIN_PASSWORD'] ?? getenv('APP_TEST_ADMIN_PASSWORD') ?: 'admin12345',
                $_ENV['APP_TEST_ADMIN_NAME'] ?? getenv('APP_TEST_ADMIN_NAME') ?: 'Test Admin',
                ['ROLE_ADMIN']
            );

            $this->ensureUser(
                $manager,
                $_ENV['APP_TEST_USER_EMAIL'] ?? getenv('APP_TEST_USER_EMAIL') ?: 'user@test.local',
                $_ENV['APP_TEST_USER_PASSWORD'] ?? getenv('APP_TEST_USER_PASSWORD') ?: 'user12345',
                $_ENV['APP_TEST_USER_NAME'] ?? getenv('APP_TEST_USER_NAME') ?: 'Test User',
                ['ROLE_USER_FRONTEND']
            );
        } else {
            $adminEmail = $_ENV['APP_ADMIN_EMAIL'] ?? getenv('APP_ADMIN_EMAIL') ?: null;
            $adminPassword = $_ENV['APP_ADMIN_PASSWORD'] ?? getenv('APP_ADMIN_PASSWORD') ?: null;
            $adminName = $_ENV['APP_ADMIN_NAME'] ?? getenv('APP_ADMIN_NAME') ?: null;

            if ($adminEmail && $adminPassword) {
                $this->ensureUser($manager, $adminEmail, $adminPassword, $adminName ?: $adminEmail, ['ROLE_ADMIN']);
            }
        }

        $manager->flush();
    }

    private function ensureUser(ObjectManager $manager, string $email, string $password, string $name, array $roles): void
    {
        $existing = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            return;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles($roles);
        $user->setEnabled(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);
    }
}
