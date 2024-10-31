<?php

namespace PowerUp;

class Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
        return $this;
    }

    public function admin_menu() {
        $icon = GSPU_PLUGIN_URI . '/assets/img/icon.svg';
        add_menu_page( __('PowerUp', 'powerup'), __('PowerUp', 'powerup'), 'manage_options', 'powerup', [ $this, 'admin_view' ], $icon, 6 );
    }

    public function admin_view() {
        include GSPU_PLUGIN_DIR . 'includes/template.php';
    }

    public function scripts( $hook ) {

        if ( 'toplevel_page_powerup' !== $hook ) return;

        wp_register_style( 'powerup-admin-grid', GSPU_PLUGIN_URI . '/assets/libs/powerup-grid/powerup-grid.min.css', [], GSPU_VERSION );
        wp_register_style( 'powerup-admin-font-awesome', GSPU_PLUGIN_URI . '/assets/libs/font-awesome/css/all.min.css', [], GSPU_VERSION );
        wp_register_style( 'powerup-admin', GSPU_PLUGIN_URI . '/assets/admin/css/admin.min.css', ['powerup-admin-font-awesome', 'powerup-admin-grid'], GSPU_VERSION );
        
        wp_register_script( 'powerup-admin', GSPU_PLUGIN_URI . '/assets/admin/js/admin.min.js', ['jquery'], GSPU_VERSION, true );
        
        wp_enqueue_style( 'powerup-admin-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap', [], GSPU_VERSION );
        wp_enqueue_style( 'powerup-admin' );
        wp_enqueue_script( 'powerup-admin' );

        $data = [
            "nonce"                 => wp_create_nonce( "gs_powerup_security" ),
            "ajax_url"              => admin_url( "admin-ajax.php" ),
            "admin_url"             => admin_url(),
            "site_url"              => home_url(),
            "ajax_action"           => 'gspu_ajax_handler',
            'translations'          => $this->get_translation_srtings(),
            'settings_options'      => $this->get_setting_options(),
            'modules'               => $this->get_module_list()
        ];

        wp_localize_script( 'powerup-admin', '_powerup_data', $data );
    }

    function get_module_list() {
        return plugin()->modules->get_all_modules();
    }

    function get_translation_srtings() {
        return [
            'modules'                            => __('Modules', 'powerup'),
            'disable_on_post_types'              => __( 'Disable On Post Types', 'poweup' ),
            'disable_on_post_types_placeholders' => __( 'Disable Comments', 'powerup' ),
            'disable_on_post_types_help'         => __( 'Disable Post Types', 'powerup' ),
            'disable_based_on_roles'             => __( 'Disable On Roles', 'powerup' ),  
        ];
    }

    function get_settings() {
        return plugin()->modules->get_settings();
    }

    function get_setting_options() {
        $pref_options = [
            'user_roles'            => Helpers::get_role_options(),
            'users'                 => Helpers::get_user_options(),
            'capabilities'          => Helpers::get_capability_options(),
            'condition_types'       => Helpers::get_condition_type_options(),
            'post_types'            => Helpers::post_type_options(),
            'user_roles'            => Helpers::get_editable_roles(),
            'select_mode'           => Helpers::select_mode(),
            'location'              => Helpers::location()
        ];
        return $pref_options;
    }
}