<?php

namespace CiscoSystems\DirectoryBundle\Directory;

use CiscoSystems\DirectoryBundle\Directory\QueryRepository;
use InvalidArgumentException;
use Exception;

class DirectoryManager
{
    protected $configuration;
    protected $baseRepositoryClassName;
    protected $connectedRepositories;

    /**
     * @param array $configuration
     */
    public function __construct( array $configuration = array(), $baseRepositoryClassName = "" )
    {
        $this->configuration = $directoryConfiguration;
        $this->baseRepositoryClassName = $baseRepositoryClassName;
        $this->connectedRepositories = array();
    }

    /**
     * Get a query repository for a given directory
     *
     * Parameter $directoryName specifies which of the configured directory to use.
     * Not providing the parameter will result in the default directory being used.
     *
     * Parameters $bindRdn and $bindPassword are used to bind to this directory.
     * Not providing these parameters will result in the default RDN being used.
     *
     * The parameter $performAnonymousBind forces an anonymous bind. An anonymous
     * bind will also be performed if no RDN is provided here and no default RDN
     * is configured for this directory, regardless of this parameter being set to
     * true.
     *
     * @param string $directoryName
     * @param string $bindRdn
     * @param string $bindPassword
     * @param boolean $performAnonymousBind
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @return \CiscoSystems\DirectoryBundle\Directory\QueryRepository
     */
    public function getRepository( $directoryName = "", $bindRdn = "", $bindPassword = "", $performAnonymousBind = false )
    {
        $repository = null;
        // Use the default directory if no directory specified
        if ( "" == $directoryName )
        {
            $directoryName = $this->configuration['default_directory'];
        }
        // Grab the configuration data for the specified directory
        if ( !array_key_exists( $directoryName, $this->configuration['directories'] ))
        {
            throw new InvalidArgumentException( "Directory '" . $directoryName . "' is not configured." );
        }
        $directoryConfiguration = $this->configuration['directories'][$directoryName];
        // Check if we already have a repository connected to the requested directory
        if ( array_key_exists( $directoryName, $this->connectedRepositories ))
        {
            $repository = $this->connectedRepositories[$directoryName];
            // Check if we need to bind another RDN
            if ( "" != $bindRdn )
            {
                if ( $repository->getBindRdn() != $bindRdn )
                {
                    $repository->bind( $bindRdn, $bindPassword );
                }
            }
        }
        else
        {
            // If not, try to instantiate and connect a new one
            foreach ( $directoryConfiguration['servers'] as $server )
            {
                $link = @ldap_connect( $server['host'], $server['port'] );
                if ( $link )
                {
                    // Perform initial bind
                    if ( $performAnonymousBind )
                    {
                        $bindRdn = null;
                        $bindPassword = null;
                    }
                    elseif ( "" == $bindRdn || "" == $bindPassword )
                    {
                        $bindRdn = $directoryConfiguration['default_rdn'];
                        $bindPassword = $directoryConfiguration['default_password'];
                    }
                    $repositoryClass = $directoryConfiguration['repository'];
                    if ( !class_exists( $repositoryClass ))
                    {
                        throw new Exception( "Directory repository class " . $repositoryClass . " is not defined." );
                    }
                    if ( !in_array( $this->baseRepositoryClassName, class_parents( $repositoryClass )))
                    {
                        throw new Exception( "Directory repository class " . $repositoryClass . " must extend " . $this->baseRepositoryClassName . "." );
                    }
                    $repository = new $repositoryClass( $link );
                    $repository->setDirectoryConfiguration( $directoryConfiguration )
                               ->bind( $bindRdn, $bindPassword );
                    $this->connectedRepositories[$directoryName] = $repository;
                    break;
                }
            }
        }
        if ( null == $repository )
        {
            throw new Exception( "Could not connect to directory '" . $directoryName . "'" );
        }
        // Return the connected repository for use in controller code
        return $repository;
    }
}
