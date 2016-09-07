<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

use Symfony\Component\Security\Core\User\UserInterface;

class DirectoryUser implements UserInterface
{
    protected $username;
    protected $password;
    protected $salt;
    protected $roles;

    public function __construct( $username, $password, $salt, array $roles )
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function eraseCredentials()
    {
        // No need to implement this,
        // credentials not stored locally
    }
}
