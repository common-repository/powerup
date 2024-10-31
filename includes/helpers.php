<?php

namespace PowerUp;

class Helpers {

    static function nocache_headers() {
        if ( headers_sent() ) {
            return;
        }
    
        $headers = wp_get_nocache_headers();
    
        unset( $headers['Last-Modified'] );
    
        header_remove( 'Last-Modified' );
    
        foreach ( $headers as $name => $field_value ) {
            header( "{$name}: {$field_value}" );
        }
    }

    static function get_condition_types() {
        return [
            'user' => __('User Name', 'powerup'),
            'user_role' => __('User Role', 'powerup'),
            'user_calability' => __('User Capability', 'powerup')
        ];
    }

    static function get_condition_type_options() {
        $con_types = self::get_condition_types();
        $_con_types = [];
        
        foreach ( $con_types as $con_type => $con_type_label ) {
            $_con_types[] = [
                'label' => $con_type_label,
                'value' => $con_type
            ];
        }

        return $_con_types;
    }

    static function get_capabilities() {
        global $wp_roles;

        $caps = array();

        foreach ($wp_roles->roles as $wp_role) {
            if ( isset($wp_role['capabilities']) && is_array($wp_role['capabilities']) ) {
                $caps = array_merge($caps, array_keys($wp_role['capabilities']));
            }
        }
        
        $caps = array_unique($caps);

        $caps = (array) apply_filters( 'gspu_capabilities', $caps );

        sort($caps);

        return array_combine($caps, $caps);
    }

    static function get_capability_options() {
        $capabilities = self::get_capabilities();
        $_capabilities = [];
        
        foreach ( $capabilities as $capability ) {
            $_capabilities[] = [ 'label' => $capability, 'value' => $capability ];
        }

        return $_capabilities;
    }

    static function get_roles() {
        global $wp_roles;
        $user_roles = apply_filters( 'gspu_user_roles', $wp_roles->roles );
        return wp_list_pluck( $user_roles, 'name' );
    }

    static function get_role_options() {
        $roles = self::get_roles();
        $_roles = [];
        foreach ( $roles as $role => $role_label ) {
            $_roles[] = [
                'label' => $role_label,
                'value' => $role
            ];
        }
        return $_roles;
    }

    static function get_user_options() {
        $users = self::get_all_users();
        $_users = [];
        foreach ( $users as $user => $user_label ) {
            $_users[] = [
                'label' => $user_label,
                'value' => $user
            ];
        }
        return $_users;
    }

    static function get_all_users() {
        $users = get_users();
        $users = apply_filters( 'gspu_all_users', $users );
        $_users = [];
        foreach ( $users as $user ) {
            $_users[ $user->data->ID ] = $user->data->display_name;
        }
        return $_users;
    }

    static function get_user_capabilities( $user ) {
        return array_keys( $user->allcaps );
    }

    static function get_user_roles( $user ) {
        return $user->roles;
    }

    static function get_class_name( $file_name ) {
        $parts = explode('-', $file_name);
        $parts = array_map( 'ucfirst', $parts );
        return implode('_', $parts);
    }

    static function has_intersect_items( $array1, $array2 ) {
        $ddd = ! empty( array_intersect( $array1, $array2 ) );
        return $ddd;
    }

    public static function set_allowed_host( $url ) {
        $url_parsed = parse_url( $url );
        if ( isset($url_parsed['host']) ) {
            $allowed_hosts[] = $url_parsed['host'];
            add_filter( 'allowed_redirect_hosts', function ($hosts) use ($allowed_hosts) {
                return array_merge( $hosts, $allowed_hosts  );
            });
        }
    }
    
    public static function loginwp_var($bucket, $key, $default = false, $empty = false) {
        if ($empty) {
            return ! empty($bucket[$key]) ? $bucket[$key] : $default;
        }
        return isset($bucket[$key]) ? $bucket[$key] : $default;
    }

    public static function get_post_types() {

        $typeargs = array('public' => true);
		
		$types = get_post_types($typeargs, 'objects');

		return apply_filters( 'gspu_post_types', $types );   
    } 

    public static function post_type_options() {
        $post_types = Helpers::get_post_types();

        $selected_types = [];
        foreach( $post_types as $type ) {
            $selected_types[] = [
                "label"     => $type->label,
				"value"     => $type->name
            ];
        }
        
        return $selected_types;
    }

    public static function get_editable_roles( ) {
		$roles = [
			[
                'label' => __('Logged out users', 'powerup'),
                'value' => 'logged-out-users'
			]
		];

		$editable_roles = array_reverse( get_editable_roles() );

		foreach ( $editable_roles as $role => $details ) {
			$roles[] = [
				"label"    => translate_user_role( $details['name'] ),
				"value"     => esc_attr($role)				
			];
		}
		
		return $roles;
	}

    public static function condition_model( $condition ) {
        $model = [
            'condition'       => '',
            'condition_value' => [],
            'redirect_to'     => '',
            'condition_order' => 10
        ];
        return shortcode_atts( $model, $condition );
    }

    public static function select_mode() {
        return 
        [
            [
                'label' => __('HTML', 'powerup'),
                'value' => 'gs_html'
			],
            [
                'label' => __('CSS', 'powerup'),
                'value' => 'gs_css'
			],
            [
                'label' => __('JS', 'powerup'),
                'value' => 'gs_js'
			]
           
        ];
    }

    public static function location() {
        return 
        [
            [
                'label' => __('Header', 'powerup'),
                'value' => 'gs_header'
			],
            [
                'label' => __('Body', 'powerup'),
                'value' => 'gs_body'
			],
            [
                'label' => __('Footer', 'powerup'),
                'value' => 'gs_footer'
			]
            
        ];
    }

    /**
     * Initialize the plugin tracker
     *
     * @return void
     */
    public static function gs_appsero_init() {

        if ( !class_exists('GSBrandAppSero\Insights') ) {
            require_once GSPU_PLUGIN_DIR . 'appsero/Client.php';
        }

        $client = new \GSBrandAppSero\Client('363b4dcd-af4d-4720-b1f8-fc9a67549b1c', 'Powerup', __FILE__);
        
        // Active insights
        $client->insights()->init();
    }

}