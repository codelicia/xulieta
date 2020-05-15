<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Lint;

use Exception;
use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use function array_map;
use function count;
use function implode;
use const PHP_EOL;

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
        $tokens = Lexer::getTokens($code, true);
        $parser = new Parser($tokens);
        $parser->parse();

        return implode(PHP_EOL, array_map(
            fn (Exception $e) => $e->getMessage(),
            $parser->errors
        ));
    }
}
