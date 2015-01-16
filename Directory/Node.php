<?php

/*
 * Class may still need some work but works fine when
 * treated as an array so it is compatible and usable
 */

namespace CiscoSystems\DirectoryBundle\Directory;

use ArrayAccess;

class Node implements ArrayAccess
{
    protected $count = 0;
    protected $dn = "";
    protected $data = array();

    /**
     * Constructor
     */
    public function __construct( array $data = array() )
    {
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
     * Return first value of an attribute
     *
     * @param unknown $key
     * @param string $attr
     *
     * @return string
     */
    public function getFirstAttributeValue( $key, $attr = null )
    {
        // TODO
        return isset( $this->data[$key][0] ) ? $this->data[$key][0] : "";
    }

    /**
     * Return field from DN
     * CN=awolder,OU=Employees,OU=Cisco Users,DC=cisco,DC=com
     */
    public function get( $key, $dn = null )
    {
        $values = array();
        // ...
        return $values;
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
                if ( !array_key_exists( $key, $dnArray )) $dnArray[$key] = array();
                $dnArray[$key][] = $value;
            }
        }
        return $dnArray;
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

    /**
     * Return array representation of this node
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
