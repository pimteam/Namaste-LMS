<?php
class NamasteLMSStudentModel {
	// this model will first show a page where to select course with dropdown
	// then will show the students in this course in table with lessons, status etc
	// clicking on student name will go to the same page that student sees in their dashboard
	// edit profile is not required because we'll use Wordpress users
	static function manage() {
		 global $wpdb;
		 $_course = new NamasteLMSCourseModel();
		 
		 // select all courses
		 $courses = $_course -> select();
				 
		 // if course selected, select lessons and enrolled students
		 if(!empty($_GET['course_id'])) {
				// cleanup student record
				if(!empty($_GET['cleanup'])) {
					 $wpdb->query( $wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_COURSES." 
					 	WHERE course_id = %d AND user_id=%d", $_GET['course_id'], $_GET['student_id']) );
					 	
					 $wpdb->query( $wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_LESSONS." WHERE student_id= %d AND lesson_id IN (SELECT ID FROM {$wpdb->posts} tP JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course' AND tM.meta_value = %d WHERE post_type = 'namaste_lesson');", $_GET['student_id'], $_GET['course_id']) );					 	
					 	
					namaste_redirect("admin.php?page=namaste_students&course_id=$_GET[course_id]&status=$_GET[status]");		
				}		 	
		 	
				// enroll student
				if(!empty($_GET['enroll'])) {
					 // find the user
					 $error = false;
					 $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE user_email=%s", $_GET['email']));
					 
					 // user exists?
					 if(empty($student->ID)) $error = __('Sorry, I cannot find user with this email.', 'namaste');
					 
					 // allowed to use Namaste!?
					 if(!$error and !user_can($student->ID, 'namaste')) {
					 	$error = __("This user's role does not allow them to use Namaste! LMS. You'll have either to change their role or allow the role work with the LMS from the Settings page", 'namaste');
					 }	
					 
					 // already enrolled?
					 if(!$error) {
						 $is_enrolled = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
						 	WHERE user_id = %d AND course_id = %d", $student->ID, $_GET['course_id']));
						 if($is_enrolled) $error = __('This user is already enrolled in the course', 'namaste');
					 }
					 
					 // finally, enroll
					 if(empty($error)) {
					 		$wpdb -> query($wpdb -> prepare("INSERT INTO ".NAMASTE_STUDENT_COURSES." SET
					 			course_id = %d, user_id = %d, status = 'enrolled', 
					 			enrollment_date = CURDATE(), completion_date = '1900-01-01', comments=''",
					 			$_GET['course_id'], $student->ID));
					 		$success = __('User successfully enrolled in the course', 'namaste');	
					 		
					 		// do_action('namaste_enrolled_course', $student->ID, $_GET['course_id'], true);
					 }	
				}
				
				// change student status
				if(!empty($_GET['change_status'])) {
					 $wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_COURSES." SET
					 			status=%s, completion_date=CURDATE() 
					 			WHERE user_id=%d AND course_id=%d", $_GET['status'], $_GET['student_id'], $_GET['course_id']));
					 			
					 if($_GET['status'] == 'enrolled') do_action('namaste_enrollment_approved', $_GET['student_id'], $_GET['course_id']);
					 else do_action('namaste_enrollment_rejected', $_GET['student_id'], $_GET['course_id']);					
					 namaste_redirect("admin.php?page=namaste_students&course_id=$_GET[course_id]");					 							 	
				}				
				 	
		 		// select lessons
		 		$_lesson = new NamasteLMSLessonModel();
		 		$lessons = $_lesson -> select($_GET['course_id']);
		 		$lids = array(0);
		 		foreach($lessons as $lesson) $lids[] = $lesson->ID;
		 				 		
		 		// select students
		 		$status_sql = '';
		 		if(!empty($_GET['status']) and $_GET['status']!='any') { 
		 			$status_sql = $wpdb->prepare(" AND tS.status=%s", $_GET['status']);
		 		}
		 		$students = $wpdb->get_results($wpdb->prepare("SELECT tU.*, tS.status as namaste_status 
		 		FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_COURSES." tS 
		 		ON tS.user_id = tU.ID AND tS.course_id=%d $status_sql
		 		ORDER BY user_nicename", $_GET['course_id']));		 		
		 		
		 		// select student - to - lesson relations
		 		$completed_lessons = $wpdb->get_results("SELECT * FROM ".NAMASTE_STUDENT_LESSONS."
		 			WHERE lesson_id IN (".implode(',', $lids).")");	
		 				 		
		 		// match to students	
		 		foreach($students	as $cnt=>$student) {
		 			 $student_completed_lessons = $student_incomplete_lessons = array();
		 			 foreach($completed_lessons as $lesson) {
		 			 		if($lesson->student_id == $student->ID) {
		 			 				if($lesson->status) $student_completed_lessons[] = $lesson->lesson_id;
		 			 				else $student_incomplete_lessons[] = $lesson->lesson_id;
		 			 		}
		 			 }	
		 			 
		 			 $students[$cnt]->completed_lessons = $student_completed_lessons;
		 			 $students[$cnt]->incomplete_lessons = $student_incomplete_lessons;
		 		}
		 } // end if course selected
		 
		wp_enqueue_script('thickbox',null,array('jquery'));
	  wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0'); 
		require(NAMASTE_PATH."/views/manage-students.php"); 
	}
	
	// change lesson status - not started (so the record in namatse_student_lessons does not exist),
	// in progress (0 + update with current date), and completed (1 + update with current date)
	static function lesson_status($student_id, $lesson_id, $status) {
		global $wpdb, $user_ID;
		
		// security check
		if(!current_user_can('namaste_manage') and $user_ID != $student_id) 
			wp_die(__("You cannot change someone else's status", 'namaste'));
			
		// if status == -1 we have to remove the existing record if any
		if($status == -1) {
			 $wpdb -> query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_LESSONS." 
			 	WHERE student_id = %d AND lesson_id = %d", $student_id, $lesson_id));	
		}
		
		// complete lesson - don't allow "completed" if there are unsatisfied requirements
	  if($status == 1) {
			if(!NamasteLMSLessonModel :: is_ready($lesson_id, $student_id, true)) return false; 
			
			NamasteLMSLessonModel :: complete($lesson_id, $student_id);
	  }		
		
		// set as 'in progress'
		if($status == 0 ) {
			 // record exists?
			 $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_LESSONS."
			 	WHERE student_id = %d AND lesson_id = %d", $student_id, $lesson_id));
			 
			 if($exists) {
			 		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_LESSONS." 
			 			SET status=%d, completion_date = CURDATE() WHERE id=%d", $status, $exists));
			 } 
			 else {
			 		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_LESSONS." SET
			 			lesson_id = %d, student_id = %d, status = %d, completion_date = CURDATE()",
			 			$lesson_id, $student_id, $status));
			 }	
		}	
		
		return true;
	} // end lesson_status
	
	// is student enrolled in course?
	static function is_enrolled($student_id, $course_id) {
		global $wpdb;
		
		$is_enrolled = $wpdb -> get_var( $wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
			WHERE user_id = %d AND course_id = %d", $student_id, $course_id));
			
		return $is_enrolled;	
	}
}