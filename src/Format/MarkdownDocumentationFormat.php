<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Codelicia\Xulieta\External\Markinho;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use Webmozart\Assert\Assert;
use function in_array;
use const PHP_EOL;

/**
 * @psalm-type TData = array{
 *   element: array{
 *     text: array{
 *        text: string,
 *        attributes: array{class: string}
 *      }
 *   }
 * }
 */
final class MarkdownDocumentationFormat implements DocumentationFormat
{
    private PhpParser $phpParser;

    public function __construct()
    {
        // TODO: Inject all these properties
        $this->phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions() : array
    {
        return ['markdown', 'md'];
    }

    public function canHandle(SplFileInfo $file) : bool
    {
        return in_array($file->getExtension(), $this->supportedExtensions(), true);
    }

    public function __invoke(SplFileInfo $file, OutputInterface $output) : bool
    {
        $documentation = Markinho::extractCodeBlocks($file->getContents());

        try {
            foreach ($documentation as $nodes) {
                if ($nodes['language'] !== 'php') {
                    continue;
                }

                $this->phpParser->parse($nodes['code']);
            }
        } catch (Throwable $e) {
            $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            if (isset($nodes['code'])) {
                Assert::string($nodes['code']);
                $output->writeln($nodes['code']);
            }

            return false;
        }

        return true;
    }
}
