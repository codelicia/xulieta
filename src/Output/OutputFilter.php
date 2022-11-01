<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Psl;

use function array_filter;
use function current;

final class OutputFilter
{
    /**
     * @psalm-param class-string<OutputFormatter> $outputFormatters
     *
     * @psalm-return class-string<OutputFormatter>
     */
    public function __invoke(string $outputStyle, string ...$outputFormatters): string
    {
        Psl\invariant($outputFormatters !== [], 'At least one output formatter should be provided.');

        return current(array_filter(
            $outputFormatters,
            /** @param class-string<OutputFormatter> $o */
            static fn (string $o) => $o::canResolve($outputStyle)
        ));
    }
}
