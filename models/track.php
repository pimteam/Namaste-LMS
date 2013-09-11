<?php
// tracks visits to lessons, courses etc
// used for basic stats and to allow more advanced reports by other plugins
class NamasteTrack {
	static function visit($what, $id, $user_id) {
		global $wpdb;
		
		if(empty($user_id)) return false;
		
		$field = ($what == 'course') ? 'course_id' : 'lesson_id';
		
		// already visited today?
		$exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM ".NAMASTE_VISITS." 
			WHERE user_id=%d AND $field=%d AND date = CURDATE()", $user_id, $id));
			
		if($exists) {
			$wpdb->query( $wpdb->prepare("UPDATE ".NAMASTE_VISITS." SET
				visits = visits+1 WHERE id=%d", $exists));
		}	
		else {
			$wpdb->query( $wpdb->prepare("INSERT INTO ".NAMASTE_VISITS." SET
				$field=%d, user_id=%d, date=CURDATE(), visits=1", $id, $user_id));
		}		
	} // end visit
	
	// gets the number of unique and total visits
	static function get_visits($what, $id) {
		global $wpdb;
		$field = ($what == 'course') ? 'course_id' : 'lesson_id';		
		
		$visits = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_VISITS." 
					WHERE $field=%d GROUP BY user_id", $id));
		$total = $unique = 0;
		foreach($visits as $visit) {
			$total += $visit->visits;
			$unique++;
		}	 
		
		return array($total, $unique);
	} // end get_visits
}