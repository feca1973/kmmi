<?php

namespace App\Search;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The words the visitor searches for: the `search_form[term]` query parameter (the legacy form), null when there is
 * none (or the parameter is not a text).
 */
class SearchTerm
{
    public function fromRequest(Request $request): ?string
    {
        try {
            $form = $request->query->all('search_form');
        } catch (BadRequestException) {
            return null;
        }

        $term = is_string($form['term'] ?? null) ? trim($form['term']) : '';

        return $term === '' ? null : $term;
    }
}
