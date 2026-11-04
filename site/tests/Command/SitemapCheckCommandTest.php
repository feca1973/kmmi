<?php

namespace App\Tests\Command;

use App\Tests\Support\PublicSiteFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\ResetDatabase;

final class SitemapCheckCommandTest extends KernelTestCase
{
    use ResetDatabase;

    public function testEveryUrlOfTheSitemapAnswers(): void
    {
        $kernel = self::bootKernel();
        (new PublicSiteFixtures(self::getContainer()->get(EntityManagerInterface::class)))->create();

        $tester = new CommandTester((new Application($kernel))->find('app:sitemap:check'));
        $status = $tester->execute([]);

        self::assertSame(0, $status, $tester->getDisplay());
        self::assertStringContainsString('All the 33 URLs of the sitemap answer 200.', $tester->getDisplay());
    }

    public function testASectionAndALimitNarrowTheCheck(): void
    {
        $kernel = self::bootKernel();
        (new PublicSiteFixtures(self::getContainer()->get(EntityManagerInterface::class)))->create();

        $tester = new CommandTester((new Application($kernel))->find('app:sitemap:check'));
        $tester->execute(['--section' => 'events', '--limit' => '2']);

        self::assertStringContainsString('All the 2 URLs of the sitemap answer 200.', $tester->getDisplay());
    }
}
