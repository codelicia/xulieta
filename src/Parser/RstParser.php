<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Parser;

use Codelicia\Xulieta\ValueObject\SampleCode;
use Doctrine\RST\Kernel;
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

    public function __construct(DoctrineRstParser|null $rstParser = null)
    {
        // @todo(malukenho): move all this instantiation logic to another place
        $kernel        = new Kernel();
        $configuration = $kernel->getConfiguration();
        $configuration->silentOnError();
        $configuration->abortOnError(true);
        $configuration->treatWarningsAsError(true);
        $this->rstParser = $rstParser ?? new DoctrineRstParser($kernel);
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
        } catch (Throwable) {
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
