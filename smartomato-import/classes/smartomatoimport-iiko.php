<?php

defined( 'ABSPATH' ) || exit;

class STI_Iiko{

    public static function auth( $key = null ){

        if( !$key ) return null;

        return true;
    }

    public static function set_webhook( $token, $revoke = null ){

        if( !$token ) return null;

        return null;
    }
}