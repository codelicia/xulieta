<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Validator;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Codelicia\Xulieta\ValueObject\Violation;

interface Validator
{
    public function supports(SampleCode $sampleCode): bool;

    public function hasViolation(SampleCode $sampleCode): bool;

    public function getViolation(SampleCode $sampleCode): Violation;
}
