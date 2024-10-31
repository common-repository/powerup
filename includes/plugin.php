<?php

namespace PowerUp;

class Plugin {

    public static $_instance = null;
    public $modules;
    public $admin;

    public static function instance() {
        if ( self::$_instance === null ) self::$_instance = new self();
        return self::$_instance;
    }

    public function __construct() {
        $this->load_files();
        $this->init_hooks();

        $this->modules = new Modules();
        $this->admin   = new Admin();
    }

    public function load_files() {
        require_once GSPU_PLUGIN_DIR . 'includes/helpers.php';
        require_once GSPU_PLUGIN_DIR . 'includes/modules.php';
        require_once GSPU_PLUGIN_DIR . 'includes/admin.php';
    }

    public function init_hooks() {
        add_action( 'plugins_loaded', [ $this, 'load_appsero' ] );
        add_action( 'init', [ $this, 'load_textdomain_i18n' ] );
        add_action( 'init', [ $this, 'update_version' ] );
        add_action( 'init', [ $this, 'flush_rewrite_rules' ] );
        add_action( 'activate_' . GSPU_BASE_NAME, 'flush_rewrite_rules' );
        add_action( 'activated_plugin', [ $this, 'welcome_redirection' ], 99999 );
        add_action( 'wp_ajax_gspu_ajax_handler', [ $this, 'ajax_handler' ] );
        add_action( 'current_screen', [$this, 'remove_all_notices'] );
    }

    public function load_appsero() {
        Helpers::gs_appsero_init();
    } 

    public function remove_all_notices( $curr_screen ) {
        
        if( $curr_screen->base === 'toplevel_page_powerup' ) {
            remove_all_actions( 'admin_notices' );
        }
    }

    public function ajax_handler() {
        
        check_ajax_referer( 'gs_powerup_security' );

        if ( ! empty($_POST['route']) ) {
            $route_handler = 'handle_' . sanitize_key( $_POST['route'] );
            if ( is_callable( get_class($this), $route_handler ) ) {
                $this->$route_handler();
            }
        }

        wp_send_json_error([
            'message' => __('Something is wrong, no route found', 'powerup')
        ], 400);
    }

    public function handle_save_settings() {

        $settings = json_decode( stripslashes($_POST['settings']), true );

        $settings = $this->modules->save_settings( $settings );
        if ( !empty( $settings ) ) {
            wp_send_json_success([
                'message' => __( 'Settings Updated', 'powerup' ),
                'data' => $settings
            ]);
        }
        wp_send_json_error( __('Something is wrong, please try again!', 'powerup') );
    }

    public function handle_set_active_modules() {
        $modules = empty( $_POST['modules'] ) ? [] : (array) $_POST['modules'];
        $this->modules->set_active_modules( $modules );
        wp_send_json_success();
    }

    public function handle_get_module_settings() {
        $settings = $this->modules->get_module_settings( $_POST['module_name'] );
        wp_send_json_success([ 'data' => $settings ]);
    }

    public function load_textdomain_i18n() {
        load_plugin_textdomain( 'powerup', false, dirname( GSPU_BASE_NAME ) . '/languages' );
    }

    public function flush_rewrite_rules() {
        if ( ! get_option( 'gspu_permalinks_flushed' ) ) {
            flush_rewrite_rules();
            update_option( 'gspu_permalinks_flushed', 1 );
        }
    }

    public function welcome_redirection( $plugin ) {
        if ( $plugin === GSPU_BASE_NAME ) {
            wp_redirect( admin_url( 'admin.php?page=powerup' ) );
            exit;
        }
    }

    public function update_version() {
        $old_version = get_option('gspu_plugin_version');
        if ( GSPU_VERSION === $old_version ) return;
        update_option( 'gspu_plugin_version', GSPU_VERSION );
    }
}

function plugin() {
    return Plugin::instance();
}
plugin();