<?php

namespace CiscoSystems\DirectoryBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\Session\Session;
use Twig_Extension;
use Twig_Function_Method;
use CiscoSystems\DirectoryBundle\Authentication\DirectoryUser;
use CiscoSystems\DirectoryBundle\Directory\DirectoryManager;
use CiscoSystems\DirectoryBundle\Directory\Node;

class DirectoryExtension extends Twig_Extension
{
    protected $ldap;
    protected $session;
    protected $authenticationChecker;

    public function __construct( DirectoryManager $directoryManager, Session $session, $authenticationChecker )
    {
        $this->ldap = $directoryManager;
        $this->session = $session;
        $this->authenticationChecker = $authenticationChecker;
    }

    public function getName()
    {
        return 'directory_extension';
    }

    public function getFunctions()
    {
        return array(
            'ldap_value_for_user' => new Twig_Function_Method( $this, 'ldapValueForUser' ),
        );
    }

    protected function loadUserDataFromSessionBeforeLdap( $username )
    {
        // make sure we have a container array
        if ( !$this->session->has( 'ldap_user_data' ))
        {
            $this->session->set( 'ldap_user_data', array() );
        }
        $data = $this->session->get( 'ldap_user_data' );
        // if data for user isn't set in there, load from directory
        if ( !array_key_exists( $username, $data ))
        {
            $ldapUserData = $this->ldap->getRepository()->findUser( $username );
            $data[$username] = $ldapUserData->toArray();
            $this->session->set( 'ldap_user_data', $data );
        }
        return $data[$username];
    }

    /**
     * Method behind Twig function ldap_value_for_user()
     *
     * @param mixed $user LDAP user
     * @param string $key LDAP key name
     *
     * Usage in Twig template:
     *
     * Using an instance of CiscoSystems\DirectoryBundle\Authentication\DirectoryUser
     *
     *  {{ ldap_value_for_user( user, 'description' ) }}
     *
     * Using a username
     *
     *  {{ ldap_value_for_user( 'awolder', 'description' ) }}
     */
    public function ldapValueForUser( $user, $key )
    {
        $value = "";
        if ( $this->authenticationChecker->isGranted( 'IS_AUTHENTICATED_FULLY' ))
        {
            if ( $user instanceof DirectoryUser ) $user = $user->getUsername();
            $data = $this->loadUserDataFromSessionBeforeLdap( $user );
            if ( !( $data instanceof Node )) $data = new Node( $data );
            $value = $data->getFirstAttributeValue( $key );
        }
        return $value;
    }
}