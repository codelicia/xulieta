<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Validator;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Codelicia\Xulieta\ValueObject\Violation;
use LogicException;
use Override;
use Psl;

use function array_any;

/** @psalm-suppress UnusedClass */
final class MultipleValidator implements Validator
{
    /** @var Validator[] */
    private array $validators;

    public function __construct(Validator ...$validators)
    {
        Psl\invariant($validators !== [], 'At least one validator should me provided.');

        $this->validators = $validators;
    }

    #[Override]
    public function supports(SampleCode $sampleCode): bool
    {
        return array_any($this->validators, static fn ($validators) => $validators->supports($sampleCode));
    }

    #[Override]
    public function hasViolation(SampleCode $sampleCode): bool
    {
        return array_any(
            $this->validators,
            static fn ($validators) => $validators->supports($sampleCode)
                && $validators->hasViolation($sampleCode),
        );
    }

    #[Override]
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
