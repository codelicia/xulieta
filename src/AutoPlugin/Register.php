<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\AutoPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use DOMDocument;
use DOMElement;
use DOMException;
use Psl;
use Symfony\Component\Config\Util\XmlUtils;

use function assert;
use function dirname;
use function file_exists;
use function in_array;
use function is_array;
use function is_string;

/**
 * Based on {@see https://github.com/laminas/laminas-component-installer/blob/2.5.x/src/ComponentInstaller.php}
 *
 * In order to have your xulieta extension auto configurable, you need to put in
 * your composer.json the following keys, if applicable:
 *
 * - extra.xulieta.parser
 * - extra.xulieta.validator
 *
 * The values should have the FQCN as the following example:
 *
 * <code class="lang-javascript">
 * {
 *   "extra": {
 *     "xulieta": {
 *       "parser": ["Malukenho\\QuoPrimumTempore\\JsonParser"],
 *       "validator": ["Malukenho\\QuoPrimumTempore\\JsonValidator"]
 *     }
 *   }
 * }
 * </code>
 *
 * @internal
 *
 * @psalm-type TPackageMetadata = array{xulieta?: object|array{parser?: string, validator?: string}}
 */
final class Register implements PluginInterface, EventSubscriberInterface
{
    public static function scan(PackageEvent $event): void
    {
        if (! $event->isDevMode()) {
            return;
        }

        $operation = $event->getOperation();
        assert($operation instanceof InstallOperation);
        $package = $operation->getPackage();

        /** @psalm-var TPackageMetadata $packageExtra */
        $packageExtra = $package->getExtra();
        $extra        = self::getExtraMetadata($packageExtra);

        if (empty($extra)) {
            // Package does not define anything of interest; do nothing.
            return;
        }

        self::injectModuleIntoConfig($extra, $event->getIO(), $event->getComposer());
    }

    /**
     * Retrieve the metadata from the "extra" section
     *
     * @param TPackageMetadata $extra
     *
     * @return array<string,mixed>
     */
    private static function getExtraMetadata(array $extra): array
    {
        $pluginConfiguration = [];

        if (isset($extra['xulieta']) && is_array($extra['xulieta'])) {
            /** @var array<string,mixed> $pluginConfiguration */
            $pluginConfiguration = $extra['xulieta'];
        }

        return $pluginConfiguration;
    }

    private static function injectModuleIntoConfig(array $extra, IOInterface $io, Composer $composer): void
    {
        $rootDir           = dirname($composer->getConfig()->getConfigSource()->getName());
        $xulietaConfigFile = $readFile = $rootDir . '/.xulieta.xml';

        if (! file_exists($xulietaConfigFile)) {
            if (! $io->askConfirmation('Do you want us to create a ".xulieta.xml" for you? ')) {
                return;
            }

            $readFile = __DIR__ . '/../../default-config.xml.dist';
        }

        $xml = XmlUtils::loadFile($readFile);

        self::appendChild($xml, $extra, 'parser');
        self::appendChild($xml, $extra, 'validator');

        $config = $xml->saveXML();
        Psl\invariant(is_string($config) && ! empty($config), '$config is not valid.');

        $domxml                     = new DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput       = true;
        $domxml->loadXML($config);
        $domxml->save($xulietaConfigFile);

        $io->write('Xulieta configuration is up-to-date...');
    }

    /** @throws DOMException */
    private static function appendChild(DOMDocument $document, array $extra, string $tag): void
    {
        $root = $document->documentElement;
        assert($root instanceof DOMElement);

        $validators     = $root->getElementsByTagName($tag);
        $textAggregator = [];

        foreach ($validators as $taggedElements) {
            $textAggregator[] = $taggedElements->textContent;
        }

        if (! isset($extra[$tag])) {
            return;
        }

        foreach ($extra[$tag] as $toBeRegistered) {
            Psl\invariant(is_string($toBeRegistered), 'Plugin to be registered is not a string.');
            if (in_array($toBeRegistered, $textAggregator, true)) {
                continue;
            }

            if (! isset($taggedElements)) {
                $root->append($document->createElement($tag, $toBeRegistered));

                continue;
            }

            if (! $taggedElements->parentNode) {
                continue;
            }

            $taggedElements->parentNode->insertBefore(
                $document->createElement($tag, $toBeRegistered),
                $taggedElements,
            );
        }
    }

    /** @psalm-return array{post-package-install: string} */
    public static function getSubscribedEvents(): array
    {
        return [PackageEvents::POST_PACKAGE_INSTALL => 'scan'];
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // Intentionally left blank
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // Intentionally left blank
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        // Intentionally left blank
    }
}
