<?php 
class NamasteLMSNoteModel {
	static function add_note($in_shortcode = false) {
		global $wpdb, $user_ID, $post;
		
		// select lesson
		$lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $_GET['lesson_id']));	
		
		// select student
		$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $_GET['student_id']));
		
		// select course		
		$course_id = get_post_meta($lesson->ID, 'namaste_course', true);
		$course = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $course_id));
		
		// select homework
		$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", $_GET['homework_id']));
		
		if(!empty($_POST['ok']) and !empty($_POST['note'])) {
			// add the note
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HOMEWORK_NOTES." SET
				homework_id=%d, student_id=%d, teacher_id=%d, note=%s, datetime=NOW()",
				$homework->id, $student->ID, $user_ID, $_POST['note']));			
				
			do_action('namaste_added_homework_note', $student->ID, $homework->id, $_POST['note']);	
			
			// redirect back
			if($in_shortcode) {
				$permalink = get_permalink($post->ID);
			   $params = array('lesson_id' => $_GET['lesson_id']);
				$target_url = add_query_arg( $params, $permalink );
				namaste_redirect($target_url);
			} 			
			else namaste_redirect("admin.php?page=namaste_lesson_homeworks&lesson_id=".$lesson->ID."&student_id=".$student->ID);
		}		
		
		if(@file_exists(get_stylesheet_directory().'/namaste/add-note.php')) require get_stylesheet_directory().'/namaste/add-note.php';
		else require(NAMASTE_PATH."/views/add-note.php");
	}
}