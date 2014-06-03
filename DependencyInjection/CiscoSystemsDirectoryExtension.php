<?php

namespace CiscoSystems\DirectoryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class CiscoSystemsDirectoryExtension extends Extension
{
    public function prepend( ContainerBuilder $container )
    {
        $config = $this->processConfiguration( new Configuration(), $configs );
        // Check configuration of default directory
        $defDir = $config['default_directory'];
        if ( !isset( $config['directories'][$defDir] ) )
        {
            throw new InvalidConfigurationException( 'Default directory is not configured.' );
        }
        if ( count( $config['directories'][$defDir]['servers'] ) < 1 )
        {
            throw new InvalidConfigurationException( 'At least one directory server must be configured for directory ' . $defDir . '.' );
        }
        // If authentication is configured check whether the required bundles are registered
        if ( $config['authentication'] )
        {
            $bundles = $container->getParameter( 'kernel.bundles' );
            if ( !isset( $bundles['FOSUserBundle'] ))
            {
                throw new \Exception( 'You must enable the FOSUserBundle in your AppKernel for authentication to work.' );
            }
            if ( !isset( $bundles['StofDoctrineExtensionsBundle'] ))
            {
                throw new \Exception( 'You must enable the StofDoctrineExtensionsBundle in your AppKernel for authentication to work.' );
            }
            // Configure FOSUserBundle
            if ( !array_key_exists( 'db_driver', $config ))
            {
                throw new InvalidConfigurationException( 'The child node "db_driver" at path "cisco_systems_directory" must be configured.' );
            }
            die( 'horribly' ); exit;
            $container->prependExtensionConfig( 'fos_user', array( 'db_driver' => $config['db_driver'] ));
            if ( !array_key_exists( 'firewall_name', $config ))
            {
                throw new InvalidConfigurationException( 'The child node "firewall_name" at path "cisco_systems_directory" must be configured.' );
            }
            $container->prependExtensionConfig( 'fos_user', array( 'firewall_name' => $config['firewall_name'] ));
            if ( array_key_exists( 'group_class', $config ))
            {
                $container->prependExtensionConfig( 'fos_user', array( 'group_class' => $config['group_class'] ));
            }
        }
        //Check whether authentication directory is configured, and if not set it to the default directory
        if ( $config['authentication_directory'] )
        {
            if ( !array_key_exists( $config['authentication_directory'], $config['directories'] ))
            {
                $config['authentication_directory'] = $defDir;
            }
        }
        // Store configuration data as injectable parameter
        $container->setParameter( 'cisco.ldap.configuration', $config );
    }

    public function load( array $configs, ContainerBuilder $container )
    {
        $config = $this->processConfiguration( new Configuration(), $configs );
        // Services
        $fileLocator = new FileLocator( __DIR__ . '/../Resources/config' );
        $loader = new Loader\YamlFileLoader( $container, $fileLocator );
        $loader->load( 'services.yml' );
        $loader->load( sprintf('%s.yml', $config['db_driver'] ));
        // Check configuration of default directory
        $defDir = $config['default_directory'];
        if ( !isset( $config['directories'][$defDir] ) )
        {
            throw new InvalidConfigurationException( 'Default directory is not configured.' );
        }
        if ( count( $config['directories'][$defDir]['servers'] ) < 1 )
        {
            throw new InvalidConfigurationException( 'At least one directory server must be configured for directory ' . $defDir . '.' );
        }
        // If authentication is configured check whether the required bundles are registered
        if ( $config['authentication'] )
        {
            $bundles = $container->getParameter( 'kernel.bundles' );
            if ( !isset( $bundles['FOSUserBundle'] ))
            {
                throw new \Exception( 'You must enable the FOSUserBundle in your AppKernel for authentication to work.' );
            }
            if ( !isset( $bundles['StofDoctrineExtensionsBundle'] ))
            {
                throw new \Exception( 'You must enable the StofDoctrineExtensionsBundle in your AppKernel for authentication to work.' );
            }
        }
        // Configure FOSUserBundle
        if ( !array_key_exists( 'db_driver', $config ))
        {
            throw new InvalidConfigurationException( 'The child node "db_driver" at path "cisco_systems_directory" must be configured.' );
        }
        $container->prependExtensionConfig( 'fos_user', array( 'db_driver' => $config['db_driver'] ));
        if ( !array_key_exists( 'firewall_name', $config ))
        {
            throw new InvalidConfigurationException( 'The child node "firewall_name" at path "cisco_systems_directory" must be configured.' );
        }
        $container->prependExtensionConfig( 'fos_user', array( 'firewall_name' => $config['firewall_name'] ));
        if ( !array_key_exists( 'user_class', $config ))
        {
            throw new InvalidConfigurationException( 'The child node "user_class" at path "cisco_systems_directory" must be configured.' );
        }
        $container->prependExtensionConfig( 'fos_user', array( 'user_class' => $config['user_class'] ));
        //Check whether authentication directory is configured, and if not set it to the default directory
        if ( $config['authentication_directory'] )
        {
            if ( !array_key_exists( $config['authentication_directory'], $config['directories'] ))
            {
                $config['authentication_directory'] = $defDir;
            }
        }
        // Store configuration data as injectable parameter
        $container->setParameter( 'cisco.ldap.configuration', $config );
    }
}
