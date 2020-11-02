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

    public function hasViolation(string $code): bool
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

    public function getViolation(string $code): string
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

    private function ensureCodePrefix(string $code): string
    {
        // @fixme: it invalidates the other scenario where
        //         a php code is embedded into a html template
        $lines = explode("\n", $code);
        $lines = array_map("array_filter", [$lines])[0];
        $isPhpTagFound = false;

        // @fixme: purely experimental
        foreach ($lines as $line) {
            $isPhpTagFound = str_starts_with(trim($line), '<?php') || $isPhpTagFound;
        }

        if (! $isPhpTagFound) {
            return '<?php ' . PHP_EOL . $code;
        }

        return $code;
    }
}
