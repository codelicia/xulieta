<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Config;

use Codelicia\Xulieta\Plugin\PhpOnMarkdownPlugin;
use Codelicia\Xulieta\Plugin\PhpOnRstPlugin;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigFileValidation implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xulieta');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('plugin')
                    ->defaultValue([PhpOnRstPlugin::class, PhpOnMarkdownPlugin::class])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('exclude')
                    ->defaultValue(['vendor', 'node_modules'])
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
