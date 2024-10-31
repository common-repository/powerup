<?php

namespace PowerUp\Modules;
use PowerUp\Base\Module;

final class Disable_Comments extends Module {
    
    public function default_settings() {
        return [
            'disable_globally'       => true,
            'disable_for_post_types' => [],
            'disable_based_on_roles' => []        
        ];
    }

    public function __construct() {
        parent::__construct();

        add_action( 'admin_menu', array($this, 'filter_admin_menu'), 9999 ); 
        add_action( 'template_redirect', [ $this, 'disable_comments' ], 0 );
        add_filter( 'woocommerce_product_tabs', [ $this, 'remove_reviews_tab' ], 999 );
    }

    public function remove_reviews_tab( $data ) {

        $settings = $this->get_settings();

        $roles = $settings['disable_based_on_roles'];
        $current_role = wp_get_current_user()->roles;

        if( $settings['disable_globally'] || !empty( array_intersect( $roles, $current_role ) ) || 
        in_array('product', $settings['disable_for_post_types'] )) {

            unset( $data['reviews'] );
        }

        return $data;
    } 

    public function filter_admin_menu() {

        $settings = $this->get_settings();

        $roles = $settings['disable_based_on_roles'];
        $current_role = wp_get_current_user()->roles;

        if($settings['disable_globally'] || !empty( array_intersect( $roles, $current_role ) ) ) {

            global $pagenow;

            if ($pagenow == 'comment.php' || $pagenow == 'edit-comments.php') {
                wp_die(__('Comments are closed.', 'disable-comments'), '', array('response' => 403));
            }

            remove_menu_page('edit-comments.php');
            
            if ($pagenow == 'options-discussion.php') {
                wp_die(__('Comments are closed.', 'disable-comments'), '', array('response' => 403));
            }

            remove_submenu_page('options-general.php', 'options-discussion.php');
        }
    } 

    public function disable_comments() {

        $settings = $this->get_settings();

        $roles = $settings['disable_based_on_roles'];
        $current_role = wp_get_current_user()->roles;

        if( $settings['disable_globally'] || $this->is_disabled( get_post_type() ) || !empty( array_intersect( $roles, $current_role ) ) ) {
          
            add_action('template_redirect', array($this, 'check_comment_template'));

        }
        wp_deregister_script('comment-reply');

    } 

    public function is_disabled( $post_type ) {

        $settings = $this->get_settings();
        $disabled_types = $settings['disable_for_post_types'];

        return in_array( $post_type, $disabled_types );
    } 

    public function check_comment_template() {
        add_filter( 'comments_template', array($this, 'dummy_comments_template'), 9999 );
    } 

    public function dummy_comments_template() { 
        return GSPU_PLUGIN_DIR . '/view/dummy.php';
    } 

    public function disable_comments_posts() {

        global $pagenow;
        
        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }
        
        $settings   = $this->get_settings();
        $post_types = $settings['disable_for_post_types'] ?? [];
        
        // Disable support for comments and trackbacks in post types
        foreach ( $post_types as $post_type ) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
        
        // Remove comments metabox from dashboard
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    } 

    public function disable_site_widget() {
        unregister_widget('WP_Widget_Recent_Comments');
        add_filter('show_recent_comments_widget_style', '__return_false');
    }  

    function validate_settings( $settings ) {

        $settings['disable_globally']           = wp_validate_boolean( $settings['disable_globally'] );
        $settings['disable_for_post_types']     = array_map( 'sanitize_key', (array) $settings['disable_for_post_types'] );
        $settings['disable_based_on_roles']     = array_map( 'sanitize_key', (array) $settings['disable_based_on_roles'] );
        return $settings;
    }

}