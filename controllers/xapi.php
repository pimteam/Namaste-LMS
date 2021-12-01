<?php
// integrates Namaste! LMS with the WP Experience API plugin
// https://wordpress.org/plugins/wp-experience-api/
class NamasteXAPI {
	// manage xAPI options
	static function options() {
		if(!empty($_POST['ok']) and check_admin_referer('namaste_xapi')) {
			$options = array(
				'enrolled_course' => empty($_POST['enrolled_course']) ? 0 : 1,
				'completed_course' => empty($_POST['completed_course']) ? 0 : 1,
				'exited_course' => empty($_POST['exited_course']) ? 0 : 1,
				'started_lesson' => empty($_POST['started_lesson']) ? 0 : 1,
				'completed_lesson' => empty($_POST['completed_lesson']) ? 0 : 1,
				'submitted_solution' => empty($_POST['submitted_solution']) ? 0 : 1,
				'solution_approved' => empty($_POST['solution_approved']) ? 0 : 1,
				'solution_rejected' => empty($_POST['solution_rejected']) ? 0 : 1,
			);			
			update_option('namaste_xapi', $options);
		}
		
		$options = get_option('namaste_xapi');
		
		include(NAMASTE_PATH . '/views/xapi-options.html.php');
	}	

	static function register_triggers() {
		if(!class_exists('WP_Experience_API')) return false;
		
		// make all conditional, i.e. hook only if chosen so in Namaste! xAPI Settings page
		$options = get_option('namaste_xapi');
		
		### Courses ###
		// enrolled course
		if(!empty($options['enrolled_course'])) {
			WP_Experience_API::register( 'enrolled_course', array(
				'hooks' => array( 'namaste_enrolled_course', 'namaste_admin_enrolled_course' ),
				'num_args' => array( 'namaste_enrolled_course' => 3, 'namaste_admin_enrolled_course' => 3 ),
				'process' => function( $hook, $args ) {
					// args parameter should return $user_id, $achievement_id, $this_trigger, $site_id, $args
					$student_id = get_current_user_id();
					if ( isset( $args[0] ) ) $student_id = absint( $args[0] );
			
					if ( !empty( $args[1] ) ) $course_id = absint( $args[1] );
					if(empty($course_id)) return false;				
					
					$course = get_post($course_id);
					
					$status = @$args[2];
					if(!$status) return false;
			
					$statement = array(
								'verb' => array(
									'id' => 'http://adlnet.gov/expapi/verbs/registered',
									'display' => array( 'en-US' => 'registered' ),
								),
								'object' => array(
									'id' => get_permalink($course_id),
									'definition' => array(
										'name' => array(
											'en-US' => $course->post_title . ' | ' . get_bloginfo( 'name' ),
										),
										'description' => array(
											'en-US' => $course->post_title,
										),
										'type' => 'http://adlnet.gov/expapi/activities/course',
									)
								),
								'context_raw' => self :: context_raw(),		
								'timestamp_raw' => date( 'c' ),
								'user' => $student_id
							);
					
					$statement = self :: add_author($student_id, $statement);
					
					return $statement;
				} // end process
			) ); // end enrolled course
		}
		
		// completed course
		if(!empty($options['completed_course'])) {
			WP_Experience_API::register( 'completed_course', array(
				'hooks' => array( 'namaste_completed_course' ),
				'num_args' => array( 'namaste_completed_course' => 2 ),
				'process' => function( $hook, $args ) {
					// args parameter should return $user_id, $achievement_id, $this_trigger, $site_id, $args
					$student_id = get_current_user_id();
					if ( isset( $args[0] ) ) $student_id = absint( $args[0] );
			
					if ( !empty( $args[1] ) ) $course_id = absint( $args[1] );
					if(empty($course_id)) return false;				
					
					$course = get_post($course_id);
			
					$statement = array(
								'verb' => array(
									'id' => 'http://adlnet.gov/expapi/verbs/completed',
									'display' => array( 'en-US' => 'completed' ),
								),
								'object' => array(
									'id' => get_permalink($course_id),
									'definition' => array(
										'name' => array(
											'en-US' => $course->post_title . ' | ' . get_bloginfo( 'name' ),
										),
										'description' => array(
											'en-US' => $course->post_title,
										),
										'type' => 'http://adlnet.gov/expapi/activities/course',
									)
								),
								'context_raw' => self :: context_raw(),		
								'timestamp_raw' => date( 'c' ),
								'user' => $student_id
							);
					
					$statement = self :: add_author($student_id, $statement);
					
					return $statement;
				} // end process
			) ); // end completed course
		}
		
		// exited course - unenrolled or cleaned up by admin
		if(!empty($options['exited_course'])) {
			WP_Experience_API::register( 'exited_course', array(
				'hooks' => array( 'namaste_cleaned_student' ),
				'num_args' => array( 'namaste_cleaned_student' => 2 ),
				'process' => function( $hook, $args ) {				
					// args parameter should return $user_id, $achievement_id, $this_trigger, $site_id, $args
					$student_id = get_current_user_id();
					if ( isset( $args[1] ) ) $student_id = absint( $args[1] );
			
					if ( !empty( $args[0] ) ) $course_id = absint( $args[0] );
					if(empty($course_id)) return false;				
					
					$course = get_post($course_id);
			
					$statement = array(
								'verb' => array(
									'id' => 'http://adlnet.gov/expapi/verbs/exited',
									'display' => array( 'en-US' => 'exited' ),
								),
								'object' => array(
									'id' => get_permalink($course_id),
									'definition' => array(
										'name' => array(
											'en-US' => $course->post_title . ' | ' . get_bloginfo( 'name' ),
										),
										'description' => array(
											'en-US' => $course->post_title,
										),
										'type' => 'http://adlnet.gov/expapi/activities/course',
									)
								),
								'context_raw' => self :: context_raw(),		
								'timestamp_raw' => date( 'c' ),
								'user' => $student_id
							);
					
					$statement = self :: add_author($student_id, $statement);
					
					return $statement;
				} // end process
			) ); // end exited course
		}
		
		### Lessons ###
		// started lesson
		if(!empty($options['started_lesson'])) {
			WP_Experience_API::register( 'started_lesson', array(
				'hooks' => array( 'namaste_started_lesson' ),
				'num_args' => array( 'namaste_started_lesson' => 2 ),
				'process' => function( $hook, $args ) {				
					// args parameter should return $user_id, $achievement_id, $this_trigger, $site_id, $args
					$student_id = get_current_user_id();
					if ( isset( $args[0] ) ) $student_id = absint( $args[0] );
			
					if ( !empty( $args[1] ) ) $lesson_id = absint( $args[1] );
					if(empty($lesson_id)) return false;				
					
					$lesson = get_post($lesson_id);
			
					$statement = array(
								'verb' => array(
									'id' => 'http://adlnet.gov/expapi/verbs/initialized',
									'display' => array( 'en-US' => 'started' ),
								),
								'object' => array(
									'id' => get_permalink($lesson_id),
									'definition' => array(
										'name' => array(
											'en-US' => $lesson->post_title . ' | ' . get_bloginfo( 'name' ),
										),
										'description' => array(
											'en-US' => $lesson->post_title,
										),
										'type' => 'http://adlnet.gov/expapi/activities/lesson',
									)
								),
								'context_raw' => self :: context_raw(),		
								'timestamp_raw' => date( 'c' ),
								'user' => $student_id
							);
					$statement = self :: add_author($student_id, $statement);					
					
					return $statement;
				} // end process
			) ); 
		} // end started lesson
		
		// started lesson
		if(!empty($options['completed_lesson'])) {
			WP_Experience_API::register( 'completed_lesson', array(
				'hooks' => array( 'namaste_completed_lesson' ),
				'num_args' => array( 'namaste_completed_lesson' => 2 ),
				'process' => function( $hook, $args ) {				
					// args parameter should return $user_id, $achievement_id, $this_trigger, $site_id, $args
					$student_id = get_current_user_id();
					if ( isset( $args[0] ) ) $student_id = absint( $args[0] );
			
					if ( !empty( $args[1] ) ) $lesson_id = absint( $args[1] );
					if(empty($lesson_id)) return false;				
					
					$lesson = get_post($lesson_id);
			
					$statement = array(
								'verb' => array(
									'id' => 'http://adlnet.gov/expapi/verbs/completed',
									'display' => array( 'en-US' => 'completed' ),
								),
								'object' => array(
									'id' => get_permalink($lesson_id),
									'definition' => array(
										'name' => array(
											'en-US' => $lesson->post_title . ' | ' . get_bloginfo( 'name' ),
										),
										'description' => array(
											'en-US' => $lesson->post_title,
										),
										'type' => 'http://adlnet.gov/expapi/activities/lesson',
									)
								),
								'context_raw' => self :: context_raw(),		
								'timestamp_raw' => date( 'c' ),
								'user' => $student_id
							);
					$statement = self :: add_author($student_id, $statement);					
					
					return $statement;
				} // end process
			) ); 
		} // end completed lesson
		
		### Homework ###
		// submitted solution
		if(!empty($options['submitted_solution'])) {
			WP_Experience_API::register( 'submitted_solution', array(
				'hooks' => array( 'namaste_submitted_solution' ),
				'num_args' => array( 'namaste_submitted_solution' => 2 ),
				'process' => function( $hook, $args ) {		
					global $wpdb;		
					// args parameter should return $user_id, $achievement_id, $this_trigger, $site_id, $args
					$student_id = get_current_user_id();
					if ( isset( $args[0] ) ) $student_id = absint( $args[0] );
			
					if ( !empty( $args[1] ) ) $homework_id = absint( $args[1] );
					if(empty($homework_id)) return false;				
					
					$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . NAMASTE_HOMEWORKS." WHERE id=%d", $homework_id));
			
					$statement = array(
								'verb' => array(
									'id' => 'http://adlnet.gov/expapi/verbs/responded',
									'display' => array( 'en-US' => 'submitted a solution to' ),
								),
								'object' => array(
									'id' => get_permalink($homework->lesson_id),
									'definition' => array(
										'name' => array(
											'en-US' => stripslashes($homework->title),
										),
										'description' => array(
											'en-US' => stripslashes($homework->title),
										),
										'type' => 'http://adlnet.gov/expapi/activities/assessment',
									)
								),
								'context_raw' => self :: context_raw(),									
								'timestamp_raw' => date( 'c' ),
								'user' => $student_id
							);
					$statement = self :: add_author($student_id, $statement);					
					
					return $statement;
				} // end process
			) ); 
		} // end submitted solution
		
		// solution approved or rejected
		if(!empty($options['solution_approved']) or  !empty($options['solution_rejected'])) {
			
			WP_Experience_API::register( 'processed_solution', array(
				'hooks' => array( 'namaste_change_solution_status' ),
				'num_args' => array( 'namaste_change_solution_status' => 3 ),
				'process' => function( $hook, $args ) {		
					global $wpdb;		
					// args parameter should return $user_id, $achievement_id, $this_trigger, $site_id, $args
					$student_id = get_current_user_id();
					if ( isset( $args[0] ) ) $student_id = absint( $args[0] );
			
					if ( !empty( $args[1] ) ) $solution_id = absint( $args[1] );
					if(empty($solution_id)) return false;		
					
					$status = @$args[2];
					
					if(empty($status)) return false;		
					$options = get_option('namaste_xapi');
					if($status == 'approved' and empty($options['solution_approved'])) return false;					
					if($status == 'rejected' and empty($options['solution_rejected'])) return false;
					
					$homework_id = $wpdb->get_var($wpdb->prepare("SELECT homework_id FROM " . NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $solution_id));
					$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . NAMASTE_HOMEWORKS." WHERE id=%d", $homework_id));
					
					if($status == 'approved') {
						$verb = 'passed';
						$description = 'got solution approved to';
					}
					else {
						$verb = 'failed';
						$description = 'got solution rejected to';
					}
			
					$statement = array(
								'verb' => array(
									'id' => 'http://adlnet.gov/expapi/verbs/'.$verb,
									'display' => array( 'en-US' => $description ),
								),
								'object' => array(
									'id' => get_permalink($homework->lesson_id),
									'definition' => array(
										'name' => array(
											'en-US' => stripslashes($homework->title),
										),
										'description' => array(
											'en-US' => stripslashes($homework->title),
										),
										'type' => 'http://adlnet.gov/expapi/activities/assessment',
									)
								),
								'context_raw' => self :: context_raw(),									
								'timestamp_raw' => date( 'c' ),
								'user' => $student_id
							);
					$statement = self :: add_author($student_id, $statement);					
					
					return $statement;
				} // end process
			) ); 
		} // end solution approved/rejected
		
	} // end register triggers
	
	// adds author to statement
	static function add_author($student_id, $statement) {
		$student = get_userdata($student_id);		
		$user = array(
			'objectType' => 'Agent',
			'name' => $student->display_name,
			'mbox' => $student->user_email,
		);		
		$statement = array_merge( $statement, array( 'actor' => $user ) );
		return $statement;
	}
	
	// return raw context
	static function context_raw() {
		$context = array(
			'extensions' => array(
				'http://id.tincanapi.com/extension/browser-info' => array( 'user_agent' => $_SERVER['HTTP_USER_AGENT'] ),
				'http://nextsoftwaresolutions.com/xapi/extensions/referer' => @$_SERVER['HTTP_REFERER'],
			),
			'platform' => defined( 'CTLT_PLATFORM' ) ? constant( 'CTLT_PLATFORM' ) : 'unknown'
		);
		return $context;							
	} // end context_rar
}