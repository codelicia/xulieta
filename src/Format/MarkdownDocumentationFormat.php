<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Codelicia\Xulieta\Parser\Markdown;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function in_array;
use function preg_match;
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
    private Markdown $parser;
    private PhpParser $phpParser;

    public function __construct(?Markdown $parser = null)
    {
        // TODO: Inject all these properties
        $this->phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->parser    = $parser ?: new Markdown();
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
        try {
            /** @psalm-var list<TData> $documentation */
            $documentation = $this->parser->dryRun($file->getContents());
        } catch (Throwable $e) {
            $output->writeln(PHP_EOL . '<error>Error parsing file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            return false;
        }

        try {
            foreach ($documentation as $nodes) {
                if (! isset($nodes['element']['text']['attributes']['class'])
                    || $nodes['element']['text']['attributes']['class'] !== 'language-php'
                ) {
                    continue;
                }

                if (! preg_match('/\<\?php/i', $nodes['element']['text']['text'])) {
                    $this->phpParser->parse('<?php ' . PHP_EOL . $nodes['element']['text']['text']);
                    continue;
                }

                $this->phpParser->parse($nodes['element']['text']['text']);
            }
        } catch (Throwable $e) {
            $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            if (isset($nodes['element']['text']['text'])) {
                $output->writeln((string) $nodes['element']['text']['text']);
            }

            return false;
        }

        return true;
    }
}
