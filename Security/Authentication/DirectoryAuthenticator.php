<?php

namespace CiscoSystems\DirectoryBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Psr\Log\LoggerInterface;
use CiscoSystems\DirectoryBundle\Security\Authentication\DirectoryUserToken;

class DirectoryAuthenticator implements SimpleFormAuthenticatorInterface
{
    protected $encoderFactory;
    protected $directoryConfiguration;
    protected $logger;

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param array $directoryConfiguration
     */
    public function __construct( EncoderFactoryInterface $encoderFactory, array $directoryConfiguration = array(), LoggerInterface $logger )
    {
        $this->encoderFactory = $encoderFactory;
        $this->directoryConfiguration = $directoryConfiguration;
        $this->logger = $logger;
        $logger->info( 'cisco.ldap: authenticator instantiated' );
    }

    public function authenticateToken( TokenInterface $token, UserProviderInterface $userProvider, $providerKey )
    {
        $logger->info( 'cisco.ldap: authenticateToken() called' );
        try
        {
            $user = $userProvider->loadUserFromDirectoryByUsernameAndPassword( $token->getUsername(), $token->getPassword() );
            $logger->info( 'cisco.ldap: user object returned by provider' );
            return new DirectoryUserToken(
                    $user,
                    $user->getPassword(),
                    $providerKey,
                    $user->getRoles()
            );
        }
        catch( UsernameNotFoundException $e ) {}
        $logger->info( 'cisco.ldap: caught UsernameNotFoundException' );
        throw new AuthenticationException('Invalid username or password');
    }

    public function supportsToken( TokenInterface $token, $providerKey )
    {
        return $token instanceof DirectoryUserToken; //&& $token->getProviderKey() === $providerKey; // check what is provided here
    }

    public function createToken( Request $request, $username, $password, $providerKey )
    {
        return new DirectoryUserToken( $username, $password, $providerKey );
    }
}
