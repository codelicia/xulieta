<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Plugin;

use Assert\Assert;
use Codelicia\Xulieta\Output\OutputFormatter;
use Symfony\Component\Finder\SplFileInfo;
use function array_map;
use function array_merge_recursive;
use function array_values;

final class MultiplePlugin implements Plugin
{
    /** @var Plugin[] */
    private array $plugins;

    public function __construct(Plugin ...$plugins)
    {
        Assert::that($plugins)
            ->notEmpty();

        $this->plugins = $plugins;
    }

    /** @psalm-return list<non-empty-string> */
    public function supportedExtensions() : array
    {
        return array_values(array_merge_recursive([], ...array_map(
            static fn (Plugin $plugin) => $plugin->supportedExtensions(),
            $this->plugins
        )));
    }

    public function canHandle(SplFileInfo $file) : bool
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin->canHandle($file)) {
                return true;
            }
        }

        return false;
    }

    public function __invoke(SplFileInfo $file, OutputFormatter $output) : bool
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin->canHandle($file)) {
                return $plugin($file, $output);
            }
        }

        return false;
    }
}
