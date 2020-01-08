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

        $phpParser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        /* @var $file \Symfony\Component\Finder\SplFileInfo */
        foreach ($finder as $file) {
        $rst = new RstDocumentationFormat();

        if ($rst->canHandler($file)) {
            $rst($file, $output);
        } else {
            echo "could not handle file: " . $file->getFilename();
        }

        //            try {
//                foreach ($documentation->getNodes() as $node) {
//                    if ($node instanceof CodeNode && $node->getLanguage() === 'php') {
//
//                        // FIXME: missing open php tag
//                        if (! preg_match('/\<\?php/i', $node->getValueString())) {
//                            $output->writeln('<error>Snippet missing PHP open tag on file: ' . $file->getRealPath() . '</error>');
//                            continue;
//                        }
//                        $phpParser->parse($node->getValueString());
//                    }
//                }
//            } catch (Throwable $e) {
//
//                $this->signalizeError();
//
//                $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
//                $output->writeln($e->getMessage() . PHP_EOL);
//            }
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
