<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Psr\Log\LoggerInterface;
use CiscoSystems\DirectoryBundle\Directory\DirectoryManager;
use CiscoSystems\DirectoryBundle\Authentication\DirectoryUserToken;

class DirectoryAuthenticator implements SimpleFormAuthenticatorInterface
{
    protected $encoderFactory;
    protected $ldap;
    protected $logger;

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param array $directoryConfiguration
     */
    public function __construct( EncoderFactoryInterface $encoderFactory, DirectoryManager $ldap, LoggerInterface $logger )
    {
        $this->encoderFactory = $encoderFactory;
        $this->ldap = $ldap;
        $this->logger = $logger;
        $logger->info( 'cisco.ldap: authenticator instantiated' );
    }

    public function authenticateToken( TokenInterface $token, UserProviderInterface $userProvider, $providerKey )
    {
        try
        {
            $authDir = $this->ldap->getAuthenticationDirectoryName();
            $repo = $this->ldap->getRepository( $authDir, $token->getUsername(), $token->getCredentials() );
            if ( $repo )
            {
                try
                {
                    $user = $userProvider->loadUserByUsername( $token->getUsername() );
                    $this->logger->info( 'cisco.ldap: user object returned by provider' );
                    return new DirectoryUserToken(
                            $user,
                            $user->getPassword(),
                            $providerKey,
                            $user->getRoles()
                    );
                }
                catch( UsernameNotFoundException $e ) {}
                $this->logger->info( 'cisco.ldap: caught UsernameNotFoundException' );
                throw new AuthenticationException('Invalid username or password');
            }
        }
        catch ( \Exception $e )
        {
            $this->logger->info( 'cisco.ldap: caught Exception' );
            throw new AuthenticationException( 'Could not validate supplied credentials against directory.' );
        }
    }

    public function supportsToken( TokenInterface $token, $providerKey )
    {
        return $token instanceof DirectoryUserToken && $token->getProviderKey() === $providerKey; // check what is provided here
    }

    public function createToken( Request $request, $username, $password, $providerKey )
    {
        return new DirectoryUserToken( $username, $password, $providerKey );
    }
}
