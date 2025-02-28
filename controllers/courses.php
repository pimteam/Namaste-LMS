<?php
class NamasteLMSCoursesController {
	// displays courses of a student, lets them enroll in a course
	// @param $simplified boolean - when true outputs the page without "view lessons" link
	static function my_courses($simplified = false, $atts = null) {
		global $wpdb, $user_ID, $user_email, $wp;
		if(empty($atts)) $atts = array();
		
		$currency = get_option('namaste_currency');
		$is_manager = current_user_can('namaste_manage');
		$_course = new NamasteLMSCourseModel();
		
		// stripe integration goes right on this page
		$accept_stripe = false;
		$accept_paypal = get_option('namaste_accept_paypal');
		$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
		$accept_moolamojo = get_option('namaste_accept_moolamojo');
		if($accept_stripe) $stripe = NamasteStripe::load();
		
		if($simplified) {
			$current_url = home_url(add_query_arg(array(),$wp->request));
			$target_url = add_query_arg('namaste', 1, $current_url);
		}
		
		if(!empty($_POST['stripe_pay'])) {
			 //NamasteStripe::pay($currency);
			 namaste_redirect('admin.php?page=namaste_my_courses');
		}	
		
		if(!empty($_POST['enroll']) && !empty($_POST['namaste_enroll_nonce']) && wp_verify_nonce($_POST['namaste_enroll_nonce'], 'namaste_enroll_action')) {
			$mesage = self::enroll($is_manager);
		}

		// unenroll?
		if(!empty($_GET['unenroll']) && !empty($_GET['namaste_unenroll_nonce']) && wp_verify_nonce($_GET['namaste_unenroll_nonce'], 'namaste_unenroll_action')) {
			NamasteLMSStudentModel::cleanup($_GET['unenroll'], $user_ID);
			if($simplified) namaste_redirect($current_url);
			else namaste_redirect("admin.php?page=namaste_my_courses");
		}


		// filters from other plugins like Namaste! PRO		
		$filter_sql = '';
		$filter_sql = apply_filters('namaste-course-select-sql', $filter_sql, $user_ID);
		
		$join = "LEFT JOIN";

		// enrolled set as requirement in Settings?
		if(!isset($atts['enrolled']) and get_option('namaste_mycourses_only_enrolled') == 1) $atts['enrolled'] = 1;			
		
		if(!empty($atts['enrolled'])) $join = "JOIN";
		
		// select all courses join to student courses so we can have status.
		$courses = $wpdb -> get_results($wpdb->prepare("SELECT tSC.*, 
			tC.post_title as post_title, tC.ID as post_id, tC.post_excerpt as post_excerpt
			 FROM {$wpdb->posts} tC $join ".NAMASTE_STUDENT_COURSES." tSC ON tC.ID = tSC.course_id
			 AND tSC.user_id = %d WHERE tC.post_status = 'publish'
			 AND tC.post_type='namaste_course' $filter_sql 
			 GROUP BY tC.ID ORDER BY tC.post_title", $user_ID));
			 
			 
		// external reorder?
		$courses = apply_filters('namaste-reorder-courses', $courses);	 
			 
		if(!empty($currency) and !$is_manager) {
			foreach($courses as $cnt=>$course) {
				$courses[$cnt]->fee = get_post_meta($course->post_id, 'namaste_fee', true); 
			}
		}	 
				
		$_course->currency = $currency;
		$_course->accept_other_payment_methods = $accept_other_payment_methods;
		$_course->accept_paypal = $accept_paypal;
		$_course->accept_stripe = $accept_stripe;		
		$_course->accept_moolamojo = $accept_moolamojo;
		$_course->stripe = @$stripe;		
		wp_enqueue_script('thickbox',null,array('jquery'));
		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');	 
		
		$links_target = get_option('namaste_links_target');
		if(empty($links_target)) $links_target = '_blank';
		
		// column widths passed as shortcode attribute?
		if(!empty($atts['column_widths'])) {
			$column_widths = explode(',', esc_attr($atts['column_widths']));
		}
			 
		if(@file_exists(get_stylesheet_directory().'/namaste/my_courses.php')) require get_stylesheet_directory().'/namaste/my_courses.php';
		else require(NAMASTE_PATH."/views/my_courses.php");
	}
	
	// processes the whole enrollment thing so it can be reused in shortcode as well.
	static function enroll($is_manager) {
		global $wpdb, $user_ID, $user_email;
		$_course = new NamasteLMSCourseModel();
		
		$message = '';		
		
		// enroll in course
		$course = $_course->select($_POST['course_id']);
		if(empty($course->ID)) return __('Missing course ID', 'namaste');
				
		// course fee? 
		$fee = get_post_meta($course->ID, 'namaste_fee', true);
		
		// When fee is paid, enrollment is automatic so this is just fine here
		if(!empty($fee)) {
			$fee = apply_filters('namaste-coupon-applied', $fee, $course->ID); // coupon code from other plugin?
			// if $fee is 0 this means 100% coupon code is used. We must call namaste-paid action to ensure the coupon will be marked as used
			do_action('namaste-paid', $user_ID, $fee, "course", $course->ID, 0);
		}
		if($fee > 0 and !$is_manager) wp_die("You can't enroll yourself in a course when there is a fee"); 			
		
		$enroll_mode = get_post_meta($course->ID, 'namaste_enroll_mode', true);
			
		// if already enrolled, just skip this altogether
		if(!NamasteLMSStudentModel :: is_enrolled($user_ID, $course->ID)) {			
			// depending on mode, status will be either 'pending' or 'enrolled'
			$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';
			
			$_course->enroll($user_ID, $course->ID, $status);	
				
			if($enroll_mode == 'free') $message = sprintf(__('You enrolled in "%s"', 'namaste'), $course->post_title);
			else $message = __('Thank you for your interest in enrolling this course. A manager will review your application.', 'namaste');	
		}
		else $message = __('You have already enrolled in this course','namaste');
		
		return $message;
	}
	
	// mass enroll students from admin (obviously this won't do any checks if enrollment is allowed, paid etc)
	static function mass_enroll() {
		global $wpdb, $wp_roles;
		$roles = $wp_roles->roles;		
		$_course = new NamasteLMSCourseModel();
		$_course->ignore_restrictions = true;
		
		$blog_id = get_current_blog_id();
		// get all the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['namaste']) or !empty($r->capabilities['namaste_manage'])) $enabled_roles[] = $key;
		}
		
		// get course
		$course = get_post($_GET['course_id']);		
		$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
		$ob = empty($_GET['ob']) ? 'display_name' : sanitize_sql_orderby($_GET['ob']);
 		$dir = empty($_GET['dir']) ? 'ASC' : $_GET['dir'];
 		if(!in_array($dir, array('ASC', 'DESC'))) $dir = 'ASC';
 		$odir = ($dir == 'ASC') ? 'DESC' : 'ASC'; 		
 		$page_limit = empty($_GET['page_limit']) ? 20 : intval($_GET['page_limit']);
		
		if(!empty($_POST['mass_enroll']) and check_admin_referer('namaste_mass_enroll')) {
			$uids = empty($_POST['uids']) ? array(0) : $_POST['uids'];
			$tags = sanitize_text_field($_POST['tags']);		
			$tags = str_replace(array(', ', ' ,'), ',', $tags);		
			
			foreach($uids as $uid) {
				$uid = intval($uid);
				$_course->enroll($uid, $course->ID, 'enrolled', true, $tags);	
			}
		}
		
		// now select all users from the given roles and remove these who are already enrolled				
		$role_like_sql = '';
		foreach($enabled_roles as $cnt=>$role) {
			if($cnt) $role_like_sql .= " OR ";
			$role_like_sql .= " tM.meta_value LIKE '%".$role."%' ";
		}
		
		$users = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS tU.ID as ID, tU.user_login as user_login, 
		      tU.user_email as user_email, tU.display_name as display_name
				FROM {$wpdb->users} tU JOIN {$wpdb->usermeta} tM ON 
				tM.meta_key='".$wpdb->prefix.'capabilities'."' AND ($role_like_sql)
				AND tM.user_id=tU.ID
				AND tU.ID NOT IN (SELECT user_id FROM ".NAMASTE_STUDENT_COURSES." WHERE course_id=".intval($course->ID).")
				ORDER BY tU.$ob $dir LIMIT $offset, $page_limit"); 		
			
		$total_users = $wpdb->get_var("SELECT FOUND_ROWS()");
		
		include(NAMASTE_PATH . "/views/mass-enroll.html.php");
		
	} // end mass enroll
	
	// called by the namaste-condition shortcode. Returns the content depending on if the user is enrolled in course or not
	static function is_enrolled_shortcode($atts, $content) {
		global $user_ID, $post;
		
		// figure out course ID - comes from $atts or is the ID of the current post
		$course_id = empty($atts['course_id']) ? intval(@$post->ID) : intval($atts['course_id']);
		if(empty($course_id)) return '';
		
		if($atts['is_enrolled'] == 1) {			
			if(!is_user_logged_in()) return "";
			// returns the content only if the user is enrolled
			if(NamasteLMSStudentModel :: is_enrolled($user_ID, $course_id))  return apply_filters('namaste_content', $content);
		}
		else {
			// returns the content only if the user is NOT enrolled
			if(!is_user_logged_in()) return apply_filters('namaste_content', $content);
			if(!NamasteLMSStudentModel :: is_enrolled($user_ID, $course_id)) return apply_filters('namaste_content', $content);
		}
		
		return '';
	} // end is_enrolled_shortcode
}
