<?php

defined( 'ABSPATH' ) || exit;

class STI_Main{

    protected $version      = null;
    protected $filepath     = null;
    protected $directory    = null;

    public function __construct( $version, $filepath, $directory ){
        $this->version      = $version;
        $this->filepath     = $filepath;
        $this->directory    = $directory;
    }

    public function run(){
        $this->add_hooks();
    }

	public function add_hooks(){
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 90 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'wp_ajax_stipush', array( $this, 'ajax_push_exchange' ) );
        add_action( 'wp_ajax_stisaveoptions', array( $this, 'ajax_save_options' ) );
        add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
        add_action( 'STI_run_exchange', 'STI_Main::run_exchange' );

        add_action( 'wp_ajax_nopriv_ajaxdeliveryload', array( $this, 'ajax_delivery_load' ) );
        add_action( 'wp_ajax_ajaxdeliveryload', array( $this, 'ajax_delivery_load' ) );
        add_action( 'wp_ajax_nopriv_deliverysearch', array( $this, 'delivery_search_ajax' ) );
        add_action( 'wp_ajax_deliverysearch', array( $this, 'delivery_search_ajax' ) );
        add_action( 'wp_ajax_nopriv_deliverysearchresult', array( $this, 'delivery_search_result' ) );
        add_action( 'wp_ajax_deliverysearchresult', array( $this, 'delivery_search_result' ) );

