<?php
/*
Plugin Name:  Импорт товаров Smartomato
Description:  Импорт и сохранение товаров с сервиса Smartomato. Плагин создан для внутреннего использования и продолжает дорабатываться, в случае возникновения неисправностей обращаться в телеграм: @alex_stk2
Version:      1.1.4
Author:       Nervniy 
*/

defined( 'ABSPATH' ) || exit;

define( 'SMARTOMATO_IMPORT_VERSION', '1.1.4' );

define( 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_FILE', __FILE__ );

if ( version_compare( PHP_VERSION, '7.3', '<' ) ) {
    function STI_wrong_version_notice(){
        echo '<div class="notice-error"><p>Для активации плагина требуется версия php 7.3 и новее.</p></div>';
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
    function STI_deactivate_self(){
        deactivate_plugins( plugin_basename( PLUGIN_FILE ) );
    }

    add_action( 'admin_notices', 'STI_wrong_version_notice' );
    add_action( 'admin_init', 'STI_deactivate_self' );

    return;
}

require_once PLUGIN_DIR . '/classes/smartomatoimport-main.php';
require_once PLUGIN_DIR . '/classes/smartomatoimport-db.php';
require_once PLUGIN_DIR . '/classes/smartomatoimport-api.php';
require_once PLUGIN_DIR . '/classes/smartomatoimport-iiko.php';

function smartomatoimport() {

    static $plugin = null;

    if ( $plugin === null ) {
        $plugin = new STI_Main( SMARTOMATO_IMPORT_VERSION, PLUGIN_FILE, PLUGIN_DIR );
    }

    return $plugin;
}

smartomatoimport()->run();

if( get_option( 'sti_past_exchange_date' ) === false ){
    add_action( 'admin_notices', function(){
        echo '<div class="notice notice-success is-dismissible"><p>
            Плагин успешно активирован, а значит мы почти готовы получить блюда. Обмен произойдёт автоматически в 00:00, 
            либо его можно запустить вручную на странице настроек плагина.
        </p></div>';
    } );
}