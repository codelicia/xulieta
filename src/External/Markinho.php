<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\External;

use function preg_match_all;

final class Markinho
{
    private const PATTERN = '/`{3}([\w]*)\n([\S\s]+?)\n`{3}/';

    private function __construct()
    {
    }

    /** @return array<array-key, array{code: string, language: string}> */
    public static function extractCodeBlocks(string $markdown) : array
    {
        preg_match_all(self::PATTERN, $markdown, $matches);

        $blocks = [];

        foreach ($matches[1] as $index => $language) {
            $blocks[$index]['language'] = $language;
        }

        foreach ($matches[2] as $index => $code) {
            $blocks[$index]['code'] = $code;
        }

        return $blocks;
    }
}
