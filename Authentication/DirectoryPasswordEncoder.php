<?php

namespace CiscoSystems\DirectoryBundle\Authentication;

use Symfony\Component\HttpFoundation\Session\Session;

class DirectoryPasswordEncoder
{
    static public function encode( $password )
    {
        $uid   = self::setUniqueEncryptionId();
        $block = mcrypt_get_block_size( 'des', 'ecb' );
        $pad = $block - ( strlen( $password ) % $block );
        $password .= str_repeat( chr( $pad ), $pad );
        $encodedPassword = mcrypt_encrypt( MCRYPT_DES, $uid, $password, MCRYPT_MODE_ECB, 7 );
        return $encodedPassword;
    }

    static public function decode( $password )
    {
        $str = mcrypt_decrypt( MCRYPT_DES, self::getUniqueEncryptionId(), $password, MCRYPT_MODE_ECB, 7 );
        $pad = ord( $str[( $len = strlen( $str ) ) - 1] );
        $decodedPassword = substr( $str, 0, strlen( $str ) - $pad );
        return $decodedPassword;
    }

    static public function setUniqueEncryptionId(SESSION $session)
    {
        return $session->set('directoryEncryptionId', self::gen_uuid());
    }

    static public function getUniqueEncryptionId(SESSION $session)
    {
        return $session->get('directoryEncryptionId');
    }

    static public function gen_uuid()
    {
        return sprintf(

                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

                // 32 bits for "time_low"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

                // 16 bits for "time_mid"
                mt_rand( 0, 0xffff ),

                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand( 0, 0x0fff ) | 0x4000,

                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand( 0, 0x3fff ) | 0x8000,

                // 48 bits for "node"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )

        );
    }
}
