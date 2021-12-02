<?php
// student reviews on courses
class NamasteLMSReviews {
	// submits a review 
	// access for the student is checked before calling the function
	public static function submit($vars) {
		global $wpdb;
		$student_id = get_current_user_id();
		
		self :: prepare_vars($vars);	
		
		$hold_reviews = get_post_meta($vars['course_id'], 'namaste_hold_reviews', true);	
		$is_approved = empty($hold_reviews) ? 1 : 0;
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_COURSE_REVIEWS." SET 
			student_id=%d, course_id=%d, rating=%d, datetime=%s, review=%s, is_approved=%d",
			$student_id, $vars['course_id'], $vars['rating'], current_time('mysql'), $vars['review'], $is_approved));
	} // end submit
	
	// display the review form
	public static function display_form($course_id)	{
		$course_id = intval($course_id);
		
		$rating_options = [
		  1 => __('Poor', 'namaste'),
		  2 => __('Accedptable', 'namaste'),
		  3 => __('Average', 'namaste'),
		  4 => __('Good', 'namaste'),
		  5 => __('Great', 'namaste'),
		];		
		
		$content = '';
		$editor_id = 'namaste_review_course_'.$course_id;
		$settings =   array(
		    'wpautop' => true, // use wpautop?
		    'media_buttons' => true, // show insert/upload button(s)
		    'textarea_name' => 'review', // set the textarea name to something different, square brackets [] can be used here
		    'textarea_rows' => get_option('default_post_edit_rows', 10), // rows="..."
		    'tabindex' => '',
		    'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
		    'editor_class' => '', // add extra class(es) to the editor textarea
		    'teeny' => true, // output the minimal editor config used in Press This
		    'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
		    'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
		    'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
		);
		
		if(@file_exists(get_stylesheet_directory().'/namaste/review-course.html.php')) include get_stylesheet_directory().'/namaste/review-course.html.php';
		else include(NAMASTE_PATH."/views/review-course.html.php");
	} // end display_form
	
	// shows the reviews on a course
	public static function list_reviews() {
	} // end list_reviews
	
	// safety
	private static function prepare_vars(&$vars) {		
		$vars['course_id'] = intval($vars['course_id']);
		$vars['rating'] = intval($vars['namaste_rating']);
		$vars['review'] = wp_kses_post($vars['review']);
	}
}