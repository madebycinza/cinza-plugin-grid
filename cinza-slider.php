<?php

/**
 * Plugin Name:       Cinza Slider
 * Plugin URI:        https://cinza.io/plugin/slider
 * Description:       A minimal slider plugin.
 * Version:           1.0.7
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Cinza Web Design
 * Author URI:        https://cinza.io/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Register scrips for frontend
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'wp_enqueue_scripts', 'cslider_scripts_frontend_init' );
function cslider_scripts_frontend_init( $hook ) {
	
	// Register scripts only on frontend
	if ( is_admin() ) return;
	
    // CSS
    wp_register_style('flickity', plugins_url('/assets/css/flickity.min.css', __FILE__), array(), '2.2.2', false);
    wp_register_style('flickity-fade', plugins_url('/assets/css/flickity-fade.css', __FILE__), array(), '1.0.0', false);
    wp_register_style('animate', plugins_url('/assets/css/animate.min.css', __FILE__), array(), '4.1.1', false);
    wp_register_style('cslider-frontend', plugins_url('/assets/css/frontend-style.css', __FILE__), array(), '1.0.0', false);

    // JS
    wp_register_script('flickity', plugins_url('/assets/js/flickity.pkgd.min.js', __FILE__), array('jquery'), '2.2.2', false);
    wp_register_script('flickity-fade', plugins_url('/assets/js/flickity-fade.js', __FILE__), array('jquery'), '1.0.0', false);
    wp_enqueue_script('cslider-frontend', plugins_url('/assets/js/frontend-script.js', __FILE__), array('jquery'), '1.0.0', false);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Register scrips for backend
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action( 'admin_enqueue_scripts', 'add_script_to_cslider_cpt' );
function add_script_to_cslider_cpt() {
    global $post_type;
    
	// Register scripts only on backend
	if ( !is_admin() ) return;

    // Admin
    wp_register_style('cslider-admin', plugins_url('/assets/css/backend-admin.css', __FILE__), array(), '1.0.0', false);
    wp_enqueue_style('cslider-admin');
 
    // Register scripts below only on cslider CPT pages only
	if( $post_type != 'cinza_slider' ) return;

    // CSS
    wp_register_style('cslider-backend-css', plugins_url('/assets/css/backend-style.css', __FILE__), array(), '1.0.0', false);
    wp_enqueue_style('cslider-backend-css');

    // JS
    wp_enqueue_script('cslider-backend-js', plugins_url('/assets/js/backend-script.js', __FILE__), array('jquery'), '1.0.0', false);
    wp_enqueue_media();
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Include plugin files
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

define( 'CSLIDER_PATH', plugin_dir_path( __FILE__ ) );
include_once( CSLIDER_PATH . 'includes/backend-cpts.php' );
include_once( CSLIDER_PATH . 'includes/backend-shortcodes.php' );

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Activation hook
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

register_activation_hook( __FILE__, 'cslider_activate' );
function cslider_activate() { 
	
    // Register CPT
    cslider_register_post_type(); 
    
    // Reset permalinks
    flush_rewrite_rules(); 
}
    
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Deactivation hook
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

register_deactivation_hook( __FILE__, 'cslider_deactivate' );
function cslider_deactivate() {
    
    // Unregister CPT
    unregister_post_type( 'cinza_slider' );
    
    // Reset permalinks
    flush_rewrite_rules();
}

?>
