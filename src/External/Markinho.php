<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\External;

use Codelicia\Xulieta\ValueObject\SampleCode;
use function array_shift;
use function explode;
use function implode;
use function rtrim;
use function trim;

final class Markinho
{
    private MarkinhoLexer $lexer;

    public function __construct()
    {
        $this->lexer = new MarkinhoLexer();
    }

    /** @return SampleCode[] */
    public function extractCodeBlocks(string $file, string $markdown) : array
    {
        $this->lexer->setInput($markdown);
        $this->lexer->moveNext();

        $sampleCode = [];
        while (true) {
            if (! $this->lexer->lookahead) {
                break;
            }

            $this->lexer->moveNext();

            if ($this->lexer->token['type'] !== MarkinhoLexer::T_CODE_BLOCK) {
                continue;
            }

            $codeSample = $this->lexer->token['value'];
            $codeSample = explode("\n", trim($codeSample, '`'));
            $language   = array_shift($codeSample);
            $codeSample = rtrim(implode("\n", $codeSample));

            $sampleCode[] = new SampleCode($file, $language, $this->lexer->token['position'], $codeSample);
        }

        return $sampleCode;
    }
}
