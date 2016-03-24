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
//         $this->password = DirectoryPasswordEncoder::encode( $credentials );
        $this->password = $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->password;
//         return DirectoryPasswordEncoder::decode( $this->password );
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
