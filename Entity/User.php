<?php

namespace CiscoSystems\DirectoryBundle\Entity;

use CiscoSystems\DirectoryBundle\Model\User as AbstractUser;

class User extends AbstractUser
{
    public function __construct()
    {
        trigger_error(sprintf('%s is deprecated. Extend FOS\UserBundle\Model\User directly.', __CLASS__), E_USER_DEPRECATED);
        parent::__construct();
    }
}
