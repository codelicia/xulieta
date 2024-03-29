<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Validator;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Codelicia\Xulieta\ValueObject\Violation;
use LogicException;
use Psl;

final class MultipleValidator implements Validator
{
    /** @var Validator[] */
    private array $validators;

    public function __construct(Validator ...$validators)
    {
        Psl\invariant($validators !== [], 'At least one validator should me provided.');

        $this->validators = $validators;
    }

    public function supports(SampleCode $sampleCode): bool
    {
        foreach ($this->validators as $validators) {
            if ($validators->supports($sampleCode)) {
                return true;
            }
        }

        return false;
    }

    public function hasViolation(SampleCode $sampleCode): bool
    {
        foreach ($this->validators as $validators) {
            if ($validators->supports($sampleCode) && $validators->hasViolation($sampleCode)) {
                return true;
            }
        }

        return false;
    }

    public function getViolation(SampleCode $sampleCode): Violation
    {
        foreach ($this->validators as $validators) {
            if ($validators->supports($sampleCode) && $validators->hasViolation($sampleCode)) {
                return $validators->getViolation($sampleCode);
            }
        }

        throw new LogicException();
    }
}
