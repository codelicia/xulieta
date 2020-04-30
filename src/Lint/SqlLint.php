<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Lint;

use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use function count;

final class SqlLint implements Lint
{
    public function hasViolation(string $sql) : bool
    {
        $tokens = Lexer::getTokens($sql, true);
        $parser = new Parser($tokens);
        $parser->parse();

        return count($parser->errors) > 0;
    }

    public function getViolation(string $code) : string
    {
        return '';
    }
}
