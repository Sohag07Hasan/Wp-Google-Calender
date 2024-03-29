<?php
/*
 * creates metabox
 */

session_start();

class Gc_Integration{
	
	static $client;
	static $calender;
	static $tracker = 1;


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
		
		//saving the event to the database
		//add_action('save_the_gc_event', array(get_class(), 'save_event'), 10, 3);
	}
	
	
	/*
	 * function with custom hook to save the event id and calender id to the database
	 */
	static function save_event_info($event, $post, $calender){
		update_post_meta($post->ID, 'gc_enabled', '1');
		update_post_meta($post->ID, 'event_info', array('cal_id'=>$calender, 'event_id'=>$event['id']));
	}




	/*
	 * saving post data
	 */
	static function save_post($post_ID, $post){
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		self::push_to_gc($post_ID, $post);
			
	}
	
	
	/*
	 * push post to the google calender
	 */
	static function push_to_gc($post_ID, $post){
		if($_POST['gc_enabled'] == '1') :
			if(self::$tracker > 1) return;
			
			$start_time = trim($_POST['gc-event-date_start']) . ' ' . trim($_POST['gc-event-time_start']);
			$end_time = trim($_POST['gc-event-date_end']) . ' ' . trim($_POST['gc-event-time_end']);
			
			$title = self::sanitized_title(trim($_POST['gc-event-title']), $post->post_title);
			$des = trim($_POST['gc-event-description']);
			$event_start = self::sanitized_datetime($start_time);
			
			$event_end = self::sanitized_datetime($end_time);
			
			
			$event = self::set_event($title, $des, $event_start, $event_end, $_POST['gc_id']);
			
			//do_action('save_the_gc_event', $event, $post, $_POST['gc_id']);
			self:: save_event_info($event, $post, $_POST['gc_id']);
			
			self::$tracker += 1;			
		endif;
	}
	
	/*
	 * set an event to the googel calender and return the event for further use
	 */
	static function set_event($title, $des, $event_start, $event_end, $gc_id){
		
		self::set_client_calender();		
		
		$event = new Event();
		$event->setSummary($title);
		if(strlen($des)>3){
			$event->setDescription($des);
		}
		$start = new EventDateTime();
		$start->setDateTime($event_start);
		//$start->setTimeZone('Asia/Dhaka');
		
		$end = new EventDateTime();
		
		
		$end->setDateTime($event_end);
		//$end->setTimeZone('Asia/Dhaka');
		
		$event->setStart($start);
		$event->setEnd($end);
		
		
		/*
		$attendee1 = new EventAttendee();
		$attendee1->setEmail('hyde.sohag@gmail.com');

		$attendees = array($attendee1);
		$event->attendees = $attendees;
		
		$createdEvent = self::$calender->events->insert($gc_id, $event);
		*/
		
		if(self::is_new()){
			
			$createdEvent = self::$calender->events->insert($gc_id, $event);
		}
		else{
			//$event->setId($_POST['event_prev_id']);
			//$createdEvent = self::$calender->events->update($gc_id, $_POST['event_prev_id'], $event);
			$createdEvent = self::$calender->events->patch($gc_id, $_POST['event_prev_id'], $event);
		}
		
		$_SESSION['gc_token'] = self::$client->getAccessToken();
		
		return $createdEvent;
	}
	
	/*
	 * is update or new
	 */
	static function is_new(){
		if($_POST['gc_update'] == 'Y'){
			if($_POST['gc_prev_id'] == $_POST['gc_id']){
				return false;
			}
		}
		return true;
	}



	/*
	 * some sanitizing fuction
	 */
	static function sanitized_title($et='', $pt=''){
		if(strlen($et) < 2) return $pt;
		return $et;
	}
	
	
	/*
	 * format the date and time to the google calender format
	 */
	static function sanitized_datetime($date_time){
		if(strlen($date_time) < 5){
			$date_time = trim($_POST['gc-event-date_start']) . ' ' . trim($_POST['gc-event-time_start']);
			$dt = new DateTime($date_time, new DateTimeZone(self::get_timezone()));
			$timestamp = $dt->getTimestamp() + 3600;
			$dt->setTimestamp($timestamp);
		}
		else{		
			$dt = new DateTime($date_time, new DateTimeZone(self::get_timezone()));
		}
		
		//$timestamp -= self::get_gmt_offset();
		
		return $dt->format('c');
				
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
			
			$url = get_option('site_url');
									
			if(isset($_SESSION['gc_redirect_url'])){
				if(is_ssl()){
					$url = 'https://' . $_SESSION['gc_redirect_url'];
				}
				else{
					$url = 'http://' . $_SESSION['gc_redirect_url'];
				}
				
			}
			wp_redirect($url);
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
			update_option('gc_timezone', trim($_POST['gc_timezone']));
		}
		
		$gc = get_option('gc_app_info');
		$timezone = get_option('gc_timezone');
		
		include dirname(__FILE__) . '/includes/options-page.php';
	}
	
	
	/*
	 * returns the timezone
	 */
	static function get_timezone(){
		return get_option('gc_timezone');				
		
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
		$gc_event = array();
		
		if (isset($_SESSION['gc_token'])) {
			self::$client->setAccessToken($_SESSION['gc_token']);
		}
		
		if (self::$client->getAccessToken()) {
			$calList = self::$calender->calendarList->listCalendarList();			
			$enabled = get_post_meta($post->ID, 'gc_enabled', true);
			if($enabled){
				$event_meta = get_post_meta($post->ID, 'event_info', true);
				$gc_event = self::$calender->events->get($event_meta['cal_id'], $event_meta['event_id']);
				//var_dump($gc_event);
			}			
			include dirname(__FILE__) . '/metabox/metabox.php';
			$_SESSION['gc_token'] = self::$client->getAccessToken();
		}
		else{
			$_SESSION['gc_redirect_url'] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
			 $authUrl = self::$client->createAuthUrl();
			 print "<a class='login' href='$authUrl'>Connect Me!</a>";
		}
	}
	
	
	/*
	 * return clanederservice object ana api client
	 */
	static function set_client_calender(){
		
		if(is_object(self::$calender)) return;
		$url = get_option('siteurl') . '/wp-admin/google-calender/redirect=yes';
					
		//including the api classes
		if(!class_exists('apiClient')) :
			include GCALENDERDIR . '/gc-api/src/apiClient.php';
			include GCALENDERDIR . '/gc-api/src/contrib/apiCalendarService.php';
		endif;
		$gc_info = self::get_calender_access_info();
		
		self::$client = new apiClient();
		self::$client->setApplicationName(trim($gc_info['app_name']));

		// Visit https://code.google.com/apis/console?api=calendar to generate your
		// client id, client secret, and to register your redirect uri.
		self::$client->setClientId(trim($gc_info['client_id']));
		self::$client->setClientSecret(trim($gc_info['client_secret']));
		self::$client->setRedirectUri($url);
		self::$client->setDeveloperKey(trim($gc_info['api_key']));
		
		self::$calender = new apiCalendarService(self::$client);
		
		if (isset($_SESSION['gc_token'])) {
			self::$client->setAccessToken($_SESSION['gc_token']);
		}
	}
	
	/*
	 * google calelnder format to normal format 
	 */
	static function get_normalized_date($rfc){
		if(empty($rfc)) return '';
		
		$dt = new DateTime($rfc, new DateTimeZone(self::get_timezone()));
		//$rfc = strtotime($rfc) + self::get_gmt_offset();
		return $dt->format('m/d/Y');
	}
	
	static function get_normalized_time($rfc){
		if(empty($rfc)) return '';
		
		$dt = new DateTime($rfc, new DateTimeZone(self::get_timezone()));
		//$rfc = strtotime($rfc) + self::get_gmt_offset();
		return $dt->format('h:i A');
	}
	
	
	
	/*
	 * get_timezones option
	 */
	static function get_timezone_options($selected){
		$option = '';
		$zones = DateTimeZone::listIdentifiers();
		foreach($zones as $zone){
			$option .= '<option ' . selected($zone, $selected) . ' value="' . $zone . '">' . $zone . '</option>';
		}
		
		return $option;
	}
}
