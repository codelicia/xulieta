<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Plugin;

use Codelicia\Xulieta\External\Markinho;
use Codelicia\Xulieta\Lint\Lint;
use Codelicia\Xulieta\Lint\PhpLint;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use function in_array;
use const PHP_EOL;

final class PhpOnMarkdownPlugin implements Plugin
{
    private Lint $phpLint;

    public function __construct(?Lint $phpLint = null)
    {
        $this->phpLint = $phpLint ?: new PhpLint();
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions() : array
    {
        return ['markdown', 'md'];
    }

    public function canHandle(SplFileInfo $file) : bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), true);
    }

    public function __invoke(SplFileInfo $file, OutputInterface $output) : bool
    {
        foreach (Markinho::extractCodeBlocks($file->getContents()) as $block) {
            if ($block['language'] !== 'php') {
                continue;
            }

            if ($this->phpLint->hasViolation($block['code'])) {
                $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
                $output->writeln($this->phpLint->getViolation($block['code']) . PHP_EOL);
                $output->writeln($block['code']);

                return false;
            }
        }

        return true;
    }
}
