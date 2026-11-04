<?php

namespace App\Tests\Twig;

use App\Twig\Components\MainMenu;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * The mega menu keeps the public URLs of the live site: every link of it must be a route of the site.
 */
final class MainMenuTest extends KernelTestCase
{
    public function testEveryLinkOfTheMegaMenuIsARoute(): void
    {
        self::bootKernel();
        $router = self::getContainer()->get(RouterInterface::class);
        $menu = new MainMenu(self::getContainer()->get('doctrine')->getRepository(\App\Entity\Menu\MenuItem::class));

        $urls = [];
        foreach ($menu->getDataMenu() as $column) {
            foreach ($column['links'] as $link) {
                $urls = [...$urls, ...($link['links'] ?? [$link])];
            }
        }

        self::assertGreaterThan(25, count($urls));
        foreach ($urls as $link) {
            try {
                $router->match($link['url']);
            } catch (ResourceNotFoundException) {
                self::fail(sprintf('The menu link "%s" (%s) is not a route.', $link['label'], $link['url']));
            }
        }
    }
}
