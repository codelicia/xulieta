<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Assert\Assert;

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
        Assert::that($outputFormatters)
            ->notEmpty();

        return current(array_filter(
            $outputFormatters,
            static fn (string $o) => $o::canResolve($outputStyle)
        ));
    }
}
