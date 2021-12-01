<?php
// main model containing general config and UI functions
class NamasteLMS {
   static function install($update = false) {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	$collation = $wpdb->get_charset_collate();
   	
   	$old_version = get_option('namaste_version');
   	update_option( 'namaste_version', "1.49");
   	if(!$update) self::init();
   	
   	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	  
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
	  
		 // relations to modules
   	if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_STUDENT_MODULES."'") != NAMASTE_STUDENT_MODULES) {        
			$sql = "CREATE TABLE `" . NAMASTE_STUDENT_MODULES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`module_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
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
	  
	  // file uploads to homework solutions
		if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_SOLUTION_FILES."'") != NAMASTE_SOLUTION_FILES) {        
			$sql = "CREATE TABLE `" . NAMASTE_SOLUTION_FILES . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					`homework_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`student_id` INT UNSIGNED NOT NULL DEFAULT 0,
					`solution_id` INT UNSIGNED NOT NULL DEFAULT 0,	
					`file` VARCHAR(255) NOT NULL DEFAULT '',
					`fileblob` LONGBLOB
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
	  
	  // history of various actions, for example points awarded and spent
	   if($wpdb->get_var("SHOW TABLES LIKE '".NAMASTE_HISTORY."'") != NAMASTE_HISTORY) {        
			$sql = "CREATE TABLE `" . NAMASTE_HISTORY . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `datetime` DATETIME,
				  `action` VARCHAR(255) NOT NULL DEFAULT '',
				  `value` VARCHAR(255) NOT NULL DEFAULT '', /* some textual value if required */
				  `num_value` INT UNSIGNED NOT NULL DEFAULT 0 /* some numeric value, for example points */
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  		  
	  	  
	  	// Student reviews on courses
		$sql = "CREATE TABLE " . NAMASTE_COURSE_REVIEWS . " (
			  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  student_id int(11) UNSIGNED NOT NULL DEFAULT 0,
			  course_id int(11) UNSIGNED NOT NULL DEFAULT 0,
			  rating tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
			  datetime datetime,
			  review text,
			  is_approved tinyint UNSIGNED NOT NULL DEFAULT 0,
			  PRIMARY KEY  (id)			  
			) $collation";
		dbDelta( $sql );	  	 
	  
	  // add extra fields in new versions
	  namaste_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),	  
		  array("name"=>"fileblob", "type"=>"LONGBLOB"),
		  array("name"=>"points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* points earned */		  
	  ), NAMASTE_STUDENT_HOMEWORKS);
	  
	   namaste_add_db_fields(array(
		  array("name"=>"filepath", "type"=>"VARCHAR(255) NOT NULL DEFAULT ''"),
	  ), NAMASTE_SOLUTION_FILES);
	  
	  
	   namaste_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),	  
		  array("name"=>"enrollment_time", "type"=>"DATETIME"),
		  array("name"=>"completion_time", "type"=>"DATETIME"),
		  array("name"=>"points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* cumulative points from  the course itself, lessons and homeworks */
		  array("name"=>"tags", "type"=>"TEXT"), /* Allow to tag each student to course enrollment, for example by year or source, etc*/
	  ), NAMASTE_STUDENT_COURSES);
	  
	   namaste_add_db_fields(array(
		  array("name"=>"grade", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),
		  array("name"=>"start_time", "type"=>"DATETIME"),
		  array("name"=>"completion_time", "type"=>"DATETIME"),	  
		  array("name"=>"points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* cumulative points from the lesson itself and homeworks */
		  array("name"=>"pending_admin_approval", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* all other is done, the lesson relation is pending admin approval */ 
	  ), NAMASTE_STUDENT_LESSONS);
	  
	  namaste_add_db_fields(array(
		  array("name"=>"award_points", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), 
		  array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"limit_by_date", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"accept_date_from", "type"=>"DATE"),
		  array("name"=>"accept_date_to", "type"=>"DATE"),
		  array("name"=>"auto_grade_lesson", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"self_approving", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* does not require submission, just "mark completed" */
	  ), NAMASTE_HOMEWORKS);
	  
	  namaste_add_db_fields(array(		   
		  array("name"=>"editor_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
		  array("name"=>"has_expiration", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),	      		
    		array("name"=>"expiration_period", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"), /* SQL-friendly text like "3 month" or "1 year"*/
    		array("name"=>"expired_message", "type"=>"TEXT"),	   
    		array("name"=>"expiration_mode", "type"=>"VARCHAR(255) NOT NULL DEFAULT 'period'"), /* period or date */
    		array("name"=>"expiration_date", "type"=>"DATE"), /* when expiration_mode='date' */   		
	  ), NAMASTE_CERTIFICATES);
	  
	  namaste_add_db_fields(array(		   
		  array("name"=>"for_item_type", "type"=>"VARCHAR(100) NOT NULL DEFAULT '' "), /* when awarding points etc to know is it course or lesson etc */
		  array("name"=>"for_item_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* when awarding points etc to know the id of the course or lesson etc */
		  array("name"=>"group_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* from classes in Namaste PRO when available */
		  array("name"=>"course_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* any activity happens within a course */
		  array("name"=>"module_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"), /* to handle the future modules */
	  ), NAMASTE_HISTORY);
	  
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
    
    // update fileblob
    if($old_version < 1.27) {
    	$wpdb->query("ALTER TABLE ".NAMASTE_STUDENT_HOMEWORKS." CHANGE fileblob fileblob LONGBLOB");
    }
    
    // fush rewrite rules
    NamasteLMSCourseModel::register_course_type();
    NamasteLMSLessonModel::register_lesson_type();
    NamasteLMSModuleModel::register_module_type();
    NamasteLMSHomeworkModel::register_homework_type();
    flush_rewrite_rules();	  	  
	  // exit;
   }
   
   // main menu
   static function menu() {   	  	
   
		$namaste_cap = current_user_can('namaste_manage') ? 'namaste_manage' : 'namaste';   	
		$use_grading_system = get_option('namaste_use_grading_system');
		$homework_menu = $students_menu = $certificates_menu = $gradebook_menu = $settings_menu = $massenroll_menu = $help_menu = $plugins_menu = true;
		if(!current_user_can('administrator') and current_user_can('namaste_manage')) {
			// perform these checks only for managers that are not admins, otherwise it's pointless use of resourses
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
			$user = new WP_User( $user_ID );
			$homework_menu = NamasteLMSMultiUser :: item_access('homework_access', $role_settings, $user, $enabled_roles); 
			$students_menu = NamasteLMSMultiUser :: item_access('students_access', $role_settings, $user, $enabled_roles);
			$massenroll_menu = NamasteLMSMultiUser :: item_access('mass_enroll_access', $role_settings, $user, $enabled_roles);
			$certificates_menu = NamasteLMSMultiUser :: item_access('certificates_access', $role_settings, $user, $enabled_roles);
			$gradebook_menu = NamasteLMSMultiUser :: item_access('gradebook_access', $role_settings, $user, $enabled_roles);
			$settings_menu = NamasteLMSMultiUser :: item_access('settings_access', $role_settings, $user, $enabled_roles);			
			$help_menu = NamasteLMSMultiUser :: item_access('help_access', $role_settings, $user, $enabled_roles);
			$plugins_menu = NamasteLMSMultiUser :: item_access('plugins_access', $role_settings, $user, $enabled_roles);
		}
		
		// if a manager has no access to the settings page, let's turn the to-do into the main menu
		if($settings_menu) {
			add_menu_page(__('Namaste! LMS', 'namaste'), __('Namaste! LMS', 'namaste'), "namaste_manage", "namaste_options", array(__CLASS__, "options"));
			add_submenu_page('namaste_options', __("To Do", 'namaste'), __("To Do", 'namaste'), 'namaste_manage', 'namaste_todo', array('NamasteToDo', "manager_todo"));
		}
		else {
			add_menu_page(__('Namaste! LMS', 'namaste'), __('Namaste! LMS', 'namaste'), "namaste_manage", "namaste_options", array('NamasteToDo', "manager_todo"));
			add_submenu_page('namaste_options', __("To Do", 'namaste'), __("To Do", 'namaste'), 'namaste_manage', 'namaste_options', array('NamasteToDo', "manager_todo"));
		}
   	   		
		if($homework_menu) add_submenu_page('namaste_options', __("Assignments", 'namaste'), __("Assignments", 'namaste'), 'namaste_manage', 'namaste_homeworks', array('NamasteLMSHomeworkModel', "manage"));
		if($students_menu) add_submenu_page('namaste_options', __("Students", 'namaste'), __("Students", 'namaste'), 'namaste_manage', 'namaste_students', array('NamasteLMSStudentModel', "manage"));		
		if($certificates_menu) {
			add_submenu_page('namaste_options', __("Certificates", 'namaste'), __("Certificates", 'namaste'), 'namaste_manage', 'namaste_certificates', array('NamasteLMSCertificatesController', "manage"));
			add_submenu_page(NULL, __("Students Earned Certificate", 'namaste'), __("Students Earned Certificate", 'namaste'), 'namaste_manage', 'namaste_student_certificates', array('NamasteLMSCertificatesController', "student_certificates"));			
		}
		if($gradebook_menu and !empty($use_grading_system)) add_submenu_page('namaste_options', __("Gradebook", 'namaste'), __("Gradebook", 'namaste'), 'namaste_manage', 'namaste_gradebook', array('NamasteLMSGradebookController', "manage"));
		if($settings_menu) add_submenu_page('namaste_options', __("Namaste! Settings", 'namaste'), __("Settings", 'namaste'), 'namaste_manage', 'namaste_options', array(__CLASS__, "options"));     

		if(class_exists('WP_Experience_API')) {
			add_submenu_page('namaste_options', __("xAPI / Tin Can", 'namaste'), __("xAPI / Tin Can", 'namaste'), 'manage_options', 'namaste_xapi', array('NamasteXAPI', "options"));        
		}		
		
		if($help_menu) add_submenu_page('namaste_options', __("Help", 'namaste'), __("Help", 'namaste'), 'namaste_manage', 'namaste_help', array(__CLASS__, "help"));        
		if($plugins_menu) add_submenu_page('namaste_options', __("Namaste! Plugins &amp; API", 'namaste'), __("Plugins &amp; API", 'namaste'), 'namaste_manage', 'namaste_plugins', array(__CLASS__, "plugins"));
   		
		// not visible in menu
		add_submenu_page( NULL, __("Student Lessons", 'namaste'), __("Student Lessons", 'namaste'), $namaste_cap, 'namaste_student_lessons', array('NamasteLMSLessonModel', "student_lessons"));
		add_submenu_page( NULL, __("Homeworks", 'namaste'), __("Homeworks", 'namaste'), $namaste_cap, 'namaste_lesson_homeworks', array('NamasteLMSHomeworkModel', "lesson_homeworks"));
		add_submenu_page( NULL, __("Send note", 'namaste'), __("Send note", 'namaste'), 'namaste_manage', 'namaste_add_note', array('NamasteLMSNoteModel', "add_note"));
		add_submenu_page( NULL, __("Submit solution", 'namaste'), __("Submit solution", 'namaste'), $namaste_cap, 'namaste_submit_solution', array('NamasteLMSHomeworkController', "submit_solution"));
		add_submenu_page( NULL, __("View solutions", 'namaste'), __("View solutions", 'namaste'), $namaste_cap, 'namaste_view_solutions', array('NamasteLMSHomeworkController', "view"));
		add_submenu_page( NULL, __("View all solutions", 'namaste'), __("View all solutions", 'namaste'), 'namaste_manage', 'namaste_view_all_solutions', array('NamasteLMSHomeworkController', "view_all"));
		add_submenu_page( NULL, __("View Certificate", 'namaste'), __("View Certificate", 'namaste'), $namaste_cap, 'namaste_view_certificate', array('NamasteLMSCertificatesController', "view_certificate"));
		add_submenu_page( NULL, __("Download solution", 'namaste'), __("Download solution", 'namaste'), $namaste_cap, 'namaste_download_solution', array('NamasteLMSHomeworkController', "download_solution"));
		add_submenu_page( NULL, __("Multi user configuration", 'namaste'), __("Multi user configuration", 'namaste'), 'manage_options', 'namaste_multiuser', array('NamasteLMSMultiUser', "manage"));
		if($massenroll_menu) add_submenu_page( NULL, __("Mass enroll students", 'namaste'), __("Mass enroll students", 'namaste'), 'namaste_manage', 'namaste_mass_enroll', array('NamasteLMSCoursesController', "mass_enroll"));
		add_submenu_page( NULL, __("Shortcode generator", 'namaste'), __("Shortcode generator", 'namaste'), 'namaste_manage', 'namaste_shortcode_generator', array('NamasteLMSShortcodesController', "generator"));
		
		do_action('namaste_lms_admin_menu');
		
		// should we display "My Courses" link?
		if(current_user_can('namaste_manage') and !current_user_can('administrator')) {
			$role_settings = unserialize(get_option('namaste_role_settings'));
			$current_user = wp_get_current_user();
			$current_user_roles = $current_user->roles;
			$role = array_shift($current_user_roles);
			if(!empty($role_settings[$role]['no_mycourses'])) $dont_show_mycourses = true;			
		}
		
		if(empty($dont_show_mycourses)) {
			// student menu
			$menu = add_menu_page(__('My Courses', 'namaste'), __('My Courses', 'namaste'), $namaste_cap, "namaste_my_courses", array('NamasteLMSCoursesController', "my_courses"));
				add_submenu_page('namaste_my_courses', __("My Certificates", 'namaste'), __("My Certificates", 'namaste'), $namaste_cap, 'namaste_my_certificates', array('NamasteLMSCertificatesController', "my_certificates"));
				if(!empty($use_grading_system)) add_submenu_page('namaste_my_courses', __("My Gradebook", 'namaste'), __("My Gradebook", 'namaste'), $namaste_cap, 'namaste_my_gradebook', array('NamasteLMSGradebookController', "my_gradebook"));
		}		
			
		do_action('namaste_lms_user_menu');	
	} // end menu
	
	// CSS and JS
	static function scripts() {
		// CSS
		wp_register_style( 'namaste-css', NAMASTE_URL.'css/main.css?v=1.2');
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
		
		$translation_array = array('ajax_url' => admin_url('admin-ajax.php'),
		'all_modules' => __('All Modules', 'namaste'));	
		wp_localize_script( 'namaste-common', 'namaste_i18n', $translation_array );	
		
		// jQuery Validator
		wp_enqueue_script(
				'jquery-validator',
				'//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js',
				false,
				'0.1.0',
				false
		);
	}
	
	// initialization
	static function init() {
		global $wpdb;		
		load_plugin_textdomain( 'namaste', false, NAMASTE_RELATIVE_PATH."/languages/" );
		
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
		define( 'NAMASTE_HISTORY', $wpdb->prefix. "namaste_history");
		define( 'NAMASTE_STUDENT_MODULES', $wpdb->prefix. "namaste_student_modules");
		define( 'NAMASTE_SOLUTION_FILES', $wpdb->prefix. "namaste_solution_files");
		define( 'NAMASTE_COURSE_REVIEWS', $wpdb->prefix. "namaste_course_reviews");
		
		define( 'NAMASTE_VERSION', get_option('namaste_version'));
		
		self :: define_filters();
		
		// shortcodes
		add_shortcode('namaste-todo', array("NamasteLMSShortcodesController", 'todo'));		
		add_shortcode('namaste-enroll', array("NamasteLMSShortcodesController", 'enroll'));
		add_shortcode('namaste-points', array("NamasteLMSShortcodesController", 'points'));
		add_shortcode('namaste-leaderboard', array("NamasteLMSShortcodesController", 'leaderboard'));
		add_shortcode('namaste-mycourses', array("NamasteLMSShortcodesController", 'my_courses'));
		add_shortcode('namaste-mycertificates', array("NamasteLMSShortcodesController", 'my_certificates'));
		add_shortcode('namaste-course-lessons', array("NamasteLMSShortcodesController", 'lessons'));
		add_shortcode('namaste-course-modules', array("NamasteLMSShortcodesController", 'modules'));
		add_shortcode('namaste-module-lessons', array("NamasteLMSShortcodesController", 'module_lessons'));
		add_shortcode('namaste-next-lesson', array("NamasteLMSShortcodesController", 'next_lesson'));
		add_shortcode('namaste-prev-lesson', array("NamasteLMSShortcodesController", 'prev_lesson'));
		add_shortcode('namaste-first-lesson', array("NamasteLMSShortcodesController", 'first_lesson'));
		add_shortcode('namaste-grade', array("NamasteLMSShortcodesController", 'grade'));
		add_shortcode('namaste-mark', array("NamasteLMSShortcodesController", 'mark'));
		add_shortcode('namaste-assignments', array("NamasteLMSShortcodesController", 'assignments'));
		add_shortcode('namaste-earned-certificates', array("NamasteLMSShortcodesController", 'earned_certificates'));
		add_shortcode('namaste-course-link', array("NamasteLMSShortcodesController", 'course_link'));
		add_shortcode('namaste-module-link', array("NamasteLMSShortcodesController", 'module_link'));
		add_shortcode('namaste-condition', array("NamasteLMSShortcodesController", 'condition'));
		add_shortcode('namaste-search', array("NamasteLMSShortcodesController", 'search'));
		add_shortcode('namaste-num-courses', array("NamasteLMSShortcodesController", 'num_courses'));
		add_shortcode('namaste-num-modules', array("NamasteLMSShortcodesController", 'num_modules'));
		add_shortcode('namaste-num-lessons', array("NamasteLMSShortcodesController", 'num_lessons'));
		add_shortcode('namaste-num-students', array("NamasteLMSShortcodesController", 'num_students'));
		add_shortcode('namaste-num-lessons', array("NamasteLMSShortcodesController", 'num_lessons'));
		add_shortcode('namaste-num-assignments', array("NamasteLMSShortcodesController", 'num_assignments'));
		add_shortcode('namaste-userinfo', array("NamasteLMSShortcodesController", 'userinfo'));
		add_shortcode('namaste-gradebook', array("NamasteLMSShortcodesController", 'gradebook'));
		add_shortcode('namaste-mygradebook', array("NamasteLMSShortcodesController", 'my_gradebook'));
		add_shortcode('namaste-lesson-status', array("NamasteLMSShortcodesController", 'lesson_status'));
		add_shortcode('namaste-course-status', array("NamasteLMSShortcodesController", 'course_status'));
		add_shortcode('namaste-breadcrumb', array("NamasteLMSShortcodesController", 'breadcrumb'));
		
		// Paypal IPN
		add_filter('query_vars', array(__CLASS__, "query_vars"));
		add_action('parse_request', array("NamastePayment", "parse_request"));
		
		// wp_loaded actions
		add_action('wp_loaded', array(__CLASS__, "wp_loaded"));
		
		// exam related actions and filters
		add_action('watu_exam_submitted', array('NamasteLMSLessonModel','exam_submitted_watu'));
		add_action('watupro_completed_exam', array('NamasteLMSLessonModel','exam_submitted_watupro'));
		add_action('watupro_completed_exam_edited', array('NamasteLMSLessonModel','exam_submitted_watupro'));
		add_action('chained_quiz_completed', array('NamasteLMSLessonModel','exam_submitted_chained'));
		add_filter( 'post_row_actions', array('NamasteLMSLessonModel','quiz_results_link'), 10, 2 );
		
		// custom columns
		add_filter('manage_namaste_lesson_posts_columns', array('NamasteLMSLessonModel','manage_post_columns'));
		add_action( 'manage_posts_custom_column' , array('NamasteLMSLessonModel','custom_columns'), 10, 2 );
		add_filter('manage_namaste_course_posts_columns', array('NamasteLMSCourseModel','manage_post_columns'));
		add_action( 'manage_posts_custom_column' , array('NamasteLMSCourseModel','custom_columns'), 10, 2 );
		add_action('restrict_manage_posts',array('NamasteLMSLessonModel','restrict_manage_posts'));
		add_action('parse_query',array('NamasteLMSLessonModel','parse_admin_query'));
		add_action('restrict_manage_posts',array('NamasteLMSModuleModel','restrict_manage_posts'));
		add_action('parse_query',array('NamasteLMSModuleModel','parse_admin_query'));
		add_filter( 'post_row_actions', array('NamasteLMSCourseModel','post_row_actions'), 10, 2 );
		add_filter( 'post_row_actions', array('NamasteLMSModuleModel','post_row_actions'), 10, 2 );		
		
		// certificates
		add_action('template_redirect', array('NamasteLMSCertificatesController', 'certificate_redirect'));
		
		// comments on lessons shouldn't be visible for unenrolled
		add_filter('comments_array', array('NamasteLMSLessonModel','restrict_visible_comments'));
		
		// add points in custom column on the users page
		if(get_option('namaste_use_points_system') != '') {				
			add_filter('manage_users_columns', array('NamastePoint', 'add_custom_column'));
			add_action('manage_users_custom_column', array('NamastePoint','manage_custom_column'), 10, 3);
			add_action('pre_user_query', array('NamastePoint','pre_user_query'));
			add_filter( 'manage_users_sortable_columns', array('NamastePoint','sortable_columns') );
		} 
		
		// auto enroll in courses
		add_action('user_register', array('NamasteLMSCourseModel', 'register_enroll'));
		
		$version = get_option('namaste_version');
		if($version != '1.49') self::install(true);

		// default 'you need to be logged in' messages for lessons and courses
		if(get_option('namaste_need_login_text_lesson') == '') {
			$text = sprintf(__('You need to be <a href="%s">logged in</a> to access this lesson.', 'namaste'), wp_login_url());
			update_option('namaste_need_login_text_lesson', $text);
		}
		if(get_option('namaste_need_login_text_course') == '') {
			$text = sprintf(__('You can enroll in this course from your student dashboard. You need to be <a href="%s">logged in</a>.', 'namaste'), wp_login_url());
			update_option('namaste_need_login_text_course', $text);
		}
		if(get_option('namaste_need_login_text_module') == '') {
			$text = sprintf(__('You need to be <a href="%s">logged in</a> to access this module.', 'namaste'), wp_login_url());
			update_option('namaste_need_login_text_module', $text);
		}
		
		// xAPI triggers
		NamasteXAPI :: register_triggers();
		
		// WooCommerce integration
		// catch the woocommerce actions
		add_action('woocommerce_order_status_completed', 'namastewoo_bridge_order_complete');
		add_action('template_redirect', 'namastewoo_bridge_template_redirect');
		add_action('woocommerce_thankyou', 'namastewoo_bridge_thankyou');
		
		define('NAMASTE_NEED_LOGIN_TEXT_LESSON', stripslashes(get_option('namaste_need_login_text_lesson')));
		define('NAMASTE_NEED_LOGIN_TEXT_MODULE', stripslashes(get_option('namaste_need_login_text_module')));
		define('NAMASTE_NEED_LOGIN_TEXT_COURSE', stripslashes(get_option('namaste_need_login_text_course')));
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
		global $wp_roles, $wp_rewrite;				
		$is_admin = current_user_can('administrator');		
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('settings_access');
		
		if(!empty($_POST['namaste_options']) and check_admin_referer('save_options', 'nonce_options')) {
			$roles = $wp_roles->roles;			
			
			foreach($roles as $key=>$r) {
				if($key == 'administrator') continue;
				
				$role = get_role($key);

				// use Namaste!
				if(!empty($_POST['use_roles']) and is_array($_POST['use_roles']) and in_array($key, $_POST['use_roles'])) {					
    				if(!$role->has_cap('namaste')) $role->add_cap('namaste');
				}
				else $role->remove_cap('namaste');
				
				// manage Namaste! - allow only admin change this
				if($is_admin) {
					if(!empty($_POST['manage_roles']) and is_array($_POST['manage_roles']) and in_array($key, $_POST['manage_roles'])) {					
	    				if(!$role->has_cap('namaste_manage')) $role->add_cap('namaste_manage');
					}
					else $role->remove_cap('namaste_manage');
				}	// end if can_manage_options
			} // end foreach role 
			
			$use_modules = empty($_POST['use_modules']) ? 0 : 1;
			$show_courses_in_blog = empty($_POST['show_courses_in_blog']) ? 0 : 1;
			$show_lessons_in_blog = empty($_POST['show_lessons_in_blog']) ? 0 : 1;
			update_option('namaste_show_courses_in_blog', $show_courses_in_blog);
			update_option('namaste_show_lessons_in_blog', $show_lessons_in_blog);
			$_POST['course_slug'] = preg_replace('/[^\w\-]/', '', $_POST['course_slug']);
			$_POST['lesson_slug'] = preg_replace('/[^\w\-]/', '', $_POST['lesson_slug']);
			$_POST['module_slug'] = preg_replace('/[^\w\-]/', '', $_POST['module_slug']);
			update_option('namaste_use_modules', $use_modules);
			update_option('namaste_course_slug', $_POST['course_slug']);
			update_option('namaste_lesson_slug', $_POST['lesson_slug']);
			update_option('namaste_module_slug', $_POST['module_slug']);
			$link_to_course = empty($_POST['link_to_course']) ? 0 : 1;
			update_option('namaste_link_to_course', $link_to_course);
			update_option('namaste_link_to_course_text', sanitize_text_field($_POST['link_to_course_text']));
			$mycourses_only_enrolled = empty($_POST['mycourses_only_enrolled']) ? 0 : 1;
			update_option('namaste_mycourses_only_enrolled', $mycourses_only_enrolled);
			$links_target = sanitize_text_field($_POST['links_target']);			
			update_option('namaste_links_target', $links_target);			
			
			$wp_rewrite->flush_rules();  
			
			// login texts
			update_option('namaste_need_login_text_lesson', namaste_strip_tags($_POST['need_login_text_lesson']));
			update_option('namaste_need_login_text_course', namaste_strip_tags($_POST['need_login_text_course']));
			update_option('namaste_need_login_text_module', namaste_strip_tags($_POST['need_login_text_module']));
			
			do_action('namaste-saved-options-main');
		}
		
		if(!empty($_POST['namaste_exam_options']) and check_admin_referer('save_exam_options', 'nonce_exam_options')) {
				update_option('namaste_use_exams', sanitize_text_field($_POST['use_exams']));
				update_option('namaste_cleanup_exams', sanitize_text_field(@$_POST['cleanup_exams']));
				$access_exam_started_lesson = empty($_POST['access_exam_started_lesson']) ? 0 : 1; 
				update_option('namaste_access_exam_started_lesson', $access_exam_started_lesson);
				do_action('namaste-saved-options-exams');
		}
		
		if(!empty($_POST['save_homework_options']) and check_admin_referer('namaste_homework_options')) {
			update_option('namaste_allowed_file_types', sanitize_text_field($_POST['allowed_file_types']));
			
			$store_filesystem = empty($_POST['store_filesystem']) ? 0 : 1;			
			update_option('namaste_store_files_filesystem', $store_filesystem);
			
			$file_upload_progress = empty($_POST['file_upload_progress']) ? 0 : 1;			
			update_option('namaste_file_upload_progress', $file_upload_progress);
			
			$protected_folder = preg_replace("/[^A-z0-9_]/", "", $_POST['protected_folder']);
			update_option('namaste_protected_folder', $protected_folder);
			
			// if folder does not exist create it
			if(!empty($protected_folder)) {
				$dir = wp_upload_dir();
				if(!file_exists($dir['basedir'].'/'.$protected_folder)) {
					mkdir($dir['basedir'].'/'.$protected_folder, 0755);
					$fp = fopen($dir['basedir'].'/'.$protected_folder.'/.htaccess', 'wb');
					$contents = 'deny from all';
					fwrite($fp, $contents);
					fclose($fp);
				}
			}
			
			update_option('namaste_homework_size_total', intval($_POST['homework_size_total']));
			update_option('namaste_homework_size_per_file', intval($_POST['homework_size_per_file']));
			
		} // end homework options
		
		if(!empty($_POST['namaste_payment_options']) and check_admin_referer('save_payment_options', 'nonce_payment_options')) {
			update_option('namaste_accept_other_payment_methods', @$_POST['accept_other_payment_methods']);
			update_option('namaste_other_payment_methods', $_POST['other_payment_methods']);
			if(empty($_POST['currency'])) $_POST['currency'] = sanitize_text_field($_POST['custom_currency']);
			update_option('namaste_currency', sanitize_text_field($_POST['currency']));
			update_option('namaste_accept_paypal', (empty($_POST['accept_paypal']) ? 0 : 1));
			update_option('namaste_paypal_sandbox', (empty($_POST['paypal_sandbox']) ? 0 : 1));
			update_option('namaste_paypal_id', sanitize_text_field($_POST['paypal_id']));
			update_option('namaste_paypal_return', esc_url_raw($_POST['paypal_return']));
			update_option('namaste_use_pdt', (empty($_POST['use_pdt']) ? 0 : 1));
			update_option('namaste_pdt_token', sanitize_text_field($_POST['pdt_token']));
			
			update_option('namaste_accept_stripe', (empty($_POST['accept_stripe']) ? 0 : 1));
			update_option('namaste_stripe_public', sanitize_text_field($_POST['stripe_public']));
			update_option('namaste_stripe_secret', sanitize_text_field($_POST['stripe_secret']));
			
			update_option('namaste_accept_moolamojo', (empty($_POST['accept_moolamojo']) ? 0 : 1));
			update_option('namaste_moolamojo_price', intval($_POST['moolamojo_price']));
			update_option('namaste_moolamojo_button', namaste_strip_tags($_POST['moolamojo_button']));
			
			$woocommerce = empty($_POST['namaste_woocommerce']) ? 0 : 1;
			update_option('namaste_woocommerce', $woocommerce);
			
			do_action('namaste-saved-options-payments');
		} 
		
		if(!empty($_POST['namaste_grade_options']) and check_admin_referer('namaste_grade_options')) {
			$use_grading_system = empty($_POST['use_grading_system']) ? 0 : 1;
			$use_points_system = empty($_POST['use_points_system']) ? 0 : 1;
			$moolamojo_points = empty($_POST['moolamojo_points']) ? 0 : 1; // connect to MoolaMojo?
			update_option('namaste_use_grading_system', $use_grading_system);
			update_option('namaste_grading_system', sanitize_text_field($_POST['grading_system']));
			update_option('namaste_use_points_system', $use_points_system);
			update_option('namaste_points_course', intval($_POST['points_course']));
			update_option('namaste_points_lesson', intval($_POST['points_lesson']));
			update_option('namaste_points_homework', intval($_POST['points_homework']));
			update_option('namaste_moolamojo_points', $moolamojo_points);
			
			do_action('namaste-saved-options-grading');
		}
		
		// select all roles in the system
		$roles = $wp_roles->roles;
				
		// what exams to use
		$use_exams = get_option('namaste_use_exams');
		
		// see if watu/watuPRO are available and activate		
		$watu_active = $watupro_active = $chained_active = false;
		if(function_exists('watu_init')) $watu_active = true;
		if(function_exists('watupro_init')) $watupro_active = true;
		if(class_exists('ChainedQuiz')) $chained_active = true;
			
		$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
		$accept_paypal = get_option('namaste_accept_paypal');
		$accept_stripe = get_option('namaste_accept_stripe');
		
		$accept_moolamojo = get_option('namaste_accept_moolamojo');
		$moolamojo_button = get_option('namaste_moolamojo_button');
		if(empty($moolamojo_button)) $moolamojo_button = "<p align='center'>".__('You can also buy access to this {{{item}}} with {{{credits}}} virtual credits from your balance. You currently have [moolamojo-balance] credits total.', 'namaste')."</p><p align='center'>{{{button}}}</p>";
		
		$currency = get_option('namaste_currency');
		$currencies=array('USD'=>'$', "EUR"=>"&euro;", "GBP"=>"&pound;", "JPY"=>"&yen;", "AUD"=>"AUD",
	   "CAD"=>"CAD", "CHF"=>"CHF", "CZK"=>"CZK", "DKK"=>"DKK", "HKD"=>"HKD", "HUF"=>"HUF",
	   "ILS"=>"ILS", "INR"=>"INR", "MXN"=>"MXN", "NOK"=>"NOK", "NZD"=>"NZD", "PLN"=>"PLN", "SEK"=>"SEK",
	   "SGD"=>"SGD", "ZAR"=>"ZAR");		
	   $currency_keys = array_keys($currencies);  
	   
	   $use_grading_system = get_option('namaste_use_grading_system');
	   $grading_system = stripslashes(get_option('namaste_grading_system'));
	   if(empty($grading_system)) $grading_system = "A, B, C, D, F";
	   $use_points_system = get_option('namaste_use_points_system');
	   
	   $payment_errors = get_option('namaste_errorlog');
	   // strip to reasonable length
	   $payment_errors = substr($payment_errors, 0, 10000);
	   
	   $course_slug = get_option('namaste_course_slug');
	   if(empty($course_slug)) $course_slug = 'namaste-course';
	   $lesson_slug = get_option('namaste_lesson_slug');
	   if(empty($lesson_slug)) $lesson_slug = 'namaste-lesson';
	   $module_slug = get_option('namaste_module_slug');
	   if(empty($module_slug)) $module_slug = 'namaste-module';
	   $use_modules = get_option('namaste_use_modules');
	   
	   $use_pdt = get_option('namaste_use_pdt');
	   
	   $link_to_course = get_option('namaste_link_to_course');
	   $link_to_course_text = stripslashes(get_option('namaste_link_to_course_text'));
	   if(empty($link_to_course_text)) $link_to_course_text = __('<p>Course: {{{course-link}}}</p>', 'namaste');
	   
	   $links_target = get_option('namaste_links_target');
		if(empty($links_target)) $links_target = '_blank';
		
		// if "Store the files in the filesystem instead of the database" and custom folder is added, make sure it's there and writable
		$protected_folder = get_option('namaste_protected_folder');
		 if(get_option('namaste_store_files_filesystem') == '1' and $protected_folder != '') {
		 	$dir = wp_upload_dir();
		 	$namaste_dir = $dir['basedir'].'/'.$protected_folder;
		 	
		 	if(!is_writable($namaste_dir)) {
		 		$upload_error = true;
		 	}	 	
		 }
				
		if(@file_exists(get_stylesheet_directory().'/namaste/options.php')) require get_stylesheet_directory().'/namaste/options.php';
		else require(NAMASTE_PATH."/views/options.php");
	}	
	
	static function help() {
		require(NAMASTE_PATH."/views/help.html.php");
	}	
	
	static function plugins() {
		if(@file_exists(get_stylesheet_directory().'/namaste/plugins.php')) require get_stylesheet_directory().'/namaste/plugins.php';
		else require(NAMASTE_PATH."/views/plugins.php");
	}	
	
	static function register_widgets() {
		// register_widget('NamasteWidget');
	}
	
	// manually apply Wordpress filters on the content
	// to avoid calling apply_filters('the_content')	
	static function define_filters() {
		global $wp_embed, $watupro_keep_chars;
		
		add_filter( 'namaste_content', 'wptexturize' ); // Questionable use!
		add_filter( 'namaste_content', 'convert_smilies' );
	   add_filter( 'namaste_content', 'convert_chars' );
		add_filter( 'namaste_content', 'shortcode_unautop' );
		add_filter( 'namaste_content', 'do_shortcode' );
		
		// Compatibility with specific plugins
		// qTranslate
		if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
			add_filter('namaste_content', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
			add_filter('namaste_qtranslate', 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage');
			add_filter( 'namaste_qtranslate', 'wptexturize' );
		}
	}
	
	// personal data eraser
	static function register_eraser($erasers) {
		 $erasers['namaste'] = array(
		    'eraser_friendly_name' => __( 'Namaste! LMS', 'namaste' ),
		    'callback'             => array('NamasteLMSStudentModel', 'erase_data')
		    );
		    
		  return $erasers;
	}
	
	// erase student's personal data when the WP Data Eraser is called
	static function erase_data($email_address, $page = 1) {
		 global $wpdb;

		 $number = 200; // Limit us to avoid timing out
  		 $page = (int) $page;
  		 
  		 // find student
  		 $user = get_user_by('email', $email_address);
  		 
  		 if($page == 1) {
  		 	  // delete history
	  		 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_HISTORY." WHERE user_id=%d", $user->ID));
	  		 
	  		 // delete student-courses, student-lessons, student-modules and student-certificates relations
	  		 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_COURSES." WHERE user_id=%d", $user->ID));
			 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_LESSONS." WHERE student_id=%d", $user->ID));			 
			 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_CERTIFICATES." WHERE student_id=%d", $user->ID));
			 
			 // delete homework notes
			 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_HOMEWORK_NOTES." WHERE student_id=%d", $user->ID));
			 
			 // delete visits
			 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_VISITS." WHERE user_id=%d", $user->ID));
  		 }  		 
  		 
  		 // remove homework solutions & files
  		 $homework_removed = false;
  		 
  		 $solutions = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM ".NAMASTE_STUDENT_HOMEWORKS." 
	  		 WHERE student_id=%d ORDER BY id LIMIT %d", $user->ID, $page));
	  	 $number = $wpdb->get_var("SELECT FOUND_ROWS()"); 
	  	 	 
	  	 foreach($solutions as $solution) {
	  	 	 // select soltion files and delete them
	  	 	 $files = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_SOLUTION_FILES." 
	  	 	 	WHERE student_id=%d AND solution_id=%d", $user->ID, $solution->id));
	  	 	 	
	  	 	 // delete the physical files if any	
	  	 	 foreach($files as $file) {
	  	 	 	if(!empty($file->filepath)) @unlink($file->filepath);
	  	 	 }
	  	 	 
	  	 	 // delete the DB files with query
	  	 	 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_SOLUTION_FILES." 
	  	 	 	WHERE student_id=%d AND solution_id=%d", $user->ID, $solution->id));
	  	 	 
	  	 	 // now delete the solution
	  	 	 $wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $solution->id));
	  	 }	 
	  	 
	  	 $done = count( $solutions ) <= $number; 
	  	 return array( 'items_removed' => true,
		    'items_retained' => false, // always false in this example
		    'messages' => array(), // no messages in this example
		    'done' => $done,
		  );
  		 
	} // end erase_data
	
	// call actions on WP loaded
	static function wp_loaded() {
	   if(!empty($_GET['namaste_pdt'])) NamastePayment::paypal_ipn();	   
	}	
}