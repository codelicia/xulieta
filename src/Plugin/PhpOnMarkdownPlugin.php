<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Plugin;

use Codelicia\Xulieta\External\Markinho;
use Codelicia\Xulieta\Lint\Lint;
use Codelicia\Xulieta\Lint\PhpLint;
use Codelicia\Xulieta\Output\OutputFormatter;
use Codelicia\Xulieta\ValueObject\Violation;
use Symfony\Component\Finder\SplFileInfo;

use function in_array;
use function preg_match;

final class PhpOnMarkdownPlugin implements Plugin
{
    private Lint $phpLint;

    public function __construct(?Lint $phpLint = null)
    {
        $this->phpLint = $phpLint ?: new PhpLint();
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions(): array
    {
        return ['markdown', 'md'];
    }

    public function canHandle(SplFileInfo $file): bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), true);
    }

    public function __invoke(SplFileInfo $file, OutputFormatter $output): bool
    {
        foreach (Markinho::extractCodeBlocks($file->getPathname(), $file->getContents()) as $codeBlock) {
            if ($codeBlock->language() !== 'php') {
                continue;
            }

            if ($this->phpLint->hasViolation($codeBlock->code())) {
                $message = $this->phpLint->getViolation($codeBlock->code());

                preg_match('{on line (\d+)}', $message, $line);

                $validationErrorInLine = $line[1] ?? 0;

                $output->addViolation(new Violation($codeBlock, $message, (int) $validationErrorInLine));

                return false;
            }
        }

        return true;
    }
}
