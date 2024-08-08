<?php
/**
 * Main plugin class file.
 *
 * @package WordPress Plugin Template/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class WordPress_Plugin_Template {

	/**
	 * The single instance of WordPress_Plugin_Template.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0.0
	 */
	private static $_instance = null; //phpcs:ignore

	/**
	 * Local instance of WordPress_Plugin_Template_Admin_API
	 *
	 * @var WordPress_Plugin_Template_Admin_API|null
	 */
	public $admin = null;

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version; //phpcs:ignore

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token; //phpcs:ignore

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for JavaScripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor funtion.
	 *
	 * @param string $file File constructor.
	 * @param string $version Plugin version.
	 */
	public function __construct( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token   = 'wordpress_plugin_template';
		$this->_name   = 'WordPress Plugin Template';
		$this->_description   = 'Lorem Ipsum dolor sit amet.';

		// Load plugin environment variables.
		$this->file       = $file;
		$this->dir        = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->dir_url = esc_url( trailingslashit( plugins_url( '/', $this->file ) ) );
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions.
		if ( is_admin() ) {
			$this->admin = new WordPress_Plugin_Template_Admin_API($this);
		}

		// Handle localisation.
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );


		add_action( 'init', array( $this, 'wordpress_plugin_template_init'), 0 );
	} // End __construct ()

	/**
	 * Register post type function.
	 *
	 * @param string $post_type Post Type.
	 * @param string $plural Plural Label.
	 * @param string $single Single Label.
	 * @param string $description Description.
	 * @param array  $options Options array.
	 *
	 * @return bool|string|WordPress_Plugin_Template_Post_Type
	 */
	public function register_post_type( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) {
			return false;
		}

		$post_type = new WordPress_Plugin_Template_Post_Type( $this, $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $plural Plural Label.
	 * @param string $single Single Label.
	 * @param array  $post_types Post types to register this taxonomy for.
	 * @param array  $taxonomy_args Taxonomy arguments.
	 *
	 * @return bool|string|WordPress_Plugin_Template_Taxonomy
	 */
	public function register_taxonomy( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) {
			return false;
		}

		$taxonomy = new WordPress_Plugin_Template_Taxonomy( $this, $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}


	/**
	 * Wrapper function to register a new meta box.
	 *
	 * @param array  $meta_box_args Meta box arguments.
	 *
	 * @return bool|string|WordPress_Plugin_Template_Meta_Box
	 */
	public function register_meta_box( $meta_box_args = array() ) {

		if ( ! $meta_box_args ) {
			return false;
		}

		$metabox = new WordPress_Plugin_Template_Meta_Box( $this, $meta_box_args );

		return $metabox;
	}

	/**
	 * Load frontend CSS.
	 *
	 * @access  public
	 * @return void
	 * @since   1.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Admin enqueue style.
	 *
	 * @param string $hook Hook parameter.
	 *
	 * @return void
	 */
	public function admin_enqueue_styles( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 *
	 * @access  public
	 *
	 * @param string $hook Hook parameter.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'wordpress-plugin-template', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = 'wordpress-plugin-template';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main WordPress_Plugin_Template Instance
	 *
	 * Ensures only one instance of WordPress_Plugin_Template is loaded or can be loaded.
	 *
	 * @param string $file File instance.
	 * @param string $version Version parameter.
	 *
	 * @return Object WordPress_Plugin_Template instance
	 * @see WordPress_Plugin_Template()
	 * @since 1.0.0
	 * @static
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
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
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cloning of WordPress_Plugin_Template is forbidden' ) ), esc_attr( $this->_version ) );

	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Unserializing instances of WordPress_Plugin_Template is forbidden' ) ), esc_attr( $this->_version ) );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function install() {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	private function _log_version_number() { //phpcs:ignore
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * Installation. Runs on init.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function wordpress_plugin_template_init() {

		WordPress_Plugin_Template()->register_post_type( 'lbr', __( 'Baterias recargables', 'wordpress-plugin-template'), __( 'Bateria recargable', 'wordpress-plugin-template'), '', array('menu_position'         => 5,
			'menu_icon'             => 'dashicons-star-half',
    		'supports' => array('title', 'thumbnail'),));

		if (is_admin()){
	  		$prefix = 'wpt_';


	  		$prefix = 'rhv_';
	  		$metaboxconfig = array(
			    'id'             => 'demo_meta_box1',          // meta box id, unique per meta box
			    'title'          => __('Basic Meta Box fields', 'wordpress-plugin-template'),          // meta box title
			    'pages'          => array('lbr'),      // post types, accept custom post types as well, default is array('post'); optional
			    'pageTemplate'   => 'default',
			    'context'        => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
			    'priority'       => 'high',            // order of meta box: high (default), low; optional
			    'fields'         => array(),            // list of meta fields (can be added by field arrays)
			    'local_images'   => false,          // Use local or hosted images (meta box images for add/remove)
			    'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
			);
	  		$metabox = WordPress_Plugin_Template()->register_meta_box($metaboxconfig);
				  //text field
			$metabox->addText($prefix.'text_field_id',array('name'=> 'My Text '));
			//textarea field
			$metabox->addTextarea($prefix.'textarea_field_id',array('name'=> 'My Textarea '));
			//checkbox field
			$metabox->addCheckbox($prefix.'checkbox_field_id',array('name'=> 'My Checkbox '));
			//select field
			$metabox->addSelect($prefix.'select_field_id',array('name'=> 'My select ', 'std'=> array('selectkey2')),array('selectkey1'=>'Select Value1','selectkey2'=>'Select Value2'));
			//radio field
			$metabox->addRadio($prefix.'radio_field_id',array('name'=> 'My Radio Filed', 'std'=> array('radionkey2')),array('radiokey1'=>'Radio Value1','radiokey2'=>'Radio Value2'));
			//Image field
			$metabox->addImage($prefix.'image_field_id',array('name'=> 'My Image '));
			//file upload field
			$metabox->addFile($prefix.'file_field_id',array('name'=> 'My File'));
			//file upload field with type limitation
			$metabox->addFile($prefix.'file_pdf_field_id',array('name'=> 'My File limited to PDF Only','ext' =>'pdf','mime_type' => 'application/pdf'));

			//Finish Meta Box Declaration 
			$metabox->Finish();
			  $config2 = array(
			    'id'             => 'demo_meta_box2',          // meta box id, unique per meta box
			    'title'          => 'Advanced Meta Box fields',          // meta box title
			    'pages'          => array('lbr'),      // post types, accept custom post types as well, default is array('post'); optional
			    'context'        => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
			    'priority'       => 'high',            // order of meta box: high (default), low; optional
			    'fields'         => array(),            // list of meta fields (can be added by field arrays)
			    'local_images'   => false,          // Use local or hosted images (meta box images for add/remove)
			    'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
			  );
			  $my_meta2 =  WordPress_Plugin_Template()->register_meta_box($config2);
			  //add checkboxes list 
			  $my_meta2->addCheckboxList($prefix.'CheckboxList_field_id',array('name'=> 'My checkbox list ', 'std'=> array('checkboxkey2')),array('checkboxkey1'=>'checkbox Value1','checkboxkey2'=>'checkbox Value2'));
			  //date field
			  $my_meta2->addDate($prefix.'date_field_id',array('name'=> 'My Date '));
			  //Time field
			  $my_meta2->addTime($prefix.'time_field_id',array('name'=> 'My Time '));
			  //Color field
			  $my_meta2->addColor($prefix.'color_field_id',array('name'=> 'My Color '));
			  //wysiwyg field
			  $my_meta2->addWysiwyg($prefix.'wysiwyg_field_id',array('name'=> 'My wysiwyg Editor '));
			  //taxonomy field
			  $my_meta2->addTaxonomy($prefix.'taxonomy_field_id',array('name'=> 'My Taxonomy '),array('taxonomy' => 'category'));
			  //posts field
			  $my_meta2->addPosts($prefix.'posts_field_id',array('name'=> 'My Posts '),array('post_type' => 'post'));
			  //add Code editor field
			  $my_meta2->addCode($prefix.'code_field_id',array(
			    'name'   => 'Code editor Field', 
			    'syntax' => 'php',
			    'theme'  => 'light'
			  ));
			  $repeater_fields[] = $my_meta2->addText($prefix.'re_text_field_id',array('name'=> 'My Text '),true);
			  $repeater_fields[] = $my_meta2->addTextarea($prefix.'re_textarea_field_id',array('name'=> 'My Textarea '),true);
			  $repeater_fields[] = $my_meta2->addCheckbox($prefix.'re_checkbox_field_id',array('name'=> 'My Checkbox '),true);
			  $repeater_fields[] = $my_meta2->addImage($prefix.'image_field_id',array('name'=> 'My Image '),true);
			  //repeater block
			  $my_meta2->addRepeaterBlock($prefix.'re_',array(
			    'inline'   => true, 
			    'name'     => 'This is a Repeater Block',
			    'fields'   => $repeater_fields, 
			    'sortable' => true
			  ));
			  $Conditinal_fields[] = $my_meta2->addText($prefix.'con_text_field_id',array('name'=> 'My Text '),true);
			  $Conditinal_fields[] = $my_meta2->addTextarea($prefix.'con_textarea_field_id',array('name'=> 'My Textarea '),true);
			  $Conditinal_fields[] = $my_meta2->addCheckbox($prefix.'con_checkbox_field_id',array('name'=> 'My Checkbox '),true);
			  $Conditinal_fields[] = $my_meta2->addColor($prefix.'con_color_field_id',array('name'=> 'My color '),true);
			  //repeater block
			  $my_meta2->addCondition('conditinal_fields',
			      array(
			        'name'   => __('Enable conditinal fields? ','mmb'),
			        'desc'   => __('<small>Turn ON if you want to enable the <strong>conditinal fields</strong>.</small>','mmb'),
			        'fields' => $Conditinal_fields,
			        'inline'    => true,
			        'std'    => false
			      ));
			  //Finish Meta Box Declaration 
			  $my_meta2->Finish();
			  
			  
			  $prefix = "_groupped_";
			  $config3 = array(
			    'id'             => 'demo_meta_box3',          // meta box id, unique per meta box
			    'title'          => 'Groupped Meta Box fields',          // meta box title
			    'pages'          => array('lbr'),      // post types, accept custom post types as well, default is array('post'); optional
			    'context'        => 'normal',            // where the meta box appear: normal (default), advanced, side; optional
			    'priority'       => 'low',            // order of meta box: high (default), low; optional
			    'fields'         => array(),            // list of meta fields (can be added by field arrays)
			    'local_images'   => false,          // Use local or hosted images (meta box images for add/remove)
			    'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
			  );
			  $my_meta3 =  WordPress_Plugin_Template()->register_meta_box($config3);
			  //first field of the group has 'group' => 'start' and last field has 'group' => 'end'
			  
			  //text field
			  $my_meta3->addText($prefix.'text_field_id',array('name'=> 'My Text ','group' => 'start','grouptitle' => 'PRUEBA'));
			  //textarea field
			  $my_meta3->addTextarea($prefix.'textarea_field_id',array('name'=> 'My Textarea '));
			  //checkbox field
			  $my_meta3->addCheckbox($prefix.'checkbox_field_id',array('name'=> 'My Checkbox '));
			  //select field
			  $my_meta3->addSelect($prefix.'select_field_id',array('name'=> 'My select ', 'std'=> array('selectkey2')),array('selectkey1'=>'Select Value1','selectkey2'=>'Select Value2'));
			  //radio field
			  $my_meta3->addRadio($prefix.'radio_field_id',array('name'=> 'My Radio Filed', 'std'=> array('radionkey2'),'group' => 'end'),array('radiokey1'=>'Radio Value1','radiokey2'=>'Radio Value2'));
			  //Finish Meta Box Declaration 
			  $my_meta3->Finish();

		}
	}

}
