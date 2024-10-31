<?php

namespace PowerUp\Modules;
use PowerUp\Base\Module;
use PowerUp\Helpers;

final class Redirection_Rules extends Module {

    public function __construct() {
        parent::__construct();

        add_filter( 'login_redirect', [ $this, 'login_redirect_callback'], 9999999999, 3);
        add_filter( 'logout_redirect', [ $this, 'logout_redirect_callback'], 9999999999, 3 );
        add_filter( 'registration_redirect', [ $this, 'registration_redirect_callback'], 20, 2 );
    }
    
    public function default_settings() {
        return [
            'login_conditions' => [],
            'logout_conditions' => [],
            'general_conditions' => [
                'login_redirect_to' => '',
                'logout_redirect_to' => '',
                'signup_redirect_to' => ''
            ]
        ];
    }

    public function validate_settings( $settings ) {

        $settings['general_conditions']['login_redirect_to']    = sanitize_url( $settings['general_conditions']['login_redirect_to'] );
        $settings['general_conditions']['logout_redirect_to']   = sanitize_url( $settings['general_conditions']['logout_redirect_to'] );
        $settings['general_conditions']['signup_redirect_to']   = sanitize_url( $settings['general_conditions']['signup_redirect_to'] );

        if ( ! empty( $settings['login_conditions'] ) ) {
            foreach ( $settings['login_conditions'] as $key => &$login_condition ) {

                $login_condition = Helpers::condition_model( $login_condition );

                $login_condition['condition']       = sanitize_key( $login_condition['condition'] );
                $login_condition['condition_value'] = array_map( 'sanitize_key', (array) $login_condition['condition_value'] );
                $login_condition['redirect_to']     = sanitize_url( $login_condition['redirect_to'] );
                $login_condition['condition_order'] = absint( $login_condition['condition_order'] );

                if ( empty( $login_condition['condition'] ) || empty( $login_condition['condition_value'] ) || empty( $login_condition['redirect_to'] ) ) {
                    unset( $settings['login_conditions'][$key] );
                }
            }
            $settings['login_conditions'] = array_values( $settings['login_conditions'] );
        }

        if ( ! empty( $settings['logout_conditions'] ) ) {
            foreach ( $settings['logout_conditions'] as $key => &$logout_condition ) {

                $logout_condition = Helpers::condition_model( $logout_condition );

                $logout_condition['condition']          = sanitize_key( $logout_condition['condition'] );
                $logout_condition['condition_value']    = array_map( 'sanitize_key', (array) $logout_condition['condition_value'] );
                $logout_condition['redirect_to']        = sanitize_url( $logout_condition['redirect_to'] );
                $logout_condition['condition_order']    = absint( $logout_condition['condition_order'] );

                if ( empty( $logout_condition['condition'] ) || empty( $logout_condition['condition_value'] ) || empty( $logout_condition['redirect_to'] ) ) {
                    unset( $settings['logout_conditions'][$key] );
                }
                $settings['logout_conditions'] = array_values( $settings['logout_conditions'] );
            }
        }
        
        return $settings;
    }

    public function get_redirect_url( $conditions, $user, $default_url ) {

        $user_caps = Helpers::get_user_capabilities( $user );
        $user_roles = Helpers::get_user_roles( $user );

        $conditions = wp_list_sort( $conditions, 'condition_order', 'DESC' );

        foreach( $conditions as $condition ) {

            $con_redirect_to = sanitize_url( $condition['redirect_to'] );
            
            if ( empty($con_redirect_to) ) continue;

            $condition_type = $condition['condition'];
            $condition_value = $condition['condition_value'];

            if ( $condition_type == 'user' && in_array( $user->ID, $condition_value ) ) return $con_redirect_to;
            if ( $condition_type == 'user_role' && array_intersect( $user_roles, $condition_value ) ) return $con_redirect_to;
            if ( $condition_type == 'user_calability' && array_intersect( $user_caps, $condition_value ) ) return $con_redirect_to;

        }

        return $default_url;
    }

    public function login_redirect_callback( $redirect_to, $requested_redirect_to, $user ) {

        $settings = $this->get_settings();

        // Replace $redirect_to with general settings if available
        $_redirect_to = sanitize_url( $settings['general_conditions']['login_redirect_to'] );
        if ( ! empty($_redirect_to) ) $redirect_to = $_redirect_to;
        
        // Return $redirect_to when no user found
        if ( ! isset($user->user_login) ) return $redirect_to;
        
        // Return $redirect_to when login condition found
        if ( empty($settings['login_conditions']) ) return $redirect_to;
        
        // Return matched $redirect url if found
        $_redirect_to = $this->get_redirect_url( $settings['login_conditions'], $user, $redirect_to );
        if ( ! empty($_redirect_to) ) $redirect_to = $_redirect_to;
        
        Helpers::set_allowed_host( $redirect_to );
        return $redirect_to;
    }

    public function logout_redirect_callback( $redirect_to, $requested_redirect_to, $user ) {

        $settings = $this->get_settings();

        // Replace $redirect_to with general settings if available
        $_redirect_to = sanitize_url( $settings['general_conditions']['logout_redirect_to'] );
        if ( ! empty($_redirect_to) ) $redirect_to = $_redirect_to;
        
        // Return $redirect_to when no user found
        if ( ! isset($user->user_login) ) return $redirect_to;
        
        // Return $redirect_to when logout condition found
        if ( empty($settings['logout_conditions']) ) return $redirect_to;
        
        // Return matched $redirect url if found
        $_redirect_to = $this->get_redirect_url( $settings['logout_conditions'], $user, $redirect_to );
        if ( ! empty($_redirect_to) ) $redirect_to = $_redirect_to;
        
        Helpers::set_allowed_host( $redirect_to );
        return $redirect_to;
    }

    public function registration_redirect_callback( $redirect_to ) {

        $_redirect_to = sanitize_url( $this->get_settings( 'general_conditions' )['signup_redirect_to'] );

        if ( ! empty($_redirect_to) ) {
            Helpers::set_allowed_host( $_redirect_to );
            return $_redirect_to;
        }

        return $redirect_to;
    }

}