<?php
// student reviews on courses
class NamasteReviews {
	// submits a review 
	// access for the student is checked before calling the function
	public static function submit($vars) {
		global $wpdb;
		
		self :: prepare_vars($vars);	
		
		$hold_reviews = get_post_meta($vars['course_id'], 'namaste_hold_reviews', true);	
		$is_approved = empty($hold_reviews) ? 1 : 0;
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_REVIEWS." SET 
			student_id=%d, course_id=%d, rating=%d, datetime=%s, review=%s, is_approved=%d",
			$vars['student_id'], $vars['course_id'], $vars['rating'], current_time('mysql'), $vars['review'], $is_approved)));
	} // end submit
	
	// display the review form
	public static function display_form()	{
	} // end display_form
	
	// shows the reviews on a course
	public static function list_reviews() {
	} // end list_reviews
	
	// safety
	private static function prepare_vars(&$vars) {
		$vars['student_id'] = intval($vars['student_id']);
		$vars['course_id'] = intval($vars['course_id']);
		$vars['rating'] = intval($vars['rating']);
		$vars['review'] = wp_kses_post($vars['review']);
	}
}