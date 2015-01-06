<?php

namespace CiscoSystems\DirectoryBundle\Directory;

use CiscoSystems\DirectoryBundle\Directory\Node;

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
        // This is FINAL because of the way directory repositories are instantiated by the manager
    }

    /**
     * Find a person in directory and return their data
     *
     * @param string $username
     *
     * @throws \Exception
     *
     * @return CiscoSystems\DirectoryBundle\Directory\Node
     */
    public function findUser( $username = "" )
    {
        if ( !array_key_exists( 'default_base_dn', $this->directoryConfiguration ))
        {
            throw new \Exception( 'Base DN must be configured for this directory in order to retrieve user data!' );
        }
        $results = $this->search( "(&(objectClass=person)(cn=" . $username . "))" );
        if ( count( $results ) > 0 )
        {
            return $results[0];
        }
    }

    /**
     * Get a value for an attribute from a search result
     */
    public function getValue( $searchResultItem, $attribute )
    {
        return isset( $searchResultItem[$attribute][0] ) ? $searchResultItem[$attribute][0] : '';
    }

    /**
     * Perform a directory search
     *
     * @param string $filter
     * @param string $baseDistinguishedName
     * @param array  $attributes
     * @param number $attrsonly
     * @param number $sizelimit
     * @param number $timelimit
     * @param string $deref
     *
     * @return CiscoSystems\DirectoryBundle\Directory\Node
     */
    final public function search(
            $filter = '',
            $baseDistinguishedName = '',
            array $attributes = array(),
            $attrsonly = 0,
            $sizelimit = 0,
            $timelimit = 0,
            $deref = LDAP_DEREF_NEVER )
    {
        $baseDn = $baseDistinguishedName ?: $this->directoryConfiguration['default_base_dn'];
        $res = ldap_search(
                   $this->link,
                   $baseDn,
                   $filter,
                   $attributes,
                   $attrsonly,
                   $sizelimit,
                   $timelimit,
                   $deref
                ) or ldap_error( $this->link );
        $result = new Node( @ldap_get_entries( $this->link, $res ));
        return $result;
    }

    /**
     * @param unknown_type $link
     *
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
     *
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
     *
     * @return \CiscoSystems\DirectoryBundle\Directory\QueryRepository
     */
    final public function bind( $rdn = null, $password = null )
    {
        if ( null !== $rdn ) $rdn .= $this->directoryConfiguration['bind_rdn_suffix'];
        @ldap_set_option( $this->link, LDAP_OPT_PROTOCOL_VERSION, $this->directoryConfiguration['protocol_version'] );
        @ldap_set_option( $this->link, LDAP_OPT_REFERRALS, $this->directoryConfiguration['referrals'] );
        @ldap_set_option( $this->link, LDAP_OPT_NETWORK_TIMEOUT, $this->directoryConfiguration['network_timeout'] );
        $bound = ldap_bind( $this->link, $rdn, $password ) or ldap_error( $this->link );
        if ( $bound ) $this->bindRdn = $rdn;
        return $this;
    }
}
