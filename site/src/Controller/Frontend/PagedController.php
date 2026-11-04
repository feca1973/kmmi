<?php

namespace App\Controller\Frontend;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base of the controllers with paged lists: the page is the `page` query parameter.
 */
abstract class PagedController extends AbstractController
{
    protected const PAGE_SIZE = 10;

    public function __construct(private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * @param Query<int, object>|list<mixed> $target a query, or the items themselves (the ids of the hits of a search)
     *
     * @return PaginationInterface<int, mixed>
     */
    protected function paginate(
        Query|array $target,
        Request $request,
        int $limit = self::PAGE_SIZE,
    ): PaginationInterface {
        // A page that is not a number (?page=abc) is the first page, not an error.
        $page = $request->query->filter('page', 1, FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $this->paginator->paginate($target, max(1, (int) $page), $limit);
    }
}
