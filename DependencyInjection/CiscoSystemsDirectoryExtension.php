<?php

namespace CiscoSystems\DirectoryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class CiscoSystemsDirectoryExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container )
    {
        $config = $this->processConfiguration( new Configuration(), $configs );
        // Services
        $fileLocator = new FileLocator( __DIR__ . '/../Resources/config' );
        $loader = new Loader\YamlFileLoader( $container, $fileLocator );
        $loader->load( 'services.yml' );
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
