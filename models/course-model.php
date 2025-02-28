<?php
// possible course statuses: pending, rejected, enrolled, completed
class NamasteLMSCourseModel {	
	// custom post type Course	
	static function register_course_type() {
		
		$course_slug = get_option('namaste_course_slug');
	   if(empty($course_slug)) $course_slug = 'namaste-course';
	   $has_archive = get_option('namaste_show_courses_in_blog');
	   	  	   
		$args = array(
			"label" => __("Namaste! Courses", 'namaste'),
			"labels" => array
				(
					"name"=>__("Courses", 'namaste'), 
					"singular_name"=>__("Course", 'namaste'),
					"add_new_item"=>__("Add New Course", 'namaste')
				),
			"public"=> true,
			"show_ui"=>true,
			"has_archive"=> $has_archive ? true : false,
			"rewrite"=> array("slug"=>$course_slug, "with_front"=>false),
			"description"=>__("This will create a new course in your Namaste! LMS.",'namaste'),
			"supports"=>array("title", 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats'),
			"taxonomies"=>array("category", 'post_tag'),
			"show_in_nav_menus" => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_rest' => true,
			'map_meta_cap' => true,
			'show_in_menu' => 'namaste_options',
			"register_meta_box_cb" => array(__CLASS__,"meta_boxes")
		);
		register_post_type( 'namaste_course', $args );
		register_taxonomy_for_object_type('category', 'namaste_course');
	}
	
	// add courses to the homepage and archive listings
	static function query_post_type($query) {
		if(!get_option('namaste_show_courses_in_blog')) return $query;
		
		if ( (is_home() or is_archive()) and $query->is_main_query() ) {
			$post_types = $query->query_vars['post_type'] ?? null;
			
			// empty, so we'll have to create post_type setting			
			if(empty($post_types)) {
				if(is_home()) $post_types = array('post', 'namaste_course');
				else $post_types = array('post', 'namaste_course');
			}
			
			// not empty, so let's just add
			if(!empty($post_types) and is_array($post_types)) {
				$post_types[] = 'namaste_course';				
				$query->set( 'post_type', $post_types );
			}
		}		
		return $query;
	}
	
	static function meta_boxes() {
		add_meta_box("namaste_meta", __("Namaste! Settings", 'namaste'), 
							array(__CLASS__, "print_meta_box"), "namaste_course", 'normal', 'high');
		add_meta_box("namaste_advanced_reports_hint", __("Advanced Reports", 'namaste'), 
							array(__CLASS__, "print_reports_box"), "namaste_course", 'side', 'default');					
	}
	
	static function print_meta_box($post) {
			global $wpdb, $wp_roles;
			$roles = $wp_roles->roles;
			
			// select lessons in this course
			$_lesson = new NamasteLMSLessonModel();
			$lessons = $_lesson -> select($post->ID);
			$lessons = apply_filters('namaste-reorder-lessons', $lessons);	
						
			// required lessons
			$required_lessons = get_post_meta($post->ID, 'namaste_required_lessons', true);	
			if(!is_array($required_lessons)) $required_lessons = array();
			
			// enrollment - for now free or admin approved, in the future also paid
			$enroll_mode = get_post_meta($post->ID, 'namaste_enroll_mode', true);
			
			$fee = get_post_meta($post->ID, 'namaste_fee', true);
			$currency = get_option('namaste_currency');
			
			$use_points_system = get_option('namaste_use_points_system');
			$award_points = get_post_meta($post->ID, 'namaste_award_points', true);
			if($award_points === '') $award_points = get_option('namaste_points_course');
			
			$use_grading_system = get_option('namaste_use_grading_system');
			if(!empty($use_grading_system)) {
				$auto_grade = get_post_meta($post->ID, 'namaste_auto_grade', true);
			}
						
			// other courses
			$other_courses = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP			
			WHERE post_type = 'namaste_course'  AND (post_status='publish' OR post_status='draft') 
			AND ID!=%d ORDER BY post_title", $post->ID));

			// course will be accessible after these course(s) are completed			
			$course_access = get_post_meta($post->ID, 'namaste_access', true);	
			if(!is_array($course_access)) $course_access = array();
			
			$unenroll_allowed = get_post_meta($post->ID, 'namaste_unenroll', true);
			$register_enroll = get_post_meta($post->ID, 'namaste_register_enroll', true);
			
			// required roles?
			$require_roles = get_post_meta($post->ID, 'namaste_require_roles', true);
			$required_roles = get_post_meta($post->ID, 'namaste_required_roles', true); // this is the array of roles
			
			// reviews
			$accept_reviews = get_post_meta($post->ID, 'namaste_accept_reviews', true);
			$hold_reviews = get_post_meta($post->ID, 'namaste_hold_reviews', true);
			
			// buddypress?
			if(function_exists('bp_is_active') and bp_is_active( 'groups' ) and class_exists('BP_Groups_Group')) {
				
				// select BP groups
				$bp_groups = BP_Groups_Group::get(array(
									'type'=>'alphabetical',
									'per_page'=>999
									));
									
				$bp = get_post_meta($post->ID ?? 0, 'namaste_buddypress', true);
				$bp_enroll_group = $bp['enroll_group'] ?? null;
				$bp_complete_group = $bp['complete_group'] ?? null;
				$bp_enroll_group_remove = $bp['enroll_group_remove'] ?? null;
				$bp_complete_group_remove = $bp['complete_group_remove'] ?? null;
				
			}
			
			// WooCommerce integration?
			if(class_exists('woocommerce') and get_option('namaste_woocommerce') == 1) {
				// find all virtual and downloadable products
				$args =  array(
				    'post_type'      => array('product'),
				    'post_status'    => 'publish',
				    'posts_per_page' => -1,
				    'meta_query'     => array( 
				        array(
				            'key' => '_virtual',
				            'value' => 'yes',
				            'compare' => '=',  
				        ),
				        array(
				            'key' => '_downloadable',
				            'value' => 'yes',
				            'compare' => '=',  
				        )  
				    ),
				);
				$products = new WP_Query( $args );
				
				$namastewoo_id = get_post_meta($post->ID, 'namastewoo_id', true);
			}			
			
			wp_nonce_field( plugin_basename( __FILE__ ), 'namaste_noncemeta' );			  
			if(@file_exists(get_stylesheet_directory().'/namaste/course-meta-box.php')) require get_stylesheet_directory().'/namaste/course-meta-box.php';
			else require(NAMASTE_PATH."/views/course-meta-box.php");
	}
	
	static function print_reports_box($post) {
			global $wpdb;
			
			// for now simply remind there are reports
			// or hint to the plugin. In the future we'll allow some basic report to be shown right in the box
			if(is_plugin_active('namaste-reports/namaste-reports.php')) {
				echo "<p>".sprintf(__('For advanced reports on this course, <a href="%s">click here</a>.', 'namaste'), 'admin.php?page=namasterep&action=courses&course_id='.$post->ID)."</p>";
			} else {
				echo "<p>".sprintf(__('You can get <b>advanced reports</b> on this course if you install the <a href="%s" target="_blank">Namaste! Reports</a> plugin.', 'namaste'), 'http://namaste-lms.org/reports.php"')."</p>";
			}
	}
	
	static function save_course_meta($post_id) {
			global $wpdb;			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;		
	  		if ( empty($_POST['namaste_noncemeta']) or !wp_verify_nonce( $_POST['namaste_noncemeta'], plugin_basename( __FILE__ ) ) ) return;  	  		
	  		if ( !current_user_can( 'edit_post', $post_id ) ) return;
	 	 	if ('namaste_course' != $_POST['post_type']) return;
			
			update_post_meta($post_id, "namaste_enroll_mode", sanitize_text_field($_POST['namaste_enroll_mode']));
			update_post_meta($post_id, "namaste_required_lessons", namaste_int_array(@$_POST['namaste_required_lessons']));			
			update_post_meta($post_id, "namaste_fee", floatval($_POST['namaste_fee']));
			update_post_meta($post_id, "namaste_access", namaste_int_array(@$_POST['namaste_access']));
			$unenroll = empty($_POST['namaste_unenroll']) ? 0 : 1;
			update_post_meta($post_id, "namaste_unenroll", $unenroll);
			if(isset($_POST['namaste_award_points'])) update_post_meta($post_id, "namaste_award_points", intval($_POST['namaste_award_points']));
			$require_roles = empty($_POST['namaste_require_roles']) ? 0 : 1;
			$required_roles = empty($_POST['namaste_require_roles']) ? array() : esc_sql($_POST['namaste_required_roles']);
			update_post_meta($post_id, "namaste_require_roles", $require_roles);
			update_post_meta($post_id, "namaste_required_roles", $required_roles);
			
			$accept_reviews = empty($_POST['namaste_accept_reviews']) ? 0 : 1;
			$hold_reviews = empty($_POST['namaste_hold_reviews']) ? 0 : 1;
			update_post_meta($post_id, "namaste_accept_reviews", $accept_reviews);
			update_post_meta($post_id, "namaste_hold_reviews", $hold_reviews);
			
			$use_grading_system = get_option('namaste_use_grading_system');
			$auto_grade = empty($_POST['namaste_auto_grade']) ? 0 : 1;
			if(!empty($use_grading_system)) update_post_meta($post_id, 'namaste_auto_grade', $auto_grade);
			
			$register_enroll = empty($_POST['namaste_register_enroll']) ? 0 : 1;
			update_post_meta($post_id, 'namaste_register_enroll', $register_enroll);
			
			if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {
				$bp = array('enroll_group' => intval($_POST['namaste_bp_enroll_group']), 'complete_group' => intval($_POST['namaste_bp_complete_group']),
					'enroll_group_remove' => intval($_POST['namaste_bp_enroll_group_remove']), 
					'complete_group_remove' => intval($_POST['namaste_bp_complete_group_remove']) );
				update_post_meta($post_id, 'namaste_buddypress', $bp);
			}
			
			if(!empty($_POST['namastewoo_id']) and class_exists('woocommerce')) {
				update_post_meta($post_id, 'namastewoo_id', intval($_POST['namastewoo_id']));
				
				// set the attributes
				$permalink = get_permalink($post_id);
				
				$atts = get_post_meta($_POST['namastewoo_id'], '_product_attributes', true);
				if(empty($atts)) $atts = array();
				
				$name_found = $redirect_found = false;
				foreach($atts as $cnt => $att) {
					if($att['name'] == 'namaste-course') {
						$atts[$cnt]['value'] = $post_id;
						$name_found = true;
					}
					
					if($att['name'] == 'namaste-redirect') {
						$atts[$cnt]['value'] = $permalink;
						$redirect_found = true;
					}
				} // end foreach $atts
				
				if(!$name_found) $atts[] = array('name' => 'namaste-course', 'value' => $post_id);
				if(!$redirect_found) $atts[] = array('name' => 'namaste-redirect', 'value' => $permalink);
				
				//print_r($atts);
				
				update_post_meta($_POST['namastewoo_id'], '_product_attributes', $atts);
			} // end connecting to WooCommerce		 		
	}	
	
	// select existing courses
	function select($id = null) {
		global $wpdb;
		
		$id_sql = $id ? $wpdb->prepare(' AND ID = %d ', $id) : '';
		
		$courses = $wpdb->get_results("SELECT *, ID as post_id FROM {$wpdb->posts}
		WHERE post_type = 'namaste_course'  AND (post_status='publish' OR post_status='draft')
		$id_sql ORDER BY post_title");
				
		if($id) return $courses[0];
		
		return $courses;	
	}
	
	// let's keep it simple for the moment - display text showing whether the user is enrolled or not
	static function enroll_text($content) {
		global $wpdb, $user_ID, $post;
				
		if(@$post->post_type != 'namaste_course') return $content;
		
		// track the visit
		if(is_user_logged_in()) NamasteTrack::visit('course', $post->ID, $user_ID);
		
		// if the shortcode is there don't show this
		if(strstr($content, '[namaste-enroll]')) return $content;
		
		// enrolled? 
		$enrolled = false;
		if(is_user_logged_in()) {
			$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES.
			" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status='completed')", $user_ID, $post->ID));
		}
		
		/** 
        * IMPORTANT: This content MUST allow HTML and JavaScript. 
        * This is not a vulnerability.
        **/
		if($enrolled) $text = __('You are enrolled in this course. Check "My courses" link in your dashboard to see the lessons and to-do list', 'namaste');
		else $text = NAMASTE_NEED_LOGIN_TEXT_COURSE;
		
		$status_text = '';
		if(!empty($post->namaste_course_status_shown)) $status_text = "<p>".$text."</p>";
		
		return $content.$status_text;		
	}
	
