<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Psl;
use Symfony\Component\Finder\SplFileInfo;

use function array_merge_recursive;
use function in_array;

final class MultipleParser implements Parser
{
    /** @var Parser[] */
    private array $parsers;

    public function __construct(Parser ...$parsers)
    {
        Psl\invariant($parsers !== [], 'At least one parser must be provided');

        $this->parsers = $parsers;
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions(): array
    {
        return Psl\Vec\values(array_merge_recursive([], ...Psl\Vec\map(
            $this->parsers,
            static fn (Parser $parser) => $parser->supportedExtensions(),
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
