<?php

namespace App\Tests\Support;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * For the WebTestCase classes that test the admin: a browser that is logged in as an administrator.
 */
trait AdminClientTrait
{
    private function adminClient(): KernelBrowser
    {
        $client = static::createClient();
        $user = (new User())->setEmail('admin-' . bin2hex(random_bytes(3)) . '@example.test')
            ->setName('Test Admin')->setEnabled(true)->setRoles(['ROLE_ADMIN']);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);

        return $client;
    }
}
