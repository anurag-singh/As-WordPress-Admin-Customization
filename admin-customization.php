<?php
/*
 * Plugin Name: As Wordpress Admin Customization
 * Version: 1.0
 * Plugin URI: http://www.wordpress.com/
 * Description: Lets clean wordpress's admin area.
 * Author: Anurag Singh
 * Author URI: http://www.wordpress.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: as-admin-customization
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Anurag Singh
 * @since 1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-admin-customization.php' );
require_once( 'includes/class-admin-customization-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-admin-customization-admin-api.php' );
require_once( 'includes/lib/class-admin-customization-post-type.php' );
require_once( 'includes/lib/class-admin-customization-taxonomy.php' );

/**
 * Returns the main instance of Admin_Customization to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Admin_Customization
 */
function Admin_Customization () {
	$instance = Admin_Customization::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Admin_Customization_Settings::instance( $instance );

	}

	return $instance;
}

Admin_Customization();


// Add custom post type (CPT) - Slider
$enableSlider = Admin_Customization()->slider_enabled;					// If Slider 'option' is enable in Admin area
if(post_type_exists('slide') == FALSE && $enableSlider == TRUE)  {
	Admin_Customization()->register_post_type( 'slide', __( 'Slider', 'as-admin-customization' ), __( 'Slide', 'as-admin-customization' ) );
}
// Add custom post type (CPT) - Slider




// Remove Welcome Panel from WordPress Dashboard
