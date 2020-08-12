<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Lint;

use LogicException;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use Throwable;
use function preg_match;
use const PHP_EOL;

class PhpLint implements Lint
{
    private PhpParser $phpParser;

    public function __construct(?PhpParser $phpParser = null)
    {
        $this->phpParser = $phpParser ?? (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    public function hasViolation(string $code) : bool
    {
        try {
            $this->phpParser->parse(
                $this->ensureCodePrefix($code)
            );
        } catch (Throwable $e) {
            return true;
        }

        return false;
    }

    public function getViolation(string $code) : string
    {
        try {
            $this->phpParser->parse(
                $this->ensureCodePrefix($code)
            );
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        throw new LogicException();
    }

    private function ensureCodePrefix(string $code) : string
    {
        if (! preg_match('/<\?php/i', $code)) {
            return '<?php ' . PHP_EOL . $code;
        }

        return $code;
    }
}
