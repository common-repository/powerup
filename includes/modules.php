<?php

namespace PowerUp;
use PowerUp\Base\Module;

class Modules {

    private $modules = [];

    public $settings;
    public $option_key;

    public $module_instances = [];

    public function __construct() {
        $this->settings = [];
        $this->option_key = 'gspu_active_modules';
        $this->register_modules();
        $this->load_active_modules();
        return $this;
    }

    public function get_settings() {
        return $this->settings;
    }

    public function get_all_modules( $name_only = false ) {
        $modules = $this->modules;
        if ( $name_only ) return array_keys( $modules );
        return $modules;
    }

    public function get_active_modules( $name_only = false ) {
        $modules = wp_list_filter( $this->get_all_modules(), ['active' => true] );
        if ( $name_only ) return array_keys( $modules );
        return $modules;
    }

    public function set_active_modules( $module_names = [] ) {
        $module_names = array_map( 'sanitize_key', (array) $module_names );
        return update_option( $this->option_key, $module_names );
    }

    public function load_active_modules() {

        require_once ABSPATH . 'wp-admin/includes/file.php';
        
        $module_dir = plugin_dir_path( __FILE__ ) . 'modules';
        
        $module_files = list_files( $module_dir );
        
        $active_modules = $this->get_active_modules( true );

        if ( empty($active_modules) ) return;

        foreach ( $module_files as $module_file ) {
            $modules_name = wp_basename( $module_file, '.php' );
            if ( in_array( $modules_name, $active_modules ) ) {
                $this->load_module( $modules_name, $module_file );
            }
        }
    }

    public function load_module( $modules_name, $module_file ) {
        if ( file_exists( $module_file ) ) {
            $module_class = Helpers::get_class_name( $modules_name );

            require_once GSPU_PLUGIN_DIR . 'includes/base/module.php';
            require_once $module_file;

            $namespace = '\PowerUp\Modules\\';
            
            $module_class = $namespace . $module_class;
            $module = new $module_class();

            $this->module_instances[ $modules_name ] = $module;
            $this->settings[ $modules_name ] = $module->get_settings();
        }
    }

    public function save_settings( Array $settings ) {
        
        foreach ( $settings as $modules_name => $module_settings ) {
            $module = $this->get_module( $modules_name );
            $module_settings = $module->save_settings( $module_settings );
            $this->settings[ $modules_name ] = $module->get_settings();
        }
 
        return $this->settings;
    }

    public function register_modules() {

        $modules = [
            [
                'name'        => 'hide-admin-bar',
                'path'        => 'hide-admin-bar',
                'title'       => __( 'Hide Admin Bar', 'powerup' ),
                'icon'        => "fa-solid fa-eye-slash",
                'description' => __( 'Show / Hide the admin bar based on the user roles & user names. Whilelist & Blacklist for specific user.', 'powerup' )
            ],
            [
                'name'        => 'redirection-rules',
                'path'        => 'redirection-rules',
                'title'       => __( 'Redirection Rules', 'powerup' ),
                'icon'        => "fa-solid fa-arrow-up-right-from-square",
                'description' => __( 'Redirect users to different pages based on conditions such as their user names, roles & capabilities.', 'powerup' )
            ],
            [
                'name'        => 'disable-comments',
                'path'        => 'disable-comments',
                'title'       => __( 'Disable Comments', 'powerup' ),
                'icon'        => "fa-solid fa-comment-slash",
                'description' => __( 'Disable Comments in the entire website, or disable it for specific posts types and based on user roles.', 'powerup' )
            ],
            [
                'name'        => 'restrict-wp-admin',
                'path'        => 'restrict-wp-admin',
                'title'       => __( 'Restrict wp-admin', 'powerup' ),
                'icon'        => "fa-solid fa-lock",
                'description' => __( 'Restrict wp-admin area based on user roles, user names, and always allow for site admins.', 'powerup' )
            ],
            [
                'name'        => 'header-footer-scripts',
                'path'        => 'header-footer-scripts',
                'title'       => __( 'Header Footer Scripts', 'powerup' ),
                'icon'        => "fa-solid fa-code",
                'description' => __( 'Add Header Footer Scripts Using this Module', 'powerup' )
            ],
            [
                'name'        => 'footer-thankyou',
                'path'        => 'footer-thankyou',
                'title'       => __( 'Remove Credit', 'powerup' ),
                'icon'        => "fa-solid fa-link-slash",
                'description' => __( 'Remove Footer Thank You Credit Text', 'powerup' )
            ]
        ];

        $active_modules_keys = get_option( $this->option_key, wp_list_pluck( $modules, 'name' ) );

        foreach ( $modules as &$module ) {
            if ( in_array( $module['name'], $active_modules_keys ) ) {
                $module['active'] = true;
            }
            $this->modules[ $module['name'] ] = $module;
        }

    }

    public function get_module( $module_name ) {
        return $this->module_instances[ $module_name ];
    }

    public function get_module_settings( $module_name ) {
        return $this->get_module( $module_name )->get_settings();
    }

}