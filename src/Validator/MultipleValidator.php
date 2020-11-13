<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Validator;

use Assert\Assert;
use Codelicia\Xulieta\ValueObject\SampleCode;
use Codelicia\Xulieta\ValueObject\Violation;
use LogicException;

use function array_map;
use function array_merge_recursive;
use function array_values;

final class MultipleValidator implements Validator
{
    /** @var Validator[] */
    private array $validator;

    public function __construct(Validator ...$validator)
    {
        Assert::that($validator)
            ->notEmpty();

        $this->validator = $validator;
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedLanguage(): array
    {
        return array_values(array_merge_recursive([], ...array_map(
            static fn (Validator $validator) => $validator->supportedLanguage(),
            $this->validator
        )));
    }

    public function hasViolation(SampleCode $sampleCode): bool
    {
        foreach ($this->validator as $validator) {
            if ($validator->hasViolation($sampleCode)) {
                return true;
            }
        }

        return false;
    }

    public function getViolation(SampleCode $sampleCode): Violation
    {
        foreach ($this->validator as $validator) {
            if ($validator->hasViolation($sampleCode)) {
                return $validator->getViolation($sampleCode);
            }
        }

        throw new LogicException();
    }
}
