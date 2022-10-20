<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Assert\Assert;
use Symfony\Component\Finder\SplFileInfo;

use function array_map;
use function array_merge_recursive;
use function array_values;
use function in_array;

final class MultipleParser implements Parser
{
    /** @var Parser[] */
    private array $parsers;

    public function __construct(Parser ...$parsers)
    {
        Assert::that($parsers)
            ->notEmpty();

        $this->parsers = $parsers;
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions(): array
    {
        return array_values(array_merge_recursive([], ...array_map(
            static fn (Parser $parser) => $parser->supportedExtensions(),
            $this->parsers,
        )));
    }

    public function supports(SplFileInfo $file): bool
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($file)) {
                return true;
            }
        }

        return false;
    }

    public function getAllSampleCodes(SplFileInfo $file): array
    {
        foreach ($this->parsers as $parser) {
            if (in_array($file->getExtension(), $parser->supportedExtensions())) {
                return $parser->getAllSampleCodes($file);
            }
        }

        return [];
    }
}
