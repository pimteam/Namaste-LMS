<?php
class NamasteLMSHomeworkModel {
	// custom post type Homework	
	static function register_homework_type() {		
		$homework_slug = get_option('namaste_homework_slug');
	   if(empty($lesson_slug)) $homework_slug = 'namaste-homework';
	   
		$args = array(
			"label" => __("Namaste! Homeworks", 'namaste'),
			"labels" => array
				(
					"name"=>__("Homeworks", 'namaste'), 
					"singular_name"=>__("Homework", 'namaste'),
					"add_new_item"=>__("Add New Homework", 'namaste'),
					'bp_activity_admin_filter' => __( 'Homeworks', 'namaste' ),
	            'bp_activity_front_filter' => __( 'Homeworks', 'namaste' ),
	            'bp_activity_new_post' => __( '%1$s created a new <a href="%2$s">Homework</a>', 'namaste' ),
				   'bp_activity_comments_admin_filter' => __( 'Comments about Homeworks', 'namaste' ),
				   'bp_activity_comments_front_filter' => __( 'Homework Comments', 'namaste' ),
				   'bp_activity_new_comment'  => __( '%1$s commented on the <a href="%2$s">Homework</a>', 'namaste' ),
				),
			"public"=> true,
			"show_ui"=>false,
			"has_archive"=>false,
			"rewrite"=> array("slug"=>$homework_slug, "with_front"=>false),
			"description"=>__("This will create a new post with homeworks for a lesson in your Namaste! LMS.",'namaste'),
			"supports"=>array("title", 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats', 'buddypress-activity'),
			'bp_activity' => array(
            'action_id'             => 'new_homework',
            'contexts'              => array( 'activity', 'member' ),
            'comment_action_id'     => 'new_homework_comment',
            'position'              => 70,
        ),
			"taxonomies"=>array("category", 'post_tag'),
			"show_in_nav_menus"=>'false',
			'show_in_menu' => false,
		);
		register_post_type( 'namaste_homework', $args );
	}		
	
	static function manage() {
		global $wpdb, $user_ID;
		$_course = new NamasteLMSCourseModel();
		$_lesson = new NamasteLMSLessonModel();
		
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('homework_access');
				
		// select courses
		$courses = $_course -> select();
		$courses = apply_filters('namaste-homeworks-select-courses', $courses);
		
		// if course and lesson are selected, populate two variables for displaying titles etc
		if(!empty($_GET['course_id'])) $this_course = $_course -> select($_GET['course_id']);
		if(!empty($_GET['lesson_id'])) $this_lesson = $_lesson -> select($_GET['course_id'], 'single', $_GET['lesson_id']);
		
		// sanitize / prepare vars
		$_GET['course_id'] = intval( $_GET['course_id'] ?? 0 );
		$_GET['lesson_id'] = intval( $_GET['lesson_id'] ?? 0 );
		$accept_files = empty($_POST['accept_files']) ? 0 : 1;
      $award_points = intval(@$_POST['award_points']);
      $auto_grade_lesson = empty($_POST['auto_grade_lesson']) ? 0 : 1;
      $self_approving = empty($_POST['self_approving']) ? 0 : 1;
      $auto_approve = empty($_POST['auto_approve']) ? 0 : 1;
		
		switch(@$_GET['do']) {
			case 'add':
				// apply permissions from other plugins 
				do_action('namaste-check-permissions', 'course', $_GET['course_id']);
				if(!empty($_POST['ok']) and check_admin_referer('namaste_homework')) {
						$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HOMEWORKS." SET
						course_id=%d, lesson_id=%d, title=%s, description=%s, accept_files=%d, 
						award_points=%d, editor_id=%d, limit_by_date=%d, accept_date_from=%s, 
						accept_date_to=%s, auto_grade_lesson=%d, self_approving=%d, auto_approve=%d",
						$_GET['course_id'], $_GET['lesson_id'], sanitize_text_field($_POST['title']), 
						wp_kses_post($_POST['description']), $accept_files, $award_points,						 
						$user_ID, intval(@$_POST['limit_by_date']), sanitize_text_field($_POST['accept_date_from']), 
						sanitize_text_field($_POST['accept_date_to']), $auto_grade_lesson, $self_approving, $auto_approve));	
						
						$id = $wpdb->insert_id;		
						
						do_action('namaste_add_homework', $id);		
						
						self :: create_homework_post($_GET['lesson_id']);
					
						namaste_redirect("admin.php?page=namaste_homeworks&course_id=$_GET[course_id]&lesson_id=$_GET[lesson_id]");
				}			
				
				namaste_enqueue_datepicker();	
			
				if(@file_exists(get_stylesheet_directory().'/namaste/homework.php')) require get_stylesheet_directory().'/namaste/homework.php';
				else require(NAMASTE_PATH."/views/homework.php");
			break;		
			
			case 'edit':
				// apply permissions from other plugins 
				do_action('namaste-check-permissions', 'homework', $_GET['id']);
				
				if($multiuser_access == 'own') {
					$homework = self::select($wpdb->prepare(' WHERE id=%d ', $_GET['id']));
					$homework = $homework[0];
					if($homework->editor_id != $user_ID) wp_die(__('You are not allowed to edit or delete this assignment', 'namaste'));
				}				
				
				if(!empty($_POST['del']) and check_admin_referer('namaste_homework')) {
					 self::delete($_GET['id']);
					 
					 namaste_redirect("admin.php?page=namaste_homeworks&course_id=".intval($_GET['course_id'])."&lesson_id=".intval($_GET['lesson_id']));
				}			
			
				if(!empty($_POST['ok']) and check_admin_referer('namaste_homework')) {
						$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_HOMEWORKS." SET
						course_id=%d, lesson_id=%d, title=%s, description=%s, accept_files=%d, award_points=%d,
						limit_by_date=%d, accept_date_from=%s, accept_date_to=%s, auto_grade_lesson=%d, 
						self_approving=%d, auto_approve=%d
						WHERE id=%d",
						$_GET['course_id'], $_GET['lesson_id'], sanitize_text_field($_POST['title']), 
						wp_kses_post($_POST['description']), $accept_files, $award_points, 
						intval(@$_POST['limit_by_date']), sanitize_text_field($_POST['accept_date_from']), 
						sanitize_text_field($_POST['accept_date_to']), $auto_grade_lesson, $self_approving, $auto_approve,
						intval($_GET['id'])));		
						
