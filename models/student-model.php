<?php
class NamasteLMSStudentModel {
	// this model will first show a page where to select course with dropdown
	// then will show the students in this course in table with lessons, status etc
	// clicking on student name will go to the same page that student sees in their dashboard
	// edit profile is not required because we'll use Wordpress users
	static function manage() {
		 global $wpdb, $user_ID;
		 $_course = new NamasteLMSCourseModel();
		 
		 	$multiuser_access = 'all';
			$multiuser_access = NamasteLMSMultiUser :: check_access('students_access');
			
			 
		 // select all courses
		 $courses = $_course -> select();
		 $courses = apply_filters('namaste-homeworks-select-courses', $courses);
		 
	 	$page_limit = empty($_GET['page_limit']) ? 20 : intval($_GET['page_limit']);
	 	$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
	 	$ob = empty($_GET['ob']) ? 'display_name' : sanitize_sql_orderby($_GET['ob']);
	 	$dir = empty($_GET['dir']) ? 'ASC' : $_GET['dir'];
	 	if(!in_array($dir, array('ASC', 'DESC'))) $dir = 'ASC';
	 	$odir = ($dir == 'ASC') ? 'DESC' : 'ASC';
				 
		 // if course selected, select lessons and enrolled students
		 if(!empty($_GET['course_id'])) {
				do_action('namaste-check-permissions', 'course', $_GET['course_id']);		 	
		 	
				// cleanup student record
				if(!empty($_GET['cleanup'])) {
					if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'namaste_cleanup')) {
						wp_die(__('Invalid request.', 'namaste'));
					}

					 if($multiuser_access  == 'view') wp_die(__('You are not allowed to do this.', 'namaste'));
                self :: cleanup($_GET['course_id'], $_GET['student_id']); 
					 	
					namaste_redirect("admin.php?page=namaste_students&course_id=".intval($_GET['course_id'])."&status=".sanitize_text_field($_GET['status']));		
				}		 	
				
				// mass cleanup
				if(!empty($_POST['mass_cleanup']) and check_admin_referer('namaste_manage_students')) {
					if($multiuser_access  == 'view') wp_die(__('You are not allowed to do this.', 'namaste'));
					$ids = empty($_POST['student_ids']) ? array() : namaste_int_array($_POST['student_ids']);
					
					foreach($ids as $id) {
						self :: cleanup($_GET['course_id'], $id);
					}
				}
				
				// mass approve or reject
				if((!empty($_POST['mass_approve']) or !empty($_POST['mass_reject'])) and check_admin_referer('namaste_manage_students')) {
					if($multiuser_access  == 'view') wp_die(__('You are not allowed to do this.', 'namaste'));
					$ids = empty($_POST['student_ids']) ? array() : namaste_int_array($_POST['student_ids']);
					$status = empty($_POST['mass_approve']) ? 'rejected' : 'enrolled';
					
					foreach($ids as $id) self :: change_status($id, $_GET['course_id'], $status);
							
				} // end mass approve/reject
		 	
				// enroll student
				if(!empty($_GET['enroll'])) {
					 if($multiuser_access  == 'view') wp_die(__('You are not allowed to do this.', 'namaste'));
					 	
					 // find the user
					 $error = false;
					 if(strstr($_GET['email'], '@')) $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE user_email=%s", sanitize_email($_GET['email'])));
					 else $student = get_user_by('login', $_GET['email']);
					 
					 // user exists?
					 if(empty($student->ID)) $error = __('Sorry, I cannot find user with this email or user handle.', 'namaste');
					 
					 // allowed to use Namaste!?
					 if(!$error and !user_can($student->ID, 'administrator') and !user_can($student->ID, 'namaste')) {
					 	$error = __("This user's role does not allow them to use Namaste! LMS. You'll have either to change their role or allow the role work with the LMS from the Settings page", 'namaste');
					 }	
					 
					 // already enrolled?
					 if(!$error) {
						 $is_enrolled = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
						 	WHERE user_id = %d AND course_id = %d", $student->ID, intval($_GET['course_id'])));
						 if($is_enrolled) $error = __('This user is already enrolled in the course', 'namaste');
					 }
					 
					 // finally, enroll
					 if(empty($error)) {
							$tags = sanitize_text_field($_GET['tags']);		
							// remove spaces for less queries
							$tags = str_replace(array(', ', ' ,'), ',', $tags);					
							$course_id = intval($_GET['course_id']);					 	
					 	
					 		$wpdb -> query($wpdb -> prepare("INSERT INTO ".NAMASTE_STUDENT_COURSES." SET
					 			course_id = %d, user_id = %d, status = 'enrolled', 
					 			enrollment_date = %s, completion_date = '1900-01-01', enrollment_time=%s, comments='', tags=%s",
					 			$course_id, $student->ID, date("Y-m-d", current_time('timestamp')), current_time('mysql'), $tags ));
					 		$success = __('User successfully enrolled in the course', 'namaste');	
					 		
					 		// insert in history
					 		$course = get_post($_GET['course_id']);
					 		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
								user_id=%d, date=CURDATE(), datetime=NOW(), action='enrolled_course', value=%s, num_value=%d, course_id=%d",
								$student->ID, sprintf(__('Enrolled in course %s. Status: %s', 'namaste'), stripslashes($course->post_title), 'enrolled'), 
								$_GET['course_id'], $_GET['course_id']));
					 		
