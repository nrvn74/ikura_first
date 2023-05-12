<?php

defined( 'ABSPATH' ) || exit;

class STI_Api{

    public static function auth( $login = null, $password = null ){
        if( $login === null || $password === null ){
            return null;
        }

        $auth_url = 'http://smartomato.ru/api/session';
        $auth_data = array(
            'login'    => $login,
            'password' => $password 
        );

        $curl = curl_init( $auth_url );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth_data);
      
        $curl_response = curl_exec( $curl );
      
        if ( $curl_response === false ){
            curl_close( $curl );
            return null;
        } else{
            $curl_response = json_decode( $curl_response, true );
            $token = $curl_response['meta']['token'];
            curl_close( $curl );

            if( $token && !empty( $token ) ){
                return $token;
            }
        }
    }

    public static function get_categories( $token = null ){
        if( $token === null ){
            return null;
        }

        $api_url = 'http://smartomato.ru/api/draft_categories';

        $curl = curl_init( $api_url );
    
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Token token=\"" . $token . "\"",
        "Content-Type: application/json"
        ));
    
        $curl_response = curl_exec( $curl );
    
        if ( $curl_response === false ) {
            curl_close( $curl );
            return null;
        } else{
            $curl_response = json_decode( $curl_response, true );
            $categories = $curl_response['draft_categories'];
            curl_close( $curl );

            if( !empty( $categories ) ){
                return $categories;
            } else{
                return null;
            }
        }
    }

    public static function get_dishes( $token = null ){
        if( $token === null ){
            return null;
        }

        $api_url = 'http://smartomato.ru/api/draft_dishes';

        $curl = curl_init( $api_url );
    
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Token token=\"" . $token . "\"",
        "Content-Type: application/json"
        ));
    
        $curl_response = curl_exec( $curl );
    
        if ( $curl_response === false ) {
            curl_close( $curl );
            return null;
        } else{
            $curl_response = json_decode( $curl_response, true );
            $dishes = $curl_response['draft_dishes'];
            curl_close( $curl );

            if( !empty( $dishes ) ){
                return $dishes;
            } else{
                return null;
            }
        }
    }

}