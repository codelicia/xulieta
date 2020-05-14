<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Parser as DoctrineRstParser;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class RstParser implements Parser
{
    private DoctrineRstParser $rstParser;

    public function __construct(?DoctrineRstParser $rstParser = null)
    {
        $this->rstParser = $rstParser ?? new DoctrineRstParser();
    }

    public function isValid(SplFileInfo $file) : bool
    {
        try {
            $this->rstParser->parse($file->getContents());
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    /** @return SampleCode[] */
    public function getAllSampleCodes(SplFileInfo $file) : array
    {
        $sampleCodes = [];
        $nodes       = $this->rstParser->parse($file->getContents())->getNodes();

        foreach ($nodes as $node) {
            if (! $node instanceof CodeNode) {
                continue;
            }

            $language = $node->getLanguage() ?? '';
            $code     = $node->getValueString();

            $sampleCodes[] = new SampleCode($file->getPathname(), $language, 0, $code);
        }

        return $sampleCodes;
    }
}
