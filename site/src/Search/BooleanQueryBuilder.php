<?php

namespace App\Search;

/**
 * The boolean mode query of the MySQL full text search from the words a visitor typed: every word must be found, as
 * the beginning of a word (Hungarian words have many endings). The operators of the boolean mode (+ - * " ( ) ...) are
 * not letters, so they cannot get into the query.
 */
class BooleanQueryBuilder
{
    /** The words of the index are at least this long (innodb_ft_min_token_size); a shorter word never matches. */
    private const MIN_WORD_LENGTH = 3;
    private const MAX_WORDS = 10;

    /**
     * Null when there is nothing to search for.
     */
    public function build(string $term): ?string
    {
        $words = $this->words($term);

        if ($words === []) {
            return null;
        }

        return implode(' ', array_map(static fn (string $word): string => '+' . $word . '*', $words));
    }

    /**
     * @return list<string>
     */
    private function words(string $term): array
    {
        $letters = mb_strtolower((string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', $term));
        $words = array_values(array_unique(preg_split('/\s+/', trim($letters), -1, PREG_SPLIT_NO_EMPTY) ?: []));

        // The short words ("és", "a") are no use: left out, unless the visitor typed nothing else.
        $long = array_values(array_filter(
            $words,
            static fn (string $word): bool => mb_strlen($word) >= self::MIN_WORD_LENGTH
        ));
        $words = $long !== [] ? $long : array_values(array_filter(
            $words,
            static fn (string $word): bool => mb_strlen($word) >= 2
        ));

        return array_slice($words, 0, self::MAX_WORDS);
    }
}
