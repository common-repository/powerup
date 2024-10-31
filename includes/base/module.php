<?php

namespace PowerUp\Base;

abstract class Module {

    const KEY_PREFIX = 'gspu_module_';

    private $settings = [];
    
    public function __construct() {
        $this->load_settings();
    }

    final function get_module_option_key() {
        return self::KEY_PREFIX . $this->get_module_name();
    }

    final function load_settings() {

        $module_key = $this->get_module_name();

        $settings = [];

        if ( array_key_exists( $module_key, $this->settings ) ) {
            $settings = $this->settings[ $module_key ];
        } else {
            $settings = (array) get_option( $this->get_module_option_key(), [] );
            $settings = $this->ensured_settings( $settings );
            $this->settings[ $module_key ] = $settings;
        }

        unset( $settings );
    }

    final function get_module_name() {
        $reflect = new \ReflectionClass( $this );
        $classname = $reflect->getShortName();
        return str_replace( '_', '-', strtolower($classname) );
    }
    
    final function get_settings( $key = null, $default = null ) {
        
        $module_key = $this->get_module_name();
        $settings = $default;
        
        if ( empty($key) ) {
            $settings = $this->settings[ $module_key ];
        } else {
            if ( array_key_exists( $key, $this->settings[ $module_key ] ) ) {
                $settings = $this->settings[ $module_key ][ $key ];
            }
        }

        if ( $default === null ) {
            return $settings;
        } else {
            return array_merge( $settings, $default );
        }
    }

    final function set_settings( $settings, $key = null ) {

        $module_key = $this->get_module_name();

        if ( empty($key) ) {
            $this->settings[ $module_key ] = $this->ensured_settings( $settings );
        } else {
            if ( array_key_exists( $key, $this->settings[ $module_key ] ) ) {
                $this->settings[ $module_key ][ $key ] = $settings;
            }
        }
    }

    final function save_settings( $settings ) {

        $settings = $this->ensured_settings( $settings );
        $settings = $this->validate_settings( $settings );
        $this->set_settings( $settings );
        
        $setting_key = $this->get_module_option_key();
        update_option( $setting_key, $settings );

        return $settings;
    }
    
    public function ensured_settings( $settings ) {
        return shortcode_atts( $this->default_settings(), $settings );
    }
    
    abstract public function default_settings();
    abstract public function validate_settings( $settings );
}