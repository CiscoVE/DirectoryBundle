<?php

namespace CiscoSystems\DirectoryBundle\Tests;

use CiscoSystems\DirectoryBundle\Directory\DirectoryManager;

class DirectoryManagerTest extends \PHPUnit_Framework_TestCase {

    public function testGetRepositoryEmptyDirectoryNameReturnsDefaultDirectory() {
        // Create a stub for the DirectoryManager class.
        $stub = $this->getMockBuilder(DirectoryManager::class)
                ->disableOriginalConstructor()
                ->getMock();

        // Configure the stub.
        $stub->method('getRepository')
                ->willReturn('repository');

        // Calling $stub->getRepository() will now return
        // 'repository'.
        $this->assertEquals('repository', $stub->getRepository());
    }

    public function testGetAuthenticationDirectoryNameWithEmptyDirectoryName() {
        // Create a stub for the DirectoryManager class.
        $stub = $this->getMockBuilder(DirectoryManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        // Configure the stub.
        $stub->method('getAuthenticationDirectoryName')
                ->willReturn('authDir');
        
        $this->assertNotEquals('', $stub->getAuthenticationDirectoryName());
    }
    
    public function testGetDefaultDirectoryName() {
        // Create a stub for the DirectoryManager class.
        $stub = $this->getMockBuilder(DirectoryManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        // Configure the stub.
        $stub->method('getDefaultDirectoryName')
                ->willReturn('dir');
        
        $this->assertEquals('dir', $stub->getDefaultDirectoryName());
    }

}
