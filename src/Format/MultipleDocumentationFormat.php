<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Assert\Assert;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Finder\SplFileInfo;
use function array_map;
use function array_merge;
use function array_merge_recursive;
use function array_reduce;

final class MultipleDocumentationFormat implements DocumentationFormat
{
    /** @var DocumentationFormat[] */
    private array $documentationFormats;

    public function __construct(DocumentationFormat ...$documentationFormats)
    {
        Assert::that($documentationFormats)
            ->notEmpty();

        $this->documentationFormats = $documentationFormats;
    }

    public function supportedExtensions() : array
    {
        return array_merge_recursive([], ...array_map(
            fn (DocumentationFormat $doc) => $doc->supportedExtensions(),
            $this->documentationFormats
        ));
    }

    public function canHandle(SplFileInfo $file) : bool
    {
        foreach ($this->documentationFormats as $documentationFormat) {
            if ($documentationFormat->canHandle($file)) {
                return true;
            }
        }

        return false;
    }

    public function __invoke(SplFileInfo $file, Output $output) : bool
    {
        foreach ($this->documentationFormats as $documentationFormat) {
            if ($documentationFormat->canHandle($file)) {
                return $documentationFormat($file, $output);
            }
        }

        return false;
    }
}
