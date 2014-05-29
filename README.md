Cisco Global VE directory bundle
================================

Symfony bundle for accessing Active Directory servers.

## Installation

Add the bundle to the requirements in your composer.json file:

    "cisco-systems/directory-bundle": "dev-master"

Register the bundle in your AppKernel:

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new CiscoSystems\DirectoryBundle\CiscoSystemsDirectoryBundle(),
            // ...
        );
        // ...
        return $bundles;
    }

## Configuration

Add configuration for the bundle to your config.yml file. You can configure as many directories as you like. A minimal bundle configuration would look like the following:

    cisco_systems_directory:
        default_directory: main
        directories:
            main:
                servers:
                    primary:
                        host: ads1.example.com

A more extensive configuration might look like this:

    cisco_systems_directory:
        default_directory: main
        directories:
            main:
                repository: 'MyProject\\MyBundle\\Directory\\MyDirectoryRepository'
                default_rdn: '%myAppsLdapUsername%'
                default_password: '%myAppsLdapPassword%'
                servers:
                    primary:
                        host: ads1.example.com
                    secondary:
                        host: ads2.example.com

It's generally advisable to put usernames and passwords into your parameters.yml file and reference those parameters as shown.

Note the `repository` key in the configuration example above: this can be used to create custom query repositories in your application level bundles, containing methods tailored to your directory and your application.

If left unconfigured, the default repository provided by this bundle offers a simple `search()` method that should cover most needs. The default repository class is `CiscoSystems\DirectoryBundle\Directory\QueryRepository`.

## Usage

In your controller you then simply request the query repository for your directory from the service provided by this bundle and call a repository method on it. If you do not provide a parameter for getRepository() it will use the configured default directory.

    // Example using the configured default directory and the default query repository:
    $repository = $this->get( 'cisco.ldap' )->getRepository();
    $result = $repository->search( $baseDn, $filter );

    // Example using a specific directory and a custom query repository:
    $repository = $this->get( 'cisco.ldap' )->getRepository( 'main' );
    $result = $repository->myCustomRepositoryMethod( $parameter, $anotherParameter );

## Custom query repositories

If you need more than the basic `search()` method provided by the default repository, you can define your own query repositories in your application-level bundles. Simply extend the default repository and use the `repository` key as shown in the configuration example above to let the `cisco.ldap` service know what class it needs to instantiate.

The default repository class you will need to extend has a `$link` property that you can provide to PHP's `ldap_*` functions when writing your custom repository methods.

## Performing an LDAP bind with a specific RDN and password

If you do not configure a default RDN and password, or if you want to use different ones in your code somewhere, you can do that in two ways.

1. Specify the configured directory name when calling `getRepository()`, and add the RDN and password as further parameters:

    $repository = $this->get( 'cisco.ldap' )->getRepository( 'main', $bindRdn, $bindPassword );

2. Or use the repository's `bind()` method for binding to a specific RDN and password combination:

    $repository->bind( $bindRdn, $bindPassword );

That's all.
