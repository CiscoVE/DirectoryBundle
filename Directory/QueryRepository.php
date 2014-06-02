<?php

namespace CiscoSystems\DirectoryBundle\Directory;

class QueryRepository
{
    protected $link;
    protected $directoryConfiguration;
    protected $bindRdn;

    /**
     * Constructor
     */
    final public function __construct()
    {
    }

    /**
     *
     * @param string $username
     * @throws \Exception
     * @return array
     */
    public function findUser( $username = "" )
    {
        if ( !array_key_exists( 'default_base_dn', $this->directoryConfiguration ))
        {
            throw new \Exception( 'Base DN must be configured for this directory in order to retrieve user data!' );
        }
        $baseDn = $this->directoryConfiguration['default_base_dn'];
        $result = $this->search( $baseDn, "(&(objectClass=person)(cn=" . $username . "))" );
        if ( count( $result ) > 0 )
        {
            return $result[0];
        }
    }

    /**
     * Perform a directory search
     *
     * @param string $baseDistinguishedName
     * @param string $filter
     * @param array  $attributes
     * @param number $attrsonly
     * @param number $sizelimit
     * @param number $timelimit
     * @param string $deref
     */
    final public function search(
            $baseDistinguishedName = '',
            $filter = '',
            array $attributes = array(),
            $attrsonly = 0,
            $sizelimit = 0,
            $timelimit = 0,
            $deref = LDAP_DEREF_NEVER )
    {
        $res = ldap_search(
                   $this->link,
                   $baseDistinguishedName,
                   $filter,
                   $attributes,
                   $attrsonly,
                   $sizelimit,
                   $timelimit,
                   $deref
                ) or ldap_error( $this->link );
        return @ldap_get_entries( $this->link, $res );
    }

    /**
     * @param unknown_type $link
     * @return \CiscoSystems\DirectoryBundle\Directory\QueryRepository
     */
    final public function setLink( $link )
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string
     */
    final public function getBindRdn()
    {
        return $this->bindRdn;
    }

    /**
     * @return string
     */
    final public function getDirectoryConfiguration()
    {
        return $this->directoryConfiguration;
    }

    /**
     * @param array $directoryConfiguration
     * @return \CiscoSystems\DirectoryBundle\Directory\QueryRepository
     */
    final public function setDirectoryConfiguration( array $directoryConfiguration = array() )
    {
        $this->directoryConfiguration = $directoryConfiguration;
        return $this;
    }

    /**
     * Bind to directory
     *
     * If $relativeDistinguishedName and $password are not
     * supplied, the client will attempt an anonymous bind
     *
     * @param string $relativeDistinguishedName
     * @param string $password
     * @return \CiscoSystems\DirectoryBundle\Directory\QueryRepository
     */
    final public function bind( $rdn = null, $password = null )
    {
        @ldap_set_option( $this->link, LDAP_OPT_PROTOCOL_VERSION, $this->directoryConfiguration['protocol_version'] );
        @ldap_set_option( $this->link, LDAP_OPT_REFERRALS, $this->directoryConfiguration['referrals'] );
        @ldap_set_option( $this->link, LDAP_OPT_NETWORK_TIMEOUT, $this->directoryConfiguration['network_timeout'] );
        $bound = ldap_bind( $this->link, $rdn, $password ) or ldap_error( $this->link );
        if ( $bound ) $this->bindRdn = $rdn;
        return $this;
    }
}
