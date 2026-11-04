<?php

namespace App\Command;

use App\Seo\SitemapBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Requests every URL of the sitemap in this process and reports the ones that do not answer 200. The URLs of the site
 * must not change (the search engines know them), so this is run on the real data before a release.
 */
#[AsCommand(
    name: 'app:sitemap:check',
    description: 'Requests every URL of the sitemap and reports the ones that are not 200',
)]
final class SitemapCheckCommand extends Command
{
    public function __construct(
        private readonly SitemapBuilder $sitemapBuilder,
        #[Autowire(service: 'kernel')]
        private readonly KernelInterface&TerminableInterface $kernel,
        #[Autowire(service: 'services_resetter')]
        private readonly ResetInterface $servicesResetter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('section', null, InputOption::VALUE_REQUIRED, 'Only this sitemap section (events ...)')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'At most this many URLs of a file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $section = $input->getOption('section');
        $limit = $input->getOption('limit') === null ? null : (int) $input->getOption('limit');

        $checked = 0;
        $failures = [];
        foreach ($this->sitemapBuilder->files() as $file) {
            if (is_string($section) && !str_starts_with($file->name, $section)) {
                continue;
            }
            $entries = $limit === null ? $file->entries : array_slice($file->entries, 0, $limit);
            foreach ($style->progressIterate($entries) as $entry) {
                ++$checked;
                $status = $this->statusOf($entry->path);
                if ($status !== 200) {
                    $failures[] = [$status, $entry->path];
                }
            }
        }

        if ($failures !== []) {
            $style->table(['Status', 'Path'], $failures);
            $style->error(sprintf('%d of %d URLs do not answer 200.', count($failures), $checked));

            return Command::FAILURE;
        }

        $style->success(sprintf('All the %d URLs of the sitemap answer 200.', $checked));

        return Command::SUCCESS;
    }

    private function statusOf(string $path): int
    {
        $request = Request::create($path);
        $response = $this->kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, false);
        $status = $response->getStatusCode();
        $this->kernel->terminate($request, $response);
        $this->servicesResetter->reset();

        return $status;
    }
}
