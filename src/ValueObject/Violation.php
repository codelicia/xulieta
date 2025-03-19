<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

/**
* @psalm-suppress UnusedClass
 * @psalm-immutable
 */
final readonly class Violation
{
    public function __construct(
        public SampleCode $code,
        public string $message,
        public int $violationLine = 0,
        public string $validatedBy = '',
    ) {
    }

    public function absoluteLine(): int
    {
        return $this->code->position + $this->violationLine;
    }
}
