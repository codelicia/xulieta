<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Symfony\Component\Finder\SplFileInfo;

use function array_pop;
use function array_shift;
use function count;
use function explode;
use function implode;
use function in_array;
use function ltrim;
use function preg_match;
use function preg_split;

use const PREG_SPLIT_DELIM_CAPTURE;

final class MarkdownParser implements Parser
{
    private const PATTERN = '/\n?(`{3}\w*\n[\S\s]+?\n`{3})\n/';

    /**
     * @return string[]
     * @psalm-return list{'markdown', 'md'}
     */
    public function supportedExtensions(): array
    {
        return ['markdown', 'md'];
    }

    public function supports(SplFileInfo $file): bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), false);
    }

    /**
     * @return SampleCode[]
     * @psalm-return list<Codelicia\Xulieta\ValueObject\SampleCode>
     */
    public function getAllSampleCodes(SplFileInfo $file): array
    {
        $sampleCode    = [];
        $chunks        = preg_split(self::PATTERN, $file->getContents(), -1, PREG_SPLIT_DELIM_CAPTURE);
        $startPosition = 0;
        $endPosition   = 0;

        foreach ($chunks as $documentChunk) {
            $lines        = explode("\n", $documentChunk);
            $endPosition += count($lines);

            preg_match('/^```/', $lines[0] ?? '', $matches);

            if ($matches === []) {
                $startPosition = $endPosition;

                continue;
            }

            $languageDeclaration = array_shift($lines);
            $language            = ltrim($languageDeclaration, '`');

            array_pop($lines);

            $codeBlock = implode("\n", $lines);

            $sampleCode[] = new SampleCode($file->getPathname(), $language, $startPosition, $codeBlock);

            $startPosition = $endPosition;
        }

        return $sampleCode;
    }
}
