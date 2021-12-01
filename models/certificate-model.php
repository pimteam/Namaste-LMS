<?php
class NamasteLMSCertificateModel {
	function add($vars) {
		global $wpdb, $user_ID;
		
		$this->prepare_vars($vars);
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_CERTIFICATES." SET
			course_ids=%s, title=%s, content=%s, editor_id=%d, 
			has_expiration=%d, expiration_period=%s, expired_message=%s, expiration_mode=%s, expiration_date=%s", 
			'|'.implode("|", $vars['course_ids']).'|', $vars['title'], $vars['content'], $user_ID,
			$vars['has_expiration'], $vars['expiration_period'], $vars['expired_message'], $vars['expiration_mode'], $vars['expiration_date']));
			
		return $wpdb->insert_id;	
	}
	
	function edit($vars, $id) {
		global $wpdb;
		
		$id = intval($id);
		$this->prepare_vars($vars);
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_CERTIFICATES." SET
			course_ids=%s, title=%s, content=%s, 
			has_expiration=%d, expiration_period=%s, expired_message=%s, expiration_mode=%s, expiration_date=%s 
			WHERE id=%d", 
			'|'.implode("|", $vars['course_ids']).'|', $vars['title'], $vars['content'], 
			$vars['has_expiration'], $vars['expiration_period'], $vars['expired_message'], $vars['expiration_mode'], $vars['expiration_date'], $id));
	}
	
	// prepare and sanitize variables
	function prepare_vars(&$vars) {		
		if(!is_array($vars['course_ids']) or empty($vars['course_ids'])) $vars['course_ids'] = array(0);
		$vars['course_ids'] = namaste_int_array($vars['course_ids']);
		$vars['title'] = sanitize_text_field($vars['title']);
		$vars['content'] = namaste_strip_tags($vars['content']);
		$vars['has_expiration'] = empty($vars['has_expiration']) ? 0 : 1;
		if(!is_numeric($vars['expiration_period_num'])) $vars['has_expiration'] = false;
		$vars['expiration_period'] = $vars['expiration_period_num'].' '.$vars['expiration_period_period'];	
		$vars['expiration_mode'] = empty($vars['expiration_mode']) ? 'period' : sanitize_text_field($vars['expiration_mode']);
		$vars['expired_message'] = namaste_strip_tags($vars['expired_message']);
		$vars['expiration_date'] = sanitize_text_field($vars['expiration_date']);
	}
	
	function delete($id) {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_CERTIFICATES." WHERE id=%d", $id));		
	}
	
	// return acquired certificates for a student
	// along with completed courses
	function student_certificates($student_id) {
		global $wpdb;
		
		$certificates = $wpdb->get_results($wpdb->prepare("SELECT tC.*, tM.id as my_id, tM.date as date 
			FROM ".NAMASTE_CERTIFICATES." tC JOIN ".NAMASTE_STUDENT_CERTIFICATES." tM ON tM.certificate_id = tC.id
			WHERE tM.student_id=%d ORDER BY tM.date DESC", $student_id));			
			
		// select courses to match to certificates
		$_course = new NamasteLMSCourseModel();
		$courses = $_course->select();
		
		foreach($certificates as $cnt => $certificate) {
			$c_courses = array();
			
			foreach($courses as $course) {
				if(strstr($certificate -> course_ids, "|".$course->ID."|")) {
					$c_courses[] = "<a href='".get_permalink($course->ID)."' target='_blank'>".$course->post_title."</a>";
				}
			}
			
			$certificates[$cnt]->courses = implode(", ", $c_courses);
		}
		
		return $certificates;
	}
	
	// when course is completed, assign any associated certificates
	function complete_course($course_id, $student_id) {
		global $wpdb;
		
		$_course = new NamasteLMSCourseModel();
			
		$certificates = $wpdb->get_results("SELECT * FROM ".NAMASTE_CERTIFICATES." 
			WHERE course_ids LIKE '%|$course_id|%' ");
			
		foreach($certificates as $certificate) {
			// see if the other courses are completed
			$courses_completed = true;
			$course_ids = explode("|", $certificate->course_ids);
			foreach($course_ids as $cid) {
				if(empty($cid) or $cid == $course_id) continue; 
				if(!$_course -> is_ready($cid, $student_id)) $courses_completed = false;
			}
			
			// assign this certificate
			if($courses_completed) {
				$wpdb->query($wpdb->prepare("INSERT IGNORE INTO ".NAMASTE_STUDENT_CERTIFICATES." SET
					student_id=%d, certificate_id=%d, date=%s", $student_id, $certificate->id, date("Y-m-d")));
					
				do_action('namaste_achieved_certificate', $student_id, $certificate->id);	
			}
		} // end foreach certificate	
	} // end complete_course
}