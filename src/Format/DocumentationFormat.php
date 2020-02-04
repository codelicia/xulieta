<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Format;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Finder\SplFileInfo;

// @todo getMatch() : file extensions
interface DocumentationFormat
{
    public function canHandle(SplFileInfo $file) : bool;

    public function __invoke(SplFileInfo $file, Output $output) : bool;
}
