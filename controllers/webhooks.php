<?php
// Webhooks / Zapier management and integration
class NamasteLMSWebhooks {
	public static function manage() {
		global $wpdb;
		$action = empty($_GET['action']) ? 'list' : $_GET['action'];
		
		// select courses
		$_course = new NamasteLMSCourseModel();
		$courses = $_course->select();
				
		switch($action) {
			case 'add':
				if(!empty($_POST['ok']) and check_admin_referer('namaste_webhooks')) {
					$payload_config = self :: payload_config();
					$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_WEBHOOKS." SET item_id=%d, item_type=%s, hook_url = %s, action=%s, payload_config=%s",
					intval($_POST['item_id']), 'course', esc_url_raw($_POST['hook_url']), sanitize_text_field($_POST['action']), serialize($payload_config) ));
					
					namaste_redirect("admin.php?page=namaste_webhooks");
				}
				
				require(NAMASTE_PATH."/views/webhook.html.php");
			break;
			
			case 'edit':
				if(!empty($_POST['test'])	and check_admin_referer('namaste_webhooks')) {
					list($data, $result) = self :: test($_GET['id']);
				}		
			
				if(!empty($_POST['ok']) and check_admin_referer('namaste_webhooks')) {
					$payload_config = self :: payload_config();
					$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_WEBHOOKS." SET item_id=%d, hook_url = %s, action=%s, payload_config=%s WHERE ID=%d",
					intval($_POST['item_id']), esc_url_raw($_POST['hook_url']), sanitize_text_field($_POST['action']), serialize($payload_config), intval($_GET['id'])) );
				}
				
				// select hook
				$hook = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_WEBHOOKS." WHERE id=%d", intval($_GET['id'])));
				$payload_config = unserialize(stripslashes($hook->payload_config));
								
				require(NAMASTE_PATH."/views/webhook.html.php");
			break;
			
			case 'list':
			default: 
				if(!empty($_GET['delete']) and wp_verify_nonce($_GET['namaste_hook_nonce'], 'delete_hook')) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_WEBHOOKS." WHERE id=%d", intval($_GET['id'])));
					namaste_redirect("admin.php?page=namaste_webhooks");
				}			
			
				// select hooks join grades
				$hooks = $wpdb->get_results("SELECT tH.id as id, tH.hook_url as hook_url, 
					tC.post_title as course, tH.action as action
					FROM ".NAMASTE_WEBHOOKS." tH JOIN {$wpdb->posts} tC ON tH.item_id = tC.ID					
					ORDER BY tH.id");		
					
				// depending if there are hooks, set the option
				update_option('namaste_webhooks', count($hooks));		
			
				require(NAMASTE_PATH."/views/webhooks.html.php");
			break;
		}
	} // end manage
	
	// called on subscribe or unsibscribe action, figures out whether any webhooks should be sent
	public static function dispatch($item_id, $item_type, $user_id, $action) {
		global $wpdb;
		
		// to avoid unnecessary queries this option is set to 1 only if there are webhooks in the system
		if(get_option('namaste_webhooks') <= 0) return false;
		
		$user = get_userdata($user_id);
		if(empty($user->ID)) return false;
				
		$hooks = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_WEBHOOKS." 
			WHERE item_id=%d AND item_type=%s AND action=%s ORDER BY id", $item_id, $item_type, $action));
			
		foreach($hooks as $hook) {
			// prepare data
			$config = unserialize(stripslashes($hook->payload_config));
			$data = [];
			
			foreach($config as $key => $setting) {				
				if(empty($setting) or !is_array($setting)) continue;
				
				// all keys are predefined. $setting['name'] is the customizable param name
				// $setting['value'] is empty and should come from $taking data except on the custom pre-filled keys
				switch($key) {
					case 'email':
						$data[$setting['name']] = $user->user_email;
					break;
					case 'user_login':
						$data[$setting['name']] = $user->user_login;
					break;	
					case 'display_name':
						$data[$setting['name']] = $user->display_name;
					break;				
					case 'custom_key1':
					case 'custom_key2':
					case 'custom_key3':
						$data[$setting['name']] = stripslashes($setting['value']);
					break;
				} // end switch
			} // end foreach config param
			
			self :: send($hook->hook_url, $data);			
			
		} // end foreach hook	
	} // end dispatch

	
	// send webhook
	public static function send($url, $data) {
		$args = array(
	        'headers' => array(
	            'Content-Type' => 'application/json',
	        ),
	        'body' => json_encode( $data )
	    );

	    //$return = wp_remote_post( $url, $args );
	    
	    // probably make includings headers optional?
	    $headers = ['Content-Type' => 'application/json',];
	    
	    $args = ['body' => $data];
	    $return = wp_remote_post( $url, $args);
		if(is_wp_error($return)) {
			$error_string = $return->get_error_message();
   		echo '<div id="message" class="error"><p>' . sprintf(__('Webhook error: %s', 'namaste'), $error_string) . '</p></div>';
   		return false;
		}
	   return true;
	} // end send

	// test a hook	
	public static function test($hook_id) {
		global $wpdb;
		
		$hook = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_WEBHOOKS." WHERE id=%d", intval($hook_id)));
		$config = unserialize(stripslashes($hook->payload_config));
		$data = [];
		
		foreach($config as $key => $setting) {				
				if(empty($setting) or !is_array($setting)) continue;
				
				// all keys are predefined. $setting['name'] is the customizable param name
				// $setting['value'] is empty and should come from $taking data except on the custom pre-filled keys
				switch($key) {
					case 'email':
						$data[$setting['name']] = get_option('admin_email');
					break;
					case 'user_login':
						$data[$setting['user_login']] = $user->user_login;
					break;	
					case 'display_name':
						$data[$setting['display_name']] = $user->display_name;
					break;						
					case 'custom_key1':
					case 'custom_key2':
					case 'custom_key3':
						$data[$setting['name']] = stripslashes($setting['value']);
					break;
				} // end switch
			} // end foreach config param
			
			$args = array(

	        'headers' => array(
	            'Content-Type' => 'application/json',
	        ),
	        'body' => json_encode( $data )
	    );
			
		  $return = wp_remote_post( $hook->hook_url, $args );
		  
		  return [$data, $return];
	} // end test
	
	// add hook actions
	public static function add_actions() {
		$enroll_function = function($student_id, $course_id, $status) {			  
			   if($status != 'enrolled') return false;
				self :: dispatch($course_id, 'course', $student_id, 'enroll'); 
			};
			
		add_action('namaste_enrolled_course', $enroll_function, 10, 3);
		add_action('namaste_admin_enrolled_course', $enroll_function, 10, 3);
			
		add_action('namaste_completed_course', function($student_id, $course_id) {			   
				self :: dispatch($course_id, 'course', $student_id, 'complete'); 
			}, 10, 2);	
	} // end actions
	
	// helper to prepare the payload_config array
	private static function payload_config() {
		$payload_config = [];
		if(!empty($_POST['email_name']))	$payload_config['email'] = ['name' => sanitize_text_field($_POST['email_name'])];
		if(!empty($_POST['user_login_name']))	$payload_config['user_login'] = ['name' => sanitize_text_field($_POST['user_login_name'])];		
		if(!empty($_POST['display_name_name']))	$payload_config['display_name'] = ['name' => sanitize_text_field($_POST['display_name_name'])];
		if(!empty($_POST['custom_key1_name']))	$payload_config['custom_key1'] = ['name' => sanitize_text_field($_POST['custom_key1_name']), "value" => sanitize_text_field($_POST['custom_key1_value'])];
		if(!empty($_POST['custom_key2_name']))	$payload_config['custom_key2'] = ['name' => sanitize_text_field($_POST['custom_key2_name']), "value" => sanitize_text_field($_POST['custom_key2_value'])];
		if(!empty($_POST['custom_key3_name']))	$payload_config['custom_key3'] = ['name' => sanitize_text_field($_POST['custom_key3_name']), "value" => sanitize_text_field($_POST['custom_key3_value'])];
		
		return $payload_config;
	} // end payload_config
}