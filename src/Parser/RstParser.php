<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Parser as DoctrineRstParser;
use LogicException;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

use function in_array;
use function sprintf;

class RstParser implements Parser
{
    private DoctrineRstParser $rstParser;

    public function __construct(?DoctrineRstParser $rstParser = null)
    {
        $this->rstParser = $rstParser ?? new DoctrineRstParser();
    }

    public function supportedExtensions(): array
    {
        return ['rst'];
    }

    public function supports(SplFileInfo $file): bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), false);
    }

    /** @return SampleCode[] */
    public function getAllSampleCodes(SplFileInfo $file): array
    {
        $sampleCodes = [];

        try {
            $nodes = $this->rstParser->parse($file->getContents())->getNodes();
        } catch (Throwable $e) {
            throw new LogicException(sprintf('Could not parse the file "%s"', $file->getPathname()));
        }

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
