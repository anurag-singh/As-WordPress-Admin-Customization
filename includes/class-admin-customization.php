<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin_Customization {

	/**
	 * The single instance of Admin_Customization.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Get the Bx Slider display permissions.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $slider_enabled;

	/**
	 * Get the Testimonials display permissions.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $testimonials_enabled;
	public $testimonial_content_box;
	public $testimonial_featured_image_box;

	/**
	 * Get the FAQ display permissions.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $faq_enabled;

	/**
	 * Get the Custom Forms Display permissions.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $custom_forms_enabled;


	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'admin_customization';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$this->slider_enabled = get_option( 'admin_customization_enable_slider');	// Setup a global var for getting Slider display permissions

		$this->testimonials_enabled = get_option( 'admin_customization_enable_testimonials');
		$this->testimonial_content_box = get_option( 'admin_customization_testimonial_content_box');
		$this->testimonial_featured_image_box = get_option( 'admin_customization_testimonial_featured_image');


		$this->faq_enabled = get_option( 'admin_customization_enable_faq');

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// bx-slider
		if($this->slider_enabled == TRUE) {	// Will add bx slider lib only if slider is enable from admin area
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_for_bx_slider' ), 10 );
			add_action('wp_footer', array( $this, 'define_bx_slider_properties'));
		}
		add_shortcode('bx-slider', array( $this, 'render_bx_slider'));
		// bx-slider

		add_shortcode('testimonials', array( $this, 'render_testimonials'));

		add_shortcode('faq', array( $this, 'render_faq'));


		// Logn / register / forget pass form
		add_shortcode( 'register-form', array( $this, 'display_register_form' ));
		add_action('wp_ajax_nopriv_register_user_for_site', array( $this, 'register_user' ));

		add_shortcode( 'login-form', array( $this, 'display_login_form' ));
		add_action('wp_ajax_nopriv_get_user_logged_in', array( $this, 'user_logged_in' ));

		add_shortcode( 'forget-password-form', array( $this, 'display_forget_password_form' ));
		add_action('wp_ajax_nopriv_send_user_password_reset_email', array( $this, 'user_password_reset' ));



		$this->custom_forms_enabled = get_option( 'admin_customization_enable_custom_forms');
		if($this->custom_forms_enabled == TRUE) {	// Will add bx slider lib only if slider is enable from admin area
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_for_custom_form' ), 10 );
			//add_action('wp_footer', array( $this, 'define_bx_slider_properties'));

		}


		// Logn / register / forget pass form


		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Remove Welcome Panel from WordPress Dashboard
		remove_action('welcome_panel', 'wp_welcome_panel');
		// Remove Welcome Panel from WordPress Dashboard

		// function remove_activity_dashboard_widget() {
		//     remove_meta_box( 'dashboard_activity', 'dashboard', 'side' );
		// }

		// // Hook into the 'wp_dashboard_setup' action to register our function
		// add_action('wp_dashboard_setup', 'remove_activity_dashboard_widget' );


		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Admin_Customization_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new Admin_Customization_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new Admin_Customization_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_register_style( $this->_token . '-font-awesome', esc_url( $this->assets_url ) . 'css/font-awesome.min.css', array(), $this->_version );

		wp_enqueue_style( $this->_token . '-frontend' );
		wp_enqueue_style( 'font-awesome' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()



	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_register_style( 'font-awesome-admin', esc_url( $this->assets_url ) . 'css/font-awesome.min.css', array(), $this->_version );

		wp_enqueue_style( $this->_token . '-admin' );
		wp_enqueue_style( 'font-awesome-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'admin-customization', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'admin-customization';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Admin_Customization Instance
	 *
	 * Ensures only one instance of Admin_Customization is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Admin_Customization()
	 * @return Main Admin_Customization instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()


	/* Remove Comments */
	public function get_all_selected_posts () {
		$post_types = get_option('as_hide_comments_for_post_types');
		return $post_types;
	}
	public function as_hide_comments() {
		add_action('admin_init', 'df_disable_comments_post_types_support');
		$post_types = $this->get_all_selected_posts();
		if(in_array('all', $post_types)){
			add_filter('comments_open', 'df_disable_comments_status', 20, 2);
			add_filter('pings_open', 'df_disable_comments_status', 20, 2);
			add_filter('comments_array', 'df_disable_comments_hide_existing_comments', 10, 2);
			add_action('admin_menu', 'df_disable_comments_admin_menu');
			add_action('admin_init', 'df_disable_comments_admin_menu_redirect');
			add_action('admin_init', 'df_disable_comments_dashboard');
			add_action('init', 'df_disable_comments_admin_bar');
			add_action( 'wp_before_admin_bar_render', 'my_admin_bar_render' );
		}

	}
	/* Remove Comments */

	/**
	 * Load frontend Javascript for bx Slider.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts_for_bx_slider () {
		wp_register_script( $this->_token . '-bxslider', esc_url( $this->assets_url ) . 'js/jquery.bxslider.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-bxslider' );

		// $slider_params = get_option( 'admin_customization_display_captions_on_slider');

		// wp_localize_script( $this->_token . '-bxslider', 'sliderParams', $slider_params );
	} // End enqueue_scripts ()


	/**
	 * Load bx Slider for shortcode [bx-slider]
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	//
	public function render_bx_slider() {
		if($this->slider_enabled == TRUE) {
			$slider_captions = get_option( 'admin_customization_display_captions_on_slider');

			$sliderArg = array(
				'post_type' => 'slide'						// Set post-type to 'slide'
			);
			$bxSlider = new WP_Query( $sliderArg ); 		// Store query data in var

			if($bxSlider->have_posts()) : 					// If post found
			$html = '<ul class="bxslider">';			// Setup main container for 'bx slider'

				while ($bxSlider->have_posts()) : 		// #Start a loop to fetch each post
					$bxSlider->the_post();	 			// Setup post data

					$attachedImageId = get_post_thumbnail_id(get_the_ID());			// Get the img ID

					$attachedImageSrc = wp_get_attachment_image_src( $attachedImageId, 'full' );	// Get img source

					$attachedImageSrc = array_shift($attachedImageSrc);

					$html .= '<li style="background: url('. $attachedImageSrc . ') no-repeat;">';
					// $html .= '<div class="slider-title">';
					// $html .= get_the_title();
					// $html .= '</div>';
					if($slider_captions == 'true') {
						$html .= '<div class="slider-content">';
						$html .= get_the_content();
						$html .= '</div>';
					}
					$html .= '</li>';

				endwhile;

			$html .= '</ul>';							// Close main container

			endif;

		} else {
			$html = "Slider is disabled by admin.";										// Close 'if' condition
		}
		return $html;									// Return the var
	}


	function define_bx_slider_properties() {
		$slider_auto_play = get_option( 'admin_customization_autoPlay_slider');
		$slider_mode = get_option( 'admin_customization_mode_slider');
		$slider_auto_controls = get_option( 'admin_customization_autoControls_slider');
		$slider_speed = get_option( 'admin_customization_speed_slider');
		$slider_pager = get_option( 'admin_customization_pager_slider');
		// $slider_captions = get_option( 'admin_customization_display_captions_on_slider');

		?>
		<script>
			jQuery(document).ready(function ($) {
			    $('.bxslider').bxSlider({	// Setup Bx slider
			        auto: <?php echo $slider_auto_play ?>,
			        mode: '<?php echo $slider_mode ?>',
			        autoControls: <?php echo $slider_auto_controls ?>,
			        speed: <?php echo $slider_speed ?>,
			        stopAutoOnClick: true,
			        pager: <?php echo $slider_pager ?>,
			    });
			});
		</script>
	<?php
	}

	/**
	 * Load Testimonials for shortcode [testimonials]
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	//
	public function render_testimonials() {
		if($this->testimonials_enabled == TRUE) {
			$testimonialArg = array(
				'post_type' => 'testimonial',
				'order'		=> 'DESC',
				'orderby'	=>	'ID'
			);
			$testimonials = new WP_Query( $testimonialArg ); 		// Store query data in var

			if($testimonials->have_posts()) :
							// If post found
			$html = '<ul class="list-unstyled">';			// Setup main container for 'bx slider'

				while ($testimonials->have_posts()) : 		// #Start a loop to fetch each post
				$testimonials->the_post();	 			// Setup post data

				$html .= '<li>';
				$html .= '<blockquote class="blockquote text-left">';
				$html .= '<p class="mb-0">'. get_the_content().'</p>';
				$html .= '<footer class="blockquote-footer"><cite title="'. get_the_title().'">'. get_the_title().'</cite></footer>';
				$html .= '</blockquote>';
				$html .= '</li>';

				endwhile;								// #Exit from loop, when all post fetched

			$html .= '</ul>';							// Close main container

			endif;

		} else {
			$html = "Testimonials are disabled by admin.";										// Close 'if' condition
		}
		return $html;									// Return the var
	}

	/**
	 * Load FAQ for shortcode [faq]
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	//
	public function render_faq() {
		if($this->faq_enabled == TRUE) {
			$faqArg = array(
				'post_type' => 'faq',
				'order'		=> 'DESC',
				'orderby'	=>	'ID'
			);
			$faqs = new WP_Query( $faqArg ); 		// Store query data in var

			if($faqs->have_posts()) :
				$faqCounter = 1;
							// If post found
			$html = '<div id="accordion faq" role="tablist">';			// Setup main container for 'bx slider'

				while ($faqs->have_posts()) : 		// #Start a loop to fetch each post
				$faqs->the_post();	 			// Setup post data

				$html .= '<div class="card">';
			    $html .= '<div class="card-header" role="tab" id="headingOne">';
			    $html .= '<h5 class="mb-0">';
			    $html .= '<a data-toggle="collapse" href="#collapse'.$faqCounter .'" role="button" aria-expanded="true" aria-controls="collapse'.$faqCounter .'">';
				$html .= get_the_title();
				$html .= '</a>';
				$html .= '</h5>';
				$html .= '</div>';

				// if($faqCounter == 1) {
				// 	$html .= '<div id="collapse' .$faqCounter . '" class="collapse show" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion">';
				// } else {
				// 	$html .= '<div id="collapse' .$faqCounter . '" class="collapse" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion">';

				// }
				$html .= '<div id="collapse' .$faqCounter . '" class="collapse" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion">';
				$html .= '<div class="card-body">';
				$html .= get_the_content();
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';

				$faqCounter++;
				endwhile;								// #Exit from loop, when all post fetched

			$html .= '</div>';							// Close main container

			endif;

		} else {
			$html = "FAQs are disabled by admin.";										// Close 'if' condition
		}
		return $html;									// Return the var
	}


	/**
	 * Load frontend scripts for custom forms (login / register / forget password).
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts_for_custom_form () {
		wp_register_style( $this->_token . '-asForms', esc_url( $this->assets_url ) . 'css/as-registerLoginForgetPass-forms.css');
		wp_enqueue_style( $this->_token . '-asForms' );

		wp_register_script( $this->_token . '-asForms', esc_url( $this->assets_url ) . 'js/as-registerLoginForgetPass-forms.js');

		wp_enqueue_script( 'form-validate', get_stylesheet_directory_uri() . '/assets/js/jquery.validate.min.js', array('jquery'), '', true);
		wp_enqueue_script( $this->_token . '-asForms' );

	    wp_localize_script( $this->_token . '-asForms', 'ajax_object',
	            array(
	                'ajax_url' => admin_url( 'admin-ajax.php' ),
	                'home_url' => home_url()
	            ) );
	} // End enqueue_scripts ()


	/**
	 * Display Register for when shortcode is used
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function display_register_form(){
		if(is_user_logged_in()) : 				// If user not logged in
			wp_safe_redirect( home_url( 'Dashboard' ) );	// Send them to dashboard page
		endif;
		?>
		<div class="card">
			<div class="card-header text-center">
				<?php the_title(); ?>
			</div>
			<div class="card-body">
				<form id="as-register" role="form" method="post">
					<h2>Please Sign Up <small>It's free and always will be.</small></h2>
					<hr class="colorgraph">
					<div class="row">
						<div class="col-xs-12 col-sm-6 col-md-6">
							<div class="form-group">
		                        <input type="text" name="user_register_first_name" id="user_register_first_name" class="form-control input-lg" placeholder="First Name" tabindex="1" required="">
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-6">
							<div class="form-group">
								<input type="text" name="user_register_last_name" id="user_register_last_name" class="form-control input-lg" placeholder="Last Name" tabindex="2">
							</div>
						</div>
					</div>
					<div class="form-group">
						<input type="email" name="user_register_email" id="user_register_email" class="form-control input-lg" placeholder="Email Address" tabindex="4" required="">
					</div>
					<div class="row">
						<div class="col-xs-12 col-sm-6 col-md-6">
							<div class="form-group">
								<input type="password" name="user_register_password" id="user_register_password" class="form-control input-lg" placeholder="Password" tabindex="5" required="">
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-6">
							<div class="form-group">
								<input type="password" name="user_register_password_confirmation" id="user_register_password_confirmation" class="form-control input-lg" placeholder="Confirm Password" tabindex="6" required="">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<div class="form-check">
								<label class="form-check-label">
									<input class="form-check-input" name="user_register_t_and_c" id="user_register_t_and_c" type="checkbox"> By clicking <strong class="badge badge-primary">Register</strong>, you agree to the <a href="#" data-toggle="modal" data-target="#user_register_t_and_c">Terms and Conditions</a> set out by this site, including our Cookie Use.
								</label>
							</div>
						</div>
					</div>

					<hr class="colorgraph">

					<div class="row">
						<div class="col-12">
							<div class="alert" id="form-action-status" style="display: none;"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12 col-md-6">
							<button type="submit" class="btn btn-primary btn-block btn-lg" tabindex="7">Register</button>
						</div>
						<div class="col-xs-12 col-md-6">
							<a href="#" class="btn btn-success btn-block btn-lg">Sign In</a>
						</div>
					</div>
				</form>
			</div>
			<div class="card-footer text-muted">
				<p>
					Already registered! Click here to login.
					<a href="<?php echo get_permalink( get_page_by_title( 'Login' ) ); ?>" class="btn btn-primary btn-sm">Login</a>
				</p>
			</div>
		</div>

		<?php
		}


		/**
		 * Proccess register form when submitted
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function register_user() {
			$userData['first_name'] = $_POST['register_user_f_name'];
			$userData['last_name'] = $_POST['register_user_l_name'];
			$userData['user_email'] = $_POST['register_user_email'];
			$userData['user_pass'] = $_POST['register_user_password'];
			$userData['user_login'] = $userData['first_name'] . ' ' . $userData['last_name'];

			global $wp_roles;
			$roles = $wp_roles->get_names();
			if(in_array("Member", $roles)){					// If user role 'member' found
				$userData['role'] = 'member';				// then set role to 'member'
			} else {
				$userData['role'] = 'subscriber';			// then set role to 'subscriber'
			}


			if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_user_email']) && isset($_POST['register_user_password']) ) { // Server side validation => TRUE

					$newUserID = wp_insert_user( $userData );

					if ( ! is_wp_error( $newUserID ) ) {
			            $statusCode = 1;
						$msg = 'You have successfully register on our site.';
					} else {
			            $statusCode = 0;
						$msg = $newUserID->get_error_message();
					}

					if(! is_wp_error( $newUserID )) {
						// Send new user to a notification email along with password
						$sender = get_option('blogname');
						$senderEmail = get_option('admin_email');

						$to = $userData['user_email'];
						$subject = 'Thanks for register with us!';

						$message = 'Hello ' . $userData['last_name'] .',<br/><br/>';
						$message .= 'We warmly welcome you for register with us.<br/><br/>';
						$message .= 'Kindly note beneath mentioned login credentials for further use: <br/><br/>';
						$message .= '<b>User Name:</b> '. $userData['user_email'] .'<br/>';
						$message .= '<b>Password:</b> '. $userData['user_pass'] .'<br/><br/><br/>';
						$message .= 'Thanks';

						$header = 'MIME-Version: 1.0' . "\r\n";
						$header .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
						$header .= "X-Mailer: PHP \r\n";
						$header .= 'From: '.$sender.' < '.$senderEmail.'>' . "\r\n";

						$mail = wp_mail( $to, $subject, $message, $header);

						if( $mail ) {
							$statusCode = 3;;
							$msg = 'Check your email address for you new password.';
						} else {
							$statusCode = 4;;
							$msg = 'Unable to send email - ' . $to . ' / ' . $subject . ' / ' . $message;
						}
						// Send new user to a notification email along with password
					}


			} else {
				$statusCode = 5;
				$msg = "Unable to fetch form's value.";
			}

			$response = array(
		                    'status' => $statusCode,
		                    'msg'=> $msg
		                    );

			echo json_encode($response);

			exit;
		}


		/**
		 * Display login form by shortcodde
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function display_login_form(){
			if(is_user_logged_in()) : 				// If user not logged in
				wp_safe_redirect( home_url( 'Dashboard' ) );	// Send them to dashboard page
			endif;
		?>
		<div class="card">
			<div class="card-header text-center">
				<?php the_title(); ?>
			</div>
			<div class="card-body">
				<form id="as-login" method="post">
					<div class="form-group">
						<label for="user_email">Email address</label>
						<input type="email" class="form-control" name="user_email" id="user_email" placeholder="Enter email" required="">
					</div>
					<div class="form-group">
						<label for="user_pass">Password</label>
						<input type="password" class="form-control" name="user_pass" id="user_pass" placeholder="Password" required="">
						<small id="emailHelp" class="form-text text-muted">Forget Password? <a href="<?php echo get_permalink( get_page_by_title( 'Forget Password' ) ); ?>">Click here to reset.</a></small>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="exampleCheck1">
						<label class="form-check-label" for="exampleCheck1">remember me</label>
					</div>
					<button type="submit" class="btn btn-outline-info float-right">Submit</button>
					<div class="alert" id="form-action-status" style="display: none;"></div>
				</form>
			</div>
			<div class="card-footer text-muted">
				<p>
					Not a member yet! Click here to get register
					<a href="<?php echo get_permalink( get_page_by_title( 'Register' ) ); ?>" class="btn btn-primary btn-sm">Register</a>
				</p>
			</div>
		</div>

		<?php
		}

		/**
		 * Proccess login form when submitted
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function user_logged_in() {
			$userEmail 	= $_POST['user_email'];
			$userPass 	= $_POST['user_pass'];
			$rememberMe = $_POST['remember_me'];

			if($_SERVER["REQUEST_METHOD"] == "POST" && isset($userEmail) && isset($userPass) ) { // Server side validation => TRUE

		        $credentials = array(
		        				'user_login' 		=>	$userEmail
		        				,'user_password' 	=>	$userPass
		        				,'remember' 		=>	$rememberMe
		        			);

		        $user_signon = wp_signon( $credentials, false );

				if ( is_wp_error($user_signon) ){
		            $response = array(
		                            'status' => 0
		                            ,'msg' => 'Wrong username or password, Please provide correct information.'
		                            //,'remind' => $info['remember']
		                        );
		        } else {
		            $response = array(
		                            'status' => 1
		                            ,'msg' => 'Login successful, redirecting.'
		                            //,'remind' => $info['remember']
		                        );
		        }

			} else {																			// Server side validation => FALSE
				$response = array(
		                    'status' => 0,
		                    'msg'=> "Unable to fetch form's value."
		                    );
			}


		   	echo json_encode($response);

		  	exit;

		}

		/**
		 * Dispaly forget password form when shortcode used
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function display_forget_password_form(){
			if(is_user_logged_in()) : 				// If user not logged in
				wp_safe_redirect( home_url( 'Dashboard' ) );	// Send them to dashboard page
			endif;
		?>
			<div class="card">
				<div class="card-header text-center">
					<?php the_title(); ?>
				</div>
				<div class="card-body">
					<form id="as-forget-password" method="post">
						<div class="form-group">
							<label for="user_email">Email address</label>
							<input type="email" class="form-control" name="user_email" id="user_email" placeholder="Enter email" required="">
						</div>

						<button type="submit" class="btn btn-outline-info float-right">Submit</button>
						<div class="alert" id="form-action-status" style="display: none;"></div>
					</form>
				</div>
				<div class="card-footer text-muted">
					<p>You can also :</p>
					<ul class="list-inline">
						<li class="list-inline-item"><a href="<?php echo get_permalink( get_page_by_title( 'Register' ) ); ?>" class="btn btn-primary btn-sm">Register</a></li>
						<li class="list-inline-item"><a href="<?php echo get_permalink( get_page_by_title( 'Login' ) ); ?>" class="btn btn-primary btn-sm">Login</a></li>
					</ul>

				</div>
			</div>
		<?php
		}


		/**
		 * process forget pass form when submitted
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function user_password_reset() {
			$userEmail 	= $_POST['user_email'];

			if($_SERVER["REQUEST_METHOD"] == "POST" ) {

				if( empty( $userEmail ) ) {
					$statusCode = 0;
		            $msg = 'Enter a username or email address.';
		        } else if( ! is_email( $userEmail )) {
		        	$statusCode = 0;
		            $msg = 'Enter a valid email address.';
		        } else if( ! email_exists( $userEmail ) ) {
		        	$statusCode = 0;
		            $msg = 'There is no user registered with that email address.';
		        } else {

		            $random_password = wp_generate_password( 12, false );
		            $user = get_user_by( 'email', $userEmail );

		            $update_user = wp_update_user( array (
		                    'ID' => $user->ID,
		                    'user_pass' => $random_password
		                )
		            );

		            // if  update user return true then lets send user an email containing the new password
		            if( $update_user ) {
		                $to = $userEmail;
		                $subject = 'Your new password';
		                $sender = get_option('name');

		                $message = 'Your new password is: '.$random_password;

		                $headers[] = 'MIME-Version: 1.0' . "\r\n";
		                $headers[] = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		                $headers[] = "X-Mailer: PHP \r\n";
		                $headers[] = 'From: '.$sender.' < '.$userEmail.'>' . "\r\n";

		                $mail = wp_mail( $to, $subject, $message);
		                if( $mail ) {
		                	$statusCode = 1;;
		                    $msg = 'Check your email address for you new password.';
		                } else {
							$statusCode = 0;;
		                    $msg = 'Unable to send email' . $user->ID . $random_password;
		                }


		            } else {
		            	$statusCode = 0;
		                $msg = 'Oops something went wrong updaing your account.';
		            }
		        }

				$response = array(
			                'status' => $statusCode,
			                'msg'=> $msg
		                );

			} else {
				$response = array(
				                'status' => 0,
				                'msg'=> "Unable to fetch form's value."
			                );
			}

			echo json_encode($response);

			exit;
		}






}



// Disable support for comments and trackbacks in post types
	function df_disable_comments_post_types_support() {
		//$post_types = get_post_types();
		$post_types = get_option('admin_customization_hide_comments_for_post_types');
		foreach ($post_types as $post_type) {
			if(post_type_supports($post_type, 'comments')) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	}
	// Close comments on the front-end
	function df_disable_comments_status() {
		return false;
	}
	// Hide existing comments
	function df_disable_comments_hide_existing_comments($comments) {
		$comments = array();
		return $comments;
	}
	// Remove comments page in menu
	function df_disable_comments_admin_menu() {
		remove_menu_page('edit-comments.php');
	}
	// Redirect any user trying to access comments page
	function df_disable_comments_admin_menu_redirect() {
		global $pagenow;
		if ($pagenow === 'edit-comments.php') {
			wp_redirect(admin_url()); exit;
		}
	}
	// Remove comments metabox from dashboard
	function df_disable_comments_dashboard() {
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	}
	// Remove comments links from admin bar
	function df_disable_comments_admin_bar() {
			if (is_admin_bar_showing()) {
					remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
			}
	}
	function my_admin_bar_render() {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('comments');
	}
