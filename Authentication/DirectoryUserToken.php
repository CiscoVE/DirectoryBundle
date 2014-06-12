<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

/**
 * This class provides methods for encoding and decoding the user password which
 * is not meant to be stored in a local user store but only temporarily in the
 * session to facilitate repeated calls to an Active Directory server.
 */

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
        $this->setUser( $user );
        $this->password = DirectoryPasswordEncoder::encode( $this->getUsername(), $password );
        $this->providerKey = $providerKey;
        $this->roles = array();
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
        return serialize( array( $this->user, $this->authenticated, $this->roles, $this->attributes ));
    }

    public function unserialize( $serialized )
    {
        list( $this->user, $this->authenticated, $this->roles, $this->attributes ) = unserialize( $serialized );
    }

    public function __toString()
    {
        $class = substr( $class, strrpos(get_class($this), '\\') +1 );
        $roles = array();
        foreach ( $this->roles as $role ) $roles[] = $role->getRole();
        return sprintf( '%s(user="%s", authenticated=%s, roles="%s")', $class, $this->getUsername(), json_encode( $this->authenticated ), implode( ', ', $roles ));
    }
}
