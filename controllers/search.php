<?php
class NamasteLMSSearchController {
	// when Namaste search is done we have to search only in courses and/or lessons (if user is enrolled)
   static function pre_get_posts($query) {
   	global $wpdb, $user_ID;   	
   	if(empty($_GET['namaste_search'])) return $query;
  
   	// search in Namaste. If user is not logged in, limit the $post_type to namaste_course, otherwise include also namaste_lesson
   	if(is_user_logged_in()) $post_types = array('namaste_course', 'namaste_lesson');
   	else $post_types = array('namaste_course');
   	
   	add_filter('posts_where', array(__CLASS__, 'filter_where'));	  
   	
   	$query->set( 'post_type', $post_types );
   	return $query;
   } // end pre_get_posts filter
    
    
   // filters the posts based on the drop-down selection AND the user's enrollment
   static function filter_where($where) {
		global $wpdb, $user_ID;
		
		if(empty($_GET['namaste_search'])) return $where;

		$post_ids = array(-1);		
		
		// course limit?
		if(!empty($_GET['namaste_course_id'])) {
			$post_ids[] = intval($_GET['namaste_course_id']);			
			
			// if I am enrolled, add the lessons
			$is_enrolled = $wpdb -> get_var( $wpdb->prepare("SELECT id FROM ".$wpdb->prefix. "namaste_student_courses
			WHERE user_id = %d AND course_id = %d", $user_ID, $_GET['namaste_course_id']));
			
			if($is_enrolled) {
				if(empty($_GET['namaste_lesson_id'])) {
					$_lesson = new NamasteLMSLessonModel();
					$lessons = $_lesson->select($_GET['namaste_course_id']);
					foreach($lessons as $lesson) $post_ids[] = $lesson->ID;
				}
				else {
					// add the lesson if it really belongs to the course
					$course_id = get_post_meta($_GET['namaste_lesson_id'], 'namaste_course', true);					
					if($course_id == $_GET['namaste_course_id']) $post_ids[] = $_GET['namaste_lesson_id'];
				}
			}							
		}
		else {
			// but still add all course IDs because we can search in all courses contents 			
			$course_ids = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} 
				WHERE post_type='namaste_course' AND post_status = 'publish' ");
			foreach($course_ids as $id) $post_ids[] = $id->ID;				
			
			// limit lessons to courses I'm enrolled in
			$cids = array(-1);
			
			$courses = $wpdb->get_results($wpdb->prepare("SELECT tP.ID as ID FROM {$wpdb->posts} tP 
			JOIN ".$wpdb->prefix. "namaste_student_courses tSC ON tP.ID = tSC.course_id 
			AND (tSC.status='enrolled' or tSC.status = 'completed')
			WHERE tSC.user_id=%d", $user_ID));
			foreach($courses as $course) $cids[] = $course->ID; 
			$course_id_sql = implode(',', $cids);
			
			$lessons = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course'
			AND tM.meta_value IN ($course_id_sql)			
			WHERE post_type = 'namaste_lesson'  AND (post_status='publish' OR post_status='draft')");
			
			foreach($lessons as $lesson) $post_ids[] = $lesson->ID;
		}		
		
		// actually add the where
		if(!empty($post_ids)) {			
			$where .= " AND ID IN (".implode(',', $post_ids).") ";			
		}   	
   	
   	return $where;
   } 
   
   // create search form
   static function form() {
   	global $wpdb, $user_ID;
   	
   	$current_lessons = array();
   	
   	// select all courses
   	$courses = $wpdb->get_results("SELECT ID, post_title, ID as post_id FROM {$wpdb->posts}
			WHERE post_type = 'namaste_course'  AND (post_status='publish' OR post_status='draft')
			ORDER BY post_title");
   	
   	// select lessons in courses I'm enrolled to
		if(is_user_logged_in()) {	
			$cids = array(-1);
			$enrolled_courses = $wpdb->get_results($wpdb->prepare("SELECT tP.ID as ID FROM {$wpdb->posts} tP 
			JOIN ".$wpdb->prefix. "namaste_student_courses tSC ON tP.ID = tSC.course_id 
			AND (tSC.status='enrolled' or tSC.status = 'completed')
			WHERE tSC.user_id=%d", $user_ID));
			foreach($courses as $course) $cids[] = $course->ID; 
			$course_id_sql = implode(',', $cids);
			
			$lessons = $wpdb->get_results("SELECT ID, post_title, tM.meta_value as course_id FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course'
			AND tM.meta_value IN ($course_id_sql)			
			WHERE post_type = 'namaste_lesson'  AND (post_status='publish' OR post_status='draft')");
			
			// if coure is selected by GET
	   	if(!empty($_GET['namaste_course_id'])) {
	   		foreach($lessons as $lesson) {
	   			if($lesson->course_id == $_GET['namaste_course_id']) $current_lessons[] = $lesson;
				}
			}
			
			// match lessons to courses
	   	foreach($courses as $cnt=>$course) {
	   		$course_lessons = array();
	   		foreach($lessons as $lesson) {
	   			if($lesson->course_id == $course->ID) $course_lessons[] = $lesson;
	   		}
	   		
	   		$courses[$cnt]->lessons = $course_lessons;
	   	}
		}
   	
   	if(@file_exists(get_stylesheet_directory().'/namaste/search-form.html.php')) require get_stylesheet_directory().'/namaste/search-form.html.php';
		else require(NAMASTE_PATH."/views/search-form.html.php");
	}
}