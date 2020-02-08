<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Config;

use Codelicia\Xulieta\Format\MarkdownDocumentationFormat;
use Codelicia\Xulieta\Format\RstDocumentationFormat;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigFileValidation implements ConfigurationInterface
{
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xulieta');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('plugins')
                    ->addDefaultChildrenIfNoneSet()
                    ->scalarPrototype()
                        ->defaultValue([
                            MarkdownDocumentationFormat::class,
                            RstDocumentationFormat::class,
                        ])
                    ->end()
                ->end()
                ->arrayNode('exclude_dirs')
                    ->addDefaultChildrenIfNoneSet()
                    ->scalarPrototype()
                        ->defaultValue(['vendor', 'node_modules'])
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
