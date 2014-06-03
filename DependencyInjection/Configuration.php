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
                                'protocol_version' => 3,
                                'referrals' => 0,
                                'network_timeout' => 20,
                                'default_base_dn' => null,
                                'default_relative_dn' => null,
                                'default_password' => null,
                                'repository' => '%cisco.ldap.base_repository.class%',
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
                            ->scalarNode( 'protocol_version' )->defaultValue( 3 )->end()
                            ->scalarNode( 'referrals' )->defaultValue( 0 )->end()
                            ->scalarNode( 'network_timeout' )->defaultValue( 20 )->end()
                            ->scalarNode( 'default_base_dn' )->defaultNull()->end()
                            ->scalarNode( 'default_relative_dn' )->defaultNull()->end()
                            ->scalarNode( 'default_password' )->defaultNull()->end()
                            ->scalarNode( 'repository' )->defaultValue( '%cisco.ldap.base_repository.class%' )->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode( 'default_directory' )->cannotBeEmpty()->defaultValue( 'default' )->end()
                ->scalarNode( 'authentication' )->defaultFalse()->end()
                ->scalarNode( 'authentication_directory' )->defaultValue( 'default' )->end()
                ->scalarNode( 'autocreate_new_user' )->defaultFalse()->end()
                // Configuration options for FOSUserBundle
                ->scalarNode( 'db_driver' )->defaultValue( 'orm' )->end()
                ->scalarNode( 'firewall_name' )->defaultValue( 'main' )->end()
                ->scalarNode( 'user_class' )->defaultValue( 'User' )->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
