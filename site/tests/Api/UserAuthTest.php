<?php

namespace App\Tests\Api;

final class UserAuthTest extends ApiTestBase
{
    public function testLoginSuccess(): void
    {
        $client = static::createClient();
        $email = $this->randomEmail('login');
        $password = $this->randomPassword();
        $this->createUserEntity($email, $password, ['ROLE_USER_FRONTEND']);

        $response = $client->request('POST', '/api/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertNotEmpty($data['token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $client = static::createClient();
        $email = $this->randomEmail('login-fail');
        $password = $this->randomPassword();
        $this->createUserEntity($email, $password, ['ROLE_USER_FRONTEND']);

        $client->request('POST', '/api/login', [
            'json' => [
                'email' => $email,
                'password' => $this->randomPassword(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDisabledUserCannotLogin(): void
    {
        $client = static::createClient();
        $email = $this->randomEmail('disabled-login');
        $password = $this->randomPassword();
        $this->createUserEntity($email, $password, ['ROLE_USER_FRONTEND'], null, false);

        $client->request('POST', '/api/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRefreshTokenFlow(): void
    {
        $client = static::createClient();
        $email = $this->randomEmail('refresh');
        $password = $this->randomPassword();
        $this->createUserEntity($email, $password, ['ROLE_USER_FRONTEND']);

        $response = $client->request('POST', '/api/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $loginData = $response->toArray();

        $refreshResponse = $client->request('POST', '/api/token/refresh', [
            'json' => [
                'refresh_token' => $loginData['refresh_token'],
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $refreshData = $refreshResponse->toArray();
        $this->assertArrayHasKey('token', $refreshData);
        $this->assertNotEmpty($refreshData['token']);
    }

    public function testRefreshTokenFailsWithInvalidToken(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/token/refresh', [
            'json' => [
                'refresh_token' => 'invalid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCurrentUserEndpointRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/users/me');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCurrentUserEndpointReturnsUser(): void
    {
        $client = static::createClient();
        $user = $this->createUserAndLogin($client);
        $token = $user['token'];

        $client->request('GET', '/api/users/me', [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanListUsers(): void
    {
        $client = static::createClient();
        $admin = $this->createAdminAndLogin($client);

        $client->request('GET', '/api/users', [
            'auth_bearer' => $admin['token'],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotListUsers(): void
    {
        $client = static::createClient();
        $user = $this->createUserAndLogin($client);
        $token = $user['token'];

        $client->request('GET', '/api/users', [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserCanReadSelfButNotOthers(): void
    {
        $client = static::createClient();
        $user = $this->createUserAndLogin($client);
        $userId = $user['id'];
        $userToken = $user['token'];
        $otherUserId = $this->createUserViaRegistration(
            $client,
            $this->randomEmail('other'),
            $this->randomPassword()
        );

        $client->request('GET', sprintf('/api/users/%d', $userId), [
            'auth_bearer' => $userToken,
        ]);
        $this->assertResponseIsSuccessful();

        $client->request('GET', sprintf('/api/users/%d', $otherUserId), [
            'auth_bearer' => $userToken,
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanDeleteUser(): void
    {
        $client = static::createClient();
        $admin = $this->createAdminAndLogin($client);

        $email = sprintf('to-delete-%s@test.local', bin2hex(random_bytes(4)));
        $userId = $this->createUserViaRegistration($client, $email, $this->randomPassword());

        $client->request('DELETE', sprintf('/api/users/%d', $userId), [
            'auth_bearer' => $admin['token'],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testUserCannotDeleteUser(): void
    {
        $client = static::createClient();
        $user = $this->createUserAndLogin($client);
        $userToken = $user['token'];
        $otherUserId = $this->createUserViaRegistration(
            $client,
            $this->randomEmail('delete-target'),
            $this->randomPassword()
        );

        $client->request('DELETE', sprintf('/api/users/%d', $otherUserId), [
            'auth_bearer' => $userToken,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserCanUpdateSelfButCannotEscalateRoles(): void
    {
        $client = static::createClient();
        $user = $this->createUserAndLogin($client);
        $userToken = $user['token'];
        $userId = $user['id'];

        $client->request('PATCH', sprintf('/api/users/%d', $userId), [
            'auth_bearer' => $userToken,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'body' => json_encode(['name' => 'Updated User'], JSON_THROW_ON_ERROR),
        ]);
        $this->assertResponseIsSuccessful();

        $client->request('PATCH', sprintf('/api/users/%d', $userId), [
            'auth_bearer' => $userToken,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'body' => json_encode(['roles' => ['ROLE_ADMIN']], JSON_THROW_ON_ERROR),
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanUpdateRoles(): void
    {
        $client = static::createClient();
        $admin = $this->createAdminAndLogin($client);
        $email = sprintf('role-target-%s@test.local', bin2hex(random_bytes(4)));
        $userId = $this->createUserViaRegistration($client, $email, $this->randomPassword());

        $client->request('PATCH', sprintf('/api/users/%d', $userId), [
            'auth_bearer' => $admin['token'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'body' => json_encode(['roles' => ['ROLE_ADMIN']], JSON_THROW_ON_ERROR),
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanRemoveRoles(): void
    {
        $client = static::createClient();
        $admin = $this->createAdminAndLogin($client);

        $email = $this->randomEmail('role-remove');
        $password = $this->randomPassword();
        $target = $this->createUserEntity($email, $password, ['ROLE_ADMIN']);

        $response = $client->request('PATCH', sprintf('/api/users/%d', $target->getId()), [
            'auth_bearer' => $admin['token'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'body' => json_encode(['roles' => ['ROLE_USER_FRONTEND']], JSON_THROW_ON_ERROR),
        ]);
        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertNotContains('ROLE_ADMIN', $data['roles'] ?? []);

        $userToken = $this->getAccessToken($client, $email, $password);
        $client->request('GET', '/api/users', [
            'auth_bearer' => $userToken,
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanCreateAdminUser(): void
    {
        $client = static::createClient();
        $admin = $this->createAdminAndLogin($client);
        $email = $this->randomEmail('admin-created');
        $password = $this->randomPassword();

        $response = $client->request('POST', '/api/users', [
            'auth_bearer' => $admin['token'],
            'json' => [
                'email' => $email,
                'plainPassword' => $password,
                'name' => 'Secondary Admin',
                'roles' => ['ROLE_ADMIN'],
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $data = $response->toArray();
        $this->assertContains('ROLE_ADMIN', $data['roles'] ?? []);

        $token = $this->getAccessToken($client, $email, $password);
        $this->assertNotEmpty($token);
    }

    public function testUserRegistrationCreatesAccount(): void
    {
        $client = static::createClient();
        $email = sprintf('user-%s@test.local', bin2hex(random_bytes(4)));

        $client->request('POST', '/api/users/register', [
            'json' => [
                'email' => $email,
                'plainPassword' => $this->randomPassword(),
                'name' => 'New User',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    public function testUserRegistrationRejectsDuplicateEmail(): void
    {
        $client = static::createClient();
        $email = sprintf('dup-%s@test.local', bin2hex(random_bytes(4)));
        $password = $this->randomPassword();

        $client->request('POST', '/api/users/register', [
            'json' => [
                'email' => $email,
                'plainPassword' => $password,
                'name' => 'Dup User',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/users/register', [
            'json' => [
                'email' => $email,
                'plainPassword' => $password,
                'name' => 'Dup User',
            ],
        ]);
        $this->assertResponseStatusCodeSame(422);
    }
}
