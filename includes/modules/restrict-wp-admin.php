<?php

namespace PowerUp\Modules;
use PowerUp\Base\Module;
use PowerUp\Helpers;

final class Restrict_Wp_Admin extends Module {

    public function __construct() {
        parent::__construct();
        add_action( 'admin_init', [ $this, 'redirection_data' ] );
    }
    
    public function default_settings() {
        return [
            'redirect_to' => '',
            'conditions' => [],
        ];
    }

    public function get_whitelisted_urls() {

    }

    public function redirection_data() {

        if ( wp_doing_ajax() ) return;

        $settings = $this->get_settings();
        $user_roles = wp_get_current_user()->roles;

        if ( in_array( 'administrator', $user_roles ) ) return;

        if ( ! empty( $settings['conditions'] ) ) {
            foreach( $settings['conditions'] as $setting ) {

                if ( $setting['condition'] === 'user_role' && !empty( array_intersect( $setting['condition_value'], $user_roles ) ) ) {
                    wp_redirect( $setting[ 'redirect_to' ] );
                    exit;
                }

                if ( $setting['condition'] === 'user' &&  in_array( get_current_user_id(), $setting['condition_value'] )  ) {
                    wp_redirect( $setting[ 'redirect_to' ] );
                    exit;
                }
            }
        }
        
        if ( ! empty( $settings['redirect_to'] ) ) {
            wp_redirect( $settings[ 'redirect_to' ] );
            exit;
        }
       
    }

    public function validate_settings( $settings ) {

        $settings['redirect_to']    = sanitize_url( $settings['redirect_to'] );

        if ( ! empty( $settings['conditions'] ) ) {
            foreach ( $settings['conditions'] as $key => &$condition ) {

                $condition = Helpers::condition_model( $condition );

                $condition['condition']       = sanitize_key( $condition['condition'] );
                $condition['condition_value'] = array_map( 'sanitize_key', (array) $condition['condition_value'] );
                $condition['redirect_to']     = sanitize_url( $condition['redirect_to'] );
                $condition['condition_order'] = absint( $condition['condition_order'] );

                if ( empty( $condition['condition'] ) || empty( $condition['condition_value'] ) || empty( $condition['redirect_to'] ) ) {
                    unset( $settings['conditions'][$key] );
                }
            }
            $settings['conditions'] = array_values( $settings['conditions'] );
        }

        return $settings;
    }

}