	// checks if all requirements for completion are satisfied
	function is_ready($course_id, $student_id) {
		$required_lessons = get_post_meta($course_id, 'namaste_required_lessons', true);	
		if(!is_array($required_lessons)) $required_lessons = array();
		
		foreach($required_lessons as $lesson) {
			if(!NamasteLMSLessonModel::is_completed($lesson, $student_id)) return false;
		}	
		
		// all completed, so it's ready
		return true;
	}
	
	// actually marks course as completed
	function complete($course_id, $student_id) {
		global $wpdb;
		
		$student_course = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_COURSES."
			WHERE course_id=%d AND user_id=%d", $course_id, $student_id));
		
		if(empty($student_course->id)) return false;
		
		// if the course is already completed, don't mark it again
		if($student_course->status == 'completed') return false;
		
		$course = get_post($course_id);
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_COURSES." SET status = 'completed',
			completion_date = %s, completion_time=%s WHERE id=%d", 
			date("Y-m-d", current_time('timestamp')), current_time('mysql'), $student_course->id));
			
		// should we assign certificates?
		$_cert = new NamasteLMSCertificateModel();
		$_cert -> complete_course($course_id, $student_id);
		
		// award points?
		$use_points_system = get_option('namaste_use_points_system');
		if($use_points_system) {
			$award_points = get_post_meta($course_id, 'namaste_award_points', true);
			if($award_points === '') $award_points = get_option('namaste_points_course');
			if($award_points) {				
				NamastePoint :: award($student_id, $award_points, sprintf(__('Received %d points for completing course "%s".', 'namaste'), 
					$award_points, $course->post_title, 'course', $course_id));
			}
		}
		
		// grade course
		NamasteLMSGradebookController :: auto_grade_course($course_id, $student_id);
		
		// add custom action
		do_action('namaste_completed_course', $student_id, $course_id);	
		
		// insert in history
	  $wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='completed_course', value=%s, num_value=%d, course_id=%d",
			$student_id, sprintf(__('Completed course "%s"', 'namaste'), $course->post_title), $course_id, $course_id));
			
		// join BP group?
		if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {				
			$bp = get_post_meta($course_id, 'namaste_buddypress', true);

			if(!empty($bp['complete_group'])) groups_join_group( $bp['complete_group'], $student_id);
			if(!empty($bp['complete_group_remove'])) groups_leave_group( $bp['complete_group_remove'],  $student_id  ); 

		}		
	}
	
	// returns all the required lessons along with mark whether they are completed or not
	function required_lessons($course_id, $student_id) {
		global $wpdb;
		
		$required_lessons_ids = get_post_meta($course_id, 'namaste_required_lessons', true);	
		if(!is_array($required_lessons_ids) or empty($required_lessons_ids)) return array();
		
		$required_lessons = $wpdb->get_results("SELECT * FROM {$wpdb->posts} 
			WHERE ID IN (".implode(",", $required_lessons_ids).") 
			AND (post_status='publish' OR post_status='private') ORDER BY ID");
		
		foreach($required_lessons as $cnt => $lesson) {
			$required_lessons[$cnt]->namaste_completed = 0;
			if(NamasteLMSLessonModel::is_completed($lesson->ID, $student_id)) $required_lessons[$cnt]->namaste_completed = 1;
		}	
		return $required_lessons;
	}
	
	// enrolls or applies to enroll a course
	function enroll($student_id, $course_id, $status, $mass_enroll = false, $tags = '') {
		global $wpdb;
		
		// checks from other plugins, for example Namaste PRO
		$no_access = $message = null;
		list($no_access, $message) = apply_filters('namaste-course-access', array(false, ''), $student_id, $course_id);
		// echo $no_access.'a';
		if($no_access and empty($this->ignore_restrictions)) wp_die($message);
		
		// role restriction?
   	$require_roles = get_post_meta($course_id, 'namaste_require_roles', true);
		$required_roles = get_post_meta($course_id, 'namaste_required_roles', true); // this is the array of roles
		if($require_roles == 1 and !empty($required_roles) and is_array($required_roles)) {
			$user = get_user_by('id', $student_id);
			
			$restricted = true;
			foreach($required_roles as $required_role) {
				if ( in_array( $required_role, (array) $user->roles ) )  {
					$restricted = false;
					break;
				}
			}
			
			if($restricted) wp_die(__('Your user role is not allowed to join this course.', 'namaste'));
		} // end role restriction check
		
		// check for course access requirements
		$course_access = get_post_meta($course_id, 'namaste_access', true);

		if(!empty($course_access) and is_array($course_access)) {
			// check if there is any unsatisfied requirement
			foreach($course_access as $required_course) {
				$is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
					WHERE user_id=%d AND course_id=%d AND status='completed'", $student_id, $required_course));
				if(!$is_completed and empty($this->ignore_restrictions)) wp_die(__('You cannot enroll this course - other courses have to be completed first.', 'namaste'));	
			}
		}
		
		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_COURSES." SET
					course_id = %d, user_id = %d, status = %s, enrollment_date = %s, enrollment_time=%s,
					completion_date='1900-01-01', comments='', tags=%s",
					$course_id, $student_id, $status, date("Y-m-d", current_time('timestamp')), current_time('mysql'), $tags ) );
					
		if($result !== false) {
			if($mass_enroll) do_action('namaste_admin_enrolled_course',  $student_id, $course_id, $status);
			else do_action('namaste_enrolled_course', $student_id, $course_id, $status);
			
			// insert in history
			$course = get_post($course_id);
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
				user_id=%d, date=CURDATE(), datetime=NOW(), action='enrolled_course', value=%s, num_value=%d, course_id=%d",
				$student_id, sprintf(__('Enrolled in course %s. Status: %s', 'namaste'), $course->post_title, $status), $course_id, $course_id));
				
			// join BP group?
			if(function_exists('bp_is_active') and bp_is_active( 'groups' )) {				
				$bp = get_post_meta($course_id, 'namaste_buddypress', true);
				if(!empty($bp['enroll_group'])) groups_join_group( $bp['enroll_group'], $student_id);
				if(!empty($bp['enroll_group_remove'])) groups_leave_group( $bp['enroll_group_remove'],  $student_id  ); 
			}	
		}	// end success		
	}
	
	// auto enroll student in courses when they register to the site
	static function register_enroll($user_id) {
		global $wpdb;
		
		// user has allowed role?
		if(!user_can($user_id, 'namaste')) return false;
		
		// get courses that have auto enroll on registration enabled
		$args = array( 'post_type' => 'namaste_course', 'posts_per_page' => -1, 
			'meta_key' => 'namaste_register_enroll', 'meta_value' => 1);
		$courses = get_posts($args);
		
		$_course = new NamasteLMSCourseModel();
		$_course->ignore_restrictions = true;
		
		foreach($courses as $course) {
			// enroll status - pending or enrolled?	
			$enroll_mode = get_post_meta($course->ID, 'namaste_enroll_mode', true);	
			$status = ($enroll_mode == 'manual') ? 'pending' : 'enrolled';
			
			$_course->enroll($user_id, $course->ID, $status);
		}	 
	} // end register_enroll
	
	// displays enroll buttons
	// @param $course - the course to enroll in
	// @param $is_manager (boolean) - whether the user manages the LMS
	// @param $atts - additional attributes. When available they usually come from shortcode calling
	function enroll_buttons($course, $is_manager, $atts = null) {
		global $user_ID;
		
		$currency = $this->currency;
		$accept_other_payment_methods = $this->accept_other_payment_methods;
		$accept_paypal = $this->accept_paypal;
		$accept_stripe = $this->accept_stripe;		
		$accept_moolamojo = $this->accept_moolamojo;
		$stripe = $this->stripe;
		
		// school account signup?
		$is_school = empty($atts['is_school']) ? 0 : 1;
		
		// checked for prerequisites
		list($can_enroll, $enroll_prerequisites) = $this->enroll_prerequisites($course);
		
		// can't enroll?
		if(empty($can_enroll)) {
			return $enroll_prerequisites;
		}			
		
		$output = '';	
		if(!empty($course->fee)) $course->fee = apply_filters('namaste-coupon-applied', $course->fee, $course->post_id); // coupon code from other plugin?
		
		// handle school account price (schools management is a pro feature)
		if(class_exists('NamastePROSchool') and $is_school) $course->fee = NamastePROSchool :: school_price('course', $course);
		
		$paid_button_text =  sprintf(__('Enroll for %1$s %2$s', 'namaste'), $currency, @$course->fee);
		$free_button_text = __('Click to Enroll', 'namaste');
		if(!empty($atts['paid_button_text'])) $paid_button_text =  @sprintf($atts['paid_button_text'], $currency, $course->fee);
		if(!empty($atts['free_button_text'])) $free_button_text = $atts['free_button_text'];
						
		if(!empty($course->fee) and !$is_manager) {	
			// coupon codes and discount filters from other plugins
			$output = apply_filters('namaste-coupon-form', $output, $course->post_id);		
			
			// Allow Pro or other third party plugin to skip displaying the buttons. 
			// If content contains comment "<!--NAMASTE-RETURN-OUTPUT-->", strip it and return
			if(strstr($output, '<!--NAMASTE-RETURN-OUTPUT-->')) {
				$output = str_replace('<!--NAMASTE-RETURN-OUTPUT-->', '', $output);
				return $output;
			}				
				
			if($accept_paypal or $accept_other_payment_methods or $accept_moolamojo) { 
				$url = admin_url("admin-ajax.php?action=namaste_ajax&type=course_payment");
				$box_title = __('Payment for course', 'namaste');
				$output .= "<strong><a href='#' onclick=\"namasteEnrollCourse('".$box_title."', ".$course->post_id.", ".$user_ID.", '".$url."', ".$is_school.");return false;\">".$paid_button_text."</a></strong>"; 
			}
			if($accept_stripe) {
				$output .= '<form method="post">
				  <script src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
				          data-key="'.$stripe['publishable_key'].'"
				          data-amount="'.($course->fee*100).'" data-description="'.$course->post_title.'" data-currency="'.$currency.'"></script>
				<input type="hidden" name="stripe_pay" value="1">
				<input type="hidden" name="course_id" value="'.$course->post_id.'">';
				if(!empty($is_school)) $output .= '<input type="hidden" name="is_school" value="1">';
				$output .= '</form>';
			} // end if accept stripe
			
			// check if this is WooCommerce linked course. In this case the above payment methods and any coupon fields get vanished and replaced with a button going to the store
			$namastewoo_id = get_post_meta($course->post_id, 'namastewoo_id', true);
			if(!empty($namastewoo_id) and class_exists('woocommerce') and get_option('namaste_woocommerce') == 1) {
				$product_link = get_permalink($namastewoo_id);
				if(!empty($product_link)) {
					$output = '<p align="center"><input type="button" class="namaste-button button button-primary" value="'.__('Buy Enrollment', 'namaste').'" onclick="window.location=\''.$product_link.'\'"></p>';
				}
			}
		}	
		else {
			$output .= '<form method="post">
				<input type="hidden" name="namaste_enroll_nonce" value="' . esc_attr(wp_create_nonce('namaste_enroll_action')) . '">
				<input type="submit" value="'.$free_button_text.'" class="namaste-button button button-primary">
				<input type="hidden" name="enroll" value="1">
				<input type="hidden" name="course_id" value="'.$course->post_id.'">
			</form>';
		}  
		
		return $output;
	} // end enroll buttons
	
	// adds visits column in manage courses page
	static function manage_post_columns($columns) {
		// add this after title column 
		$final_columns = array();
		foreach($columns as $key=>$column) {			
			$final_columns[$key] = $column;
			if($key == 'title') {				
				$final_columns['namaste_course_visits'] = __( 'Visits (unique/total)', 'namaste' );
			}
		}
		return $final_columns;
	}
	
	// actually displaying the course column value
	static function custom_columns($column, $post_id) {
		switch($column) {			
			case 'namaste_course_visits':
				// get unique and total visits
				list($total, $unique) = NamasteTrack::get_visits('course', $post_id);
				echo $unique.' / '.$total;
			break;
		}
	}
	
	// check course pre-requisites
	// returns array($can_enroll, $enroll_prerequisites)
	function enroll_prerequisites($course) {
		global $wpdb, $user_ID;
		// can enroll? or are there unsatisfied pre-requisites
		$can_enroll = true;		
		$enroll_prerequisites = '';
		// check for course access requirements
		$course_access = get_post_meta($course->post_id, 'namaste_access', true);
		
		if(!empty($course_access) and is_array($course_access)) {
			$enroll_prerequisites = __('These courses should be completed before you can enroll:', 'namaste');
			
			// check if there is any unsatisfied requirement
			foreach($course_access as $required_course) {
				$is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
					WHERE user_id=%d AND course_id=%d AND status='completed'", $user_ID, $required_course));
				if(!$is_completed) {
					$can_enroll = false; // even one failed is enough;
					$required_course_post = get_post($required_course);
					$enroll_prerequisites .= ' <b>' . $required_course_post->post_title. '</b>;';
				}
			} // end foreach course access
		}
		
		return array($can_enroll, $enroll_prerequisites);
	} // end enroll_prerequisites()
	
	// add "Manage lessons" & "Manage Modules" links in admin
	static function post_row_actions($actions, $post) {
		if($post->post_type == 'namaste_course') {
			$use_modules = get_option('namaste_use_modules');
			if($use_modules) {
				$url = admin_url( 'edit.php?s&post_status=all&post_type=namaste_module&namaste_course_id='.$post->ID );
				$actions['namaste_manage_modules'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( __( 'Manage Modules', 'namaste' ) ) );
			}			
			
			$url = admin_url( 'edit.php?s&post_status=all&post_type=namaste_lesson&namaste_course_id='.$post->ID );
			$actions['namaste_manage_lessons'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( __( 'Manage Lessons', 'namaste' ) ) );
		}
		
		 return $actions;
	} // end post_row_actions
	
	// Konnichiwa & Konnichiwa Pro Integration: check access to enroll course
	static function konnichiwa_access($course_id) {
		global $wpdb, $user_ID;
		$course = get_post($course_id);		
		$can_enroll = true;
		
		if(class_exists('KonnichiwaContent')) {
			$_content = new KonnichiwaContent();
			$access = $_content->get_access($course);
			if(!empty($access)) {
				if($access !== 'registered') {
					$can_enroll = false;
					$plans = explode("|", $access);
					$plans = array_filter($plans);
					if(empty($plans)) return $content;
					
					// get active user plans
					$subs = $wpdb->get_results($wpdb->prepare("SELECT plan_id FROM ".KONN_SUBS."
						WHERE user_id=%d AND expires >= CURDATE() AND status=1", $user_ID));
					 	
					foreach($subs as $sub) {
						// if even one is found we're all ok to return the content
						if(in_array($sub->plan_id, $plans)) $can_enroll = true;
					}	
					
					// no subs at all?
					if(!count($subs)) $can_enroll = false;
				} // end if $access != 'registered'
			}	 // end if !empty($access)
		} // end Konnichiwa PRO
		
		if(class_exists('KonnichiwaProContent')) {
			$_content = new KonnichiwaProContent();
			$access = $_content->get_access($course);
			if(!empty($access)) {				
				if($access !== 'registered') {				
					$can_enroll = false;					
					
					$plans = explode("|", $access);				
					$plans = array_filter($plans);
					if(empty($plans)) return $content;
					
					if(!is_numeric(end($plans))) {
						$access_after = array_pop($plans);
						$access_after = str_replace('access-after-', '', $access_after);
					} 
					
					// get active user plans
					$subs = $wpdb->get_results($wpdb->prepare("SELECT id, plan_id, date FROM ".KONPRO_SUBS."
						WHERE user_id=%d AND expires >= CURDATE() AND status=1 ORDER BY date", $user_ID));
					 	
					foreach($subs as $sub) {
						// if even one is found we're all ok to return the content
						if(in_array($sub->plan_id, $plans)) {
							// is there drip / delayed access defined?						
							if(!empty($access_after)) {						
								$target_time = strtotime($sub->date) + 24 * 3600 * $access_after;
								if($target_time > current_time('timestamp')) {
									$can_enroll = false;
									break;
								}
							} // end delayed access check
							
							// found, so true:	
							$can_enroll = true;					
						}
					}	
					
					// no subscriptions found?
					if(!count($subs)) $can_enroll = false;
				} // end if $access != 'registered'
			}	 // end if !empty($access)
		} // end Konnichiwa PRO
		
		return $can_enroll;
	} // end konnichiwa_access
}
