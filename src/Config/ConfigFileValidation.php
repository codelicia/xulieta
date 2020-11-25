<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Config;

use Codelicia\Xulieta\Output\Checkstyle;
use Codelicia\Xulieta\Output\Stdout;
use Codelicia\Xulieta\Parser\MarkdownParser;
use Codelicia\Xulieta\Parser\RstParser;
use Codelicia\Xulieta\Validator\PhpValidator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigFileValidation implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xulieta');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->fixXmlConfig('outputFormatter')
            ->children()
                ->arrayNode('outputFormatters')
                    ->defaultValue([Stdout::class, Checkstyle::class])
                    ->scalarPrototype()->end()
                ->end()
            ->end()
            ->fixXmlConfig('parser')
            ->children()
                ->arrayNode('parsers')
                    ->defaultValue([MarkdownParser::class, RstParser::class])
                    ->scalarPrototype()->end()
                ->end()
            ->end()
            ->fixXmlConfig('validator')
            ->children()
                ->arrayNode('validators')
                    ->defaultValue([PhpValidator::class])
                    ->scalarPrototype()->end()
                ->end()
            ->end()
            ->fixXmlConfig('exclude')
            ->children()
                ->arrayNode('excludes')
                    ->defaultValue(['vendor', 'node_modules'])
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
