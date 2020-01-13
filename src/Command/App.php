<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Command;

use Codelicia\Xulieta\DocFinder;
use Codelicia\Xulieta\Format\RstDocumentationFormat;
use Doctrine\RST\Nodes\CodeNode;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use function preg_match;
use function sprintf;
use const PHP_EOL;

final class App extends Command
{
    private bool $errorOccurred = false;

    /** {@inheritDoc} */
    protected function configure(): void
    {
        $this
            ->setName('check:erromeu')
            ->setDescription('Lint php code snippets through the documentation "directory"')
            ->addArgument(
                'directory',
                InputArgument::REQUIRED,
                'Path to find *.rst and *.md files'
            );
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $directory = $input->getArgument('directory');
        $finder    = (new DocFinder($directory))->__invoke();

        $output->writeln("\nFinding documentation files on <info>" . $directory . "</info>\n");

        /* @var $file \Symfony\Component\Finder\SplFileInfo */
        foreach ($finder as $file) {
            $rst = new RstDocumentationFormat();

            if ($rst->canHandler($file)) {
                $rst($file, $output);
            } else {
                $output->writeln(sprintf('<error>Could not handle file "%s"</error>', $file->getFilename()));
                $this->signalizeError();
            }
        }

        if ($this->errorOccurred) {
            return 1;
        }

        $output->writeln('<bg=green;fg=white>     Everything is OK!     </>');

        return 0;
    }

    private function signalizeError() : void
    {
        $this->errorOccurred = true;
    }
}
