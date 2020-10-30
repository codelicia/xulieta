<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\External;

use Codelicia\Xulieta\ValueObject\SampleCode;

use function array_pop;
use function array_shift;
use function count;
use function explode;
use function implode;
use function ltrim;
use function preg_match;
use function preg_split;

use const PREG_SPLIT_DELIM_CAPTURE;

final class Markinho
{
    private const PATTERN = '/\n?(`{3}[\w]*\n[\S\s]+?\n\`{3})\n/';

    private function __construct()
    {
    }

    /** @return SampleCode[] */
    public static function extractCodeBlocks(string $file, string $markdown): array
    {
        $sampleCode    = [];
        $chunks        = preg_split(self::PATTERN, $markdown, -1, PREG_SPLIT_DELIM_CAPTURE);
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

            $sampleCode[] = new SampleCode($file, $language, $startPosition, $codeBlock);

            $startPosition = $endPosition;
        }

        return $sampleCode;
    }
}
