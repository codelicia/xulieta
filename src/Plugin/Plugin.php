<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Plugin;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

interface Plugin
{
    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions() : array;

    public function canHandle(SplFileInfo $file) : bool;

    public function __invoke(SplFileInfo $file, OutputInterface $output) : bool;
}
