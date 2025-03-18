<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Psl;

use function current;

/**
 * @psalm-suppress UnusedClass
 * @psalm-suppress PossiblyFalseArgument
 * @psalm-suppress FalsableReturnStatement
 */
final class OutputFilter
{
    /**
     * @psalm-param class-string<OutputFormatter> $outputFormatters
     *
     * @return class-string<OutputFormatter>|false
     * @psalm-return class-string<OutputFormatter>
     */
    public function __invoke(string $outputStyle, string ...$outputFormatters): string|false
    {
        Psl\invariant($outputFormatters !== [], 'At least one output formatter should be provided.');

        return current(Psl\Vec\filter(
            $outputFormatters,
            /** @param class-string<OutputFormatter> $o */
            static fn (string $o) => $o::canResolve($outputStyle),
        ));
    }
}
