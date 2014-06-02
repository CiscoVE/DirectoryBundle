<?php

namespace CiscoSystems\DirectoryBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

class StorageAgnosticObjectManager
{
    /**
     * Injected storage-specific object manager
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Fully qualified name of the object class managed by this service
     *
     * @var string
     */
    protected $class;

    /**
     * Constructor
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @param string $className
     */
    public function __construct( ObjectManager $om, $className )
    {
        $this->objectManager = $om;
        $this->class = $className;
    }

    /**
     * Create an object
     *
     * @return mixed
     */
    public function create()
    {
        $object = new $this->class;
        return $object;
    }

    /**
     * Persist an object
     *
     * @param mixed $object
     */
    public function persist( $object )
    {
        $this->objectManager->persist( $object );
    }

    /**
     * Flush object data to disk
     */
    public function flush()
    {
        $this->objectManager->flush();
    }

    /**
     * Retrieve an object
     *
     * @param array $criteria
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function findOneBy( array $criteria = array() )
    {
        $pos = strrpos( $this->class, '\\' );
        if ( !$pos )
        {
            throw new \InvalidArgumentException( __FILE__ . ' line ' . __LINE__ . ': supplied class name is invalid.' );
        }
        $className = substr( $this->class, $pos + 1 );
        return $this->objectManager
                    ->getRepository( 'CiscoSystemsDirectoryBundle:' . $className )
                    ->findOneBy( $criteria );
    }

    /**
     * Retrieve a collection of objects
     *
     * @param array $criteria
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function findBy( array $criteria = array() )
    {
        $pos = strrpos( $this->class, '\\' );
        if ( !$pos )
        {
            throw new \InvalidArgumentException( __FILE__ . ' line ' . __LINE__ . ': supplied class name is invalid.' );
        }
        $className = substr( $this->class, $pos + 1 );
        return $this->objectManager
                    ->getRepository( 'CiscoSystemsDirectoryBundle:' . $className )
                    ->findBy( $criteria );
    }
}
