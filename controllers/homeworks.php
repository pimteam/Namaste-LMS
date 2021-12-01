<?php
class NamasteLMSHomeworkController {
	public static $current_homework_id;
	
	static function submit_solution($in_shortcode = false) {
		global $wpdb, $user_ID, $post;
		$_course = new NamasteLMSCourseModel();
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		if(!self :: submit_solution_prerequizites($course, $lesson, $homework)) return false;
		
		$file_upload_progress = get_option('namaste_file_upload_progress');
		
		// now submit
		if(!empty($_POST['ok'])) {
			if(empty($_POST['content']) and empty($_FILES['files']['tmp_name'][0]) and empty($_POST['solution_files_uploaded'])) wp_die(__('You cannot submit a solution without any text or files.', 'namaste'));			
			
			// avoid duplicates
			$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_HOMEWORKS."
				WHERE student_id=%d AND homework_id=%d AND content=%s", $user_ID, $homework->id,
				$_POST['content']));
			if(!$exists) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_HOMEWORKS." SET
					homework_id=%d, student_id=%d, status='pending', date_submitted=CURDATE(), 
					content=%s",
					$homework->id, $user_ID, $_POST['content']));
				$solution_id = $wpdb->insert_id;	
				
				list($total_size_error, $file_errors, $file_size_errors, $file_not_uploaded_errors) = self :: upload_files($homework, $solution_id);

				// these lines are in case of using Ajax uploads. Then we have stored the errors in session			
				$namaste_file_errors = (!empty($_COOKIE['namaste_file_errors'])) ? @unserialize(stripslashes($_COOKIE['namaste_file_errors'])) : array('', '','','', '');
				if(empty($total_size_error) and !empty($namaste_file_errors[0])) $total_size_error = $namaste_file_errors[0];
				if(empty($file_errors) and !empty($namaste_file_errors[1])) $file_errors = $namaste_file_errors[1];
				if(empty($file_size_errors) and !empty($namaste_file_errors[2])) $file_size_errors = $namaste_file_errors[2];
				if(empty($file_not_uploaded_errors) and !empty($namaste_file_errors[3])) $file_not_uploaded_errors = $namaste_file_errors[3];
				
				// any uploaded files without a solution for this user & homework? We need to update them in case they are uploaded by Ajax
				$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_SOLUTION_FILES." SET solution_id=%d
					WHERE solution_id=0 AND student_id=%d AND homework_id=%d", $solution_id, $user_ID, $homework->id)); 				
			}
			
			do_action('namaste_submitted_solution', $user_ID, $homework->id);
			
			// insert in history
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
				user_id=%d, date=CURDATE(), datetime=NOW(), action='submitted_solution', value=%s, num_value=%d, course_id=%d",
				$user_ID, sprintf(__('Submitted solution to assignment "%s"', 'namaste'), $homework->title), $homework->id, $course->ID));
			
			if(@file_exists(get_stylesheet_directory().'/namaste/solution-submitted.php')) require get_stylesheet_directory().'/namaste/solution-submitted.php';
			else require(NAMASTE_PATH."/views/solution-submitted.php");
		}
		else {			 
			 if(@file_exists(get_stylesheet_directory().'/namaste/submit-solution.php')) require get_stylesheet_directory().'/namaste/submit-solution.php';
			 else require(NAMASTE_PATH."/views/submit-solution.php");
		}		
	}
	
	// change uploads dir
	static function change_upload_dir($dirs) {
		$protected_folder = get_option('namaste_protected_folder');
		if(empty($protected_folder)) return $dirs;
		
		$dirs['subdir'] = '/'.$protected_folder.'/' . self :: $current_homework_id;
   	$dirs['path'] = $dirs['basedir'] . '/'.$protected_folder.'/' . self :: $current_homework_id;
    	$dirs['url'] = $dirs['baseurl'] . '/'.$protected_folder.'/' . self :: $current_homework_id;

    	return $dirs;
	}
	
	// handle file uploads in both cases: via regular form submit and via Ajax.
	// in the second case we will have no solution_id and the regular form submit code will assign files with missing soltuon ID but that user and homework to the latest one 
	static function upload_files($homework = null, $solution_id = 0) {
		global $wpdb, $user_ID;
		
		if(empty($_FILES['files']['name'][0])) {
			return array(null, null, null, null);
		}
				
		// select homework if needed
		$_course = new NamasteLMSCourseModel();		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		if(!self :: submit_solution_prerequizites($course, $lesson, $homework)) return false;
		
		$total_size_error = null;
		$file_errors = $file_size_errors = $file_not_uploaded_errors = array();
		$allowed_file_types = get_option('namaste_allowed_file_types');
		$allowed_file_types = str_replace(' ', '', $allowed_file_types);
		$allowed_extensions = explode(',', $allowed_file_types);			
		
		// check total size and return if it exceeds the limit
		$total_size_limit = get_option('namaste_homework_size_total');
		if(intval($total_size_limit) > 0) {
			$total_size = 0;
			
			foreach($_FILES['files']['tmp_name'] as $cnt => $tmp_name) {
				$total_size += $_FILES['files']['size'][$cnt];
			}
			
			$total_size = round($total_size / 1024);
			
			if($total_size > $total_size_limit) {
				$namaste_file_errors = array($total_size, null, null, null); // return total size error		
				?>
				<script type="text/javascript" >
			  	var d = new Date();
				d.setTime(d.getTime() + (24*3600*1000));
				var expires = "expires="+ d.toUTCString();     				
			  	document.cookie = "namaste_file_errors=<?php echo serialize($namaste_file_errors);?>;" + expires + ";path=/";
			  	</script>
				<?php				 
				return $namaste_file_errors;
			}
		} // end checking size
		
		
		$file_size_limit = get_option('namaste_homework_size_per_file');
		
		if($homework->accept_files and !empty($_FILES['files']['tmp_name'][0])) {		
		
			if ( ! function_exists( 'wp_handle_upload' ) ) {
					    require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}					

			self :: $current_homework_id = $homework->id;
			add_filter( 'upload_dir', array(__CLASS__, 'change_upload_dir') );			
			
					
			foreach($_FILES['files']['tmp_name'] as $cnt => $tmp_name) {
				$file = $_FILES['files']['name'][$cnt];
				
				$filearr = array(
					'name' => $_FILES['files']['name'][$cnt],
					'type' => $_FILES['files']['type'][$cnt],
					'tmp_name' => $_FILES['files']['tmp_name'][$cnt],
					'error' => $_FILES['files']['error'][$cnt],
					'size' => $_FILES['files']['size'][$cnt],
				);
				
				// check extension
				$parts = explode('.', $file);
				$ext = array_pop($parts);
				$ext = strtolower($ext);

				if(!empty($allowed_file_types) and count($allowed_extensions) and !in_array($ext, $allowed_extensions)) {					
					$file_errors[] = $file; 
					continue;
				}
				
				// check individual file size				
				if(intval($file_size_limit) > 0 and round($filearr['size']/1024) > $file_size_limit) {
					$file_size_errors[] = $file; 
					continue;
				}

				$file_blob = $filepath = '';
				$upload_overrides = array( 'test_form' => false );
				
				if(get_option('namaste_store_files_filesystem') != 1) $file_blob = file_get_contents($tmp_name);
				else {													
					$movefile = wp_handle_upload( $filearr, $upload_overrides );
					if ( $movefile && ! isset( $movefile['error'] ) ) {
					    $filepath = $movefile['file'];
					}		
					else echo "Error: ".$movefile['error'];					
				}
				
				if(empty($file_blob) and empty($filepath)) {
					$file_not_uploaded_errors[] = $file;
					continue;
				}
				
				$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_SOLUTION_FILES." SET
					homework_id=%d, student_id=%d, solution_id=%d, file=%s, fileblob=%s, filepath=%s",
					$homework->id, $user_ID, $solution_id, $file, $file_blob, $filepath));						
			}
			
			remove_filter( 'upload_dir', array(__CLASS__, 'change_upload_dir') );
		}	// end uploading
		
		$namaste_file_errors = array($total_size_error, $file_errors, $file_size_errors, $file_not_uploaded_errors);	
		?>
		<script type="text/javascript" >
	  	var d = new Date();
		d.setTime(d.getTime() + (24*3600*1000));
		var expires = "expires="+ d.toUTCString();     				
	  	document.cookie = "namaste_file_errors=<?php echo serialize($namaste_file_errors);?>;" + expires + ";path=/";
	  	</script>
		<?php	
		return array($total_size_error, $file_errors, $file_size_errors, $file_not_uploaded_errors);
	} // end upload_files
	
	// check if user can submit solutoin
	static function submit_solution_prerequizites($course, $lesson, $homework) {
		global $wpdb, $user_ID;
		
		// am I enrolled?
		if(!NamasteLMSStudentModel::is_enrolled($user_ID, $course->ID)) wp_die(__('You are not enrolled in this course!',
			'namaste'));
			
		// have I started this lesson at all?
		$exists = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_LESSONS." 
			WHERE student_id=%d AND lesson_id=%d", $user_ID, $lesson->ID));
		if(!$exists) {
			echo '<p>'.__('You need to read this lesson before you can submit solutions to assignments.', 'namaste').'</p>';
			return false; 
		}		
			
		// unsatisfied lesson completion requirements?
		$not_completed_ids = NamasteLMSLessonModel :: unsatisfied_complete_requirements($lesson);
		if(!empty($not_completed_ids)) {
			 $content = '<p>'.__('Before submitting solutions on this lesson you must complete the following lessons:','namaste').'</p>';			 
			 $content	.= '<ul>';
			
			 foreach($not_completed_ids as $id) {
			 		$not_completed = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE id=%d", $id));
			 		
			 		$content .= '<li><a href="'.get_permalink($id).'">'.$not_completed->post_title.'</a></li>';
			 }					 
			 
			 $content .= '</ul>';
			 echo $content;
			 // self :: mark_accessed();
			 return false;
		}
		
		if($homework->limit_by_date and 
					(current_time('timestamp') < strtotime($homework->accept_date_from.' 00:00:00')
						or current_time('timestamp') > strtotime($homework->accept_date_to.' 23:59:59')) ) {
			$dateformat = get_option('date_format');				
			printf('<p>'.__('Solutions will be accepted between %s and %s.', 'namaste'),
						date_i18n($dateformat, strtotime($homework->accept_date_from)),
						date_i18n($dateformat, strtotime($homework->accept_date_to)) .	'.</p>');
			return false;							
		}
		
		return true;
	} // end submit preprequizites
	
	// teacher views, approves, rejects submitted solutions
	static function view($in_shortcode = false) {
		global $wpdb, $user_ID, $post;
		
		$student_id = empty($_GET['student_id'])?$user_ID : $_GET['student_id'];

		if(!current_user_can('namaste_manage') and $student_id!=$user_ID) wp_die(__('You are not allowed to see these solutions', 'namaste'));
		$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $student_id));
				
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		$multiuser_access = 'all';
		$noexit = ($student_id == $user_ID) ? true : false;
		$multiuser_access = NamasteLMSMultiUser :: check_access('homework_access', $noexit);
		if($multiuser_access == 'own' and $homework->editor_id != $user_ID and $student_id != $user_ID) wp_die(__('You are not allowed to see these solutions', 'namaste'));
		
		// approve or reject solution
		if(!empty($_POST['change_status'])) self::change_solution_status($lesson, $student_id);
		
		$use_grading_system = get_option('namaste_use_grading_system');
		$grades = explode(",", stripslashes(get_option('namaste_grading_system')));
		// give grade on a solution
		if($use_grading_system and !empty($_POST['grade_solution']) and current_user_can('namaste_manage')) {
		   NamasteLMSHomeworkModel :: set_grade($_POST['grade'], $_POST['id']);
		}
		
		// select submitted solutions		
		$solutions = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS."
			WHERE student_id=%d AND homework_id=%d ORDER BY id DESC", $student_id, $homework->id));
			
		// select & match notes for each homework
		$notes = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORK_NOTES." 
		 	WHERE homework_id=%d ORDER BY id", $homework->id));
		 	
		// match notes to solutions. Currently all notes go to all solutions of a given homework, as long as it's from the same student
		foreach($solutions as $cnt=>$solution) {
			$s_notes = array();
			foreach($notes as $note) {
				if($note->homework_id == $solution->homework_id and $note->student_id == $solution->student_id) $s_notes[] = $note;
			}
			
			$solutions[$cnt]->notes = $s_notes;
		} 				
		$manager_mode = true;
		
		 wp_enqueue_script('thickbox',null,array('jquery'));
		 wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		if(@file_exists(get_stylesheet_directory().'/namaste/view-solutions.php')) require get_stylesheet_directory().'/namaste/view-solutions.php';
		else require(NAMASTE_PATH."/views/view-solutions.php");
	}
	
	// get files on a homework solution
	static function solution_files($homework, $solution) {
		global $wpdb;
		if(!$homework->accept_files) return null;
		
		$files = $wpdb->get_results($wpdb->prepare("SELECT id, file FROM ".NAMASTE_SOLUTION_FILES." WHERE solution_id=%d ORDER BY id", $solution->id));
		
		// we must handle also the old format where solution file was part of solution and just one
		if(count($files) == 0 and !empty($solution->file)) {
			return array(
				(object)array("id" => 0, "file"=>$solution->file)
			);
		}

		return $files;
	}
	
	// view everyone's solutions on a homework
	static function view_all() {
		global $wpdb, $user_ID, $post;
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('homework_access');
		if($multiuser_access == 'own' and $homework->editor_id != $user_ID) wp_die(__('You are not allowed to see these solutions', 'namaste'));
		
		$use_grading_system = get_option('namaste_use_grading_system');
		
		// approve or reject solution
		if(!empty($_POST['change_status'])) self::change_solution_status($lesson);
		
		$use_grading_system = get_option('namaste_use_grading_system');
		$grades = explode(",", stripslashes(get_option('namaste_grading_system')));
		// give grade on a solution
		if($use_grading_system and !empty($_POST['grade_solution']) and current_user_can('namaste_manage')) {
			NamasteLMSHomeworkModel :: set_grade($_POST['grade'], $_POST['id']);
		}
		
		// select submitted solutions
		$solutions = $wpdb -> get_results($wpdb->prepare("SELECT tH.*, tU.user_login as user_login 
			FROM ".NAMASTE_STUDENT_HOMEWORKS." tH JOIN {$wpdb->users} tU ON tH.student_id = tU.ID
			WHERE homework_id=%d ORDER BY id DESC", $homework->id));
			
		// select & match notes for each homework
		$notes = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORK_NOTES." 
		 	WHERE homework_id=%d ORDER BY id", $homework->id));
		 	
		// match notes to solutions. Currently all notes go to all solutions of a given homework, as long as it's from the same student
		foreach($solutions as $cnt=>$solution) {
			$s_notes = array();
			foreach($notes as $note) {
				if($note->homework_id == $solution->homework_id and $note->student_id == $solution->student_id) $s_notes[] = $note;
			}
			
			$solutions[$cnt]->notes = $s_notes;
		} 			
			
		$manager_mode = true;	
		$show_everyone = true;
		 wp_enqueue_script('thickbox',null,array('jquery'));
		 wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		if(@file_exists(get_stylesheet_directory().'/namaste/view-solutions.php')) require get_stylesheet_directory().'/namaste/view-solutions.php';
		else require(NAMASTE_PATH."/views/view-solutions.php");
	}
	
	// approve or reject a homework solution
	static function change_solution_status($lesson, $student_id = NULL) {
		global $wpdb, $user_ID;
		
		if(!current_user_can('namaste_manage')) wp_die(__('You are not allowed to do this', 'namaste'));
		
		$solution = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $_POST['solution_id']));
		if(!$student_id)  $student_id = $solution->student_id;
		$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", $solution->homework_id));
		
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('homework_access');
		if($multiuser_access == 'own' and $homework->editor_id != $user_ID) wp_die(__('You are not allowed to see these solutions', 'namaste'));
			
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_HOMEWORKS." SET
			status=%s WHERE id=%d", $_POST['status'], $_POST['solution_id']));
			
		do_action('namaste_change_solution_status', $student_id, $_POST['solution_id'], $_POST['status']);	
		
		// insert in history
		$course_id = get_post_meta($homework->lesson_id, 'namaste_course', true);	
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='solution_processed', value=%s, num_value=%d, course_id=%d",
			$student_id, sprintf(__('Solution to assignment %s was %s', 'namaste'), $homework->title, $_POST['status']), $_POST['solution_id'], $course_id));
		
		// award points?
		if($_POST['status']=='approved' and get_option('namaste_use_points_system')) {			
			if($homework->award_points) {
				NamastePoint :: award($student_id, $homework->award_points, sprintf(__('Received %d points for completing assignment "%s".', 'namaste'), 
					$homework->award_points, $homework->title), 'homework', $homework->id);
			}
		}
		
		// maybe complete the lesson if the status is approved 				
		if($_POST['status']=='approved' and NamasteLMSLessonModel::is_ready($lesson->ID, $student_id)) {
			NamasteLMSLessonModel::complete($lesson->ID, $student_id);
		}		
	} // end change_solution_status
	
	// download solution file
	static function download_solution() {
		global $wpdb, $user_ID;
		
		$solution = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", intval($_GET['id'])));
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_SOLUTION_FILES." 
			WHERE solution_id=%d AND id=%d", $solution->id, intval($_GET['file_id'])));
		
		if(empty($solution->fileblob) and empty($file->fileblob) and empty($file->filepath)) wp_die(__("There is nothing to download.", 'namaste'));
		
		if(!current_user_can('namaste_manage') and $user_ID != $solution->student_id) wp_die(__('You can download only your own solutions.', 'namaste'));
		
		// select fileblob
		if(!empty($solution->file)) {
			$fileblob = $solution->fileblob;
			$file_name = $solution->file;
		}
		
 		if(!empty($file->fileblob)) {
 			$fileblob = $file->fileblob;
 			$file_name = $file->file;
 		}
 		
 		if(!empty($file->filepath)) {
 			$fileblob = file_get_contents($file->filepath);
			$file_name = $file->file;
 		}
				
		// send download headers
		header('Content-Disposition: attachment; filename="'.$file_name.'"');				
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header("Content-Length: " . strlen($fileblob)); 
		
		echo $fileblob;
	}
}