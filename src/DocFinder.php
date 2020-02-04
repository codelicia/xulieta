<?php

declare(strict_types=1);

namespace Codelicia\Xulieta;

use Symfony\Component\Finder\Finder;
use function basename;
use function dirname;
use function is_dir;

final class DocFinder
{
    private string $directoryOrFile;

    public function __construct(string $directoryOrFile)
    {
        $this->directoryOrFile = $directoryOrFile;
    }

    private function getDirectory() : string
    {
        return is_dir($this->directoryOrFile) ? $this->directoryOrFile : dirname($this->directoryOrFile);
    }

    /** @return string[] */
    private function getFeatureMatch() : array
    {
        return is_dir($this->directoryOrFile) ? ['*.rst', '*.md', '*.markdown'] : [basename($this->directoryOrFile)];
    }

    public function __invoke(array $excludeDirs) : Finder
    {
        return Finder::create()
            ->files()
            ->exclude($excludeDirs)
            ->in($this->getDirectory())
            ->name($this->getFeatureMatch());
    }
}
