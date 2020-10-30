<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Output;

use Codelicia\Xulieta\ValueObject\Violation;

interface OutputFormatter
{
    public function addViolation(Violation $violation): void;

    public function writeln(string $text): void;
}
