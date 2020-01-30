<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Command;

use Codelicia\Xulieta\DocFinder;
use Codelicia\Xulieta\Format\MarkdownDocumentationFormat;
use Codelicia\Xulieta\Format\MultipleDocumentationFormat;
use Codelicia\Xulieta\Format\RstDocumentationFormat;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use function assert;
use function sprintf;

final class App extends Command
{
    private bool $errorOccurred = false;

    protected function configure() : void
    {
        $this
            ->setName('check:erromeu')
            ->setDescription('Lint php code snippets through the documentation "directory"')
            ->addArgument(
                'directory',
                InputArgument::REQUIRED,
                'Path to find documentation files'
            );
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $directory = $input->getArgument('directory');
        $finder    = (new DocFinder($directory))->__invoke();

        $output->writeln("\nFinding documentation files on <info>" . $directory . "</info>\n");

        $documentFormatHandler = new MultipleDocumentationFormat(
            new RstDocumentationFormat(),
            new MarkdownDocumentationFormat()
        );

        foreach ($finder as $file) {
            assert($file instanceof SplFileInfo);
            if ($documentFormatHandler->canHandler($file)) {
                if ($documentFormatHandler($file, $output) === false) {
                    $this->signalizeError();
                }
            } else {
                $output->writeln(sprintf('<error>Could not handle file "%s"</error>', $file->getFilename()));
                $this->signalizeError();
            }
        }

        $output->writeln('');
        if ($this->errorOccurred) {
            $output->writeln('<bg=red;fg=white>     Operation failed!     </>');

            return 1;
        }

        $output->writeln('<bg=green;fg=black>     Everything is OK!     </>');

        return 0;
    }

    private function signalizeError() : void
    {
        $this->errorOccurred = true;
    }
}
