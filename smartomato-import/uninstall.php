<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once PLUGIN_DIR . '/classes/smartomatoimport-main.php';
require_once PLUGIN_DIR . '/classes/smartomatoimport-db.php';

function clear_plugin_data(){
    wp_clear_scheduled_hook( 'STI_run_exchange' );
    STI_Database::drop_tables();
    $options = STI_Main::default_options();
    
    if( !empty( $options ) ){
        foreach( array_keys( $options ) as $option ){
            delete_option( $option );
        };
    };
}

if ( is_multisite() && is_network_admin() ) {
    global $wpdb;
    $blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        clear_plugin_data();
    }
    switch_to_blog( $original_blog_id );
} else{
    clear_plugin_data();
}

wp_cache_flush();