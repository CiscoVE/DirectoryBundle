<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

class DirectoryPasswordEncoder
{
    static public function encode( $username, $password )
    {
        $block = mcrypt_get_block_size( 'des', 'ecb' );
        $pad = $block - ( strlen( $password ) % $block );
        $password .= str_repeat( chr( $pad ), $pad );
        $encodedPassword = mcrypt_encrypt( MCRYPT_DES, $username, $password, MCRYPT_MODE_ECB, 7 );
        return $encodedPassword;
    }

    static public function decode( $username, $password )
    {
        $str = mcrypt_decrypt( MCRYPT_DES, $username, $password, MCRYPT_MODE_ECB, 7 );
        $pad = ord( $str[( $len = strlen( $str ) ) - 1] );
        $decodedPassword = substr( $str, 0, strlen( $str ) - $pad );
        return $decodedPassword;
    }
}
