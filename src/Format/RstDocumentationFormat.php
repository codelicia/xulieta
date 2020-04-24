<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Codelicia\Xulieta\Lint\Lint;
use Codelicia\Xulieta\Lint\PhpLint;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Parser;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function in_array;
use const PHP_EOL;

final class RstDocumentationFormat implements DocumentationFormat
{
    private Parser $rstParser;
    private Lint $phpLint;

    public function __construct(?Parser $parser = null, ?Lint $phpLint = null)
    {
        $this->rstParser = $parser ?: new Parser();
        $this->phpLint   = $phpLint ?: new PhpLint();
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions() : array
    {
        return ['rst'];
    }

    public function canHandle(SplFileInfo $file) : bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), true);
    }

    public function __invoke(SplFileInfo $file, OutputInterface $output) : bool
    {
        try {
            $documentation = $this->rstParser->parse($file->getContents());
        } catch (Throwable $e) {
            $output->writeln(PHP_EOL . '<error>Error parsing file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            return false;
        }

        foreach ($documentation->getNodes() as $node) {
            if (! ($node instanceof CodeNode) || $node->getLanguage() !== 'php') {
                continue;
            }

            $code = $node->getValueString();

            if ($this->phpLint->hasViolation($code)) {
                $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
                $output->writeln($this->phpLint->getViolation($code) . PHP_EOL);
                $output->writeln($code);

                return false;
            }
        }

        return true;
    }
}
