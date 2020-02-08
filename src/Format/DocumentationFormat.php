<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Finder\SplFileInfo;

interface DocumentationFormat
{
    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions() : array;

    public function canHandle(SplFileInfo $file) : bool;

    public function __invoke(SplFileInfo $file, Output $output) : bool;
}
