<?php

declare(strict_types=1);

namespace Codelicia\Xulieta;

use Symfony\Component\Finder\Finder;

use function array_map;
use function basename;
use function dirname;
use function is_dir;
use function sprintf;

final class DocFinder
{
    /** @psalm-var list<string> */
    private array $supportedExtensions;

    /** @psalm-param list<string> $supportedExtensions */
    public function __construct(private readonly string $directoryOrFile, array $supportedExtensions)
    {
        $this->supportedExtensions = $supportedExtensions;
    }

    private function getDirectory(): string
    {
        return is_dir($this->directoryOrFile) ? $this->directoryOrFile : dirname($this->directoryOrFile);
    }

    /** @return string[] */
    private function getFeatureMatch(): array
    {
        return is_dir($this->directoryOrFile)
            ? array_map(static fn ($x) => sprintf('*.%s', $x), $this->supportedExtensions)
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
