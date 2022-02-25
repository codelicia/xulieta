<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

final class Violation
{
    private SampleCode $code;
    private string $message;
    private int $violationLine;
    private string $validatedBy;

    public function __construct(
        SampleCode $code,
        string $message,
        int $violationLine = 0,
        string $validatedBy = ''
    ) {
        $this->code          = $code;
        $this->message       = $message;
        $this->violationLine = $violationLine;
        $this->validatedBy   = $validatedBy;
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
