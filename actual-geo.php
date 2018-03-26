<?php
    /**
     * Plugin Name: Actual Geo
     * Author: Vladislav Surikov
     * Version: 0.1
     * Description: Actual Geo adds user geo tracking and posts geo params. Plugin based on data provided by http://ipinfo.io
     * Text Domain: actual-geo
     */

    add_action( 'wp_enqueue_scripts', function() {
        if( is_front_page() && is_multisite() ) {
            $sites = get_sites();
            $redirects = get_option( 'geo_redirects' );

            wp_enqueue_script( 'actual-geo', plugin_dir_url( __FILE__ ) . 'assets/js/actual-geo-redirect.js', array( 'jquery' ) );
            wp_localize_script( 'actual-geo', 'referer', $_SERVER['HTTP_REFERER'] );
            wp_localize_script( 'actual-geo', 'origsite', 'http://' . $sites[0]->domain . '/' );
            wp_localize_script( 'actual-geo', 'redirects', $redirects );
        }
        
        $allowed_posttypes = get_option( 'posttypes' );
        
        if( $allowed_posttypes && in_array( get_post_type(), $allowed_posttypes ) ) {
            wp_enqueue_script( 'user-geotracking', plugin_dir_url( __FILE__ ) . 'assets/js/user-geotracking.js', array( 'jquery' ) );
        }
    } );

    add_action( 'admin_enqueue_scripts', function() {
        wp_register_script( 'chosen-js', plugin_dir_url( __FILE__ ) . 'libs/chosen/chosen.jquery.min.js', array( 'jquery' ) );
        wp_register_style( 'chosen-css', plugin_dir_url( __FILE__ ) . 'libs/chosen/chosen.min.css' );
    } );
    
    add_action( 'admin_enqueue_scripts', function( $hook ) {
        // var_dump( $hook );
        wp_enqueue_style( 'chosen-css' );
        wp_enqueue_style( 'admin-actual-geo-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css' );
        if( $hook == 'toplevel_page_wpmu-geo-redirects') {
            $redirects = get_option( 'geo_redirects' );
            
            if( $redirects && is_array( $redirects ) ) {
                $redirect_num = count( $redirects );
            } else {
                $redirect_num = 0;
            }

            $countries = json_decode( file_get_contents( 'http://country.io/names.json' ), true );
            asort( $countries );

            $sites = get_sites();
            $sites_arr = array();

            foreach( $sites as $site ) {
                $sites_arr[ $site->domain ] = $site->blogname;
            }

            $country_label = __( 'Country: ', 'actual-geo' );
            $countryRegion_label = __( 'Country Region: ', 'actual-geo' );
            $url_label = __( 'URL: ', 'actual-geo' );

            wp_enqueue_script( 'admin-geo-pos', plugin_dir_url( __FILE__ ) . 'assets/js/geo-pos.js', array( 'jquery', 'chosen-js' ) );

            wp_localize_script( 'admin-geo-pos', 'redirect_num', $redirect_num );
            wp_localize_script( 'admin-geo-pos', 'country_label', $country_label );
            wp_localize_script( 'admin-geo-pos', 'countryRegion_label', $countryRegion_label );
            wp_localize_script( 'admin-geo-pos', 'url_label', $url_label );
            wp_localize_script( 'admin-geo-pos', 'countries', $countries );
            wp_localize_script( 'admin-geo-pos', 'sites', $sites_arr );
        } else if ( $hook == 'toplevel_page_actual-geo' || $hook == 'post.php' || $hook == 'new.php' ) {
            wp_enqueue_script( 'admin-geo-settings', plugin_dir_url( __FILE__ ) . 'assets/js/actual-geo-settings.js', array( 'jquery', 'chosen-js' ) );
        }
    } );

    add_action( 'network_admin_menu', function() {
        add_menu_page( 
            __( 'WPMU Geo Redirects', 'actual-geo' ), 
            __( 'WPMU Geo Redirects', 'actual-geo' ), 
            'administrator', 
            'wpmu-geo-redirects', 
            'wpmu_geo_redirects_settings' 
        );
    } );

    add_action( 'admin_menu', function() {
        add_menu_page(
            __( 'Actual Geo', 'actual-geo' ),
            __( 'Actual Geo Settings', 'actual-geo' ),
            'administrator',
            'actual-geo',
            'actual_geo_settings'
        );
    } );

    add_action( 'admin_init', function() {
        register_setting( 'wpmu-geo-redirects', 'geo_redirects' );

        register_setting( 'actual-geo-settings', 'posttypes' );
        register_setting( 'actual-geo-settings', 'countries' );
        register_setting( 'actual-geo-settings', 'default-country' );
        register_setting( 'actual-geo-settings', 'upd-country' );
        register_setting( 'actual-geo-settings', 'user-place' );
    } );

    /**
     * Get settings pages
     */
    require_once 'views/actual_geo_settings.php';
    require_once 'views/wpmu_geo_redirects_settings.php';

    /**
     * Add metabox for allowed post types
     */
    add_action( 'add_meta_boxes', function() {
        $allowed_posttypes = get_option( 'posttypes' );
        add_meta_box(
            'post-geodata',
            __( 'Post Geo Data', 'actual-geo' ),
            'geodata_callback',
            $allowed_posttypes
        );
    } );

    add_action( 'save_post', function( $post_id ) {
        // Убедимся что поле установлено.
        if ( ! isset( $_POST['ag__country'] ) )
            return;

        // проверяем nonce нашей страницы, потому что save_post может быть вызван с другого места.
        if ( ! wp_verify_nonce( $_POST['actual-geo_noncename'], plugin_basename(__FILE__) ) )
            return;

        // если это автосохранение ничего не делаем
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            return;

        // проверяем права юзера
        if( ! current_user_can( 'edit_post', $post_id ) )
            return;

        // Все ОК. Теперь, нужно найти и сохранить данные
        // Очищаем значение поля input.
        $ag__country = sanitize_text_field( $_POST['ag__country'] );

        // Обновляем данные в базе данных.
        update_post_meta( $post_id, 'ag__country', $ag__country );
    } );

    function geodata_callback( $post, $meta ) {
        // var_dump( get_post_custom( $post->ID  )['post_country'][0] );
        $ag__country = get_post_custom( $post->ID  )['ag__country'][0];

        $countries = json_decode( file_get_contents( 'http://country.io/names.json' ), true );
        asort( $countries );

        $allowed_countries = get_option( 'countries' );

        wp_nonce_field( plugin_basename(__FILE__), 'actual-geo_noncename' );
?>
        <label>
            <?php echo __( 'Country: ', 'actual-geo' ); ?>
            <select class="chosen-select" name="ag__country" >
                <option value="null"><?php echo __( 'None', 'actual-geo' ); ?></option>
                <?php foreach( $countries as $code => $name ) : ?>
                    <?php if( in_array( $code, $allowed_countries ) ) { ?>
                    <option <?php echo ( $code == $ag__country ? 'selected' : '' ); ?> value="<?php echo $code; ?>"><?php echo $name; ?></option>
                    <?php } ?> 
                <?php endforeach; ?>
            </select>
        </label>
<?php
    }