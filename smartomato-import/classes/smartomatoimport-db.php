<?php

defined( 'ABSPATH' ) || exit;

class STI_Database{

    public static function create_tables(){
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = $wpdb->get_blog_prefix() . 'smartomato_dishes';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
        id int(5) unsigned NOT NULL default 0,
        name tinytext NOT NULL default '',
        description mediumtext NULL default '',
        price mediumint(4) unsigned NOT NULL default 0,
        image_id int(4) NULL default 0,
        original_image_url tinytext NULL default '',
        weight mediumint(2) NULL default 0,
        unit tinytext NULL default '',
        category_id int(5) unsigned NULL default 0,
        PRIMARY KEY  (id)
        )
        {$charset_collate};";

        dbDelta( $sql );

        $table_name = $wpdb->get_blog_prefix() . 'smartomato_categories';

        $sql = "CREATE TABLE {$table_name} (
        id int(5) unsigned NOT NULL default 0,
        name tinytext NOT NULL default '',
        parent_id int(5) NULL default NULL,
        image_id int(2) NULL default NULL,
        menu_order tinyint(1) NOT NULL default 0,
        PRIMARY KEY  (id)
        )
        {$charset_collate};";
    
        dbDelta( $sql );

        return true;
    }

    public static function drop_tables(){
        global $wpdb;

        $tables = [
            $wpdb->get_blog_prefix() . 'smartomato_categories',
            $wpdb->get_blog_prefix() . 'smartomato_dishes',
        ];

        foreach( $tables as $table ){
            $wpdb->query( "DROP TABLE IF EXISTS $table" );
        };
    }

    public static function search_images( $keyword = null ){
        if( $keyword === null ){
            return null;
        }

        global $wpdb;

        $search = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT ID FROM $wpdb->posts WHERE post_title = '%s' AND post_type = 'attachment' ORDER BY ID DESC LIMIT 1", stripslashes( $keyword ) ) );

        if( !empty( $search ) && is_numeric( $search[0] ) ){
            return intval( $search[0] );
        } else{
            return null;
        }
    }

    public static function update_dishes( $dishes = null ){
        if( $dishes === null ){
            return null;
        }

        global $wpdb;

        $table_name = $wpdb->get_blog_prefix() . 'smartomato_dishes';

        $wpdb->query("TRUNCATE TABLE $table_name");

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        foreach($dishes as $dish){
            if( $dish['is_hidden'] === false && $dish['availability'][0]['status'] === 'available' && $dish['price'] > 0 ){

                $image_id = 0;
                $image_src = $dish['photo']['original'];
                $image_desc = '[SM-Import] ID:' . $dish['id'] . ' | ' . $dish['name'];

                if( !empty( $image_src ) ){
                    $image_id = self::search_images( $image_desc );

                    if( $image_id === null ){
                        $tmp = download_url( $image_src );
                        $file_name = 'sm-' . $dish['id'] . '-' . basename( $image_src );
                        $file_array = array(
                            'name'     => $file_name,
                            'tmp_name' => $tmp
                        );

                        if ( is_wp_error( $tmp ) ) {
                            $file_array['tmp_name'] = '';
                        }

                        $fetch_image = media_handle_sideload( $file_array, 0, $image_desc );

                        // $fetch_image = media_sideload_image( $image_src, 0, $dish['name'], 'id' );

                        if( !is_wp_error( $fetch_image ) && is_int( $fetch_image ) ){
                            $image_id = $fetch_image;
                        }

                        @unlink( $tmp );
                    }
                }

                $wpdb->insert( $table_name, array(
                    'id' => $dish['id'],
                    'name' => $dish['name'],
                    'description' => $dish['description'],
                    'price' => round($dish['price']),
                    'image_id' => $image_id,
                    'original_image_url' => $dish['photo']['original'],
                    'weight' => $dish['weight'],
                    'unit' => $dish['weight_unit'],
                    'category_id' => $dish['draft_category_id'],
                ) );

            }
        }

        $rows_created = $wpdb->query("SELECT * FROM $table_name");

        if( !is_int( $rows_created ) ){
            return null;
        }

        return $rows_created;
    }

    public static function update_categories( $categories = null ){
        if( $categories === null || !is_array( $categories )){
            return null;
        }

        global $wpdb;

        $table_name = $wpdb->get_blog_prefix() . 'smartomato_categories';
        $dishes_table = $wpdb->get_blog_prefix() . 'smartomato_dishes';

        $wpdb->query("TRUNCATE TABLE $table_name");

        foreach( $categories as $category ){
            if( $category['is_hidden'] === false ){
                
                $id         = $category['id'];
                $image      = $wpdb->get_row("SELECT image_id FROM {$dishes_table} WHERE category_id = {$id} AND image_id > 0", OBJECT);
                $image_id   = 0;

                if( $image->image_id ){
                    $image_id = $image->image_id;
                }

                $wpdb->insert( $table_name, array(
                    'id'            => $id,
                    'name'          => $category['name'],
                    'parent_id'     => $category['parent_id'],
                    'image_id'      => $image_id,
                    'menu_order'    => $category['menu_order']
                ) );

            }
        }

        $rows_created = $wpdb->query("SELECT * FROM $table_name");

        if( !is_int( $rows_created ) ){
            return null;
        }

        return $rows_created;
    }

}