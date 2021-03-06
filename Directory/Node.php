<?php

/*
 * Class may still need some work but works fine when
 * treated as an array so it is compatible and usable
 */

namespace CiscoSystems\DirectoryBundle\Directory;

use ArrayAccess;
use Iterator;

class Node implements ArrayAccess, Iterator
{
    protected $count = 0;
    protected $dn = "";
    protected $data = array();
    protected $position = 0;

    /**
     * Constructor
     */
    public function __construct( array $data = array() )
    {
        $this->position = 0;
        foreach ( $data as $key => $value )
        {
            $this[$key] = $value;
            if ( "count" === $key && is_integer( $value )) $this->count = $value;
            elseif ( "dn" === $key && is_string( $value )) $this->dn = $value;
        }
    }

    ///////////////////////////////////////////////
    // OOP getters for LDAP node specific fields //
    ///////////////////////////////////////////////

    /**
     * Return the DN for this node
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * Return the count of child nodes
     */
    public function getCount()
    {
        return $this->count;
    }

    /////////////////////////
    // Convenience methods //
    /////////////////////////

    /**
     * Return first value of an attribute.
     * Optional second and third parameters
     * extract value from a DN value string.
     *
     * @param string $key
     * @param string $dnAttr
     * @param integer $dnIndex
     *
     * @return string
     */
    public function getFirstAttributeValue( $key, $dnAttr = null, $dnIndex = 0 )
    {
        $value = "";
        if ( array_key_exists( $key, $this->data ) && count( $this->data[$key] ) > 0 )
        {
            $value = $this->data[$key][0];
            if ( null !== $dnAttr )
            {
                $dnAttr = strtolower( $dnAttr );
                $dnArray = $this->dnToArray( $value );
                if ( array_key_exists( $dnAttr, $dnArray ))
                {
                    $values = $dnArray[$dnAttr];
                    if ( isset( $values[$dnIndex] ))
                    {
                        $value = $values[$dnIndex];
                    }
                }
            }
        }
        return $value;
    }
    
    /**
     * Return all values of an attribute.
     *
     * @param string $key
     *
     * @return array
     */
    public function getAttributeValues( $key )
    {
        if ( array_key_exists( $key, $this->data ) && count( $this->data[$key] ) > 0 )
        {
            return $this->data[$key]->toArray();
        }
    }

    /**
     * Return array representation of a DN string
     *
     * @param string $attr
     *
     * @return array
     */
    public function dnToArray( $dn = null )
    {
        $dnArray = array();
        $dn = $dn ?: $this->dn;
        if ( $dn )
        {
            $attributeValueStrings = explode( ',', $dn );
            foreach ( $attributeValueStrings as $attributeValueString )
            {
                list( $key, $value ) = explode( '=', $attributeValueString );
                $key = strtolower( $key );
                if ( !array_key_exists( $key, $dnArray )) $dnArray[$key] = array();
                $dnArray[$key][] = $value;
            }
        }
        return $dnArray;
    }

    /**
     * Return array representation of this node
     *
     * @return array
     */
    public function toArray()
    {
        $data = $this->data;
        if ( array_key_exists( "count", $data )) unset( $data["count"] );
        foreach ( $data as $key => $value ) if ( $value instanceof self ) $data[$key] = $value->toArray();
        return $data;
    }

    /////////////////////////////////////////////////////////
    // Methods required to be implemented for array access //
    /////////////////////////////////////////////////////////

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
    
    //////////////////////////////////////////
    // Methods required for Iterator access //
    //////////////////////////////////////////

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }
}
