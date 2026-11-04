<?php

namespace App\Tests\Entity;

use App\Entity\Menu\MenuItem;
use PHPUnit\Framework\TestCase;

final class MenuItemTest extends TestCase
{
    public function testTheUrlLinksTheItemToACustomUrl(): void
    {
        $item = (new MenuItem())->setUrl(' /cikkek/hirek ');

        self::assertSame(MenuItem::CUSTOM_URL, $item->getConnectedType());
        self::assertSame('/cikkek/hirek', $item->getConnectedValue());
        self::assertSame('/cikkek/hirek', $item->getUrl());
    }

    public function testAnEmptyUrlClearsTheLink(): void
    {
        $item = (new MenuItem())->setUrl('/rendezvenyek');

        foreach ([null, '', '   '] as $empty) {
            $item->setUrl('/rendezvenyek')->setUrl($empty);

            self::assertNull($item->getConnectedType());
            self::assertNull($item->getConnectedValue());
            self::assertNull($item->getUrl());
        }
    }

    public function testOnlyACustomUrlIsTheUrlOfTheItem(): void
    {
        $item = (new MenuItem())->setConnectedType('article')->setConnectedValue('12');

        self::assertNull($item->getUrl());
    }
}
