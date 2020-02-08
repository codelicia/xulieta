<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use function array_filter;
use function current;
use function file_get_contents;
use function realpath;

final class ConfigFile
{
    /**
     * @return string[]
     */
    public static function loadInDirectory(string $dir) : array
    {
        $configFiles = current(array_filter(
            [
                $dir . '/../../../xulieta.yaml',
                $dir . '/../../xulieta.yaml',
                $dir . '/../xulieta.yaml',
                $dir . '/xulieta.yaml',
            ],
            'is_file'
        ));

        $config = [];
        if ($configFiles !== false) {
            $config = (array) Yaml::parse(file_get_contents(realpath($configFiles)));
        }

        return (new Processor())
            ->processConfiguration(new ConfigFileValidation(), $config);
    }
}
