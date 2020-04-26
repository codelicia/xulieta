<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Plugin;

use Codelicia\Xulieta\Lint\Lint;
use Codelicia\Xulieta\Lint\PhpLint;
use Codelicia\Xulieta\Parser\Parser;
use Codelicia\Xulieta\Parser\RstParser;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use function in_array;
use const PHP_EOL;

final class PhpOnRstPlugin implements Plugin
{
    private Parser $rstParser;
    private Lint $phpLint;

    public function __construct(?Parser $parser = null, ?Lint $phpLint = null)
    {
        $this->rstParser = $parser ?: new RstParser();
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
        if (! $this->rstParser->isValid($file)) {
            $output->writeln(PHP_EOL . '<error>Error parsing file: ' . $file->getRealPath() . '</error>');

            return false;
        }

        foreach ($this->rstParser->getAllSampleCodes($file) as $sampleCode) {
            if ($sampleCode->language() !== 'php') {
                continue;
            }

            if ($this->phpLint->hasViolation($sampleCode->code())) {
                $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
                $output->writeln($this->phpLint->getViolation($sampleCode->code()) . PHP_EOL);
                $output->writeln($sampleCode->code());

                return false;
            }
        }

        return true;
    }
}
