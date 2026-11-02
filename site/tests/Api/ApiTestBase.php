<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestBase extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new \App\Kernel('test', true);
    }

    protected function createUserAndLogin($client): array
    {
        $email = $this->randomEmail('user');
        $password = $this->randomPassword();
        $id = $this->createUserViaRegistration($client, $email, $password);
        $token = $this->getAccessToken($client, $email, $password);

        return [
            'email' => $email,
            'password' => $password,
            'id' => $id,
            'token' => $token,
        ];
    }

    protected function createAdminAndLogin($client): array
    {
        $email = $this->randomEmail('admin');
        $password = $this->randomPassword();
        $user = $this->createUserEntity($email, $password, ['ROLE_ADMIN']);
        $token = $this->getAccessToken($client, $email, $password);

        return [
            'email' => $email,
            'password' => $password,
            'id' => $user->getId(),
            'token' => $token,
        ];
    }

    protected function getAccessToken($client, string $email, string $password): string
    {
        $response = $client->request('POST', '/api/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $data = $response->toArray();

        return $data['token'];
    }

    protected function createUserViaRegistration($client, string $email, string $password): int
    {
        $client->request('POST', '/api/users/register', [
            'json' => [
                'email' => $email,
                'plainPassword' => $password,
                'name' => 'Temporary User',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);

        $id = $this->findUserId($email);
        $this->assertNotNull($id);

        return $id;
    }

    protected function createUserEntity(
        string $email,
        string $password,
        array $roles,
        ?string $name = null,
        bool $enabled = true
    ): User
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setName($name ?? $email);
        $user->setRoles($roles);
        $user->setEnabled($enabled);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    protected function findUserId(string $email): int
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->assertNotNull($user);

        return $user->getId();
    }

    protected function randomEmail(string $prefix): string
    {
        return sprintf('%s-%s@test.local', $prefix, bin2hex(random_bytes(4)));
    }

    protected function randomPassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
