<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

/**
 * This class provides methods for encoding and decoding the user password which
 * is not meant to be stored in a local user store but only temporarily in the
 * session to facilitate repeated calls to an Active Directory server.
 */

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use CiscoSystems\DirectoryBundle\Authentication\DirectoryPasswordEncoder;

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
        $this->password = DirectoryPasswordEncoder::encode( $this->username, $credentials );
//         $this->credentials = $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        //return $this->password;
        return DirectoryPasswordEncoder::decode( $this->username, $this->password );
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
}

/*

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use CiscoSystems\DirectoryBundle\Authentication\DirectoryPasswordEncoder;

class DirectoryUserToken implements TokenInterface
{
    protected $user;
    protected $roles;
    protected $authenticated;
    protected $attributes;
    protected $password;
    protected $providerKey;

    public function __construct( $user = "", $password = "", $providerKey = "", array $roles = array() )
    {
        $this->user = $user;
        $this->password = DirectoryPasswordEncoder::encode( $this->getUsername(), $password );
        $this->providerKey = $providerKey;
        $this->roles = $roles;
    }

    public function getUsername()
    {
        if ( $this->user instanceof UserInterface ) return $this->user->getUsername();
        return (string) $this->user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser( $user )
    {
        $this->user = $user;
        $this->roles = $user->getRoles();
    }

    public function eraseCredentials() {}

    public function getCredentials()
    {
        return '';
    }

    public function getPassword()
    {
        return DirectoryPasswordEncoder::decode( $this->getUsername(), $this->password );
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function getAttribute( $name )
    {
        if ( !array_key_exists( $name, $this->attributes ))
        {
            throw new \InvalidArgumentException( sprintf( 'This token has no "%s" attribute.', $name ));
        }
        return $this->attributes[$name];
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function hasAttribute( $name )
    {
        return array_key_exists( $name, $this->attributes );
    }

    public function setAttribute( $name, $value )
    {
        $this->attributes[$name] = $value;
    }

    public function setAttributes( array $attributes )
    {
        $this->attributes = $attributes;
    }

    public function getRoles()
    {
        return is_array( $this->roles ) ? $this->roles : $this->roles->toArray();
    }

    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    public function setAuthenticated( $isAuthenticated )
    {
        $this->authenticated = (Boolean) $isAuthenticated;
    }

    public function serialize()
    {
//         $foo = serialize( array( $this->user, $this->authenticated, $this->roles, $this->attributes ));
        $foo = json_encode( array( $this->user, $this->authenticated, $this->roles, $this->attributes ));
        return $foo;
        echo '<pre>';
        echo is_string( $foo ) ? 'foo' : 'baa';
        echo '</pre>';
        die(); exit;
        return serialize( array( $this->user, $this->authenticated, $this->roles, $this->attributes ));
//         return \json_encode(
//                 array($this->user, $this->password, $this->roles));
    }

    public function unserialize( $serialized )
    {
//         list( $this->user, $this->authenticated, $this->roles, $this->attributes ) = unserialize( $serialized );
        list( $this->user, $this->authenticated, $this->roles, $this->attributes ) = json_decode( $serialized );
//         list($this->user, $this->password, $this->roles) = \json_decode(
//                 $serialized);
    }

    public function __toString()
    {
        $class = substr( $class, strrpos(get_class($this), '\\') +1 );
        $roles = array();
        foreach ( $this->roles as $role ) $roles[] = $role->getRole();
        return sprintf( '%s(user="%s", authenticated=%s, roles="%s")', $class, $this->getUsername(), json_encode( $this->authenticated ), implode( ', ', $roles ));
    }
}

*/
