<?php
class NamasteLMSGradebookController {
	static function manage() {
		global $wpdb;
		$_course = new NamasteLMSCourseModel();
		$_lesson = new NamasteLMSLessonModel();
		
		// select grades
		$grades = explode(",", stripslashes(get_option('namaste_grading_system')));
		
		// select courses
		$courses = $_course -> select();
		
		// if course is selected, select in progress students in the course, 
		// select lessons and homeworks in it too
		if(!empty($_GET['course_id'])) {
			$this_course = $_course -> select($_GET['course_id']);
			$lessons = $_lesson->select($_GET['course_id']);
			$lids = array(0);
			foreach($lessons as $lesson) $lids[] = $lesson->ID;
			
			$students = $wpdb->get_results($wpdb->prepare("SELECT tU.*, tS.status as namaste_status, tS.grade as course_grade 
		 		FROM {$wpdb->users} tU JOIN ".NAMASTE_STUDENT_COURSES." tS 
		 		ON tS.user_id = tU.ID AND tS.course_id=%d AND (tS.status='enrolled' OR status='completed')
		 		ORDER BY user_nicename", $_GET['course_id']));	
		 	$stids =array(0);
		 	foreach($students as $student) $stids[] = $student->ID;		 	
			
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
		
		include(NAMASTE_PATH."/views/gradebook.html.php"); 
	} // end manage();
	
	// gradebook for the current user
	static function my_gradebook() {
		global $wpdb, $user_ID;
		$_course = new NamasteLMSCourseModel();
		$_lesson = new NamasteLMSLessonModel();
		
		// select my courses
		$courses = $wpdb->get_results($wpdb->prepare("SELECT tC.* FROM {$wpdb->posts} tC
		JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tSC.course_id = tC.ID AND (tSC.status = 'enrolled' OR tSC.status = 'completed')
		AND tSC.user_id=%d
		WHERE post_type = 'namaste_course'  AND (post_status='publish' OR post_status='draft')
		ORDER BY post_title", $user_ID));
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
				
				$homeworks[$cnt]->solutions = $solutions;
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
		
		include(NAMASTE_PATH."/views/my-gradebook.html.php"); 
	} // end my_gradebook
}