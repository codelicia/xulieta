<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\ValueObject;

final readonly class SampleCode
{
    public function __construct(
        public string $file,
        public string $language,
        public int $position,
        public string $code,
    ) {
    }
}
