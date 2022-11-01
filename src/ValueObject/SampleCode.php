<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

final class SampleCode
{
    public function __construct(
        private readonly string $file,
        private readonly string $language,
        private readonly int $position,
        private readonly string $code,
    ) {
    }

    public function file(): string
    {
        return $this->file;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function language(): string
    {
        return $this->language;
    }
}
