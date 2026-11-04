<?php

namespace App\Tests\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\MessageDigestPasswordHasher;
use Zenstruck\Foundry\Test\ResetDatabase;

final class AdminLoginTest extends WebTestCase
{
    use ResetDatabase;

    private const PASSWORD = 'correct horse battery';

    public function testAnonymousVisitorIsRedirectedToTheLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/user');

        self::assertResponseRedirects('/admin/login');
    }

    public function testLegacyPasswordWorksAndIsUpgradedOnFirstLogin(): void
    {
        $client = static::createClient();
        $this->createUser('legacy@example.test', ['ROLE_ADMIN'], self::PASSWORD);

        $data = $this->login($client, 'legacy@example.test', self::PASSWORD);

        self::assertResponseIsSuccessful();
        self::assertTrue($data['success']);
        self::assertSame('/admin', parse_url($data['forwardUrl'], PHP_URL_PATH));

        $user = $this->findUser('legacy@example.test');
        self::assertNull($user->getSalt(), 'The legacy salt is cleared.');
        self::assertStringStartsWith('$', $user->getPassword(), 'The legacy hash is replaced with a modern one.');
        self::assertNotNull($user->getLastLogin());

        // The upgraded hash works too (new session, new client).
        self::ensureKernelShutdown();
        $second = static::createClient();
        $this->login($second, 'legacy@example.test', self::PASSWORD);
        self::assertResponseIsSuccessful();
    }

    public function testLoginByUsernameIsCaseInsensitive(): void
    {
        $client = static::createClient();
        $this->createUser('Mixed.Case@Example.test', ['ROLE_ADMIN'], self::PASSWORD);

        $this->login($client, 'MIXED.case@example.TEST', self::PASSWORD);

        self::assertResponseIsSuccessful();
    }

    public function testWrongPasswordIsRejected(): void
    {
        $client = static::createClient();
        $this->createUser('wrong@example.test', ['ROLE_ADMIN'], self::PASSWORD);

        $this->login($client, 'wrong@example.test', 'not the password');

        self::assertResponseStatusCodeSame(403);
        $user = $this->findUser('wrong@example.test');
        self::assertNotNull($user->getSalt(), 'A failed login does not touch the stored hash.');
    }

    public function testDisabledUserCannotLogIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser('disabled@example.test', ['ROLE_ADMIN'], self::PASSWORD);
        $user->setEnabled(false);
        $this->entityManager()->flush();

        $this->login($client, 'disabled@example.test', self::PASSWORD);

        self::assertResponseStatusCodeSame(403);
    }

    public function testUserWithoutAdminRoleCannotOpenTheAdmin(): void
    {
        $client = static::createClient();
        $user = $this->createUser('plain@example.test', ['ROLE_USER']);

        $client->loginUser($user);
        $client->request('GET', '/admin/user');

        self::assertResponseStatusCodeSame(403);
    }

    public function testSuperAdminInheritsTheAdminRole(): void
    {
        $client = static::createClient();
        $user = $this->createUser('super@example.test', ['ROLE_SUPER_ADMIN']);

        $client->loginUser($user);
        $client->request('GET', '/admin/user');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Felhasználók listája');
    }

    /**
     * @return array<string, mixed>
     */
    private function login(KernelBrowser $client, string $username, string $password): array
    {
        $crawler = $client->request('GET', '/admin/login');
        $token = $crawler->filter('input[name=_csrf_token]')->attr('value');

        $client->request('POST', '/admin/login_check', [
            '_username' => $username,
            '_password' => $password,
            '_csrf_token' => $token,
        ]);

        return json_decode((string) $client->getResponse()->getContent(), true) ?? [];
    }

    /**
     * @param list<string> $roles
     * @param string|null $legacyPassword when given, the password is stored as the legacy FOSUserBundle did
     */
    private function createUser(string $email, array $roles, ?string $legacyPassword = null): User
    {
        $user = (new User())->setEmail($email)->setName('Test User')->setEnabled(true)->setRoles($roles);

        if ($legacyPassword !== null) {
            // sha512 with salt, 5000 iterations, base64.
            $salt = 'legacy-salt-' . bin2hex(random_bytes(8));
            $user->setSalt($salt)->setPassword(
                (new MessageDigestPasswordHasher('sha512', true, 5000))->hash($legacyPassword, $salt)
            );
        }

        $entityManager = $this->entityManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function findUser(string $email): User
    {
        $entityManager = $this->entityManager();
        $entityManager->clear();

        return $entityManager->getRepository(User::class)->findOneBy(['emailCanonical' => mb_strtolower($email)])
            ?? self::fail('User not found.');
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
