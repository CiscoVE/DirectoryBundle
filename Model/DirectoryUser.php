<?php

namespace CiscoSystems\DirectoryBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class DirectoryUser implements UserInterface
{
    private $username;
    private $roles;

    public function getUsername()
    {
        return $this->username;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword() {} // not implemented

    public function getSalt() {} // not implemented

    public function eraseCredentials() {} // not implemented

    public function isEqualTo( UserInterface $user )
    {
        if ( !$user instanceof DirectoryUser ) return false;
        if ( $this->username !== $user->getUsername() ) return false;
        return true;
    }
}
