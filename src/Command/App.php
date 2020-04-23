<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Command;

use Codelicia\Xulieta\DocFinder;
use Codelicia\Xulieta\Format\DocumentationFormat;
use Codelicia\Xulieta\Format\MultipleDocumentationFormat;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;
use function array_map;
use function assert;
use function sprintf;

/**
 * @psalm-type TConfig = array{plugin: list<class-string<DocumentationFormat>>, exclude: list<string>}
 */
final class App extends Command
{
    private bool $errorOccurred = false;

    /** @psalm-var TConfig */
    private array $config;

    /** @psalm-param TConfig $config */
    public function __construct(?string $name = null, array $config)
    {
        parent::__construct($name);

        $this->config = $config;
    }

    protected function configure() : void
    {
        $this
            ->setName('check:erromeu')
            ->setDescription('Lint code snippets through the documentation "directory"')
            ->addArgument(
                'directory',
                InputArgument::REQUIRED,
                'Path where to find documentation files'
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

        Assert::string($directory);
        Assert::interfaceExists(DocumentationFormat::class);

        $documentFormatHandler = new MultipleDocumentationFormat(...array_map(
            static fn (string $class) => new $class(),
            $this->config['plugin']
        ));

        $finder = (new DocFinder($directory, $documentFormatHandler->supportedExtensions()))
            ->__invoke($this->config['exclude']);

        $output->writeln("\nFinding documentation files on <info>" . $directory . "</info>\n");

        foreach ($finder as $file) {
            assert($file instanceof SplFileInfo);
            if ($documentFormatHandler->canHandle($file)) {
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
