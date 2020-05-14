<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\External;

use Doctrine\Common\Lexer\AbstractLexer;
use function strpos;

final class MarkinhoLexer extends AbstractLexer
{
    private const PATTERN = '`{3}([\w]*)\n([\S\s]+?)\n\`{3}';

    public const T_CODE_BLOCK = 1;

    protected function getCatchablePatterns() : array
    {
        return [self::PATTERN];
    }

    protected function getNonCatchablePatterns() : array
    {
        return [];
    }

    protected function getType(&$value) : ?int
    {
        if (strpos($value, '```') === 0) {
            return self::T_CODE_BLOCK;
        }

        return null;
    }
}
