<?php

namespace App\Controller\Admin;

use App\Menu\PageSuggester;
use App\Menu\PageSuggestion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The pages offered to the menu editor while the editor types the target of a menu item (only for the admin: the
 * firewall protects everything under /admin).
 */
final class MenuPageController extends AbstractController
{
    #[Route('/admin/menu-pages', name: 'admin_pages_for_menu', methods: ['GET'])]
    public function pages(Request $request, PageSuggester $suggester): JsonResponse
    {
        $suggestions = $suggester->suggest($request->query->getString('term'));

        return $this->json(array_map(
            static fn (PageSuggestion $page): array => [
                'title' => $page->title,
                'url' => $page->url,
                'kind' => $page->kind,
            ],
            $suggestions
        ));
    }
}
