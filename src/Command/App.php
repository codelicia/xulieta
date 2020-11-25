<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Command;

use Codelicia\Xulieta\DocFinder;
use Codelicia\Xulieta\Output\OutputFilter;
use Codelicia\Xulieta\Output\OutputFormatter;
use Codelicia\Xulieta\Parser\MultipleParser;
use Codelicia\Xulieta\Parser\Parser;
use Codelicia\Xulieta\Validator\MultipleValidator;
use Codelicia\Xulieta\Validator\Validator;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

use function array_map;
use function array_merge;
use function assert;
use function interface_exists;
use function sprintf;

/**
 * @psalm-type TConfig = array{
 *   excludes: list<string>,
 *   outputFormatters: list<class-string<OutputFormatter>>,
 *   parsers: list<class-string<Parser>>,
 *   validators: list<class-string<Validator>>
 * }
 */
final class App extends Command
{
    private bool $errorOccurred = false;

    /** @psalm-var TConfig */
    private array $config;

    /** @psalm-param TConfig $config */
    public function __construct(?string $name = null, array $config)
    {
        interface_exists(OutputFormatter::class);

        Command::__construct($name);

        $this->config = $config;
    }

    protected function configure(): void
    {
        $this
            ->setName('check:erromeu')
            ->setDescription('Lint code snippets through the documentation "directory"')
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Specify output format, it can be "checkstyle" or "stdout"',
                'stdout'
            )
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory    = $input->getArgument('directory');
        $outputOption = $input->getOption('output');

        $output->writeln(
            array_merge(['Loaded OutputFormatters:'], $this->config['outputFormatters']),
            OutputInterface::VERBOSITY_VERBOSE
        );

        Assert::string($outputOption);

        $outputInference = (new OutputFilter())
            ->__invoke($outputOption, ...$this->config['outputFormatters']);

        $outputFormatter = new $outputInference($output);

        Assert::string($directory);
        Assert::interfaceExists(Parser::class);

        $output->writeln(
            array_merge(['', 'Loaded Parsers:'], $this->config['parsers']),
            OutputInterface::VERBOSITY_VERBOSE
        );

        $parserHandler = new MultipleParser(...array_map(
            static fn (string $class): Parser => new $class(),
            $this->config['parsers']
        ));

        $output->writeln(
            array_merge(['', 'Loaded Validators:'], $this->config['validators']),
            OutputInterface::VERBOSITY_VERBOSE
        );

        $validatorHandler = new MultipleValidator(...array_map(
            static fn (string $class): Validator => new $class(),
            $this->config['validators']
        ));

        $finder = (new DocFinder($directory, $parserHandler->supportedExtensions()))
            ->__invoke($this->config['excludes']);

        $outputFormatter->writeln("\nFinding documentation files on <info>" . $directory . "</info>\n");

        foreach ($finder as $file) {
            assert($file instanceof SplFileInfo);

            try {
                $allSampleCodes = $parserHandler->getAllSampleCodes($file);
            } catch (LogicException $e) {
                $outputFormatter->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                $this->signalizeError();
                continue;
            }

            foreach ($allSampleCodes as $sampleCode) {
                if (! $validatorHandler->hasViolation($sampleCode)) {
                    continue;
                }

                $outputFormatter->addViolation($validatorHandler->getViolation($sampleCode));
                $this->signalizeError();
            }
        }

        $outputFormatter->writeln('');
        if ($this->errorOccurred) {
            $outputFormatter->writeln('<bg=red;fg=white>     Operation failed!     </>');

            return 1;
        }

        $outputFormatter->writeln('<bg=green;fg=black>     Everything is OK!     </>');

        return 0;
    }

    private function signalizeError(): void
    {
        $this->errorOccurred = true;
    }
}
