<?php
class NamasteLMSHomeworkController {
	static function submit_solution() {
		global $wpdb, $user_ID;
		$_course = new NamasteLMSCourseModel();
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		// am I enrolled?
		if(!NamasteLMSStudentModel::is_enrolled($user_ID, $course->ID)) wp_die(__('You are not enrolled in this course!',
			'namaste'));
			
		// now submit
		if(!empty($_POST['ok'])) {
			if(empty($_POST['content'])) wp_die(__('You cannot submit an empty solution', 'namaste'));			
			
			// avoid duplicates
			$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_HOMEWORKS."
				WHERE student_id=%d AND homework_id=%d AND content=%s", $user_ID, $homework->id,
				$_POST['content']));
			if(!$exists) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_HOMEWORKS." SET
					homework_id=%d, student_id=%d, status='pending', date_submitted=CURDATE(), 
					content=%s, file=''",
					$homework->id, $user_ID, $_POST['content']));
			}	 			
			
			do_action('namaste_submitted_solution', $user_ID, $homework->id);
			
			require(NAMASTE_PATH."/views/solution-submitted.php");
		}
		else require(NAMASTE_PATH."/views/submit-solution.php");		
	}
	
	// teacher views, approves, rejects submitted solutions
	static function view() {
		global $wpdb, $user_ID;
		
		$student_id = empty($_GET['student_id'])?$user_ID : $_GET['student_id'];
		if(!current_user_can('namaste_manage') and $student_id!=$user_ID) wp_die(__('You are not allowed to see these solutions', 'namaste'));
		$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $student_id));
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		// approve or reject solution
		if(!empty($_POST['change_status'])) self::change_solution_status($lesson, $student_id);
		
		$use_grading_system = get_option('namaste_use_grading_system');
		$grades = explode(",", stripslashes(get_option('namaste_grading_system')));
		// give grade on a solution
		if($use_grading_system and !empty($_POST['grade_solution']) and current_user_can('namaste_manage')) {
			$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_HOMEWORKS." SET grade=%s WHERE id=%d", $_POST['grade'], $_POST['id']));
			do_action('namaste_graded_homework', $_POST['id'], $_POST['grade']);
		}
		
		// select submitted solutions
		$solutions = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS."
			WHERE student_id=%d AND homework_id=%d ORDER BY id DESC", $student_id, $homework->id));
		
		require(NAMASTE_PATH."/views/view-solutions.php");
	}
	
	// view everyone's solutions ion a homework
	static function view_all() {
		global $wpdb;
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		$use_grading_system = get_option('namaste_use_grading_system');
		
		// approve or reject solution
		if(!empty($_POST['change_status'])) self::change_solution_status($lesson);
		
		// select submitted solutions
		$solutions = $wpdb -> get_results($wpdb->prepare("SELECT tH.*, tU.user_login as user_login 
			FROM ".NAMASTE_STUDENT_HOMEWORKS." tH JOIN {$wpdb->users} tU ON tH.student_id = tU.ID
			WHERE homework_id=%d ORDER BY id DESC", $homework->id));
			
		$show_everyone = true;
		require(NAMASTE_PATH."/views/view-solutions.php");
	}
	
	// approve or reject a homework solution
	static function change_solution_status($lesson, $student_id = NULL) {
		global $wpdb;
		
		if(!current_user_can('namaste_manage')) wp_die(__('You are not allowed to do this', 'namaste'));
		
		if(!$student_id) {
			$solution = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $_POST['solution_id']));
			$student_id = $solution->student_id;
		}
			
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_HOMEWORKS." SET
			status=%s WHERE id=%d", $_POST['status'], $_POST['solution_id']));
			
		do_action('namaste_change_solution_status', $student_id, $_POST['solution_id'], $_POST['status']);	
		
		// maybe complete the lesson if the status is approved 				
		if($_POST['status']=='approved' and NamasteLMSLessonModel::is_ready($lesson->ID, $student_id)) {
			NamasteLMSLessonModel::complete($lesson->ID, $student_id);
		}		
	}
}