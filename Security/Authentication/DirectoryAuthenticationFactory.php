<?php

namespace CiscoSystems\DirectoryBundle\Security\Authentication;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class DirectoryAuthenticationFactory implements SecurityFactoryInterface
{
    /**
     * adds the listener and authentication provider to
     * the DI container for the appropriate security context
     *
     * (non-PHPdoc)
     * @see \Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::create()
     */
    public function create( ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint )
    {
        $providerId = 'security.authentication.provider.' . $id;
        $container
          ->setDefinition( $providerId, new DefinitionDecorator( 'cisco.ldap.authentication_provider' ) )
          ->replaceArgument( 0, new Reference( $userProvider ) )
        ;
        $listenerId = 'security.authentication.listener.' . $id;
        $container->setDefinition( $listenerId, new DefinitionDecorator( 'cisco.ldap.authentication_listener' ) );
        return array( $providerId, $listenerId, $defaultEntryPoint );
    }

    /**
     * must be of type pre_auth, form, http, and remember_me
     * and defines the position at which the provider is called
     *
     * (non-PHPdoc)
     * @see \Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::getPosition()
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * defines the configuration key used to reference
     * the provider in the firewall configuration
     *
     * (non-PHPdoc)
     * @see \Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::getKey()
     */
    public function getKey()
    {
        return 'ldap';
    }

    /**
     * used to define the configuration options underneath
     * the configuration key in your security configuration
     *
     * (non-PHPdoc)
     * @see \Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::addConfiguration()
     */
    public function addConfiguration(NodeDefinition $node) {}
}