<?php

if ( ! class_exists( 'Maera_Bootstrap' ) ) {

	/**
	* The Bootstrap Shell module
	*/
	class Maera_Bootstrap {

		private static $instance;

		/**
		 * Class constructor
		 */
		public function __construct() {

			if ( ! defined( 'MAERA_SHELL_PATH' ) ) {
				define( 'MAERA_SHELL_PATH', dirname( __FILE__ ) );
			}

			$this->required_plugins();

			add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );

			// Include the customizer
			include_once( MAERA_SHELL_PATH . '/customizer.php' );

			// Include other classes
			include_once( MAERA_SHELL_PATH . '/classes/class-Maera_Widget_Dropdown.php' );
			include_once( MAERA_SHELL_PATH . '/classes/class-Maera_Bootstrap_Widgets.php' );
			include_once( MAERA_SHELL_PATH . '/classes/class-Maera_Bootstrap_Styles.php' );
			include_once( MAERA_SHELL_PATH . '/classes/class-Maera_Bootstrap_Structure.php' );
			include_once( MAERA_SHELL_PATH . '/classes/class-Maera_Bootstrap_Compiler.php' );
			include_once( MAERA_SHELL_PATH . '/classes/class-Maera_Bootstrap_Images.php' );
			include_once( MAERA_SHELL_PATH . '/includes/variables.php' );

			// Instantianate addon classes
			global $bs_structure;
			$bs_structure = new Maera_Bootstrap_Structure();
			global $bs_widgets;
			$bs_widgets   = new Maera_Bootstrap_Widgets();
			global $bs_styles;
			$bs_styles    = new Maera_Bootstrap_Styles();
			global $bs_conpiler;
			$bs_compiler  = new Maera_Bootstrap_Compiler();

			$images = new Maera_Bootstrap_Images();

			global $extra_widget_areas;
			$extra_widget_areas = $bs_widgets->extra_widget_areas_array();

			// Enqueue the scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 110 );

			// Add the shell Timber modifications
			add_filter( 'timber_context', array( $this, 'timber_extras' ), 20 );

			// Excerpt
			add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
			add_filter( 'excerpt_more', array( $this, 'excerpt_more' ), 10, 2 );

			add_action( 'wp_footer', array( $this, 'custom_js' ) );

			$widget_width = new Maera_Widget_Dropdown( 'maera_widget_width', __( 'Width' ), array(
				1  => 'col-md-1',
				2  => 'col-md-2',
				3  => 'col-md-3',
				4  => 'col-md-4',
				5  => 'col-md-5',
				6  => 'col-md-6',
				7  => 'col-md-7',
				8  => 'col-md-8',
				9  => 'col-md-9',
				10 => 'col-md-10',
				11 => 'col-md-11',
				12 => 'col-md-12',
			) );

		}


		/**
		 * Singleton
		 */
		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}


		/**
		 * Add theme supports
		 */
		function theme_supports() {

			add_theme_support( 'kirki' );
			add_theme_support( 'maera_image' );
			add_theme_support( 'maera_color' );
			add_theme_support( 'less_compiler' );

		}


		/**
		* Build the array of required plugins.
		* You can use the 'maera/required_plugins' filter to add or remove plugins.
		*/
		function required_plugins() {

			$plugins[] = array(
				'name' => 'Breadcrumb Trail',
				'file' => 'breadcrumb-trail.php',
				'slug' => 'breadcrumb-trail'
			);
			$plugins[] = array(
				'name' => 'Less & scss compilers',
				'file' => 'less-plugin.php',
				'slug' => 'lessphp'
			);

			$plugins = new Maera_Required_Plugins( $plugins );

		}

		/**
		 * Register all scripts and additional stylesheets (if necessary)
		 */
		function scripts() {

			wp_register_script( 'bootstrap-min', MAERA_BOOTSTRAP_SHELL_URL . '/assets/js/bootstrap.min.js', false, null, true  );
			wp_enqueue_script( 'bootstrap-min' );

			wp_register_script( 'bootstrap-accessibility', MAERA_BOOTSTRAP_SHELL_URL . '/assets/js/bootstrap-accessibility.min.js', false, null, true  );
			wp_enqueue_script( 'bootstrap-accessibility' );

			wp_register_style( 'bootstrap-accessibility', MAERA_BOOTSTRAP_SHELL_URL . '/assets/css/bootstrap-accessibility.css', false, null, true );
			wp_enqueue_style( 'bootstrap-accessibility' );

			wp_enqueue_style( 'dashicons' );

		}

		/**
		 * Implement the custom js field output and place it to the footer.
		 */
		function custom_js() {

			$js = get_theme_mod( 'js', '' );

			if ( ! empty( $js ) ) {
				echo '<script>' . $js . '</script>';
			}

		}


		/**
		 * Timber extras.
		 */
		function timber_extras( $data ) {

			// Get the layout we're using (sidebar arrangement).
			$layout = apply_filters( 'maera/layout/modifier', get_theme_mod( 'layout', 1 ) );

			// get secondary sidebar
			$sidebar_secondary = Timber::get_widgets( 'sidebar_secondary' );
			$data['sidebar']['secondary'] = apply_filters( 'maera/sidebar/secondary', $sidebar_secondary );

			if ( 0 == $layout ) {

				$data['sidebar']['primary']   = null;
				$data['sidebar']['secondary'] = null;

				// Add a filter for the layout.
				add_filter( 'maera/layout/modifier', 'maera_return_0' );

			} elseif ( $layout < 3 ) {
				$data['sidebar']['secondary'] = null;
			}

			$comment_form_args = array(
				'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun', 'maera_bootstrap' ) . '</label><textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
				'id_submit'     => 'comment-submit',
			);

			$data['content_width'] = Maera_Bootstrap_Structure::content_width_px();
			$data['post_meta'] = Maera_Bootstrap_Structure::meta_elements();

			$data['teaser_mode'] = get_theme_mod( 'blog_post_mode', 'excerpt' );

			$data['comment_form'] = TimberHelper::get_comment_form( null, $comment_form_args );

			return $data;
		}


		/**
		 * Excerpt length
		 */
		function excerpt_length() {

			return get_theme_mod( 'post_excerpt_length', 55 );

		}


		/**
		 * The "more" text
		 */
		function excerpt_more( $more, $post_id = 0 ) {

			$continue_text = get_theme_mod( 'post_excerpt_link_text', 'Continued' );
			return ' &hellip; <a href="' . get_permalink( $post_id ) . '">' . $continue_text . '</a>';

		}

	}

}
