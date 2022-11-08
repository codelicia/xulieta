<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

/** @psalm-immutable */
final class Violation
{
    public function __construct(
        private readonly SampleCode $code,
        private readonly string $message,
        private readonly int $violationLine = 0,
        private readonly string $validatedBy = '',
    ) {
    }

    public function code(): SampleCode
    {
        return $this->code;
    }

    public function file(): string
    {
        return $this->code->file();
    }

    public function violationLine(): int
    {
        return $this->violationLine;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function validatedBy(): string
    {
        return $this->validatedBy;
    }

    public function absoluteLine(): int
    {
        return $this->code()->position() + $this->violationLine();
    }
}
