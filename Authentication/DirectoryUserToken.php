<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class DirectoryUserToken extends UsernamePasswordToken
{
    protected $username;
    protected $password;

    /**
     * Constructor.
     *
     * @param string          $user        The username (like a nickname, email address, etc.), or a UserInterface instance or an object implementing a __toString method.
     * @param string          $credentials This usually is the password of the user
     * @param string          $providerKey The provider key
     * @param RoleInterface[] $roles       An array of roles
     *
     * @throws \InvalidArgumentException
     */
    public function __construct( $user, $credentials, $providerKey, array $roles = array() )
    {
        parent::__construct( $user, $credentials, $providerKey, $roles );
        $this->username = $user instanceof UserInterface ? $user->getUsername() : (string)$user;
        $this->password = $this->encodePassword( $credentials, $this->username );
//         $this->password = $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
//         return $this->password;
        return $this->decodePassword( $this->password, $this->username );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->password, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->password, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }

    /*
     * This class also provides methods for encoding and decoding the user password
     * which is not meant to be stored in a local user store but only temporarily in
     * the session to facilitate repeated calls to an Active Directory server.
     */

    private function encodePassword( $password, $username )
    {
        $keyLength = strlen( $username );
        if ( $keyLength < 8 )
        {
            for ( ; $keyLength < 8 ; $keyLength++ ) $username .= '0';
        }
        $block = mcrypt_get_block_size( 'des', 'ecb' );
        $pad = $block - ( strlen( $password ) % $block );
        $password .= str_repeat( chr( $pad ), $pad );
        $encodedPassword = mcrypt_encrypt( MCRYPT_DES, $username, $password, MCRYPT_MODE_ECB, 7 );
        return $encodedPassword;
    }

    private function decodePassword( $password, $username )
    {
        $keyLength = strlen( $username );
        if ( $keyLength < 8 )
        {
            for ( ; $keyLength < 8 ; $keyLength++ ) $username .= '0';
        }
        $str = mcrypt_decrypt( MCRYPT_DES, $username, $password, MCRYPT_MODE_ECB, 7 );
        $pad = ord( $str[( $len = strlen( $str ) ) - 1] );
        $decodedPassword = substr( $str, 0, strlen( $str ) - $pad );
        return $decodedPassword;
    }
}
