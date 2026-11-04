<?php

namespace App\Command;

use App\Search\SearchReindexer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Rebuilds the full text search index. Run it after the database was imported from the live site and when the slug of
 * an article type or the like changed; the saving of a content updates its own row by itself.
 */
#[AsCommand(
    name: 'app:search:reindex',
    description: 'Rebuilds the full text search index from the content',
)]
final class SearchReindexCommand extends Command
{
    public function __construct(private readonly SearchReindexer $reindexer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $progress = $style->createProgressBar($this->reindexer->total());
        $progress->start();
        $indexed = $this->reindexer->rebuild($progress->advance(...));
        $progress->finish();
        $style->newLine(2);

        $style->success(sprintf('%d pages are on the search.', $indexed));

        return Command::SUCCESS;
    }
}