        register_activation_hook( $this->filepath, 'STI_Main::setup' );
        register_deactivation_hook( $this->filepath, 'STI_Main::deactivation' );
	}

    public static function default_options(){
        return array(
            'sti_past_exchange_date'    => '',
            'sti_auth_login'            => '',
            'sti_auth_password'         => '',
            'sti_organization_id'       => '',
            'sti_widget_host'           => '',
            'sti_delivery_enabled'      => 1,
            'sti_exchange_rate'         => 'daily',
            'sti_exchange_time'         => '00:00:00',
            'sti_iiko_api_key'          => '',
            'sti_iiko_api_token'        => '',
            'sti_iiko_webhook_set'      => '0',
        ); 
    }

    public function add_cron_schedules( $schedules ){
        $schedules['every-5-mins'] = array(
			'interval' => 5 * 60,
			'display'  => esc_html( 'Каждые 5 минут' )
        );

		$schedules['every-10-mins'] = array(
			'interval' => 10 * 60,
			'display'  => esc_html( 'Каждые 10 минут' )
        );

        $schedules['every-15-mins'] = array(
			'interval' => 15 * 60,
			'display'  => esc_html( 'Каждые 15 минут' )
        );

        $schedules['every-20-mins'] = array(
			'interval' => 20 * 60,
			'display'  => esc_html( 'Каждые 20 минут' )
        );

        $schedules['every-30-mins'] = array(
			'interval' => 30 * 60,
			'display'  => esc_html( 'Каждые 30 минут' )
        );

        $schedules['every-45-mins'] = array(
			'interval' => 45 * 60,
			'display'  => esc_html( 'Каждые 45 минут' )
        );
        
		$schedules['every-2-days'] = array(
			'interval' => 2 * DAY_IN_SECONDS,
			'display'  => esc_html( 'Каждые 2 дня' )
        );

        $schedules['every-3-days'] = array(
			'interval' => 3 * DAY_IN_SECONDS,
			'display'  => esc_html( 'Каждые 3 дня' )
        );

        return $schedules;
    }

    public static function setup(){
        wp_clear_scheduled_hook( 'STI_run_exchange' );
        $db_status = STI_Database::create_tables();

        if( $db_status === true ){
            $options = self::default_options();
            
            if( isset($options) ){
                foreach( $options as $key => $value ){
                    add_option($key, $value);
                };
            };
        }

        self::schedule_tasks();
    }

    public static function schedule_tasks(){
        wp_clear_scheduled_hook( 'STI_run_exchange' );

        $rate = get_option( 'sti_exchange_rate' );
        $time = stripos( $rate, 'mins' ) !== false ? time() : strtotime( get_option( 'sti_exchange_time' ) . ' - 7 hours' );
        
        if( !$time || !$rate ) return null;

        wp_schedule_event( $time, $rate, 'STI_run_exchange' );
        return true;
    }

    public function admin_menu(){
        add_management_page(
            'Смартомато Импорт',
            'Smartomato Import',
            'activate_plugins',
            'smartomato-import',
            array( $this, 'admin_menu_page' )
        );
    }

    public function admin_bar_menu( $wp_admin_bar ){
        $wp_admin_bar->add_menu( array(
			'id'        => 'smartomato-import-bar',
			'title'     => esc_html( 'Smartomato Импорт' ),
			'href'      => admin_url( 'tools.php?page=smartomato-import' ),
        ) );

        global $wpdb;
        
        $table_name = $wpdb->get_blog_prefix() . 'smartomato_dishes';
        $past_import = get_option('sti_past_exchange_date') ? get_option('sti_past_exchange_date') : 'Ещё не запускался';
        $tab_content = '<p>Товаров в базе: <b>' . $wpdb->query("SELECT * FROM $table_name") . '</b></p><p>Последний обмен: <b>' . $past_import . '</b></p>';

        $wp_admin_bar->add_menu( array(
            'parent'    => 'smartomato-import-bar',
			'id'        => 'smartomato-import-bar-content',
			'title'     => esc_html( 'Информация' ),
			'href'      => '',
            'meta'      => array(
                'html'      => $tab_content,
            ),
        ) );

        $wp_admin_bar->add_menu( array(
            'parent'    => 'smartomato-import-bar',
			'id'        => 'smartomato-import-bar-button',
			'title'     => esc_html( 'Запустить импорт' ),
			'href'      => admin_url( 'tools.php?page=smartomato-import&push-exchange' ),
        ) );
    }

    public function admin_enqueue_scripts( $hook_suffix ){
        wp_enqueue_script( 'smartomatoimport-js', plugins_url( 'smartomato-import/assets/js/app.js' ), array( 'jquery' ), $this->version, true );
        wp_enqueue_style( 'smartomatoimport-css', plugins_url( 'smartomato-import/assets/css/style.css' ), array(), $this->version );
    }

    public function admin_menu_page(){
        include $this->directory . '/admin.php';
    }

    public static function run_exchange(){
        $delivery_enabled = get_option('sti_delivery_enabled');

        if($delivery_enabled === 0){
            return null;
        }

        $login = get_option('sti_auth_login');
        $password = get_option('sti_auth_password');

        if( $login === false || $password === false ){
            return 'Wrong login or password';
        }

        $token = STI_Api::auth( $login, $password );

        if( $token === null ){
            return 'bad auth';
        }

        $dishes = STI_Api::get_dishes( $token );
        $insert_dishes = STI_Database::update_dishes( $dishes );

        $categories = STI_Api::get_categories( $token );
        $insert_categories = STI_Database::update_categories( $categories );

        if( $insert_dishes === null || $insert_categories === null ){
            return 'nothing to set';
        }

        update_option( 'sti_past_exchange_date', current_time( 'd.m.y H:i' ) );

        return array($insert_categories, $insert_dishes);
    }

    public function ajax_set_iiko_webhooks(){
        $revoke_needed = get_option('sti_iiko_webhook_set') == 0 ? false : true;
        $key = get_option('sti_iiko_api_key');

        if( !isset( $key ) || empty( $key ) ) return null;

        $auth = STI_Iiko::auth( $key );
    }

    public function ajax_push_exchange(){
        if( check_admin_referer( 'push_exchange_button' ) ){
            $exchange = self::run_exchange();

            if( !$exchange ){
                wp_send_json_error();
            } else{
                wp_send_json_success(
                    array(
                        'result' => $exchange,
                    )
                );
            }
        } else{
            wp_send_json_error();
        }
    }

    public function ajax_save_options(){
        if( check_admin_referer( 'update_options_button' ) ){
            $is_reschedule_needed = false;

            if( get_option( 'sti_exchange_rate' ) !== $_POST['exchange_rate'] || get_option( 'sti_exchange_time' ) !== $_POST['exchange_time'] ){
                $is_reschedule_needed = true;
            }

            $options = array(
                'sti_auth_login'        => sanitize_text_field( $_POST['login'] ),
                'sti_exchange_rate'     => isset( $_POST['exchange_rate'] ) ? $_POST['exchange_rate'] : 'daily',
                'sti_exchange_time'     => isset( $_POST['exchange_time'] ) ? $_POST['exchange_time'] : '00:00:00',
            );

            if( isset( $_POST['iiko_api_key']) && !empty( $_POST['iiko_api_key'] ) ){
                $options['sti_iiko_api_key'] = sanitize_text_field( $_POST['iiko_api_key'] );
            }

            if( isset( $_POST['password'] ) && !empty( $_POST['password'] ) ){
                $options['sti_auth_password'] = sanitize_text_field( $_POST['password'] );
            };

            if( isset( $_POST['organization_id'] ) && !empty( $_POST['organization_id'] ) ){
                $options['sti_organization_id'] = sanitize_text_field( $_POST['organization_id'] );
            };

            if( isset( $_POST['widget_host'] ) && !empty( $_POST['widget_host'] ) ){
                $options['sti_widget_host'] = sanitize_text_field( $_POST['widget_host'] );
            };

            foreach( $options as $key => $value ){
                update_option( $key, $value );
            };

            if($is_reschedule_needed === true){
                self::schedule_tasks();
            };

            wp_send_json_success();
        } else{
            wp_send_json_error();
        }
    }

    public static function deactivation(){
        if ( is_multisite() && is_network_admin() ) {
            global $wpdb;
            $blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            $original_blog_id = get_current_blog_id();

            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                wp_clear_scheduled_hook( 'STI_run_exchange' );
            }
            
            switch_to_blog( $original_blog_id );
        } else{
            wp_clear_scheduled_hook( 'STI_run_exchange' );
        }
    }

    private function display_delivery_products( $object = null ){
        $result = '';
    
        if( $object && !empty( $object ) ){
            foreach($object as $dish){
                $dish_name = $dish->name;
    
                if($dish->weight){
                    $dish_name .= ', ' . $dish->weight . ' гр';
                }
    
                $item_class = 'dish_item';
    
                if( empty( $dish->image_id ) ){
                    $item_class = 'dish_item dish_noimg';
                }

                $result .= '<div class="dish_wrap col-md-6">';
                $result .= 	'<div class="' . $item_class .'">';
                $result .= 		'<div class="dish_item-wrap">';
    
                if( !empty( $dish->image_id ) ){
                    $result .=		'<div class="dish_img">';
                    $result .=			'<img src="' . wp_get_attachment_image_url( $dish->image_id, 'large' ) . '" data-fancybox="' . wp_get_attachment_image_url( $dish->image_id, 'full' ) . '">';
                    $result .=		'</div>';
                }
    
                $result .= 			'<div class="dish_desc">';
                $result .= 				'<div class="title">' . $dish_name . ' </div>';

                if( $dish->description ){
                    $result .=			'<div class="text" style="font-size: 12px; word-break: break-word;">' . $dish->description . '</div>';
                }

                $result .= 				'<div class="row price_items">';
                $result .= 					'<div class="price">' . $dish->price . '₽</div>';
                $result .= 					'<div class="price-btn_row"><button class="more_info btn btn-bg shadow-red add_to_cart_button" data-dish-id="' . $dish->id . '">В корзину</button></div>';
                $result .= 				'</div>';
                $result .= 			'</div>';
                $result .= 		'</div>';
                $result .= 	'</div>';
                $result .= '</div>';
            }
        } else{
            $result = '<p>Пусто</p>';
        }
    
        return $result;
    }

    public function ajax_delivery_load(){
        check_ajax_referer('menu-ajax-nonce', 'security');
    
        $product_id = intval( $_POST['target'] );
        $return = '<p>Пусто</p>';
    
        if($product_id && $product_id > 0){
            global $wpdb;
    
            $table_name = $wpdb->get_blog_prefix() . 'smartomato_dishes';
            $dishes = $wpdb->get_results("SELECT * FROM $table_name WHERE category_id = $product_id ORDER BY price ASC", OBJECT);
        
            $return = self::display_delivery_products( $dishes );
        }
    
        echo $return;
    
        wp_die();
    }

    public function delivery_search_ajax(){
        check_ajax_referer('menu-search-nonce', 'security');
    
        $category = intval( $_POST['menu'] );
        $search = sanitize_text_field( $_POST['search'] );
        $result = '<p>Ничего не найдено</p>';
    
        if( $search ){
            global $wpdb;
    
            $table_name = $wpdb->get_blog_prefix() . 'smartomato_dishes';
            // $category_table_name = $wpdb->get_blog_prefix() . 'smartomato_categories';
            $search = '%' . $wpdb->esc_like( $search ) . '%';

            // $categories_list = $wpdb->get_results( $wpdb->prepare("SELECT id FROM $category_table_name WHERE parent_id = %s", $category), OBJECT );

            // if( isset($categories_list) && !empty($categories_list) ){
            //     $categories = array();

            //     foreach($categories_list as $item){
            //         $categories[] = $item->id;
            //     }

            //     $categories_list = implode(', ', $categories);
            //     $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE category_id IN (%s) AND name LIKE %s", $categories_list, $search );
            // } else{
            //     $query = $wpdb->prepare( "SELECT DISTINCT id, name FROM $table_name WHERE name LIKE %s", $search );
            // }

            $query = $wpdb->prepare( "SELECT DISTINCT id, name FROM $table_name WHERE name LIKE %s", $search );

            $items = $wpdb->get_results( $query );
    
            if( $items && !empty( $items ) ){
                $result = '';
    
                foreach( $items as $item ){
                    $result.= '<p onclick="showSearchItem('. $item->id .')">'. $item->name .'</p>';
                }
            }
        }
    
        echo json_encode( array( 'result' => $result ) );
    
        wp_die();
    }

    public function delivery_search_result(){
        check_ajax_referer('menu-search-nonce', 'security');
    
        $product_id = intval( $_POST['target'] );
        $result = '<p>Ничего не найдено</p>';
    
        if( $product_id && $product_id > 0 ){
            global $wpdb;
    
            $table_name = $wpdb->get_blog_prefix() . 'smartomato_dishes';
            $item = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = '%d'", $product_id ) );
    
            $result = self::display_delivery_products( $item );
        }
    
        echo $result;
    
        wp_die();
    }
}