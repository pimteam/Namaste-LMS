<?php
// procedural function to dispatch ajax requests
function namaste_ajax() {
	global $wpdb, $user_ID;	
	
	$type = empty($_POST['type']) ? $_GET['type'] : $_POST['type'];	
	
	switch($type) {
		case 'lessons_for_course':
			$_lesson = new NamasteLMSLessonModel();
			echo $_lesson->select($_POST['course_id'], 'json', null, '');
		break;
		
		// load notes for student homework
		case 'load_notes':
			// unless I am manager I can see other user's notes
			if($user_ID != $_GET['student_id'] and !current_user_can('namaste_manage')) wp_die('You are not allowed to see these notes.', 'namaste');	
			$multiuser_access = 'all';
			if($user_ID != $_GET['student_id']) $multiuser_access = NamasteLMSMultiUser :: check_access('homework_access');
		
			// select notes
			$notes = $wpdb->get_results($wpdb->prepare("SELECT tN.*, tU.user_login as username
			  FROM ".NAMASTE_HOMEWORK_NOTES." tN JOIN {$wpdb->users} tU ON tU.ID = tN.teacher_id
				WHERE homework_id=%d AND student_id=%d ORDER BY tN.id DESC", intval($_GET['homework_id']), intval($_GET['student_id'])));
				
			// select homework
			$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", intval($_GET['homework_id'])));	
				
			if(@file_exists(get_stylesheet_directory().'/namaste/homework-notes.php')) require get_stylesheet_directory().'/namaste/homework-notes.php';
			else require(NAMASTE_PATH."/views/homework-notes.php");
		break;
		
		case 'delete_note':
			// unless I am manager I can see other user's notes
			if($user_ID != $_POST['student_id'] and !current_user_can('namaste_manage')) wp_die('You are not allowed to see these notes.', 'namaste');	
			$multiuser_access = 'all';
			if($user_ID != $_POST['student_id']) $multiuser_access = NamasteLMSMultiUser :: check_access('homework_access');
			if($multiuser_access != 'all') die(__("ERROR|||You are not allowed to delete homework notes.", 'namaste'));
			
			$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_HOMEWORK_NOTES." WHERE id=%d", intval($_POST['id'])));
			echo 'SUCCESS|||';
		break;
		
		// show lesson progress
		case 'lesson_progress':
			// if i am not manager I can see only my own todo
			if(!current_user_can('namaste_manage') and $user_ID != $_GET['student_id']) die(__("You are not allowed to view this", 'namaste'));
			
			// select lesson and student
			$lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", intval($_GET['lesson_id'])));
			$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", intval($_GET['student_id'])));		
			
			$todo = NamasteLMSLessonModel :: todo($_GET['lesson_id'], $_GET['student_id']);
			$list_tag = empty($_POST['list_tag']) ? 'ol' : $_POST['list_tag'];
	   	if($list_tag !='ul' && $list_tag != 'ol') $list_tag = 'ol';		
			if(@file_exists(get_stylesheet_directory().'/namaste/lesson-todo.php')) require get_stylesheet_directory().'/namaste/lesson-todo.php';
		else require(NAMASTE_PATH."/views/lesson-todo.php");
		break;
		
		// display payment screen for a course
		case 'course_payment':
			// select course
			$course = $wpdb -> get_row( $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", intval($_GET['course_id'])));
			$fee = get_post_meta($course->ID, 'namaste_fee', true);			
			$fee = apply_filters('namaste-coupon-applied', $fee, $course->ID);
			if(class_exists('NamastePROSchool') and !empty($_GET['is_school'])) $fee = NamastePROSchool :: school_price('course', $course);	
			$currency = get_option('namaste_currency');
			$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
			$accept_moolamojo = get_option('namaste_accept_moolamojo');
			$accept_paypal = get_option('namaste_accept_paypal');
			$paypal_id = get_option('namaste_paypal_id');
			
			if($accept_other_payment_methods) {
				$other_payment_methods = stripslashes(get_option('namaste_other_payment_methods'));
				$other_payment_methods = str_replace('{{course-id}}', $course->ID, $other_payment_methods);
				$other_payment_methods = str_replace('{{course-name}}', $course->post_title, $other_payment_methods);
				$other_payment_methods = str_replace('{{user-id}}', $_GET['student_id'], $other_payment_methods);
				$other_payment_methods = str_replace('{{amount}}', $fee, $other_payment_methods);
				$other_payment_methods = str_replace('{{item-type}}', 'course', $other_payment_methods);
				$other_payment_methods = do_shortcode($other_payment_methods);
			}
			
			if(!empty($accept_moolamojo) and class_exists('MoolaMojo')) {
				$moola_price = get_option('namaste_moolamojo_price');
				$moola_button = get_option('namaste_moolamojo_button');
				
				$cost_in_moola = round($fee * $moola_price);
				
				// get balance
				$moola_balance = get_user_meta($user_ID, 'moolamojo_balance', true);
				
				if($moola_balance < $cost_in_moola) $paybutton = sprintf(__('Not enough %s.', 'namaste'), MOOLA_CURRENCY);
				else {
					$url = admin_url("admin-ajax.php?action=namaste_ajax&type=pay_with_moolamojo");
					$paybutton = "<input type='button' value='".sprintf(__('Pay %d %s', 'namaste'), $cost_in_moola, MOOLA_CURRENCY)."' onclick='NamastePay.payWithMoolaMojo({$course->ID}, \"$url\");'>";
				}
				
				// replace the codes in the design
				$moola_button = str_replace('{{{credits}}}', $cost_in_moola, $moola_button);
				$moola_button = str_replace('{{{item}}}', __('course', 'namaste'), $moola_button);
				$moola_button = str_replace('{{{button}}}', $paybutton, $moola_button);
				$moola_button = stripslashes($moola_button);
			}
			
			// return URL
			$paypal_return = get_option('namaste_paypal_return');			
			if(empty($paypal_return)) $paypal_return =  get_permalink($course->ID);
      	if(!strstr($paypal_return, 'http')) $paypal_return = 'http://'.$paypal_return;
			
		   if(class_exists('NamastePROSchool') and !empty($_GET['is_school'])) {
		      $paypal_return = esc_url(add_query_arg(array('is_school' => 1), trim($paypal_return)));
		   }	
			
			if(@file_exists(get_stylesheet_directory().'/namaste/course-pay.php')) require get_stylesheet_directory().'/namaste/course-pay.php';
		else require(NAMASTE_PATH."/views/course-pay.php");	
		break;
		
		// set student's grade for a course or lesson
		case 'set_grade':
			if(!current_user_can('namaste_manage')) die(__('You are not allowed to grade','namaste'));
			
			if($_POST['grade_what'] == 'course') {
				$table = NAMASTE_STUDENT_COURSES;
				$field = 'course_id';
				$student_field = 'user_id';
				do_action('namaste_graded_course', $_POST['student_id'], $_POST['item_id'], $_POST['grade']);
			} 
			else {
				$table = NAMASTE_STUDENT_LESSONS;
				$field = 'lesson_id';
				$student_field = 'student_id';
				do_action('namaste_graded_lesson', $_POST['student_id'], $_POST['item_id'], $_POST['grade']);
			} 
			
			// now update the grade
			$wpdb->query($wpdb->prepare("UPDATE $table SET grade=%s WHERE $field=%d AND $student_field=%d", sanitize_text_field($_POST['grade']), 
				intval($_POST['item_id']), intval($_POST['student_id'])));
		break;
		
		// creates module drop-down selector for given course ID
		case 'load_modules':
			$_module = new NamasteLMSModuleModel();
			$modules = $_module->select(0, $_POST['course_id']);
			$module_id = 0;
			if(!empty($_POST['lesson_id'])) $module_id = get_post_meta($_POST['lesson_id'], 'namaste_module', true);
			
			// in case of JSON, just return the modules, otherwise output the drop-down
			if(!empty($_POST['json'])) {
				echo json_encode($modules);
				exit;
			}
			?>
			&nbsp;
			<?php _e('Select module:', 'namaste');?>
			<select name="namaste_module">
				<option value="0"><?php _e('- No module -', 'namaste');?></option>
				<?php foreach($modules as $module):?>
					<option value="<?php echo $module->ID?>" <?php if(!empty($module_id) and $module->ID == $module_id) echo 'selected'?>><?php echo stripslashes($module->post_title);?></option>
				<?php endforeach;?>
			</select>
			<?php 
		break;
		
		// pay for course with MoolaMojo
		case 'pay_with_moolamojo':		
			if(!is_user_logged_in()) die("ERROR: Not logged in");
			
			// payment with moolamojo accepted at all?
			$accept_moolamojo = get_option('namaste_accept_moolamojo');
			if(empty($accept_moolamojo)) die("ERROR: virtual credits are not accepted as payment method."); 
			
			// enough points to pay?
			$moola_price = get_option('namaste_moolamojo_price');
			if(empty($_POST['is_bundle'])) {
				$course = get_post($_POST['id']);
				$fee = get_post_meta($_POST['id'], 'namaste_fee', true);				
			}
			// else bundle NYI
			
			if(class_exists('NamastePROCoupons')) {		   
			   $fee = NamastePROCoupons :: coupon_applied($fee, $_GET['course_id'], 'course');			   
			}
			
			// school price?
			$is_school = 0;
		   if(class_exists('NamastePROSchool') and !empty($_GET['is_school'])) {
		      $fee = NamastePROSchool :: school_price('course', $course);
		      $is_school = 1;
		   }	
		   
		   $cost_in_moola = $fee * $moola_price;
			
			$user_balance = get_user_meta($user_ID, 'moolamojo_balance', true);	
			if($user_balance < $cost_in_moola) die("ERROR: Not enough virtual credits");
			
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_PAYMENTS." SET 
				course_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, paytype='moolamojo'", 
				$course->ID, $user_ID, $fee, ''));
			
			do_action('namaste-paid', $user_ID, $fee, "course", $course->ID, $is_school);	
			
			// deduct user points
			$user_balance -= $cost_in_moola;

			update_user_meta($user_ID, 'moolamojo_balance', $user_balance);	
				
			// enroll accordingly to course settings - this will be placed in a method once we 
			// have more payment options
			$enroll_mode = get_post_meta($course->ID, 'namaste_enroll_mode', true);	
			if(!NamasteLMSStudentModel :: is_enrolled($user_ID, $course->ID))  {
				$_course = new NamasteLMSCourseModel();
				$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';				
				$_course->enroll($user_ID, $course->ID, $status);
			}	
		
			echo "SUCCESS";
		break;
		
		case 'set_student_tags':
			// updates tags on student to course relation
			$tags = sanitize_text_field($_POST['tags']);		
			$tags = str_replace(array(', ', ' ,'), ',', $tags);
			
			$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_COURSES." SET tags=%s WHERE id=%d", $tags, intval($_POST['student_course_id'])));				 
		break;
		
		// upload files for a homework
		case 'submit_solution_files':
			NamasteLMSHomeworkController :: upload_files();			
		break;
	}
	exit;
}