						do_action('namaste_save_homework', $_GET['id']);					
					
						namaste_redirect("admin.php?page=namaste_homeworks&course_id=".intval($_GET['course_id']).'&lesson_id='.intval($_GET['lesson_id']));
				}			
				
				// select homework
				$homework = self::select($wpdb->prepare(' WHERE id=%d ', $_GET['id']));
				$homework = $homework[0];
				
				namaste_enqueue_datepicker();	
			
				if(@file_exists(get_stylesheet_directory().'/namaste/homework.php')) require get_stylesheet_directory().'/namaste/homework.php';
				else require(NAMASTE_PATH."/views/homework.php");
			break;			
			
			default:
				// if course is selected, find lessons
				if(!empty($_GET['course_id'])) {
					$lessons = $_lesson->select($_GET['course_id'], 'array', null, '');
				}			
			
				// list existing homeworks if course and lesson are selected
				if(!empty($_GET['course_id']) and !empty($_GET['lesson_id'])) {
					// apply permissions from other plugins - this allows other plugins to die here if user can't access the course
					do_action('namaste-check-permissions', 'course', $_GET['course_id']);
					
					$own_sql = '';
					if($multiuser_access == 'own') $own_sql = $wpdb->prepare(" AND tH.editor_id=%d ", $user_ID);
					
					$homeworks = $wpdb->get_results($wpdb->prepare("SELECT tH.*, COUNT(tS.id) as solutions 
						FROM ".NAMASTE_HOMEWORKS." tH LEFT JOIN ".NAMASTE_STUDENT_HOMEWORKS." tS ON tS.homework_id = tH.id
						WHERE tH.course_id=%d AND tH.lesson_id=%d	$own_sql 
						GROUP BY tH.id ORDER BY tH.title", 
						$_GET['course_id'], $_GET['lesson_id']));
				} 
				
				if(@file_exists(get_stylesheet_directory().'/namaste/homeworks.php')) require get_stylesheet_directory().'/namaste/homeworks.php';
				else require(NAMASTE_PATH."/views/homeworks.php");
			break;
		}
	}
	
	// shows homeworks assigned to a lesson
	static function lesson_homeworks($in_shortcode = false, $atts = null) {
		 global $wpdb, $user_ID, $post, $wp;
		 
		 // not my own homeworks? I need to have manage caps then
		 $manager_mode = false;
		 if($user_ID != $_GET['student_id']) {		 	
		 		if(!current_user_can('namaste_manage')) wp_die(__('You are not allowed to see this page', 'namaste'));		 		 		
		 }
		 $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", intval($_GET['student_id'])));
		 if(current_user_can('namaste_manage')) $manager_mode = true;	
		 
		 
		 // select lesson
		 $lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", intval($_GET['lesson_id'])));

		 if(empty($lesson->ID)) return __('Invalid lesson ID.', 'namaste'); 			 
		 
		 // course ID
		 $course_id = get_post_meta($lesson->ID, 'namaste_course', true);
		 
		 // self approve / mark completed? a homework?
		 if(!empty($_POST['mark_completed']) and wp_verify_nonce($_POST['_wpnonce'], 'namaste_mark_solution')) {
		 	 // ensure the $homework allows marking as completed
		 	 $self_approving = $wpdb->get_var($wpdb->prepare("SELECT self_approving FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", intval($_POST['mark_completed'])));
		 	 if($self_approving and $user_ID) {
				// do not submit again if there is any
				$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_HOMEWORKS." 
					WHERE student_id=%d AND homework_id=%d AND status='approved'", $user_ID, intval($_POST['mark_completed'])));		 	 	
		 	 	
		 	 	 // insert a solution without real content but with a completed status
		 	 	 if(!$exists) {
 	 			 	 	 $wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_HOMEWORKS." SET student_id=%d, homework_id=%d, status='approved', date_submitted=%s",
			 	 	 	$user_ID, intval($_POST['mark_completed']), date('Y-m-d', current_time('timestamp'))));
		 	 	 }
				 
				 namaste_redirect(home_url( $wp->request ));
		 	 } // 
		 } // end markng a self-approving solution as completed
		 
		 // select the homeworks assigned to this lesson
		 $homeworks = self :: select($wpdb->prepare("WHERE lesson_id = %d", $lesson->ID), $atts); 
		 $ids = array(0);
		 foreach($homeworks as $homework) $ids[] = $homework->id;
		 $id_sql = implode(", ", $ids);
		 
		 // select & match student solutions for each homework
		 $solutions = $wpdb -> get_results( $wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS."
		 	WHERE student_id = %d AND homework_id IN ($id_sql) ORDER BY id", intval($_GET['student_id'])) );	
		 	
		 // select & match notes for each homework
		 $notes = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORK_NOTES." 
		 	WHERE homework_id IN ($id_sql) AND student_id = %d", intval($_GET['student_id'])));	
		 	
		 	
		 foreach($homeworks as $cnt=>$homework) {
		 		$homework_solutions = array();
		 		$homework_notes = array();
		 		
		 		foreach($solutions as $solution) {
		 			if($solution -> homework_id == $homework->id) $homework_solutions[] = $solution; 
		 		}
		 		
		 		foreach($notes as $note) {
		 			if($note->homework_id == $homework->id) $homework_notes[] = $note;
		 		}
		 		
		 		// define homework status - if even 1 solution is approved, the homework status is true
		 		$homeworks[$cnt]->status = false;
		 		foreach($homework_solutions as $solution) {
		 			if($solution->status == 'approved') $homeworks[$cnt]->status = true;
		 		}
		 		
		 		$homeworks[$cnt]->solutions = $homework_solutions;
		 		$homeworks[$cnt]->notes = $homework_notes;
		 }
		 
		 $dateformat = get_option('date_format');		 
		 
		 wp_enqueue_script('thickbox',null,array('jquery'));
		 wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		 if(@file_exists(get_stylesheet_directory().'/namaste/lesson-homeworks.php')) require get_stylesheet_directory().'/namaste/lesson-homeworks.php';
		  else require(NAMASTE_PATH."/views/lesson-homeworks.php");
	}
	
	// select homeworks
	static function select($where, $atts = null) {
		global $wpdb;
		
		$dir = (empty($atts['order']) or $atts['order'] == 'first') ? 'ASC' : 'DESC';
		
		$homeworks = $wpdb -> get_results("SELECT * FROM ".NAMASTE_HOMEWORKS." $where ORDER BY id $dir");
		
		return $homeworks;
	}
	
	// delete homework
	// for the moment delete only the DB record, but for the future 
	// consider deleting the solutions along with their files
	static function delete($id) {
			global $wpdb;
			$id = intval($id);
			
			$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", $id));
	}
	
	// full select homework - with lesson and course (used in few places)
	static function full_select($id) {
		global $wpdb;
		$_course = new NamasteLMSCourseModel();		
		$_lesson = new NamasteLMSLessonModel();
		$id = intval($id);
		
		// select this homework and lesson
		$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS."
			WHERE id=%d", $id));			
		// select course
		$course = $_course->select($homework->course_id);		
		// select lesson
		$lesson = $_lesson->select($course->ID, 'single', $homework->lesson_id);	
		
		return array($homework, $course, $lesson);
	}
	
	// grade the homework. If required, set the homework grade as lesson grade too
	// @param $grade - string, the grade
	// @param $id - int, the solution ID
	static function set_grade($grade, $id) {
	   global $wpdb;
	   $grade = sanitize_text_field($grade);
	   $id = intval($id);
	   
	   $wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_HOMEWORKS." SET grade=%s WHERE id=%d", $grade, $id));
      do_action('namaste_graded_homework', $id, $grade);
      
      // now check if the homework should also grade the lesson
      $solution = $wpdb->get_row($wpdb->prepare("SELECT student_id, homework_id FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $id));
      $homework = $wpdb->get_row($wpdb->prepare("SELECT lesson_id, auto_grade_lesson FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", $solution->homework_id));
      if($homework->auto_grade_lesson) {
         $wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_LESSONS." SET grade=%s 
            WHERE lesson_id=%d AND student_id=%d", $grade, $homework->lesson_id, $solution->student_id));
         do_action('namaste_graded_lesson', $solution->student_id, $homework->lesson_id, $grade);   
      }
	} // end set_grade()
	
	// creates custom Homework post with the shortcode for that lesson IF it doesn't already exist
	static function create_homework_post($lesson_id) {
		global $wpdb;
		
		// check if post exists
		$post_exists = $wpdb->get_var("SELECT ID FROM {$wpdb->posts}
			WHERE post_status = 'publish' AND post_date < NOW()
			AND post_content LIKE '%[namaste-assignments lesson_id=\"".$lesson_id."\"%' ORDER BY ID DESC"); 
			
		if(empty($post_exists)) {
			$lesson = get_post($lesson_id);
			
			$my_post = array(
				'post_type' => 'namaste_homework',
			  'post_title'    => sprintf(__('Homework for Lesson %s', 'namaste'), stripslashes($lesson->post_title)),
			  'post_content'  => '[namaste-assignments lesson_id="'.$lesson_id.'"]',
			  'post_status'   => 'publish',
			  'post_category' => array( 8,39 )
			);
 		
			wp_insert_post( $my_post );
		}	
	} // end create_homework_post
}
