<?php
// manage and handle the fine-tuning of multi-user access
class NamasteLMSMultiUser {
	static function manage() {
		global $wpdb, $wp_roles;
		$roles = $wp_roles->roles;
		
		// this sets the setting of a selected role
		if(!empty($_POST['config_role']) and check_admin_referer('namaste_role_settings')) {
			$role_settings = unserialize(get_option('namaste_role_settings'));	
			
			$no_mycourses = empty($_POST['no_mycourses']) ? 0 : 1;
			
			// overwrite the settings for the selected role
			$role_settings[$_POST['role_key']] = array("homework_access" => sanitize_text_field($_POST['homework_access']), 
				"certificates_access" => sanitize_text_field($_POST['certificates_access']), "settings_access" => sanitize_text_field($_POST['settings_access']),
				"students_access" => sanitize_text_field($_POST['students_access']), 
				"gradebook_access" => sanitize_text_field($_POST['gradebook_access']), 
				"no_mycourses" => $no_mycourses, "mass_enroll_access" => sanitize_text_field($_POST['mass_enroll_access']),
				"help_access" => sanitize_text_field($_POST['help_access']), "plugins_access" => sanitize_text_field($_POST['plugins_access']),
				'reviews_access' => sanitize_text_field($_POST['reviews_access']));
				
			update_option('namaste_role_settings', serialize($role_settings));	
			do_action('namaste-role-settings-saved', $_POST['role_key']);
		} // end config_role
		
		$role_settings = unserialize(get_option('namaste_role_settings'));
		
		// get the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if($key == 'administrator') continue;
			if(!empty($r->capabilities['namaste_manage'])) $enabled_roles[] = $key;
		}
		
		if(@file_exists(get_stylesheet_directory().'/namaste/multiuser.html.php')) require get_stylesheet_directory().'/namaste/multiuser.html.php';
		else require NAMASTE_PATH."/views/multiuser.html.php";
	}
	
	// checks the access of the current user
	static function check_access($what, $noexit = false) {
		global $user_ID, $wp_roles;
		$role_settings = unserialize(get_option('namaste_role_settings'));
		$roles = $wp_roles->roles;
		// get all the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['namaste_manage'])) $enabled_roles[] = $key;
		}
				
		// admin can do everything
		if(current_user_can('administrator')) return 'all';		
		$user = new WP_User( $user_ID );
				
		$has_access = self :: item_access($what, $role_settings, $user, $enabled_roles);
		
		// if we are here, it means none of his roles had 'all'
		if($has_access) return $has_access;
		
		// when no access, die
		if($noexit) return false;
		else wp_die(__('You are not allowed to do this.', 'namaste'));
	}
	
	// small helper for the above function which can also be ran externally (from the add_menu action)
	// to avoid needless queries
	static function item_access($what, $role_settings, $user, $enabled_roles) {
		$has_access = false;
		foreach($user->roles as $role) {			
			if(!empty($role_settings[$role])) {
				// empty is also true because we have to keep the defaults
				if(empty($role_settings[$role][$what]) or $role_settings[$role][$what] == 'all') return 'all';
				elseif($role_settings[$role][$what] == 'own') $has_access = 'own';	
				elseif($role_settings[$role][$what] == 'view') $has_access = 'view';	
				// when no, we just leave $has_access as false			
			}
			elseif(in_array($role, $enabled_roles)) $has_access = 'all'; // role was not specified in fine-tune so we just use the default full access
		}
		return $has_access;
	} // end item_access
}