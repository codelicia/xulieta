<?php

declare(strict_types=1);

namespace Codelicia\Xulieta\Config;

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
            ->children()
                ->arrayNode('parser')
                    ->defaultValue([MarkdownParser::class, RstParser::class])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('validator')
                    ->defaultValue([PhpValidator::class])
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
