<?php
defined( 'ABSPATH' ) or exit;

class NamasteLMSLessonsController {	
	// called by the namaste-condition shortcode. Returns the content depending on if the user has completed the lesson
	static function is_completed_shortcode($atts, $content) {
		global $user_ID, $post;
		
		// figure out course ID - comes from $atts or is the ID of the current post
		$lesson_id = absint( $atts['lesson_id'] ?? $post->ID ?? 0 );
		if(empty($lesson_id)) return '';
		
		if( isset($atts['is_lesson_completed']) and $atts['is_lesson_completed'] == 1) {			
			if(!is_user_logged_in()) return "";
			// returns the content only if the user is enrolled
			if(NamasteLMSLessonModel :: is_completed($lesson_id, $user_ID))  return apply_filters('namaste_content', $content);
		}
		else {
			// returns the content only if the user is NOT enrolled
			if(!is_user_logged_in()) return apply_filters('namaste_content', $content);
			if(!NamasteLMSLessonModel :: is_completed($lesson_id, $user_ID)) return apply_filters('namaste_content', $content);
		}
		
		return '';
	} // end is_enrolled_shortcode
}
