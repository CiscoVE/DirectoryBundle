<?php

/*
 * Class may still need some work but works fine when
 * treated as an array so it is commpatible and usable
 */

namespace CiscoSystems\DirectoryBundle\Directory;

use ArrayAccess;

class Node implements ArrayAccess
{
    protected $data = array();
//     protected $count = 0;
//     protected $dn = "";

    /**
     * Constructor
     */
    public function __construct( array $data = array() )
    {
        foreach ( $data as $key => $value ) $this[$key] = $value;
    }

    /*
     * Getters and setters for LDAP node specific fields
     */

    /**
     * Return the DN for this node
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * Set the DN for this node
     */
    public function setDn( $dn )
    {
        $this->dn = $dn;
    }

    /**
     * Return the count of child nodes
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set the count of child nodes
     */
    public function setCount( $count )
    {
        $this->count = $count;
    }

    /*
     * Convenience methods
     */
    public function getFirstAttributeValue( $attr )
    {
        return isset( $this->data[$attr][0] ) ? $this->data[$attr][0] : "";
    }

    /*
     * Methods required to be implemented for array access
     */

    /**
     * @param mixed $offset
     *
     * @return boolean
     */
    public function offsetExists( $offset )
    {
        return isset( $this->data[$offset] );
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet( $offset )
    {
        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $data
     */
    public function offsetSet( $offset, $data )
    {
        if ( is_array( $data )) $data = new self( $data );
        if ( null === $offset ) $this->data[] = $data;
        else $this->data[$offset] = $data;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset( $offset )
    {
        unset( $this->data[$offset] );
    }

    /**
     * Method necessary for deep copies
     */
    public function __clone()
    {
        foreach ( $this->data as $key => $value )
        {
            if ( $value instanceof self ) $this[$key] = clone $value;
        }
    }

    /**
     * Return array representation
     *
     * @return array
     */
    public function toArray()
    {
        $data = $this->data;
        foreach ( $data as $key => $value ) if ( $value instanceof self ) $data[$key] = $value->toArray();
        return $data;
    }
}
