<?php
// handles a "to-do" page for admin / manager and student
class NamasteToDo {
	// pending course enrollments
	// pending lesson completions that require manual approval
	// pending homework approvals
	static function manager_todo() {
		global $wpdb;
		$_course = new NamasteLMSCourseModel();
		$_lesson = new NamasteLMSLessonModel();
		
		// select courses
		$courses = $_course -> select();
		$courses = apply_filters('namaste-homeworks-select-courses', $courses);
		
		$cids = array(0);
		foreach($courses as $course) $cids[] = $course->ID;
		
		// select pending course enrollments
		$enrollments = $wpdb->get_results("SELECT COUNT(tSC.id) as cnt, tC.post_title as course,
			tC.ID as course_id 
			FROM ".NAMASTE_STUDENT_COURSES." tSC JOIN {$wpdb->posts} tC ON tC.ID = tSC.course_id
			WHERE tC.ID IN (".implode(',', $cids).") AND tSC.status='pending'
			GROUP BY tC.ID ORDER BY tC.post_title");
			
		
		// select pending lesson approvals
		$approvals = $wpdb->get_results("SELECT tU.user_login as user_login, tU.ID as student_id,
			tL.post_title as lesson, tSL.lesson_id as lesson_id
			FROM ".NAMASTE_STUDENT_LESSONS." tSL
			JOIN {$wpdb->posts} tL ON tL.ID = tSL.lesson_id
			JOIN {$wpdb->users} tU ON tU.ID = tSL.student_id	
			JOIN {$wpdb->postmeta} tM ON tM.meta_key = 'namaste_course'	
				AND tM.meta_value IN (".implode(', ', $cids).") AND tM.post_id = tSL.lesson_id
			WHERE tSL.status=0 AND tSL.pending_admin_approval=1
			ORDER BY tSL.ID");
			
		// select pending homework approvals	
		$homeworks = $wpdb->get_results("SELECT tH.title as title, tU.user_login as user_login,
			tSH.homework_id as homework_id, tSH.id as id, tSH.student_id as student_id
			FROM ".NAMASTE_STUDENT_HOMEWORKS." tSH
			JOIN ".NAMASTE_HOMEWORKS." tH ON tH.id = tSH.homework_id 
				AND tH.course_id IN (".implode(',', $cids).")
			JOIN {$wpdb->users} tU ON tU.ID = tSH.student_id
			WHERE tSH.status='pending' ORDER BY tSH.id");
		
		if(@file_exists(get_stylesheet_directory().'/namaste/managers-todo.html.php')) include get_stylesheet_directory().'/namaste/managers-todo.html.php';
		else include(NAMASTE_PATH."/views/managers-todo.html.php");
	} // end managers_todo()
}