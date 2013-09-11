<?php 
class NamasteLMSNoteModel {
	static function add_note() {
		global $wpdb, $user_ID;
		
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
			namaste_redirect("admin.php?page=namaste_lesson_homeworks&lesson_id=".$lesson->ID."&student_id=".$student->ID);
		}		
		
		require(NAMASTE_PATH."/views/add-note.php");
	}
}