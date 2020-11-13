<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Symfony\Component\Finder\SplFileInfo;

interface Parser
{
    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions(): array;

    public function supports(SplFileInfo $file): bool;

    /** @return SampleCode[] */
    public function getAllSampleCodes(SplFileInfo $file): array;
}
