<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Lint;

interface Lint
{
    public function hasViolation(string $code): bool;

    public function getViolation(string $code): string;
}
