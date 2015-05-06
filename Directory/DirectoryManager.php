<?php

namespace CiscoSystems\DirectoryBundle\Directory;

use CiscoSystems\DirectoryBundle\Directory\QueryRepository;
use Psr\Log\LoggerInterface;

class DirectoryManager
{
    protected $configuration;
    protected $baseRepositoryClassName;
    protected $connectedRepositories;
    protected $tokenStorage;
    protected $logger;

    /**
     * @param array $configuration
     */
    public function __construct( array $configuration = array(), $baseRepositoryClassName = "", $tokenStorage, LoggerInterface $logger )
    {
        $this->configuration = $configuration;
        $this->baseRepositoryClassName = $baseRepositoryClassName;
        $this->connectedRepositories = array();
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
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
            throw new \InvalidArgumentException( "Directory '" . $directoryName . "' is not configured." );
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
                    else
                    {
                        $rdnDefault = $directoryConfiguration['default_relative_dn'];
                        $pwdDefault = $directoryConfiguration['default_password'];
                        if ( $directoryConfiguration['bind_authenticated_user'] )
                        {
                            if ( $token = $this->tokenStorage->getToken() )
                            {
                                $rdnDefault = $token->getUsername();
                                $pwdDefault = $token->getPassword();
                            }
                        }
                        $bindRdn = $bindRdn ?: $rdnDefault;
                        $bindPassword = $bindPassword ?: $pwdDefault;
                    }
                    $repository = $this->instantiateRepositoryClass( $directoryConfiguration['repository'] );
                    $repository->setDirectoryConfiguration( $directoryConfiguration )
                               ->setLink( $link )
                               ->bind( $bindRdn, $bindPassword );
                    if ( !$repository->isBound() )
                    {
                        $repository = null;
                    }
                    else
                    {
                        $this->connectedRepositories[$directoryName] = $repository;
                    }
                    break;
                }
            }
        }
        if ( null == $repository )
        {
            throw new \Exception( "Could not instantiate query repository for directory '" . $directoryName . "'" );
        }
        // Return the connected repository for use in controller code
        return $repository;
    }

    /**
     * @param string $repositoryClass
     * @throws \Exception
     * @return \CiscoSystems\DirectoryBundle\Directory\QueryRepository
     */
    public function instantiateRepositoryClass( $repositoryClass )
    {
        if ( !class_exists( $repositoryClass ))
        {
            throw new \Exception( "Directory repository class " . $repositoryClass . " is not defined." );
        }
        if ( $repositoryClass == $this->baseRepositoryClassName
           || in_array( $this->baseRepositoryClassName, class_parents( $repositoryClass )))
        {
            return new $repositoryClass();
        }
        throw new \Exception( "Directory repository class " . $repositoryClass
                            . " must extend " . $this->baseRepositoryClassName . "." );
    }

    /**
     * Return name of directory used for user authentication
     *
     * @return string
     */
    public function getAuthenticationDirectoryName()
    {
        // make sure the authentication directory is configured
        // otherwise fall back to the configured default directory
        $authDir = $this->configuration['default_directory'];
        if ( array_key_exists( 'authentication_directory', $this->configuration ))
        {
            $dir = $this->configuration['authentication_directory'];
            if ( array_key_exists( $dir, $this->configuration['directories'] ))
            {
                $authDir = $dir;
            }
        }
        return $authDir;
    }

    /**
     * Return name of configured default directory
     *
     * @throws \Exception
     * @return string
     */
    public function getDefaultDirectoryName()
    {
        // make sure the default directory is configured
        $dir = $this->configuration['default_directory'];
        if ( array_key_exists( $dir, $this->configuration['directories'] ))
        {
            return $dir;
        }
        throw new \Exception( "Default directory not configured!" );
    }
}
