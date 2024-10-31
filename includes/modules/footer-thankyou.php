<?php

namespace PowerUp\Modules;
use PowerUp\Base\Module;

final class Footer_Thankyou extends Module {

    public function default_settings() {        
        return [
            'disable_thankyou'       => false     
        ];
    }

    public function __construct() {
        parent::__construct();
       
        $this->maybe_hide_thankyou();
    }

    public function validate_settings( $settings ) {

        $settings['disable_thankyou']   = wp_validate_boolean( $settings['disable_thankyou'] );
        return $settings;
    }

    public function maybe_hide_thankyou() {

        $settings = $this->get_settings();

        if( is_admin() && $settings['disable_thankyou'] ) {       

            add_action( 'in_admin_header', function() {

            ?>
                <style>
                    p#footer-left {
                        display: none;
                    }
                </style>
            <?php

            } );            
            
        }
    }
}
