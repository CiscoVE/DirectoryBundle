<?php

namespace CiscoSystems\DirectoryBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use CiscoSystems\DirectoryBundle\Security\Authentication\DirectoryUserToken;

class DirectoryAuthenticator implements SimpleFormAuthenticatorInterface
{
    protected $encoderFactory;
    protected $directoryConfiguration;

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param array $directoryConfiguration
     */
    public function __construct( EncoderFactoryInterface $encoderFactory, array $directoryConfiguration = array() )
    {
        $this->encoderFactory = $encoderFactory;
        $this->directoryConfiguration = $directoryConfiguration;
    }

    public function authenticateToken( TokenInterface $token, UserProviderInterface $userProvider, $providerKey )
    {
        try
        {
            $user = $userProvider->loadUserFromDirectoryByUsernameAndPassword( $token->getUsername(), $token->getPassword() );
            return new DirectoryUserToken(
                    $user,
                    $user->getPassword(),
                    $providerKey,
                    $user->getRoles()
            );
        }
        catch( UsernameNotFoundException $e ) {}
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
