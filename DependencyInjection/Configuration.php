<?php

namespace CiscoSystems\DirectoryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root( 'cisco_systems_directory' );
        $node
            ->children()
                ->arrayNode( 'directories' )
                    ->defaultValue(
                        array(
                            'default' => array(
                                'servers' => array(
                                    'primary' => array(
                                        'host' => 'localhost',
                                        'port' => 389
                                    )
                                ),
                                'protocol_version' => '0x03',
                                'referrals' => 0,
                                'network_timeout' => 20,
                                'default_base_dn' => null,
                                'default_relative_dn' => null,
                                'default_password' => null,
                                'bind_rdn_suffix' => null,
                                'repository' => '%cisco.ldap.base_repository.class%',
                                'bind_authenticated_user' => false,
                            ),
                        )
                    )
                    ->useAttributeAsKey( 'id' )
                    ->prototype( 'array' )
                        ->children()
                            ->arrayNode( 'servers' )
                                ->useAttributeAsKey( 'id' )
                                ->prototype( 'array' )
                                    ->children()
                                        ->scalarNode( 'host' )->cannotBeEmpty()->isRequired()->end()
                                        ->scalarNode( 'port' )->cannotBeEmpty()->defaultValue( 389 )->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode( 'protocol_version' )->defaultValue( '0x03' )->end()
                            ->scalarNode( 'referrals' )->defaultValue( 0 )->end()
                            ->scalarNode( 'network_timeout' )->defaultValue( 20 )->end()
                            ->scalarNode( 'default_base_dn' )->defaultNull()->end()
                            ->scalarNode( 'default_relative_dn' )->defaultNull()->end()
                            ->scalarNode( 'default_password' )->defaultNull()->end()
                            ->scalarNode( 'bind_rdn_suffix' )->defaultNull()->end()
                            ->scalarNode( 'repository' )->defaultValue( '%cisco.ldap.base_repository.class%' )->end()
                            ->scalarNode( 'bind_authenticated_user' )->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode( 'default_directory' )->cannotBeEmpty()->defaultValue( 'default' )->end()
                ->scalarNode( 'authentication_directory' )->defaultValue( 'default' )->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
