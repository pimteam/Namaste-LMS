<?php
class NamasteLMSGradebookController {
	// @param $in_shortcode boolean: when true we have published the gradebook with shortcode and it's in "view only" mode
	// @param @public boolean: when false we have to limit the gradebook accordingly to user permissions
	// @param $atts - the shortcode attributes when the function is called by the [namaste-gradebook] shortcode
	static function manage($in_shortcode = false, $public = false, $atts = null) {
		global $wpdb, $user_ID;
		$_course = new NamasteLMSCourseModel();
		$_lesson = new NamasteLMSLessonModel();
				// select grades
		$grades = explode(",", stripslashes(get_option('namaste_grading_system')));
		
		// select courses
		$courses = $_course -> select();
		$courses = apply_filters('namaste-homeworks-select-courses', $courses);
		
		if(!$public and !is_user_logged_in()) {
			_e('You need to be logged in to view this content.', 'namaste');
			return false;
		}
		
		// if course is selected, select in progress students in the course, 
		// select lessons and homeworks in it too
		if(!empty($_GET['course_id'])) {
			if(!$public and !current_user_can('namaste_manage')) {
				// in this case students see only their own gradebook		
				return self :: my_gradebook($_GET['course_id']);
			}			
			$students_sql = '';
			if(!$in_shortcode or !$public) do_action('namaste-check-permissions', 'course', $_GET['course_id']);
			$this_course = $_course -> select($_GET['course_id']);			
			$lessons = $_lesson->select($_GET['course_id']);
			$lessons = apply_filters('namaste-reorder-lessons', $lessons);	
			$lids = array(0);
			foreach($lessons as $lesson) $lids[] = $lesson->ID;
			
			$students = $wpdb->get_results($wpdb->prepare("SELECT tU.*, tS.status as namaste_status, tS.grade as course_grade 
		 		FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_COURSES." tS 
		 		ON tS.user_id = tU.ID AND tS.course_id=%d AND (tS.status='enrolled' OR status='completed')
		 		$students_sql
		 		ORDER BY user_nicename", $_GET['course_id']));	
		 	$stids =array(0);
		 	foreach($students as $student) $stids[] = $student->ID;		 	
		 	
		 	if(!$in_shortcode or !$public) $students = apply_filters('namaste-gradebook-students', $students, $_GET['course_id']);
		 				
			// select student-lessons and student-homeworks for this course so we can have the grades			
			$student_lessons = $wpdb->get_results("SELECT * FROM ".NAMASTE_STUDENT_LESSONS."
				WHERE lesson_id IN (".implode(',',$lids).") AND student_id IN (".implode(',', $stids).")
				ORDER BY id");
				
			$student_homeworks = $wpdb->get_results("SELECT tSH.*, tH.lesson_id as lesson_id FROM ".NAMASTE_STUDENT_HOMEWORKS." tSH
				JOIN ".NAMASTE_HOMEWORKS." tH ON tH.id = tSH.homework_id
				WHERE tH.lesson_id IN (".implode(',',$lids).") AND tSH.student_id IN (".implode(',', $stids).") 
				ORDER BY id"); 	
			
			// now match everything together so we have it ready for the table
			foreach($students as $cnt=>$student) {
				$lesson_grades = array();
				$lesson_ids = array();
				foreach($lessons as $lesson) {
					foreach($student_lessons as $student_lesson) {
						if($student_lesson->student_id == $student->ID and $student_lesson->lesson_id == $lesson->ID) {
							// get homework grades
							$homework_grades = array();
							$lesson_ids[] = $lesson->ID;
							foreach($student_homeworks as $student_homework) {								
								if($student_homework->student_id != $student->ID) continue;
								if($student_homework->lesson_id != $lesson->ID) continue;
								if(empty($student_homework->grade)) continue;
								
								$homework_grades[] = $student_homework->grade;
							}	// end foreach student_homeworks						
							
							$lesson_grades[]= array('homework_grades' => $homework_grades, "final_grade" => $student_lesson->grade, 
								'lesson_id'=>$student_lesson->lesson_id);
						} // end student & lesson match
					} // end foreach $student_lessons
				} // end foreach $lesson
				
				$students[$cnt]->lesson_grades = $lesson_grades;
				$students[$cnt]->lesson_ids = $lesson_ids;
			} // end foreach $student
		} // end if $_GET[course_id]
		 
		if(@file_exists(get_stylesheet_directory().'/namaste/gradebook.html.php')) require get_stylesheet_directory().'/namaste/gradebook.html.php';
		else require(NAMASTE_PATH."/views/gradebook.html.php");
	} // end manage();
	
	// gradebook for the current user
	// @param $course_id can be passed to remove the selector and override $_GET
	static function my_gradebook($course_id = 0, $in_shortcode = false) {
		global $wpdb, $user_ID, $wp;
		$_course = new NamasteLMSCourseModel();
		$_lesson = new NamasteLMSLessonModel();
		if($course_id) $_GET['course_id'] = $course_id;		
		$_GET['course_id'] = intval($_GET['course_id']);
		$current_url = home_url(add_query_arg(array(),$wp->request));
		
		// select my courses
		$courses = $wpdb->get_results($wpdb->prepare("SELECT tC.* FROM {$wpdb->posts} tC
		JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tSC.course_id = tC.ID AND (tSC.status = 'enrolled' OR tSC.status = 'completed')
		AND tSC.user_id=%d
		WHERE post_type = 'namaste_course'  AND (post_status='publish' OR post_status='draft')
		GROUP BY tC.ID ORDER BY post_title", $user_ID));
		$cids = array();
		foreach($courses as $course) $cids[] = $course->ID;
		
		// make sure we are only passing course_id in which user is enrolled
		if(!empty($_GET['course_id']) and in_array($_GET['course_id'], $cids)) {
			$this_course = $_course -> select($_GET['course_id']);
			$lessons = $wpdb->get_results($wpdb->prepare("SELECT tP.*, tSL.grade as grade FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course'
			AND tM.meta_value = %d
			LEFT JOIN ".NAMASTE_STUDENT_LESSONS." tSL ON tSL.student_id=%d AND tSL.lesson_id=tP.ID
			WHERE post_type = 'namaste_lesson'  AND (post_status='publish' OR post_status='draft') 
			ORDER BY post_title",  $_GET['course_id'], $user_ID));
			$lessons = apply_filters('namaste-reorder-lessons', $lessons);	
			
			// select all assignments
			$homeworks = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE course_id=%d ORDER BY id", $this_course->ID));
			$hids = array(0);
			foreach($homeworks as $homework) $hids[] = $homework->id;
			
			// select all my solutions
			$solutions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS." 
				WHERE student_id=%d AND homework_id IN (".implode(',', $hids).") ORDER BY id DESC", $user_ID));
			
			// match solutions to assignments
			foreach($homeworks as $cnt=>$homework) {
				$homework_solutions = array();
				foreach($solutions as $solution) {
					if($homework->id == $solution->homework_id) $homework_solutions[] = $solution;
				} 
				
				$homeworks[$cnt]->solutions = $homework_solutions;
			}	
			
			// match assignments to my lessons 
			foreach($lessons as $cnt=>$lesson) {
				$lesson_homeworks = array();
				foreach($homeworks as $homework) {
					if($homework->lesson_id == $lesson->ID) $lesson_homeworks[] = $homework;
				}
				
				$lessons[$cnt]->homeworks = $lesson_homeworks;
			}
		}
		 
		if(@file_exists(get_stylesheet_directory().'/namaste/my-gradebook.html.php')) require get_stylesheet_directory().'/namaste/my-gradebook.html.php';
		else require(NAMASTE_PATH."/views/my-gradebook.html.php");
	} // end my_gradebook
	
	// calculate grade for a course based on the lesson grades. This function is called when course is completed 
	// if automated course grading is selected. It calculates the average grade based on assigning points to each grade based on its rank in the system
	// then doing avg() and assigning the grade that matches the avg position.
	static function auto_grade_course($course_id, $student_id) {
		global $wpdb;
		
		if(get_option('namaste_use_grading_system') == '') return false;

		$auto_grade = get_post_meta($course_id, 'namaste_auto_grade', true);
		if(!$auto_grade) return false;
		
		$_lesson = new NamasteLMSLessonModel();
		$lessons = $_lesson->select($course_id);
		if(!sizeof($lessons)) return false;
			
		$grades_points = self :: grades_points();
		
		$total = $num_graded_lessons = 0;
		foreach($lessons as $lesson) {
			$lesson_grade = $wpdb->get_var($wpdb->prepare("SELECT grade FROM ".NAMASTE_STUDENT_LESSONS." 
				WHERE student_id=%d AND lesson_id=%d", $student_id, $lesson->ID));
			if(!empty($lesson_grade)) {
				$num_graded_lessons++;
				$lesson_grade = trim($lesson_grade);
				$points = intval(@$grades_points[$lesson_grade]);
				$total += $points;
			}	
		} // end foreach lesson
		
		if(!$num_graded_lessons) return false; // if no graded lessons we won't grade the course
		
		// calc average position
		$avg = round($total / $num_graded_lessons);
		update_option('namaste_testing', "TOTAL $total NUM: $num_graded_lessons");
		
		$grades = get_option('namaste_grading_system');		
		$grades = explode(',', $grades);
		$grades = array_reverse($grades);
		$target_grade = trim($grades[$avg]);
		
		// assign it
		if(empty($target_grade)) return false;
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_COURSES." SET grade=%s WHERE course_id=%d AND user_id=%d", $target_grade, $course_id, $student_id));
		do_action('namaste_graded_course', $student_id, $course_id, $target_grade);
	} // end calc_course_grade
	
	// helper function that gets the grades and assigns points to each of them accordingly to the grade rank 
	static function grades_points() {
		$grades = get_option('namaste_grading_system');		
		$grades = explode(',', $grades);
		$grades_points = array();	
		 
		// reorder array so highest grade gets most points and lowest gets zero
		$grades = array_reverse($grades);
		
		foreach($grades as $points => $grade) {
			$grade = trim($grade);
			$grades_points[$grade] = $points;
		} 
		// print_r($grades_points);
		return $grades_points;
	}
	
	// only view gradebook (called by shortcode)
	static function view($course_id, $public = true, $atts = null) {
	   $_GET['course_id'] = $course_id;
	   self :: manage(true, $public, $atts);
	}
}