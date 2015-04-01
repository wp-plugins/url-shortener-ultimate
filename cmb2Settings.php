<?php
/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class url_shortener_ultimate_Admin {

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'usu_options';
	
	private $urls_key = 'usu_urls';

	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'usu_option_metabox';
	
	private $urls_metabox_id = 'usu_urls_metabox';

	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	protected $option_metabox = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'Url Shortener Ultimate', 'url_shortener_ultimate' );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_init', array( $this, 'add_options_page_metabox' ) );
		add_action( 'cmb2_init', array( $this, 'add_urls_page_metabox' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars'));
		add_action( 'template_redirect', array( $this, 'save_url'));
		add_action( 'template_redirect', array( $this, 'delete_url_action'));
		add_action( 'template_redirect', array( $this, 'redirect_to_destination'));
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_save_url_script' ) );
		add_filter( 'cmb2_get_metabox_form_format', array($this, 'no_button_modify_cmb2_metabox_form_format'), 10, 3 );
	}


	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		add_submenu_page( $this->key, "Urls", "Urls", 'manage_options', "urls", array( $this, 'urls_page_display' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2_options_page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<a href="http://www.thinklandingpages.com/url-shortener-ultimate">Learn how to use the Url Shortener Ultimate</a><p>
			<a href="<?php echo admin_url('admin.php?page=urls'); ?>">Click here to create a url.</a><br>
			<?php //cmb2_metabox_form( $this->metabox_id, $this->key ); 
			?>
		</div>
		<?php
	}
	
	public function urls_page_display() {
		?>
		<div class="wrap cmb2_options_page <?php echo $this->key; ?>">
			<h2>Urls</h2>
			<?php cmb2_metabox_form( $this->urls_metabox_id, $this->urls_key ); ?>
		</div>
		<?php
		$urls = $this->get_all_urls();
		include plugin_dir_path( __FILE__ ).'include/admin_display_urls.php';
	}
	
	
	function add_query_vars($vars){
	    $vars[] = "save-url";
	    $vars[] = "delete-url";
	    return $vars;
	}
	
	function redirect_to_destination(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'usu_url';
		$slug = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$destination = $wpdb->get_var($wpdb->prepare('SELECT destination FROM '.$table_name.' WHERE slug = %s',$slug ));
		if($destination){
			wp_redirect($destination);
			exit;
		}
	}
	
	function save_url(){
		global $wp_query;
			if(isset($wp_query->query_vars['save-url'])){
				if(current_user_can("manage_options")){
					$slug = $_POST['usu_slug'];
					$destination = $_POST['usu_destination_url'];
					$this->insert_url($destination, $slug);
					wp_redirect(admin_url('admin.php?page=urls'));
					exit;
					
				}
			}
	}
	
	public function insert_url($destination, $slug){
		global $wpdb;
		$table_name = $wpdb->prefix . 'usu_url';

		$wpdb->insert( 
			$table_name, 
			array( 
				'time_created' => current_time( 'mysql' ), 
				'destination' => $destination, 
				'slug' => '/'.$slug, 
			) 
		);
	}
	
	public function get_all_urls(){
		global $wpdb;
		$table_name = $wpdb->prefix . 'usu_url';
		$urls = $wpdb->get_results("SELECT * FROM $table_name");
		return $urls;
	}
	
	public function delete_url_action(){
		global $wp_query;
		if(isset($wp_query->query_vars['delete-url'])){
			if(current_user_can("manage_options")){
				$id = $_GET['id'];
				$this->delete_url($id);
				//echo 'saving url';
				wp_redirect(admin_url('admin.php?page=urls'));
				exit;
				
			}
		}
	}
	
	function delete_url($id){
		global $wpdb;
		$table_name = $wpdb->prefix . 'usu_url';
		$isDeleted = $wpdb->get_var($wpdb->prepare('DELETE FROM '.$table_name.' WHERE id = %d',$id ));
		return $isDeleted;
		
		
	}
	
	function enqueue_save_url_script(){
		wp_enqueue_script('usu-save-url', plugin_dir_url(__FILE__).'js/usuSaveUrl.js');
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 * @param  array $meta_boxes
	 * @return array $meta_boxes
	 */
	function add_options_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields

		$cmb->add_field( array(
			'name' => __( 'Test Text', 'url_shortener_ultimate' ),
			'desc' => __( 'field description (optional)', 'url_shortener_ultimate' ),
			'id'   => 'test_text',
			'type' => 'text',
			'default' => 'Default Text',
		) );

		$cmb->add_field( array(
			'name'    => __( 'Test Color Picker', 'url_shortener_ultimate' ),
			'desc'    => __( 'field description (optional)', 'url_shortener_ultimate' ),
			'id'      => 'test_colorpicker',
			'type'    => 'colorpicker',
			'default' => '#bada55',
		) );

	}
	
	
	function add_urls_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->urls_metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->urls_key, )
			),
		) );

		// Set our CMB2 fields

		$cmb->add_field( array(
			'name' => __( 'Destination Url', 'url_shortener_ultimate' ),
			'desc' => __( 'Where do you want the user sent to.', 'url_shortener_ultimate' ),
			'id'   => 'usu_destination_url',
			'type' => 'text',
			//'default' => 'Default Text',
			'before_row' => '<a href="http://www.thinklandingpages.com/url-shortener-ultimate">Learn how to use the Url Shortener Ultimate</a>',
		) );

		$cmb->add_field( array(
			'name'    => __( 'Slug', 'url_shortener_ultimate' ),
			'desc'    => __( 'Do not place a leading forward-slash.  The plugin will do that for you.', 'url_shortener_ultimate' ),
			'id'      => 'usu_slug',
			'type'    => 'text',
			//'default' => '#bada55',
			'after_row' =>'<a href="#" onclick="usuSaveUrl(\''.site_url().'\',\'#'.$this->urls_metabox_id.'\');">Save Url</a>',
		) );
	
	}
	
	function no_button_modify_cmb2_metabox_form_format( $form_format, $object_id, $cmb ) {
	
	    if ( $this->urls_key == $object_id && $this->urls_metabox_id == $cmb->cmb_id ) {
	        return '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<div class="submit-wrap"></div></form>';
	    }
	
	    return $form_format;
	}

	/**
	 * Defines the theme option metabox and field configuration
	 * @since  0.1.0
	 * @return array
	 */
	public function option_metabox() {
		return ;
	}

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  0.1.0
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'fields', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

// Get it started
$GLOBALS['url_shortener_ultimate_Admin'] = new url_shortener_ultimate_Admin();
$GLOBALS['url_shortener_ultimate_Admin']->hooks();

/**
 * Helper function to get/return the url_shortener_ultimate_Admin object
 * @since  0.1.0
 * @return url_shortener_ultimate_Admin object
 */
function url_shortener_ultimate_Admin() {
	global $url_shortener_ultimate_Admin;
	return $url_shortener_ultimate_Admin;
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
function url_shortener_ultimate_get_option( $key = '' ) {
	global $url_shortener_ultimate_Admin;
	return cmb2_get_option( $url_shortener_ultimate_Admin->key, $key );
	
}
