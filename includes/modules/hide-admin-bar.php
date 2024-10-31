<?php

namespace PowerUp\Modules;
use PowerUp\Helpers;
use PowerUp\Base\Module;

final class Hide_Admin_Bar extends Module {
    
    public function default_settings() {
        return [
            'hide_for_all' => true,
            'hide_for_roles' => [],
            'hide_for_users' => [],
            'show_for_roles' => [],
            'show_for_users' => []
        ];
    }

    public function __construct() {
        parent::__construct();
        add_action( 'admin_print_scripts-profile.php', [ $this, 'show_admin_bar_prefs' ], 0 );
        add_filter( 'show_admin_bar', [ $this, 'show_admin_bar' ] );
    }

    function hide_it() {

        $status = true;
        
        $settings = $this->get_settings();
        
        $status = $hide_for_all = wp_validate_boolean( $settings['hide_for_all'] );

        $user = wp_get_current_user();

        if ( empty($user->ID) ) return $hide_for_all;

        if ( $hide_for_all ) {
            // High Priority For Show
            if ( ! empty($settings['show_for_roles']) ) {
                if ( Helpers::has_intersect_items( $user->roles, $settings['show_for_roles'] ) ) $status = false;
                if ( ! empty($settings['hide_for_users']) && in_array( $user->ID, $settings['hide_for_users'] ) ) $status = true;
            }
            if ( ! empty($settings['show_for_users']) && in_array( $user->ID, $settings['show_for_users'] ) ) $status = false;
        } else {
            // High Priority For Hide
            if ( ! empty($settings['hide_for_roles']) ) {
                if ( Helpers::has_intersect_items( $user->roles, $settings['hide_for_roles'] ) ) $status = true;
                if ( ! empty($settings['show_for_users']) && in_array( $user->ID, $settings['show_for_users'] ) ) $status = false;
            }
            if ( ! empty($settings['hide_for_users']) && in_array( $user->ID, $settings['hide_for_users'] ) ) $status = true;
        }

        return $status;
    }

    function show_admin_bar_prefs() {
        if ( $this->hide_it() ) echo '<style type="text/css">.show-admin-bar{display:none!important;}</style>';
    }

    function show_admin_bar( $status ) {
        if ( $this->hide_it() ) return false;
        return $status;
    }

    function validate_settings( $settings ) {
        $settings['hide_for_all']       = wp_validate_boolean( $settings['hide_for_all'] );
        $settings['hide_for_roles']     = array_map( 'sanitize_key', (array) $settings['hide_for_roles'] );
        $settings['hide_for_users']     = array_map( 'absint', (array) $settings['hide_for_users'] );
        $settings['show_for_roles']     = array_map( 'sanitize_key', (array) $settings['show_for_roles'] );
        $settings['show_for_users']     = array_map( 'absint', (array) $settings['show_for_users'] );
        return $settings;
    }

}