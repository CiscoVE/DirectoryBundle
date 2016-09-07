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
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->decodePassword( $this->password, $this->username );
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
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $keySize = mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);
        $key = substr (hash('sha256', $username), 0, $keySize);

        $encodedPassword = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $password, MCRYPT_MODE_CBC, $iv);
        $encryptedB64Data = base64_encode($iv.$encodedPassword);
        return $encryptedB64Data;
    }

    private function decodePassword( $password, $username )
    {

        $data = base64_decode($password, true);
        $keySize = mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $key = substr (hash('sha256', $username), 0, $keySize);
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

        $iv = substr ($data, 0, $ivSize);
        $data = substr ($data, $ivSize);
        $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv);
        $decodedPassword = rtrim($data, "\0");

        return $decodedPassword;
    }
}
