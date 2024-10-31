<?php

namespace PowerUp\Modules;
use PowerUp\Base\Module;

final class Header_Footer_Scripts extends Module {

    public $settings;
    public $script_settings = [];

    public function default_settings() {
        return [
            
            'header_footer'  => []
        ];
    }

    public function __construct() {
        parent::__construct();

        $this->settings        = $this->get_settings();

        add_action( 'wp_head', [$this, 'print_data_head'] );
        add_action( 'wp_body_open', [$this, 'print_data_body'] );
        add_action( 'wp_footer', [$this, 'print_data_footer'] );
        
    }

    public function print_data_head() {
        foreach( $this->settings['header_footer'] as $setting ) {
            if( $setting['condition_value'] === 'gs_header' ) {
                $this->print_scripts($setting);                
            }            
        }
    }

    public function print_data_body() {
        foreach( $this->settings['header_footer'] as $setting ) {
          if( $setting['condition_value'] === 'gs_body' ) {
                $this->print_scripts($setting);
            }
        }
    }

    public function print_data_footer() {
        foreach( $this->settings['header_footer'] as $setting ) {
           if( $setting['condition_value'] === 'gs_footer' ) {
                $this->print_scripts($setting);
            }
        }
    }

    function print_scripts( $setting ) {

        if( $setting['condition'] === 'gs_html' ) {
            echo wp_kses_post($setting['header_scripts']);
        }

        if( $setting['condition'] === 'gs_css' ) {
            echo '<style>'.wp_strip_all_tags($setting['header_scripts']).'</style>';
        }

        if( $setting['condition'] === 'gs_js' ) {
            echo '<script>'.wp_strip_all_tags($setting['header_scripts']).'</script>';
        }

    }

    function validate_settings( $settings ) {

        if( empty( $settings['header_footer'] ) ) {
            $settings['header_footer'] = [];
        }

        return $settings;
    }

}