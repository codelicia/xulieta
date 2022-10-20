<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Util\XmlUtils;

use function array_filter;
use function current;
use function realpath;

final class ConfigFile
{
    /** @psalm-return array<array-key, mixed> */
    public static function loadInDirectory(string $dir): array
    {
        $configFiles = current(array_filter(
            [
                $dir . '/../../../../.xulieta.xml',
                $dir . '/../../../.xulieta.xml',
                $dir . '/../../.xulieta.xml',
                $dir . '/../.xulieta.xml',
                $dir . '/.xulieta.xml',
            ],
            'is_file',
        ));

        $config = $configFiles === false
            ? []
            : (array) XmlUtils::convertDomElementToArray(
                XmlUtils::loadFile(realpath($configFiles))
                    ->documentElement,
            );

        return (new Processor())
            ->processConfiguration(
                new ConfigFileValidation(),
                ['xulieta' => $config],
            );
    }
}
