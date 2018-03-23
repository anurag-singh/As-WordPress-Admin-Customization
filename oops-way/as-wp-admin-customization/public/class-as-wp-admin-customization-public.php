<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://anuragsingh.me
 * @since      1.0.0
 *
 * @package    As_Wp_Admin_Customization
 * @subpackage As_Wp_Admin_Customization/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    As_Wp_Admin_Customization
 * @subpackage As_Wp_Admin_Customization/public
 * @author     Anurag Singh <developer.anuragsingh@outlook.com>
 */
class As_Wp_Admin_Customization_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $as_wp_admin_customization    The ID of this plugin.
	 */
	private $as_wp_admin_customization;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	private $slider_enabled;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $as_wp_admin_customization       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $as_wp_admin_customization, $version ) {

		$this->as_wp_admin_customization = $as_wp_admin_customization;
		$this->version = $version;

		$admin_customization = $this->slider_enabled = get_option( 'admin-customization');
		$isSliderEnable = $admin_customization['slider-enabled'];

		if($isSliderEnable == TRUE) {
			echo '<pre>................';
			print_r($isSliderEnable);
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_for_bx_slider' ), 10 );
			add_action('wp_footer', array( $this, 'define_bx_slider_properties'));
		}


	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in As_Wp_Admin_Customization_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The As_Wp_Admin_Customization_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->as_wp_admin_customization, plugin_dir_url( __FILE__ ) . 'css/as-wp-admin-customization-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in As_Wp_Admin_Customization_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The As_Wp_Admin_Customization_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->as_wp_admin_customization, plugin_dir_url( __FILE__ ) . 'js/as-wp-admin-customization-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the BX Slider's Stylesheets & JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_bx_slider_style_scripts() {

		wp_enqueue_style( $this->as_wp_admin_customization, plugin_dir_url( __FILE__ ) . 'css/as-wp-admin-customization-public.css', array(), $this->version, 'all' );

		wp_enqueue_script( $this->as_wp_admin_customization, plugin_dir_url( __FILE__ ) . 'js/as-wp-admin-customization-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Display content through short code
	 *
	 * @since    1.0.0
	 */
	public function render_shortcode() {
		global $post;
		print_r($post);
	}

}
