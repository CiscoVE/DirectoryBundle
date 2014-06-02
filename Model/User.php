<?php

namespace CiscoSystems\DirectoryBundle\Model;

use Symfony\Component\Security\Core\User\EquatableInterface;
use FOS\UserBundle\Model\User as BaseUser;
use CiscoSystems\DirectoryBundle\Model\UserInterface;
use CiscoSystems\DirectoryBundle\Security\Encoder\DirectoryPasswordEncoder;

/**
 * Storage agnostic user object
 */
abstract class User extends BaseUser implements UserInterface, EquatableInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt( $createdAt )
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt( $updatedAt )
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return boolean
     */
    public function isEqualTo( UserInterface $user )
    {
        if ( !$user instanceof DirectoryUser ) return false;
        if ( $this->username !== $user->getUsername() ) return false;
        return true;
    }

    /*
     * OVERRIDES:
     */

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserInterface::getPassword()
     */
    public function getPassword()
    {
        return DirectoryPasswordEncoder::decode( $this->username, $this->password );
    }

    public function setPassword( $passwordUnencrypted )
    {
        $this->password = DirectoryPasswordEncoder::encode( $this->username, $passwordUnencrypted );
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserInterface::getSalt()
     */
    public function getSalt() {}

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserInterface::eraseCredentials()
     */
    public function eraseCredentials() {}
}
