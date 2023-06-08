<?php

declare(strict_types=1);

namespace Codelicia\Xulieta;

use Psl;
use Symfony\Component\Finder\Finder;

use function basename;
use function dirname;
use function is_dir;

final class DocFinder
{
    /** @psalm-param list<string> $supportedExtensions */
    public function __construct(private readonly string $directoryOrFile, private array $supportedExtensions)
    {
    }

    private function getDirectory(): string
    {
        return is_dir($this->directoryOrFile) ? $this->directoryOrFile : dirname($this->directoryOrFile);
    }

    /** @psalm-return list<string> */
    private function getFeatureMatch(): array
    {
        return is_dir($this->directoryOrFile)
            ? Psl\Vec\map($this->supportedExtensions, static fn ($x) => Psl\Str\format('*.%s', $x))
            : [basename($this->directoryOrFile)];
    }

    /** @psalm-param list<string> $excludeDirs */
    public function __invoke(array $excludeDirs): Finder
    {
        return Finder::create()
            ->files()
            ->exclude($excludeDirs)
            ->in($this->getDirectory())
            ->name($this->getFeatureMatch());
    }
}
