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
    public function __construct( $link )
    {
        $this->link = $link;
    }

    /**
     * Perform a directory search
     *
     * @param string $baseDistinguishedName
     * @param string $filter
     * @param array $attributes
     * @param number $attrsonly
     * @param number $sizelimit
     * @param number $timelimit
     * @param string $deref
     */
    public function search(
            $baseDistinguishedName = '',
            $filter = '',
            array $attributes = array(),
            $attrsonly = 0,
            $sizelimit = 0,
            $timelimit = 0,
            $deref = LDAP_DEREF_NEVER
    ){
        $res = ldap_search(
                $this->link,
                $baseDistinguishedName,
                $filter,
                $attributes,
                $attrsonly,
                $sizelimit,
                $timelimit,
                $deref
        ) or exit( "Unable to search LDAP server." );
        return @ldap_get_entries( $this->link, $res );
    }

    /**
     * @return string
     */
    public function getBindRdn()
    {
        return $this->bindRdn;
    }

    /**
     * @return string
     */
    public function getDirectoryConfiguration()
    {
        return $this->directoryConfiguration;
    }

    /**
     * @param array $directoryConfiguration
     * @return \CiscoSystems\DirectoryBundle\Directory\QueryRepository
     */
    public function setDirectoryConfiguration( array $directoryConfiguration = array() )
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
    public function bind( $rdn = null, $password = null )
    {
        $this->bindRdn = $rdn;
        @ldap_set_option( $this->link, LDAP_OPT_PROTOCOL_VERSION, $this->directoryConfiguration['protocol_version'] );
        @ldap_set_option( $this->link, LDAP_OPT_REFERRALS,        $this->directoryConfiguration['referrals'] );
        @ldap_set_option( $this->link, LDAP_OPT_NETWORK_TIMEOUT,  $this->directoryConfiguration['network_timeout'] );
        if ( @ldap_bind( $this->link, $rdn, $password )) return $this;
        throw new \Exception( "LDAP bind failed." );
    }
}
