<?php

namespace CiscoSystems\DirectoryBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use CiscoSystems\DirectoryBundle\Security\Authentication\DirectoryAuthenticationFactory;

class CiscoSystemsDirectoryBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $extension = $container->getExtension( 'security' );
        $extension->addSecurityListenerFactory( new DirectoryAuthenticationFactory() );
    }
}
