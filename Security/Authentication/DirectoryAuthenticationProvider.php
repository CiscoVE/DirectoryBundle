<?php

namespace CiscoSystems\DirectoryBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use CiscoSystems\DirectoryBundle\Security\Authentication\DirectoryUserToken;
use CiscoSystems\DirectoryBundle\Directory\DirectoryManager;

class DirectoryAuthenticationProvider implements AuthenticationProviderInterface
{
    protected $userProvider;
    protected $directoryManager;
    protected $directoryConfiguration;
    protected $logger;

    public function __construct(
                        UserProviderInterface $userProvider,
                        DirectoryManager $directoryManager,
                        array $directoryConfiguration,
                        $logger
                    )
    {
        $this->userProvider = $userProvider;
        $this->directoryManager = $directoryManager;
        $this->directoryConfiguration = $directoryConfiguration;
        $this->logger = $logger;
        $logger->info( 'AUTH: Constructed AuthenticationProvider instance' );
    }

    public function authenticate( TokenInterface $token )
    {
        $this->logger->info( 'AUTH: Called AuthenticationProvider::authenticate()' );
        try
        {
            $authDir = $this->directoryConfiguration['default_directory'];
            if ( array_key_exists( 'authentication_directory', $this->directoryConfiguration ))
            {
                $dir = $this->directoryConfiguration['authentication_directory'];
                // make sure the authentication directory is configured, otherwise fall back to the configured default directory
                if ( array_key_exists( $dir, $this->directoryConfiguration['directories'] ))
                {
                    $authDir = $dir;
                }
            }
            $authSuffix = $this->directoryConfiguration['authentication_suffix'];
            $bindRdn = $token->getUsername() . $authSuffix;
            $repository = $this->directoryManager->getRepository( $authDir, $bindRdn, $token->getPassword() );
            if ( $repository->getBindRdn() == $bindRdn )
            {
                $this->logger->info( 'LDAP: bind successful' );
                $user = $this->userProvider->loadUserByUsername( $token->getUsername() );
                if ( null !== $user )
                {
                    $this->logger->info( 'AUTH: obtained User object from user provider' );
                    $token->setUser( $user );
                    $token->setAuthenticated( true );
                    return $token;
                }
                $this->logger->err( 'AUTH: could not obtain User object from user provider!' );
            }
        }
        catch( \Exception $e )
        {
        }
        throw new BadCredentialsException( 'Invalid username or password provided!' );
    }

    public function supports( TokenInterface $token )
    {
        return $token instanceof DirectoryUserToken;
    }
}
