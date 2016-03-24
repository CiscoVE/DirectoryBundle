<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Psr\Log\LoggerInterface;
use CiscoSystems\DirectoryBundle\Directory\DirectoryManager;

class DirectoryUserProvider implements UserProviderInterface
{
    protected $ldap;
    protected $logger;

    /**
     * @param \CiscoSystems\DirectoryBundle\Directory\DirectoryManager $ldap
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct( DirectoryManager $ldap, LoggerInterface $logger )
    {
        $this->ldap = $ldap;
        $this->logger = $logger;
    }

    public function loadUserByUsername($username)
    {
        $user = new DirectoryUser( $username, "", "", [ "ROLE_USER" ]);
        // TODO: load directory attributes into user object
        return $user;
    }

    public function refreshUser( UserInterface $user )
    {
        if ( !$user instanceof DirectoryUser )
        {
            throw new UnsupportedUserException(
                sprintf( 'Instances of "%s" are not supported.', get_class( $user ))
            );
        }
        return $this->loadUserByUsername( $user->getUsername() );
    }

    public function supportsClass($class)
    {
        return $class === 'CiscoSystems\DirectoryBundle\Authentication\DirectoryUser';
    }
}
