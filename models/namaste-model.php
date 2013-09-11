<?php
// main model containing general config and UI functions
class NamasteLMS {
   static function install() {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	
   	self::init();
	  
	  // enrollments to courses
   	if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_STUDENT_COURSES."'") != NAMASTE_STUDENT_COURSES) {        
			$sql = "CREATE TABLE `" . NAMASTE_STUDENT_COURSES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`course_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`user_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`status` VARCHAR(255) NOT NULL DEFAULT '',
					`enrollment_date` DATE NOT NULL DEFAULT '2000-01-01',			
					`completion_date` DATE NOT NULL DEFAULT '2000-01-01',
					`comments` TEXT NOT NULL
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
	  // assignments - let's not use custom post type for this
	  if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_HOMEWORKS."'") != NAMASTE_HOMEWORKS) {        
			$sql = "CREATE TABLE `" . NAMASTE_HOMEWORKS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`course_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`lesson_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`title` VARCHAR(255) NOT NULL DEFAULT '',
					`description` TEXT NOT NULL,
					`accept_files` TINYINT NOT NULL DEFAULT 0 /* zip only */
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  
	  // student - assignments relation
		if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_STUDENT_HOMEWORKS."'") != NAMASTE_STUDENT_HOMEWORKS) {        
			$sql = "CREATE TABLE `" . NAMASTE_STUDENT_HOMEWORKS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`homework_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`status` VARCHAR(255) NOT NULL DEFAULT '',
					`date_submitted` DATE NOT NULL DEFAULT '2000-01-01',
					`content` TEXT NOT NULL,
					`file` VARCHAR(255) NOT NULL DEFAULT ''
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
			  
	  // assignment notes (usually used as feedback from the teacher to the student. Student can't reply)
		if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_HOMEWORK_NOTES."'") != NAMASTE_HOMEWORK_NOTES) {        
			$sql = "CREATE TABLE `" . NAMASTE_HOMEWORK_NOTES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`homework_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`teacher_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`note` TEXT NOT NULL,
					`datetime` DATETIME NOT NULL DEFAULT '2000-01-01'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	  
	  // student to lessons relation - only save record if student has completed a lesson
		if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_STUDENT_LESSONS."'") != NAMASTE_STUDENT_LESSONS) {        
			$sql = "CREATE TABLE `" . NAMASTE_STUDENT_LESSONS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`lesson_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`status` INT UNSIGNED NOT NULL DEFAULT 0,
					`completion_date` TEXT NOT NULL
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_CERTIFICATES."'") != NAMASTE_CERTIFICATES) {        
			$sql = "CREATE TABLE `" . NAMASTE_CERTIFICATES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `course_ids` VARCHAR(255) NOT NULL DEFAULT '',
				  `title` VARCHAR(255) NOT NULL DEFAULT '',
				  `content` TEXT NOT NULL
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  
	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_STUDENT_CERTIFICATES."'") != NAMASTE_STUDENT_CERTIFICATES) {        
			$sql = "CREATE TABLE `" . NAMASTE_STUDENT_CERTIFICATES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `certificate_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `student_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2000-01-01'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
			
			$sql= "ALTER TABLE  `" . NAMASTE_STUDENT_CERTIFICATES . "` ADD UNIQUE (
			 `certificate_id` ,
			 `student_id`
			)";
			$wpdb->query($sql);
	  }  
	 
	  // payment records	  
	  if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_PAYMENTS."'") != NAMASTE_PAYMENTS) {        
			$sql = "CREATE TABLE `" . NAMASTE_PAYMENTS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `course_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `amount` DECIMAL(8,2),
				  `status` VARCHAR(100) NOT NULL DEFAULT 'failed',
				  `paycode` VARCHAR(100) NOT NULL DEFAULT '',
				  `paytype` VARCHAR(100) NOT NULL DEFAULT 'paypal'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  	 
	 
	   // tracks the visits on a give course or lesson
	   // 1 record per user/date
	   if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_VISITS."'") != NAMASTE_VISITS) {        
			$sql = "CREATE TABLE `" . NAMASTE_VISITS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `course_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `lesson_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `visits` INT UNSIGNED NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  	 	 
	  
	  // add extra fields in new versions
	  namaste_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''")	  
	  ), NAMASTE_STUDENT_HOMEWORKS);
	  
	   namaste_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''")	  
	  ), NAMASTE_STUDENT_COURSES);
	  
	   namaste_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''")	  
	  ), NAMASTE_STUDENT_LESSONS);
	 
	  // add student role if not exists
    $res = add_role('student', 'Student', array(
          'read' => true, // True allows that capability
          'namaste' => true));   
    if(!$res) {
    	// role already exists, check the capability
    	$role = get_role('student');
    	if(!$role->has_cap('namaste')) $role->add_cap('namaste');
    }          	
    
    // add manage cap to the admin / superadmin by default
    $role = get_role('administrator');
    if(!$role->has_cap('namaste_manage')) $role->add_cap('namaste_manage');
    
    // fush rewrite rules
    NamasteLMSCourseModel::register_course_type();
    NamasteLMSLessonModel::register_lesson_type();
    flush_rewrite_rules();
	  update_option( 'namaste_version', "0.7");	  
	  // exit;
   }
   
   // main menu
   static function menu() {
		$namaste_cap = current_user_can('namaste_manage')?'namaste_manage':'namaste';   	
		$use_grading_system = get_option('namaste_use_grading_system');
   	
   	$menu=add_menu_page(__('Namaste! LMS', 'namaste'), __('Namaste! LMS', 'namaste'), "namaste_manage", "namaste_options", 
   		array(__CLASS__, "options"));
		add_submenu_page('namaste_options', __("Assignments", 'namaste'), __("Assignments", 'namaste'), 'namaste_manage', 'namaste_homeworks', array('NamasteLMSHomeworkModel', "manage"));
		add_submenu_page('namaste_options', __("Students", 'namaste'), __("Students", 'namaste'), 'namaste_manage', 'namaste_students', array('NamasteLMSStudentModel', "manage"));		
		add_submenu_page('namaste_options', __("Certificates", 'namaste'), __("Certificates", 'namaste'), 'namaste_manage', 'namaste_certificates', array('NamasteLMSCertificatesController', "manage"));
		if(!empty($use_grading_system)) add_submenu_page('namaste_options', __("Gradebook", 'namaste'), __("Gradebook", 'namaste'), 'namaste_manage', 'namaste_gradebook', array('NamasteLMSGradebookController', "manage"));
		add_submenu_page('namaste_options', __("Namaste! Settings", 'namaste'), __("Settings", 'namaste'), 'namaste_manage', 'namaste_options', array(__CLASS__, "options"));        
		add_submenu_page('namaste_options', __("Namaste! Plugins &amp; API", 'namaste'), __("Plugins &amp; API", 'namaste'), 'namaste_manage', 'namaste_plugins', array(__CLASS__, "plugins"));
   		
		// not visible in menu
		add_submenu_page( NULL, __("Student Lessons", 'namaste'), __("Student Lessons", 'namaste'), $namaste_cap, 'namaste_student_lessons', array('NamasteLMSLessonModel', "student_lessons"));
		add_submenu_page( NULL, __("Homeworks", 'namaste'), __("Homeworks", 'namaste'), $namaste_cap, 'namaste_lesson_homeworks', array('NamasteLMSHomeworkModel', "lesson_homeworks"));
		add_submenu_page( NULL, __("Send note", 'namaste'), __("Send note", 'namaste'), 'namaste_manage', 'namaste_add_note', array('NamasteLMSNoteModel', "add_note"));
		add_submenu_page( NULL, __("Submit solution", 'namaste'), __("Submit solution", 'namaste'), $namaste_cap, 'namaste_submit_solution', array('NamasteLMSHomeworkController', "submit_solution"));
		add_submenu_page( NULL, __("View solutions", 'namaste'), __("View solutions", 'namaste'), $namaste_cap, 'namaste_view_solutions', array('NamasteLMSHomeworkController', "view"));
		add_submenu_page( NULL, __("View all solutions", 'namaste'), __("View all solutions", 'namaste'), 'namaste_manage', 'namaste_view_all_solutions', array('NamasteLMSHomeworkController', "view_all"));
		add_submenu_page( NULL, __("View Certificate", 'namaste'), __("View Certificate", 'namaste'), $namaste_cap, 'namaste_view_certificate', array('NamasteLMSCertificatesController', "view_certificate"));
		
		do_action('namaste_lms_admin_menu');
		
		
		// student menu
		$menu=add_menu_page(__('My Courses', 'namaste'), __('My Courses', 'namaste'), $namaste_cap, "namaste_my_courses", array('NamasteLMSCoursesController', "my_courses"));
			add_submenu_page('namaste_my_courses', __("My Certificates", 'namaste'), __("My Certificates", 'namaste'), $namaste_cap, 'namaste_my_certificates', array('NamasteLMSCertificatesController', "my_certificates"));
			if(!empty($use_grading_system)) add_submenu_page('namaste_my_courses', __("My Gradebook", 'namaste'), __("My Gradebook", 'namaste'), $namaste_cap, 'namaste_my_gradebook', array('NamasteLMSGradebookController', "my_gradebook"));
			
		do_action('namaste_lms_user_menu');	
	}
	
	// CSS and JS
	static function scripts() {
		// CSS
		wp_register_style( 'namaste-css', NAMASTE_URL.'css/main.css?v=1');
	  wp_enqueue_style( 'namaste-css' );
   
   	wp_enqueue_script('jquery');
	   
	   // Namaste's own Javascript
		wp_register_script(
				'namaste-common',
				NAMASTE_URL.'js/common.js',
				false,
				'0.1.0',
				false
		);
		wp_enqueue_script("namaste-common");
		
		// jQuery Validator
		wp_enqueue_script(
				'jquery-validator',
				'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js',
				false,
				'0.1.0',
				false
		);
	}
	
	// initialization
	static function init() {
		global $wpdb;
		load_plugin_textdomain( 'namaste', false, NAMASTE_RELATIVE_PATH."/languages/" );
		if (!session_id()) @session_start();
		
		// define table names 
		define( 'NAMASTE_STUDENT_COURSES', $wpdb->prefix. "namaste_student_courses");
		define( 'NAMASTE_LESSON_COURSES', $wpdb->prefix. "namaste_lesson_courses");
		if(!defined('NAMASTE_HOMEWORKS')) define( 'NAMASTE_HOMEWORKS', $wpdb->prefix. "namaste_homeworks");
		define( 'NAMASTE_STUDENT_HOMEWORKS', $wpdb->prefix. "namaste_student_homeworks");
		define( 'NAMASTE_HOMEWORK_NOTES', $wpdb->prefix. "namaste_homework_notes");
		define( 'NAMASTE_STUDENT_LESSONS', $wpdb->prefix. "namaste_student_lessons");
		define( 'NAMASTE_CERTIFICATES', $wpdb->prefix. "namaste_certificates");
		define( 'NAMASTE_STUDENT_CERTIFICATES', $wpdb->prefix. "namaste_student_certificates");
		define( 'NAMASTE_PAYMENTS', $wpdb->prefix. "namaste_payments");
		define( 'NAMASTE_VISITS', $wpdb->prefix. "namaste_visits");
		
		define( 'NAMASTE_VERSION', get_option('namaste_version'));
		
		// shortcodes
		add_shortcode('namaste-todo', array("NamasteLMSShortcodesController", 'todo'));
		add_shortcode('namaste-enroll', array("NamasteLMSShortcodesController", 'enroll'));
		
		// Paypal IPN
		add_filter('query_vars', array(__CLASS__, "query_vars"));
		add_action('parse_request', array("NamastePayment", "parse_request"));
		
		// exam related actions
		add_action('watu_exam_submitted', array('NamasteLMSLessonModel','exam_submitted_watu'));
		add_action('watupro_completed_exam', array('NamasteLMSLessonModel','exam_submitted_watupro'));
		
		// custom columns
		add_filter('manage_namaste_lesson_posts_columns', array('NamasteLMSLessonModel','manage_post_columns'));
		add_action( 'manage_posts_custom_column' , array('NamasteLMSLessonModel','custom_columns'), 10, 2 );
		add_filter('manage_namaste_course_posts_columns', array('NamasteLMSCourseModel','manage_post_columns'));
		add_action( 'manage_posts_custom_column' , array('NamasteLMSCourseModel','custom_columns'), 10, 2 );
	}
	
	// handle Namaste vars in the request
	static function query_vars($vars) {
		$new_vars = array('namaste');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 	
		
	// parse Namaste vars in the request
	static function template_redirect() {		
		global $wp, $wp_query, $wpdb;
		$redirect = false;		
		 
	  if($redirect) {
	   	if(@file_exists(TEMPLATEPATH."/".$template)) include TEMPLATEPATH."/namaste/".$template;		
			else include(NAMASTE_PATH."/views/templates/".$template);
			exit;
	  }	   
	}	
			
	// manage general options
	static function options() {
		global $wp_roles;		
		if(!empty($_POST['namaste_options']) and check_admin_referer('save_options', 'nonce_options')) {
			$roles = $wp_roles->roles;			
			
			foreach($roles as $key=>$r) {
				if($key == 'administrator') continue;
				
				$role = get_role($key);

				// use Namaste!
				if(in_array($key, $_POST['use_roles'])) {					
    			if(!$role->has_cap('namaste')) $role->add_cap('namaste');
				}
				else $role->remove_cap('namaste');
				
				// manage Namaste!
				if(@in_array($key, $_POST['manage_roles'])) {					
    			if(!$role->has_cap('namaste_manage')) $role->add_cap('namaste_manage');
				}
				else $role->remove_cap('namaste_manage');
			} 
		}
		
		if(!empty($_POST['namaste_exam_options']) and check_admin_referer('save_exam_options', 'nonce_exam_options')) {
				update_option('namaste_use_exams', $_POST['use_exams']);
		}
		
		if(!empty($_POST['namaste_payment_options']) and check_admin_referer('save_payment_options', 'nonce_payment_options')) {
			update_option('namaste_accept_other_payment_methods', $_POST['accept_other_payment_methods']);
			update_option('namaste_other_payment_methods', $_POST['other_payment_methods']);
			update_option('namaste_currency', $_POST['currency']);
			update_option('namaste_accept_paypal', $_POST['accept_paypal']);
			update_option('namaste_paypal_id', $_POST['paypal_id']);
			
			update_option('namaste_accept_stripe', $_POST['accept_stripe']);
			update_option('namaste_stripe_public', $_POST['stripe_public']);
			update_option('namaste_stripe_secret', $_POST['stripe_secret']);
		} 
		
		if(!empty($_POST['namaste_grade_options'])) {
			update_option('namaste_use_grading_system', @$_POST['use_grading_system']);
			update_option('namaste_grading_system', $_POST['grading_system']);
			update_option('namaste_use_points_system', @$_POST['use_points_system']);
			update_option('namaste_points_course', $_POST['points_course']);
			update_option('namaste_points_lesson', $_POST['points_lesson']);
			update_option('namaste_points_homework', $_POST['points_homework']);
		}
		
		// select all roles in the system
		$roles = $wp_roles->roles;
				
		// what exams to use
		$use_exams = get_option('namaste_use_exams');
		
		// see if watu/watuPRO are available and activate
		$current_plugins = get_option('active_plugins');
		$watu_active = $watupro_active = false;
		if(in_array('watu/watu.php', $current_plugins)) $watu_active = true;
		if(in_array('watupro/watupro.php', $current_plugins)) $watupro_active = true;
			
		$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
		$accept_paypal = get_option('namaste_accept_paypal');
		$accept_stripe = get_option('namaste_accept_stripe');
		
		$currency = get_option('namaste_currency');
		$currencies=array('USD'=>'$', "EUR"=>"&euro;", "GBP"=>"&pound;", "JPY"=>"&yen;", "AUD"=>"AUD",
	   "CAD"=>"CAD", "CHF"=>"CHF", "CZK"=>"CZK", "DKK"=>"DKK", "HKD"=>"HKD", "HUF"=>"HUF",
	   "ILS"=>"ILS", "MXN"=>"MXN", "NOK"=>"NOK", "NZD"=>"NZD", "PLN"=>"PLN", "SEK"=>"SEK",
	   "SGD"=>"SGD");		
	   
	   $use_grading_system = get_option('namaste_use_grading_system');
	   $grading_system = stripslashes(get_option('namaste_grading_system'));
	   if(empty($grading_system)) $grading_system = "A, B, C, D, F";
	   $use_points_system = get_option('namaste_use_points_system');
			
		require(NAMASTE_PATH."/views/options.php");
	}	
	
	static function help() {
		require(NAMASTE_PATH."/views/help.php");
	}	
	
	static function plugins() {
		require(NAMASTE_PATH."/views/plugins.php");
	}	
	
	static function register_widgets() {
		// register_widget('NamasteWidget');
	}
}