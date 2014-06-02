<?php

namespace CiscoSystems\DirectoryBundle\Model;

use FOS\UserBundle\Model\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return \CiscoSystems\DirectoryBundle\Model\UserInterface
     */
    public function setUpdatedAt( $updatedAt );

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return \CiscoSystems\DirectoryBundle\Model\UserInterface
     */
    public function setCreatedAt( $createdAt );

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt();
}
