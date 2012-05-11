<?php
/*
 * creates metabox
 */

session_start();

class Gc_Integration{
	
	static $client;
	static $calender;


	/*
	 * contains all the hooks
	 */
	static function init(){
		add_action( 'add_meta_boxes', array(get_class(), 'meta_boxes' ));
		add_action('admin_menu', array(get_class(), 'admin_menu_gc'));
		add_action('init', array(get_class(), 'initialize_gc'));
		add_action('admin_enqueue_scripts', array(get_class(), 'js_add'));
		//add_action('admin_enqueue_style', array(get_class(), 'css_add'));
		
		//saving calender data in wp and 
		add_action('save_post', array(get_class(), 'save_post'), 10, 2);
	}
	
	/*
	 * saving post data
	 */
	static function save_post($post_ID, $post){
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		var_dump($post);
		$cal = $_POST['enable_calender_event'];
		$title = $_POST['gc-event-title'];
		$des = $_POST['gc-event-description'];
		$date = $_POST['gc-event-date'];
		$time = $_POST['gc-event-time'];
		
	}
	
	
	/*
	 * css add
	 */
	static function css_add(){
		//date picker
		wp_register_style('query-ui-datepicker-addon_css', GCALENDERURL . '/date-time-picker/css/ui-lightness/jquery-ui-1.8.20.custom.css');
		wp_enqueue_style('query-ui-datepicker-addon_css');	
		
		//time picker
		wp_register_style('query-ui-timepicker-addon_css', GCALENDERURL . '/date-time-picker/jquery-ui-timepicker-addon.css');
		wp_enqueue_style('query-ui-timepicker-addon_css');	
		
	}




	/*
	 * js addition
	 */
	static function js_add(){
		wp_enqueue_script('jquery');
		
		//date picker
		wp_register_script('jquery-ui-datepicker-addon_js', GCALENDERURL . '/date-time-picker/js/jquery-ui-1.8.20.custom.min.js');
		wp_enqueue_script('jquery-ui-datepicker-addon_js');
		
		//time picker
		wp_register_script('jquery-ui-timepicker-addon_js', GCALENDERURL . '/date-time-picker/jquery-ui-timepicker-addon.js');
		wp_enqueue_script('jquery-ui-timepicker-addon_js');
		
				
		
		
		self :: css_add();
	}
	
	
	/*
	 * initialize the GC 
	 */
	static function initialize_gc(){		
		//authenticating
		if (isset($_GET['code'])) :
			
			self::set_client_calender();
			
			self::$client->authenticate();
			$_SESSION['gc_token'] = self::$client->getAccessToken();
			include ABSPATH . '/wp-includes/pluggable.php';
			wp_redirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
			exit;
		endif;
		
		
	}
	
	
	
	/*
	 * settigs page
	 */
	static function admin_menu_gc(){
		add_options_page('gc setting page', 'GClaneder', 'manage_options', 'gc_options_page', array(get_class(), 'options_page_content'));
	}
	
	/*
	 * Options page content
	 */
	static function options_page_content(){
		if($_POST['gc_saved'] == 'Y'){			
			$gc_data = array(
				'app_name' => trim($_POST['gc_app_name']),
				'client_id' => trim($_POST['gc_client_id']),
				'client_secret' =>  trim($_POST['gc_client_secret']),
				'api_key' => trim($_POST['gc_api_key'])
			);
			
			update_option('gc_app_info', $gc_data);
		}
		
		$gc = get_option('gc_app_info');
		
		include dirname(__FILE__) . '/includes/options-page.php';
	}
	
	
	/*
	 * get calender access options
	 */
	static function get_calender_access_info(){
		return get_option('gc_app_info');
	}
	



	/*
	 * add meta boxes
	 */
	static function meta_boxes(){
		$post_types=get_post_types();
		foreach($post_types as $post_type){
			add_meta_box( 'gc_metabox', 'Google Calender', array(get_class(), 'the_box'), $post_type, 'advanced', 'high');
		}
	}
	
	
	/*
	 * metabox content
	 */
	static function the_box(){
		
		self::set_client_calender();
		global $post;
		
		if (isset($_SESSION['gc_token'])) {
			self::$client->setAccessToken($_SESSION['gc_token']);
		}
		
		if (self::$client->getAccessToken()) {
			$calList = self::$calender->calendarList->listCalendarList();
			
			include dirname(__FILE__) . '/metabox/metabox.php';
			$_SESSION['gc_token'] = self::$client->getAccessToken();
		}
		else{
			 $authUrl = self::$client->createAuthUrl();
			 print "<a class='login' href='$authUrl'>Connect Me!</a>";
		}
	}
	
	
	/*
	 * return clanederservice object ana api client
	 */
	static function set_client_calender(){
		
		//if the object is initiated earlier do nothing
		//if(isset($_SESSION['gc_token'])) return;
		
		//including the api classes
		include GCALENDERDIR . '/gc-api/src/apiClient.php';
		include GCALENDERDIR . '/gc-api/src/contrib/apiCalendarService.php';
		$gc_info = self::get_calender_access_info();
		
		self::$client = new apiClient();
		self::$client->setApplicationName(trim($gc_info['app_name']));

		// Visit https://code.google.com/apis/console?api=calendar to generate your
		// client id, client secret, and to register your redirect uri.
		self::$client->setClientId(trim($gc_info['client_id']));
		self::$client->setClientSecret(trim($gc_info['client_secret']));
		self::$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
		self::$client->setDeveloperKey(trim($gc_info['api_key']));
		
		self::$calender = new apiCalendarService(self::$client);
	}
}
