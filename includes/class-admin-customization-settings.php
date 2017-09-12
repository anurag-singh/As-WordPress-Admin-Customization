<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin_Customization_Settings {

	/**
	 * The single instance of Admin_Customization_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'admin_customization_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

		// Change WP admin screen logo
		add_action( 'login_enqueue_scripts', array($this, 'custom_login_stylesheet') );


		// Change WP admin screen logo
		add_action( 'login_enqueue_scripts', array($this, 'custom_login_logo' ));

		// Change WP admin screen logo URL
		add_filter( 'login_headerurl', array($this, 'update_wp_login_logo_url' ));

		// Change WP admin screen logo Title
		add_filter( 'login_headertitle', array($this, 'update_wp_login_logo_title' ));

		// Make "remember me" as checked always
		add_action( 'init' , array( $this, 'login_checked_remember_me' ) );		

		// Redirect general user to public view 
		add_filter('login_redirect', array($this, 'my_loginredrect'), 10, 3);

		// Remove lost your password link from login screen
		add_filter( 'gettext', array($this, 'remove_lostpassword_text' ));
		
		// Add extra content in header of wp login screen
		add_action('login_header',array($this, 'login_form_header'));

		// Add extra content in footer of wp login screen
		add_action('login_footer',array($this, 'login_form_footer'));



		// Remove WP logo from wp user's area
		add_action( 'admin_bar_menu', array($this, 'remove_wp_logo_from_admin_screen'), 999 );

		// Change "Howdy" Text from wp user's area
		add_filter( 'gettext', array($this, 'change_howdy_text_from_admin_screen'), 10, 3 );
	
		// Remove widgets from wp user's dashboard
		add_action( 'wp_dashboard_setup', array($this, 'remove_dashboard_widgets'));

		// Add additional fields in WP User's profile
		add_filter('user_contactmethods',array($this, 'add_new_fields_in_user_profile'),10,1);


		// Update left admin menu
		add_filter( 'custom_menu_order', array($this, 'reorder_admin_menu' ));
		add_filter( 'menu_order', array($this, 'reorder_admin_menu' ));

	


		



	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Website UI Settings', 'admin-customization' ) , __( 'Website UI Settings', 'admin-customization' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}


	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'admin-customization' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['as-admin-area'] = array(
			'title'					=> __( 'Admin Area', 'admin-customization' ),
			'description'			=> __( 'These are fairly standard form input fields.', 'admin-customization' ),
			'fields'				=> array(
				array(
					'id' 			=> 'text_field',
					'label'			=> __( 'Some Text' , 'admin-customization' ),
					'description'	=> __( 'This is a standard text field.', 'admin-customization' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'admin-customization' )
				),
				array(
					'id' 			=> 'password_field',
					'label'			=> __( 'A Password' , 'admin-customization' ),
					'description'	=> __( 'This is a standard password field.', 'admin-customization' ),
					'type'			=> 'password',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'admin-customization' )
				),
				array(
					'id' 			=> 'secret_text_field',
					'label'			=> __( 'Some Secret Text' , 'admin-customization' ),
					'description'	=> __( 'This is a secret text field - any data saved here will not be displayed after the page has reloaded, but it will be saved.', 'admin-customization' ),
					'type'			=> 'text_secret',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'admin-customization' )
				),
				array(
					'id' 			=> 'text_block',
					'label'			=> __( 'A Text Block' , 'admin-customization' ),
					'description'	=> __( 'This is a standard text area.', 'admin-customization' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text for this textarea', 'admin-customization' )
				),
				array(
					'id' 			=> 'single_checkbox',
					'label'			=> __( 'An Option', 'admin-customization' ),
					'description'	=> __( 'A standard checkbox - if you save this option as checked then it will store the option as \'on\', otherwise it will be an empty string.', 'admin-customization' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
				array(
					'id' 			=> 'select_box',
					'label'			=> __( 'A Select Box', 'admin-customization' ),
					'description'	=> __( 'A standard select box.', 'admin-customization' ),
					'type'			=> 'select',
					'options'		=> array( 'drupal' => 'Drupal', 'joomla' => 'Joomla', 'wordpress' => 'WordPress' ),
					'default'		=> 'wordpress'
				),
				array(
					'id' 			=> 'radio_buttons',
					'label'			=> __( 'Some Options', 'admin-customization' ),
					'description'	=> __( 'A standard set of radio buttons.', 'admin-customization' ),
					'type'			=> 'radio',
					'options'		=> array( 'superman' => 'Superman', 'batman' => 'Batman', 'ironman' => 'Iron Man' ),
					'default'		=> 'batman'
				),
				array(
					'id' 			=> 'multiple_checkboxes',
					'label'			=> __( 'Some Items', 'admin-customization' ),
					'description'	=> __( 'You can select multiple items and they will be stored as an array.', 'admin-customization' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'square' => 'Square', 'circle' => 'Circle', 'rectangle' => 'Rectangle', 'triangle' => 'Triangle' ),
					'default'		=> array( 'circle', 'triangle' )
				)
			)
		);

		$settings['as-login-customization'] = array(
			'title'					=> __( 'Login Screen', 'admin-customization' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'admin-customization' ),
			'fields'				=> array(
				// array(
				// 	'id' 			=> 'client_logo',
				// 	'label'			=> __( "Client's Logo" , 'admin-customization' ),
				// 	'description'	=> __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', 'admin-customization' ),
				// 	'type'			=> 'image',
				// 	'default'		=> '',
				// 	'placeholder'	=> ''
				// ),
				array(
					'id' 			=> 'developer_logo_image',
					'label'			=> __( "Developer's Logo" , 'admin-customization' ),
					'description'	=> __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', 'admin-customization' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'developer_logo_title',
					'label'			=> __( "Developer's logo Title" , 'admin-customization' ),
					'description'	=> __( 'Displayed once mouse hover on logo image', 'admin-customization' ),
					'type'			=> 'text',
					'default'		=> 'Developer logo title',
					'placeholder'	=> __( 'Title tag', 'admin-customization' )
				),
				array(
					'id' 			=> 'developer_logo_url',
					'label'			=> __( "Developer's logo URL" , 'admin-customization' ),
					'description'	=> __( 'Hyperlink for logo', 'admin-customization' ),
					'type'			=> 'text',
					// 'default'		=> 'https://www.google.co.in/',
					'placeholder'	=> __( 'https://www.google.co.in/', 'admin-customization' )
				),
				array(
					'id' 			=> 'developer_contact_no',
					'label'			=> __( "Developer's Contact No" , 'admin-customization' ),
					'description'	=> __( 'Contact No', 'admin-customization' ),
					'type'			=> 'text',
					// 'default'		=> '0000-000-000',
					'placeholder'	=> __( '0000-000-000', 'admin-customization' )
				),
				array(
					'id' 			=> 'developer_email',
					'label'			=> __( "Developer's Email" , 'admin-customization' ),
					'description'	=> __( 'Email Id', 'admin-customization' ),
					'type'			=> 'text',
					// 'default'		=> 'email@gmail.com',
					'placeholder'	=> __( 'email@gmail.com', 'admin-customization' )
				),
				array(
					'id' 			=> 'developer_website',
					'label'			=> __( "Developer's Website" , 'admin-customization' ),
					'description'	=> __( 'Website', 'admin-customization' ),
					'type'			=> 'text',
					// 'default'		=> 'https://www.google.co.in/',
					'placeholder'	=> __( 'https://www.google.co.in/', 'admin-customization' )
				),
				
			)
		);

		$settings['as-smtp-details'] = array(
			'title'					=> __( 'SMTP Details', 'admin-customization' ),
			'description'			=> __( 'Setup SMTP details for emails.', 'admin-customization' ),
			'fields'				=> array(
				array(
					'id' 			=> 'from_email_id',
					'label'			=> __( 'Email Comes From' , 'admin-customization' ),
					'description'	=> __( 'no-reply@domainname.com', 'admin-customization' ),
					'type'			=> 'text',
					'default'		=> 'no-reply@domainname.com',
					'placeholder'	=> __( 'no-reply@domainname.com', 'admin-customization' )
				)
				,array(
					'id' 			=> 'from_email_name',
					'label'			=> __( 'Email Send By' , 'admin-customization' ),
					'description'	=> __( 'Your Name', 'admin-customization' ),
					'type'			=> 'text',
					'default'		=> 'Your Name',
					'placeholder'	=> __( 'Your Name', 'admin-customization' )
				)
				,array(
					'id' 			=> 'smtp_host',
					'label'			=> __( 'SMTP Host' , 'admin-customization' ),
					'description'	=> __( 'yourdomain.com', 'admin-customization' ),
					'type'			=> 'text',
					'default'		=> 'yourdomain.com',
					'placeholder'	=> __( 'yourdomain.com', 'admin-customization' )
				)
				,array(
					'id' 			=> 'smtp_port',
					'label'			=> __( 'SMTP Port' , 'admin-customization' ),
					'description'	=> __( '25', 'admin-customization' ),
					'type'			=> 'number',
					'default'		=> '25',
					'placeholder'	=> __( '25', 'admin-customization' )
				)
				,array(
					'id' 			=> 'smtp_username',
					'label'			=> __( 'User Name' , 'admin-customization' ),
					'description'	=> __( 'username@yourdomain.com', 'admin-customization' ),
					'type'			=> 'text',
					'default'		=> 'username@yourdomain.com',
					'placeholder'	=> __( 'username@yourdomain.com', 'admin-customization' )
				)
				,array(
					'id' 			=> 'smtp_password',
					'label'			=> __( 'Password' , 'admin-customization' ),
					'description'	=> __( 'yourpassword', 'admin-customization' ),
					'type'			=> 'text',
					'default'		=> 'yourpassword',
					'placeholder'	=> __( '*******', 'admin-customization' )
				)
				
				
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Plugin Settings' , 'admin-customization' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'admin-customization' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main Admin_Customization_Settings Instance
	 *
	 * Ensures only one instance of Admin_Customization_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Admin_Customization()
	 * @return Main Admin_Customization_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()


	/*
	 * Add custom CSS for Login screen
	 */
	function custom_login_stylesheet() {
	    wp_enqueue_style( 'custom-login', plugin_dir_url(__FILE__) . '../assets/css/frontend.css' );
	}
	

	/*
	 * Change logo of WP login Screen
	 */
	function custom_login_logo() { 
		$devLogo = get_site_option('admin_customization_developer_logo_image');

	    		
		?>
	    <style type="text/css">
	        #login h1 a, .login h1 a {
	        	/*height:150px; 
	        	width:auto;
	            background-size:auto; */
	            background-position: center center;
	            background-image: url(<?php echo plugin_dir_url( __FILE__ ); ?>../assets/images/site-logo.jpg);
	        }
	    </style>
	<?php }
	
	 /*
	 * Change logo URL on WP login Screen
	 */
	public function update_wp_login_logo_url() {
		// return get_bloginfo( 'url' );
		return esc_html(get_site_option('admin_customization_developer_logo_url')); 
	}

	/*
	 * Change logo URL on WP login Title
	 */
	public function update_wp_login_logo_title() {
		// return get_bloginfo('name');
		return get_site_option('admin_customization_developer_logo_title'); 
	}

	/*
	 * Make "remember me" as checked always
	 */
	public function login_checked_remember_me() {
		add_filter( 'login_footer', array($this, 'rememberme_checked') );
	}
	
	public function rememberme_checked() {
		echo "<script>document.getElementById('rememberme').checked = true;</script>";
	}


	/*
	 * Redirect user to public view if they are not "Administrator"
	 */
	public function my_loginredrect( $redirect_to, $request, $user ) {
		  if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		    if( in_array('administrator', $user->roles)) {
		      	return admin_url();
		    } else {
		      	return site_url();
		    }
		  } else {
		      	return site_url();
		  }
	}
	 
	/*
	 * Remove Lost Password link from login screen
	 */
	public function remove_lostpassword_text ( $text ) {
		if ($text == 'Lost your password?'){
			$text = '';
		}
		return $text;
	}

	/*
	 * Add another link to header of login screen
	*/
	public function login_form_header() { ?>
	    <div class="wp-login-header-wrapper">
	    	<!-- <a href="http://anuragsingh.me/">For the latest tips & tricks, visit my website!</a> -->
		    <h2>
		    	<a href="#">Anurag Singh</a>
		    </h2>

	    </div>
	<?php }


	/*
	 * Add another link to footer of login screen
	*/
	public function login_form_footer() { ?>
	    <div class="wp-login-footer-wrapper">
	    	<?php 
	    		$devContactNo = get_site_option('admin_customization_developer_contact_no');
	    		$devEmail = get_site_option('admin_customization_developer_email');
	    		$devWebsite = get_site_option('admin_customization_developer_website');
	    	?>
		    <ul>
		    	<li>Phone : <a href="tel:<?php echo $devContactNo; ?>"><?php echo $devContactNo ?></a></li>
		    	<li>Email : <a href="mailto:<?php echo $devEmail; ?>"><?php echo $devEmail; ?></a></li>
		    	<li>Website : <a href="<?php echo $devWebsite; ?>"><?php echo $devWebsite; ?></a></li>
		    </ul>
	    </div>
	<?php }



	/*
	 * Remove WP logo from admin area
	 */
	public function remove_wp_logo_from_admin_screen( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'wp-logo' );
	}



	/*
	 * Change "Howdy" Text from admin area
	 */
	public function change_howdy_text_from_admin_screen($translated, $text, $domain) {
	    if (!is_admin() || 'default' != $domain)
	        return $translated;

	    if (false !== strpos($translated, 'Howdy'))
	        return str_replace('Howdy', 'Welcome', $translated);

	    return $translated;
	}


	/*
	 * Remove widgets from user dashboard
	 */
	public function remove_dashboard_widgets () {

		//Completely remove various dashboard widgets (remember they can also be HIDDEN from admin)
		remove_meta_box( 'dashboard_quick_press',   'dashboard', 'side' );      //Quick Press widget
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );      //Recent Drafts
		remove_meta_box( 'dashboard_primary',       'dashboard', 'side' );      //WordPress.com Blog
		remove_meta_box( 'dashboard_secondary',     'dashboard', 'side' );      //Other WordPress News
		remove_meta_box( 'dashboard_incoming_links','dashboard', 'normal' );    //Incoming Links
		remove_meta_box( 'dashboard_plugins',       'dashboard', 'normal' );    //Plugins

	}
	

	/*
	 *  Add additional fields in WP User's profile
	 * To display in front end - "echo $curauth->twitter;"
	 */ 
	public function add_new_fields_in_user_profile( $user_contact ) {

		// Add user contact methods
		$user_contact['contact_no'] = 'Contact No.';
		$user_contact['twitter'] = 'Twitter';
		$user_contact['facebook'] = 'Facebook';
 
		return $user_contact;
	}



	/*
	 * Re-arrange in wp admin menu
	 */	
	function reorder_admin_menu( $__return_true ) {
	    return array(
	         'edit.php?post_type=page', // Pages 
	         'edit.php', // Posts
	         'upload.php', // Media
	         'separator1', // --Space--
	         'index.php', // Dashboard
	         'themes.php', // Appearance
	         'edit-comments.php', // Comments 
	         'separator2', // --Space--
	         'plugins.php', // Plugins
	         //'separator3', // --Space--
	         'tools.php', // Tools
	         'users.php', // Users
	         'options-general.php', // Settings
	   );
	}


	
}