<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Codelicia\Xulieta\External\Markinho;
use Codelicia\Xulieta\ValueObject\SampleCode;
use Symfony\Component\Finder\SplFileInfo;

class MarkdownParser implements Parser
{
    public function isValid(SplFileInfo $file) : bool
    {
        return true;
    }

    /** @return SampleCode[] */
    public function getAllSampleCodes(SplFileInfo $file) : array
    {
        $sampleCodes = [];
        $blocks      = Markinho::extractCodeBlocks($file->getContents());

        foreach ($blocks as $block) {
            if ($block['language'] !== 'php') {
                continue;
            }

            $sampleCodes[] = new SampleCode($block['language'], $block['code']);
        }

        return $sampleCodes;
    }
}