					 		// do_action('namaste_enrolled_course', $student->ID, $_GET['course_id'], true);
					 	  do_action('namaste_admin_enrolled_course', $student->ID, $_GET['course_id'], true);	
					 	  
					 	  // join BP group?
							if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {				
								$bp = get_post_meta($course_id, 'namaste_buddypress', true);
								if(!empty($bp['enroll_group'])) groups_join_group( $bp['enroll_group'], $student->ID);
							}	
					 }	
				}
				
				// change student status
				if(!empty($_GET['change_status'])) {
					if($multiuser_access  == 'view') wp_die(__('You are not allowed to do this.', 'namaste'));
					self :: change_status($_GET['student_id'], $_GET['course_id'], $_GET['status']);					 
					namaste_redirect("admin.php?page=namaste_students&course_id=".intval($_GET['course_id']));					 							 	
				}				
				 	
		 		// select lessons
		 		$_lesson = new NamasteLMSLessonModel();
		 		$lessons = $_lesson -> select($_GET['course_id']);
		 		$lessons = apply_filters('namaste-reorder-lessons', $lessons);	
		 		$lids = array(0);
		 		foreach($lessons as $lesson) $lids[] = $lesson->ID;
		 				 		
		 		// select students
		 		$status_sql = '';
		 		if(!empty($_GET['status']) and $_GET['status']!='any' and in_array($_GET['status'], array('pending', 'enrolled', 'rejected', 'completed'))) { 
		 			$status_sql = $wpdb->prepare(" AND tS.status=%s", $_GET['status']);
		 		}
		 		
            $login_sql = '';
            if(!empty($_GET['user_login'])) {
               $login_sql = $wpdb->prepare(" AND tU.user_login LIKE %s ", '%'.sanitize_text_field($_GET['user_login']).'%');
            }		
             		
            $email_sql = '';
            if(!empty($_GET['user_email'])) {
               $email_sql = $wpdb->prepare(" AND tU.user_email LIKE %s ", '%'.sanitize_text_field($_GET['user_email']).'%');
            }
            
            $tag_sql = '';
            if(!empty($_GET['filter_tags'])) {
            	$tags = explode(', ', sanitize_text_field($_GET['filter_tags']));
            	
            	if(count($tags)) {
            		$tag_sql = " AND (";
	            	$tag_sqls = array();
	            		foreach($tags as $tag) {
								$tag = trim($tag);
	            			$tag_sqls[] = $wpdb->prepare("tags LIKE %s OR tags LIKE %s OR tags LIKE %s OR tags LIKE %s", $tag, "%,".$tag, $tag.',%', '%,'.$tag.',%');
	            		}
	            	$tag_sql .= implode(' OR ', $tag_sqls);	
	            	$tag_sql .= ") ";
            	}            	
            } // end tags
            
            // lesson statuses filters
            $lesson_status_sql = '';
            foreach($lessons as $lesson) {
            	if(!empty($_GET['lesson_status_'.$lesson->ID])) {
            		switch($_GET['lesson_status_'.$lesson->ID]) {
            			case 'not_started':
            				$lesson_status_sql .= $wpdb->prepare(" AND tU.ID NOT IN (SELECT student_id FROM ".NAMASTE_STUDENT_LESSONS." WHERE lesson_id=%d) ", $lesson->ID);
            			break;
            			case 'in_progress':
            				$lesson_status_sql .= $wpdb->prepare(" AND tU.ID IN (SELECT student_id FROM ".NAMASTE_STUDENT_LESSONS." WHERE lesson_id=%d AND status=0  AND pending_admin_approval=0) ", $lesson->ID);
            			break;
            			case 'pending_approval':
            				$lesson_status_sql .= $wpdb->prepare(" AND tU.ID IN (SELECT student_id FROM ".NAMASTE_STUDENT_LESSONS." WHERE lesson_id=%d AND status=0 AND pending_admin_approval=1) ", $lesson->ID);
            			break;
            			case 'completed':
            				$lesson_status_sql .= $wpdb->prepare(" AND tU.ID IN (SELECT student_id FROM ".NAMASTE_STUDENT_LESSONS." WHERE lesson_id=%d AND status=1) ", $lesson->ID);
            			break;
            		}
            	} // end if status filter is set
            } // end foreach lesson
            // end lesson statuses
		 		
		 		$limit_sql = $wpdb->prepare("LIMIT %d, %d ", $offset, $page_limit);
		 		
		 		if(!empty($_GET['export'])) $limit_sql = '';
		 		
				// filter sql in other plugins				
				$filter_sql = apply_filters('namaste-students-filter', '');		 		
		 		
		 		$students = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS tU.*, 
		 			tS.status as namaste_status, tS.grade as grade, tU.display_name as display_name, tS.id as scid,
		 			tS.enrollment_date as enrollment_date, tS.completion_date as completion_date, tS.tags as tags 
			 		FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_COURSES." tS 
			 		ON tS.user_id = tU.ID AND tS.course_id=".intval($_GET['course_id'])." 
			 		$status_sql $login_sql $email_sql $filter_sql $tag_sql  $lesson_status_sql
			 		GROUP BY tU.ID ORDER BY $ob $dir $limit_sql");
		 		$count = $wpdb->get_var("SELECT FOUND_ROWS()");	 	
		 		
				$any_pending = false;
				foreach($students as $student) {
					if($student->namaste_status == 'pending') $any_pending = true;
				}
		 			 		
		 		// select student - to - lesson relations
		 		$completed_lessons = $wpdb->get_results("SELECT * FROM ".NAMASTE_STUDENT_LESSONS."
		 			WHERE lesson_id IN (".implode(',', $lids).")");	
		 			
		 		$use_points_system = get_option('namaste_use_points_system');
				$use_grading_system = get_option('namaste_use_grading_system');		 	
		 				 		
		 		// match to students	
		 		foreach($students	as $cnt=>$student) {
		 			 $student_completed_lessons = $student_incomplete_lessons = array();
		 			 foreach($completed_lessons as $lesson) {
	 			 		if($lesson->student_id == $student->ID) {
 			 				if($lesson->status) $student_completed_lessons[] = $lesson->lesson_id;
 			 				else $student_incomplete_lessons[] = $lesson->lesson_id;
	 			 				
	 			 			// assign the whole relation info
	 			 			$students[$cnt]->relations[$lesson->lesson_id] = $lesson;
	 			 		}
		 			 }	
		 			 
		 			 $students[$cnt]->completed_lessons = $student_completed_lessons;
		 			 $students[$cnt]->incomplete_lessons = $student_incomplete_lessons;
		 		}
		 		
		 		if(!empty($_GET['export'])) {
		 			$newline = namaste_define_newline();
				
					$titlerow = __("Student name", 'namaste').','.__("Student email", 'namaste').',';
					
					foreach($lessons as $lesson) {
						$lesson->post_title = str_replace('"', "'", $lesson->post_title);
						$titlerow .= '"'.stripslashes($lesson->post_title).'",';
					}
					
					$titlerow .= __('Status in course', 'namaste');
					
					if($use_grading_system) {
						$titlerow .= ','.__('Final grade', 'namaste');
					}
					
					
					$rows = array($titlerow);
					
					foreach($students as $student) {
						$row = $student->user_login;
						if($student->user_login != $student->display_name) $row .= ' (' . $student->display_name .')';
						$row .= ',';
						$row .= $student->user_email.',';
						
						foreach($lessons as $lesson) {
							if(in_array($lesson->ID, $student->completed_lessons)): $row .= __('Completed', 'namaste');
							elseif(in_array($lesson->ID, $student->incomplete_lessons)): $row .= __('In progress', 'namaste');
							else: $row .= __('Not started', 'namaste'); 
							endif;
						   if($use_grading_system and !empty($student->relations[$lesson->ID]->grade)) {
						   		 $row .= " / ". sprintf(__('Grade: %s', 'namaste'), $student->relations[$lesson->ID]->grade); 
						   }
							$row .=',';
						}		
						
						switch($student->namaste_status):
							case 'pending': $row .= __('Pending', 'namaste'); break;
							case 'enrolled': $row .= __('Enrolled', 'namaste'); break;
							case 'rejected': $row .= __('Rejected', 'namaste'); break;
							case 'completed': $row .= __('Completed', 'namaste'); break;
							case 'frozen': $row .= __('Frozen', 'namaste'); break;
						endswitch;
						
						if($use_grading_system) {
							$row .= ',' . (empty($student->grade) ? __('n/a', 'namaste') : $student->grade);
						}
						$rows[] = $row;
					}
					
					$csv = implode($newline, $rows);
					
					$now = gmdate('D, d M Y H:i:s') . ' GMT';
	
					header('Content-Type: ' . namaste_get_mime_type());
					header('Expires: ' . $now);
					header('Content-Disposition: attachment; filename="students.csv"');
					header('Pragma: no-cache');
					echo $csv;
					exit;				
		 		}
		 } // end if course selected

		 
    	$dateformat = get_option('date_format');	 
		wp_enqueue_script('thickbox',null,array('jquery'));
	   wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		if(@file_exists(get_stylesheet_directory().'/namaste/manage-students.php')) require get_stylesheet_directory().'/namaste/manage-students.php';
		else require(NAMASTE_PATH."/views/manage-students.php");
	}
	
	// change lesson status - not started (so the record in namatse_student_lessons does not exist),
	// in progress (0 + update with current date), and completed (1 + update with current date)
	static function lesson_status($student_id, $lesson_id, $status) {
		global $wpdb, $user_ID;
		$lesson_id = intval($lesson_id);
      $student_id = intval($student_id);
		
		// security check
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('students_access');
		if( (!current_user_can('namaste_manage') or $multiuser_access == 'view') 
				and $user_ID != $student_id) {
			wp_die(__("You cannot change someone else's status", 'namaste'));
		}
			
		// if status == -1 we have to remove the existing record if any
		if($status == -1) {
			 $wpdb -> query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_LESSONS." 
			 	WHERE student_id = %d AND lesson_id = %d", $student_id, $lesson_id));	
		}
		
		// complete lesson - don't allow "completed" if there are unsatisfied requirements
	  if($status == 1) {
			$_module = new NamasteLMSModuleModel();
			if(!$_module -> is_ready($lesson_id, $student_id, true)) return false; 
			
			NamasteLMSLessonModel :: complete($lesson_id, $student_id);
	  }		
		
		// set as 'in progress'
		if($status == 0 ) {
			 // record exists?
			 $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_LESSONS."
			 	WHERE student_id = %d AND lesson_id = %d", $student_id, $lesson_id));
			 
			 if($exists) {
			 		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_LESSONS." 
			 			SET status=%d, completion_date = %s, completion_time=%s WHERE id=%d", 
			 			$status, date("Y-m-d", current_time('timestamp')), current_time('mysql'), $exists));
			 } 
			 else {
			 		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_LESSONS." SET
			 			lesson_id = %d, student_id = %d, status = %d, completion_date = %s, completion_time=%s",
			 			$lesson_id, $student_id, $status, date("Y-m-d", current_time('timestamp')), current_time('mysql')));
			 }	
		}	
		
		return true;
	} // end lesson_status
	
	// is student enrolled in course?
	static function is_enrolled($student_id, $course_id) {
		global $wpdb;
		$student_id = intval($student_id);
      $course_id = intval($course_id);
		
		$is_enrolled = $wpdb -> get_var( $wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
			WHERE user_id = %d AND course_id = %d AND (status ='enrolled' OR status='completed') ", $student_id, $course_id));
			
		return $is_enrolled;	
	}
	
	// cleanup data about student - course relation
	
	static function cleanup($course_id, $student_id) {
		global $wpdb;
		$student_id = intval($student_id);
      $course_id = intval($course_id);
		
		$wpdb->query( $wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_COURSES." 
		 	WHERE course_id = %d AND user_id=%d", $course_id, $student_id) );
		 	
		 $wpdb->query( $wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_LESSONS." WHERE student_id= %d AND lesson_id IN (SELECT ID FROM {$wpdb->posts} tP JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course' AND tM.meta_value = %d WHERE post_type = 'namaste_lesson');", $student_id, $course_id) );					
		 
		// delete solutions
		$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE student_id=%d
			AND homework_id IN (SELECT id FROM ".NAMASTE_HOMEWORKS." WHERE course_id=%d)", $student_id, $course_id)); 
			
		// delete homework notes
		$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_HOMEWORK_NOTES." WHERE student_id=%d ", $student_id));	
		
		// cleanup module relations?
		if(get_option('namaste_use_modules')) {
		    $wpdb->query( $wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_MODULES." WHERE student_id= %d AND module_id IN 
		       (SELECT ID FROM {$wpdb->posts} tP JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course' 
		       AND tM.meta_value = %d WHERE post_type = 'namaste_module');", $student_id, $course_id) );					
		}
		
		do_action('namaste_cleaned_student', $course_id, $student_id);
								 
		// cleanup exams data? 
		$use_exams = get_option('namaste_use_exams');
		if($use_exams and get_option('namaste_cleanup_exams') == 'yes') {
			// select all exam IDs of related lessons
			$_lesson = new NamasteLMSLessonModel();
			$lessons = $_lesson->select($course_id);
			$exam_ids = array(0);
			foreach($lessons as $lesson) {
				$exam_id = get_post_meta($lesson->ID, 'namaste_required_exam', true);
				if(!empty($exam_id)) $exam_ids[] = $exam_id;
			}
			
			// now delete
			if($use_exams == 'watu') {
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}watu_takings WHERE user_id=%d AND exam_id IN (".implode(',', $exam_ids).")", $student_id));
			}
			
			if($use_exams == 'watupro') {
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}watupro_taken_exams WHERE user_id=%d AND exam_id IN (".implode(',', $exam_ids).")", $student_id));
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}watupro_student_answers WHERE user_id=%d AND exam_id IN (".implode(',', $exam_ids).")", $student_id));
			}
		}  	
	} // end cleanup()
	
	// change student status
	static function change_status($student_id, $course_id, $status) {
		global $wpdb;
		$course_id = intval($course_id);
		$student_id = intval($student_id);
		$status = sanitize_text_field($status);
		
		// if current status is the same do nothing
		$current_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM " . NAMASTE_STUDENT_COURSES." 
			WHERE user_id=%d AND course_id=%d", $student_id, $course_id));
		if($current_status == $status) return false;	
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_COURSES." SET
	 			status=%s, completion_date=%s, completion_time=%s 
	 			WHERE user_id=%d AND course_id=%d", $status, 
	 			date("Y-m-d", current_time('timestamp')), current_time('mysql'), 
	 			$student_id, $course_id));
					 			
		$course = get_post($course_id);
						 			
		 if($status == 'enrolled') {
		 	do_action('namaste_enrollment_approved',$student_id, $course_id);
		 	do_action('namaste_admin_enrolled_course',$student_id, $course_id, true);
		 	$history_msg = sprintf(__('Enrollment in %s has been approved.','namaste'), stripslashes($course->post_title));
		 }
		 else {
		 	do_action('namaste_enrollment_rejected', $student_id, $course_id);
		 	$history_msg = sprintf(__('Enrollment in %s has been rejected.','namaste'), stripslashes($course->post_title));
		 }	
		 
		 // insert in history
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
				user_id=%d, date=CURDATE(), datetime=NOW(), action='enrolled_course', value=%s, num_value=%d, course_id=%d",
				$student_id, $history_msg, $course_id, $course_id));
	} // end change_status
	
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
}
