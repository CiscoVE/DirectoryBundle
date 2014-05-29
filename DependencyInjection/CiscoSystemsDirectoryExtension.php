<?php

namespace CiscoSystems\DirectoryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class CiscoSystemsDirectoryExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container )
    {
        // Configuration
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );
        // Services
        $fileLocator = new FileLocator( __DIR__ . '/../Resources/config' );
        $loader = new Loader\YamlFileLoader( $container, $fileLocator );
        $loader->load( 'services.yml' );
        // Set parameters
//         echo '<pre>';
//         print_r( $config );
//         echo '</pre>';
//         die();
        $defDir = $config['default_directory'];
        if ( !isset( $config['directories'][$defDir] ) )
        {
            throw new \Exception( 'Default directory is not configured.' );
        }
        if ( count( $config['directories'][$defDir]['servers'] ) < 1 )
        {
            throw new \Exception( 'At least one directory server must be configured.' );
        }
        $container->setParameter( 'cisco.ldap.configuration', $config );
    }
}
