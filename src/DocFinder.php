<?php

declare(strict_types=1);

namespace Codelicia\Xulieta;

use Symfony\Component\Finder\Finder;

final class DocFinder
{
    private string $directoryOrFile;

    public function __construct(string $directoryOrFile)
    {
        $this->directoryOrFile = $directoryOrFile;
    }

    private function getDirectory(): string
    {
        return is_dir($this->directoryOrFile) ? $this->directoryOrFile : dirname($this->directoryOrFile);
    }

    private function getFeatureMatch(): array
    {
        return is_dir($this->directoryOrFile) ? ['*.rst', '*.md', '*.markdown'] : [basename($this->directoryOrFile)];
    }

    public function __invoke(): Finder
    {
        return Finder::create()
            ->files()
            ->in($this->getDirectory())
            ->name($this->getFeatureMatch());
    }
}
