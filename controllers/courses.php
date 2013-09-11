<?php
class NamasteLMSCoursesController {
	// displays courses of a student, lets them enroll in a course
	static function my_courses() {
		global $wpdb, $user_ID, $user_email;
		
		$currency = get_option('namaste_currency');
		$is_manager = current_user_can('namaste_manage');
		$_course = new NamasteLMSCourseModel();
		
		// stripe integration goes right on this page
		$accept_stripe = get_option('namaste_accept_stripe');
		$accept_paypal = get_option('namaste_accept_paypal');
		$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
		if($accept_stripe) $stripe = NamasteStripe::load();
		
		if(!empty($_POST['stripe_pay'])) {
			 NamasteStripe::pay($currency);			
			 namaste_redirect('admin.php?page=namaste_my_courses');
		}	
		
		if(!empty($_POST['enroll'])) $mesage = self::enroll($is_manager);
		
		// select all courses join to student courses so we can have status.
		$courses = $wpdb -> get_results($wpdb->prepare("SELECT tSC.*, 
			tC.post_title as post_title, tC.ID as post_id, tC.post_excerpt as post_excerpt
			 FROM {$wpdb->posts} tC LEFT JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tC.ID = tSC.course_id
			 AND tSC.user_id = %d WHERE tC.post_status = 'publish'
			 AND tC.post_type='namaste_course' ORDER BY tC.post_title", $user_ID));
			 
		if(!empty($currency) and !$is_manager) {
			foreach($courses as $cnt=>$course) {
				$courses[$cnt]->fee = get_post_meta($course->post_id, 'namaste_fee', true); 
			}
		}	 
				
		$_course->currency = $currency;
		$_course->accept_other_payment_methods = $accept_other_payment_methods;
		$_course->accept_paypal = $accept_paypal;
		$_course->accept_stripe = $accept_stripe;		
		$_course->stripe = $stripe;		
		wp_enqueue_script('thickbox',null,array('jquery'));
		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');	 
		require(NAMASTE_PATH."/views/my_courses.php");	 
	}
	
	// processes the whole enrollment thing so it can be reused in shortcode as well.
	static function enroll($is_manager) {
		global $wpdb, $user_ID, $user_email;
		$_course = new NamasteLMSCourseModel();
		
		$message = '';		
		
		// enroll in course
		$course = $_course->select($_POST['course_id']);
		
		// course fee? 
		$fee = get_post_meta($course->ID, 'namaste_fee', true);
		
		// When fee is paid, enrollment is automatic so this is just fine here
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
}