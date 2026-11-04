<?php

namespace App\Tests\Entity;

use App\Entity\Gallery\Gallery;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The legacy entities rely on the Gedmo behaviors (timestamps, slug, author); they must be switched on.
 */
final class GedmoListenersTest extends KernelTestCase
{
    use ResetDatabase;

    public function testTimestampsAndSlugAreFilledOnSave(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $gallery = (new Gallery())->setName('Próba Galéria');
        $entityManager->persist($gallery);
        $entityManager->flush();

        self::assertNotNull($gallery->getInsertDate());
        self::assertSame('proba-galeria', $gallery->getSlug());
    }

    public function testAuthorIsTheLoggedInUser(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $user = (new User())->setEmail('author@example.test')->setName('Author')->setEnabled(true);
        $entityManager->persist($user);
        $entityManager->flush();

        // The Blameable listener asks the security token for the user when the entity is saved.
        $container->get('security.token_storage')->setToken(new UsernamePasswordToken($user, 'main', ['ROLE_ADMIN']));

        $gallery = (new Gallery())->setName('Szerzős galéria');
        $entityManager->persist($gallery);
        $entityManager->flush();

        self::assertSame($user, $gallery->getAuthor());
    }
}
