<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Codelicia\Xulieta\Markdown\Parser;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function preg_match;
use const PHP_EOL;

final class MarkdownDocumentationFormat implements DocumentationFormat
{
    private Parser $parser;
    private PhpParser $phpParser;

    public function __construct(?Parser $parser = null)
    {
        // TODO: Inject all these properties
        $this->phpParser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->parser = $parser ?: new Parser();
    }

    public function canHandler(SplFileInfo $file) : bool
    {
        return 'markdown' === $file->getExtension() || 'md' === $file->getExtension();
    }

    public function __invoke(SplFileInfo $file, Output $output) : bool
    {
        $parser = new \Codelicia\Xulieta\Markdown\Parser();
        try {
            $documentation = $parser->dryRun($file->getContents());
        } catch (Throwable $e) {

            $output->writeln(PHP_EOL . '<error>Error parsing file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            return false;
        }


        try {
            foreach ($documentation as $nodes) {
                if ($nodes['element']['text']['attributes']['class'] === 'language-php') {
                    if (! preg_match('/\<\?php/i', $nodes['element']['text']['text'])) {
                        $output->writeln('<error>Snippet missing PHP open tag on file: ' . $file->getRealPath() . '</error>');
                        continue;
                    }

                    $parser->parse($nodes['element']['text']['text']);
                }
            }
        } catch (Throwable $e) {
            $output->writeln('<error>Wrong code on file: ' . $file->getRealPath() . '</error>');
            $output->writeln($e->getMessage() . PHP_EOL);

            return false;
        }

        return true;
    }
}
