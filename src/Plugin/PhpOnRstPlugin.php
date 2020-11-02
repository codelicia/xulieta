<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Plugin;

use Codelicia\Xulieta\Lint\Lint;
use Codelicia\Xulieta\Lint\PhpLint;
use Codelicia\Xulieta\Output\OutputFormatter;
use Codelicia\Xulieta\Parser\Parser;
use Codelicia\Xulieta\Parser\RstParser;
use Codelicia\Xulieta\ValueObject\Violation;
use Symfony\Component\Finder\SplFileInfo;

use function in_array;
use function preg_match;

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
    public function supportedExtensions(): array
    {
        return ['rst'];
    }

    public function canHandle(SplFileInfo $file): bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), true);
    }

    public function __invoke(SplFileInfo $file, OutputFormatter $output): bool
    {
        if (! $this->rstParser->isValid($file)) {
            $output->writeln(PHP_EOL . '<error>Error parsing file: ' . $file->getRelativePath() . '</error>');

            return false;
        }

        // @todo code is duplicated and needs to be refactored
        foreach ($this->rstParser->getAllSampleCodes($file) as $sampleCode) {
            if ($sampleCode->language() !== 'php') {
                continue;
            }

            if ($this->phpLint->hasViolation($sampleCode->code())) {
                $message = $this->phpLint->getViolation($sampleCode->code());

                preg_match('{on line (\d+)}', $message, $line);

                $validationErrorInLine = $line[1] ?? 0;

                $output->addViolation(new Violation($sampleCode, $message, (int) $validationErrorInLine));

                return false;
            }
        }

        return true;
    }
}
