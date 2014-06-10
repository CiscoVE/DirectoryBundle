<?php

namespace CiscoSystems\DirectoryBundle\Security\Authentication;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Psr\Log\LoggerInterface;
use CiscoSystems\DirectoryBundle\Directory\DirectoryManager;
use CiscoSystems\DirectoryBundle\Model\DirectoryUser;
use CiscoSystems\DirectoryBundle\Model\StorageAgnosticObjectManager;

class DirectoryUserProvider implements UserProviderInterface
{
    protected $directoryManager;
    protected $directoryConfiguration;
    protected $userManager;
    protected $logger;

    /**
     * Constructor
     *
     * @param DirectoryManager $directoryManager
     * @param array $directoryConfiguration
     */
    public function __construct( DirectoryManager $directoryManager, array $directoryConfiguration, StorageAgnosticObjectManager $userManager, LoggerInterface $logger )
    {
        $this->directoryManager = $directoryManager;
        $this->directoryConfiguration = $directoryConfiguration;
        $this->userManager = $userManager;
        $this->logger = $logger;
        $logger->info( 'cisco.ldap: user provider instantiated' );
    }

    /**
     *
     * @param string $username
     * @param string $password
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @return \CiscoSystems\DirectoryBundle\Model\DirectoryUser
     */
    public function loadUserFromDirectoryByUsernameAndPassword( $username = "", $password = "" )
    {
        $logger->info( 'cisco.ldap: trying to load user data from directory' );
        $authDir = $this->directoryManager->getAuthenticationDirectoryName();
        $repository = $this->directoryManager->getRepository( $authDir, $username, $password );
        if ( $repository )
        {
            try
            {
                return $this->loadUserByUsername( $username );
            }
            catch( UsernameNotFoundException $e )
            {
                if ( $this->directoryConfiguration['autocreate_new_user'] )
                {
                    $user = $this->userManager->create();
                    $user->setUsername( $username );
                    $user->setPassword( $password );
                    $user->addRole( 'ROLE_USER' );
                    $this->userManager->persist( $user );
                    $this->userManager->flush();
                    return $user;
                }
            }
        }
        throw new UsernameNotFoundException( sprintf( 'User "%s" does not exist.', $username ));
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
     */
    public function loadUserByUsername( $username = "" )
    {
        $user = $this->userManager->findOneBy( array( "username" => $username ));
        if ( $user ) return $user;
        throw new UsernameNotFoundException( sprintf( 'Username "%s" does not exist.', $username ) );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::refreshUser()
     */
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

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
     */
    public function supportsClass( $class )
    {
        return $class === 'CiscoSystems\DirectoryBundle\Model\User';
    }
}
