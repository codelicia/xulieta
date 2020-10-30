<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

final class Violation
{
    private SampleCode $code;
    private string $message;
    private int $violationLine;

    public function __construct(
        SampleCode $code,
        string $message,
        int $violationLine = 0
    ) {
        $this->code          = $code;
        $this->message       = $message;
        $this->violationLine = $violationLine;
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

    public function absoluteLine(): int
    {
        return $this->code()->position() + $this->violationLine();
    }
}
