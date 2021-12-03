<?php
// student reviews on courses
class NamasteLMSReviews {
	// admin management of reviews
	public static function manage() {
		global $wpdb;
		
		// approve a review
		if(!empty($_POST['approve']) and check_admin_referer('namaste_reviews')) {
			if(class_exists('NamastePROReviews') and method_exists(['NamastePROReviews', 'has_access'])) NamastePROReviews :: has_access($_POST['id']);
			
			$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_COURSE_REVIEWS." SET is_approved=1 WHERE id=%d", intval($_POST['id']) ));
		}
		
		// delete a review
		if(!empty($_POST['del']) and check_admin_referer('namaste_reviews')) {
			if(class_exists('NamastePROReviews') and method_exists(['NamastePROReviews', 'has_access'])) NamastePROReviews :: has_access($_POST['id']);
			
			$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_COURSE_REVIEWS." WHERE id=%d", intval($_POST['id']) ));
		}
		
		// list reviews
		$course_filter = $status_filter = $other_filter_sql = '';
		$other_filter_sql = apply_filters('namaste-filter-reviews', $other_filter_sql);	
		
		if(!empty($_GET['course_id'])) $course_filter = $wpdb->prepare(" AND course_id=%d ", intval($_GET['course_id']));
		if(isset($_GET['status']) and $_GET['status'] !== '') $status_filter = $wpdb->prepare(" AND is_approved=%d ", intval($_GET['status']));	
		
		$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
		$page_limit = 20;
		
		$reviews = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tR.*, tP.post_title as course_name, tU.display_name as user_name 
			FROM ".NAMASTE_COURSE_REVIEWS." tR JOIN {$wpdb->posts} tP ON tP.ID = tR.course_id AND tP.post_type = 'namaste_course'
			JOIN {$wpdb->users} tU ON tU.ID = tR.student_id
			WHERE 1 $course_filter $status_filter $other_filter_sql ORDER BY tR.id DESC LIMIT %d, %d", $offset, $page_limit));	
		
		$count = $wpdb->get_var("SELECT FOUND_ROWS()");		
		
		// select courses for the drop-down
		$_course = new NamasteLMSCourseModel();
		$courses = $_course->select();
		
		$date_format = get_option('date_format');
		$time_format = get_option('date_format');
		
		if(@file_exists(get_stylesheet_directory().'/namaste/reviews.html.php')) include get_stylesheet_directory().'/namaste/reviews.html.php';
		else include(NAMASTE_PATH."/views/reviews.html.php");
	} // end manage			
	
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
		
		$rating_options = self :: get_rating_options();
		
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
	public static function list_reviews($course_id, $number = 0, $show = 'user_login') {
		global $wpdb;
							
		$limit_sql = $number ? " LIMIT ".intval($number) : '';							
							
		$reviews = $wpdb->get_results($wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS tR.*, tP.post_title as course_name, tU.$show as user_name 
			FROM ".NAMASTE_COURSE_REVIEWS." tR JOIN {$wpdb->posts} tP ON tP.ID = tR.course_id AND tP.post_type = 'namaste_course'
			JOIN {$wpdb->users} tU ON tU.ID = tR.student_id
			WHERE tR.course_id=%d AND is_approved=1 ORDER BY tR.id DESC $limit_sql", intval($course_id)));
			
		$date_format = get_option('date_format');
		$time_format = get_option('date_format');	
		
		if(@file_exists(get_stylesheet_directory().'/namaste/list-reviews.html.php')) include get_stylesheet_directory().'/namaste/list-reviews.html.php';
		else include(NAMASTE_PATH."/views/list-reviews.html.php");
	} // end list_reviews
	
	// a helper to display the stars for a review
	public static function stars($rating) {
		$rating_options = self :: get_rating_options();
	   
	   $output = '';
	   		
		foreach($rating_options as $r => $option) {
			$class = $r <= $rating ? 'filled' : 'empty';
			$output .= '<span class="dashicons dashicons-star-'.$class.'" style="color:#ffb900 !important;""></span>';
		}
		
		return $output;
	} // end stars()
	
	// safety
	private static function prepare_vars(&$vars) {		
		$vars['course_id'] = intval($vars['course_id']);
		$vars['rating'] = intval($vars['namaste_rating']);
		$vars['review'] = wp_kses_post($vars['review']);
	}
	
	private static function get_rating_options() {
		return [
		  1 => __('Poor', 'namaste'),
		  2 => __('Accedptable', 'namaste'),
		  3 => __('Average', 'namaste'),
		  4 => __('Good', 'namaste'),
		  5 => __('Great', 'namaste'),
		];
	} 		
}