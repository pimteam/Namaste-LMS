<?php
class NamasteLMSModuleModel {
	// custom post type Module
	static function register_module_type() {	
		$use_modules = get_option('namaste_use_modules');
		if(empty($use_modules)) return false;	
		$module_slug = get_option('namaste_module_slug');
	   if(empty($module_slug)) $module_slug = 'namaste-module';
	   $has_archive = get_option('namaste_show_modules_in_blog');
	  	   
		$args=array(
			"label" => __("Namaste! Modules", 'namaste'),
			"labels" => array
				(
					"name"=>__("Modules", 'namaste'), 
					"singular_name"=>__("Module", 'namaste'),
					"add_new_item"=>__("Add New Module", 'namaste')
				),
			"public"=> true,
			"show_ui"=>true,
			"has_archive"=> $has_archive ? true : false,
			"rewrite"=> array("slug"=>$module_slug, "with_front"=>false),
			"description"=>__("This will create a new module in your Namaste! LMS.",'namaste'),
			"supports"=>array("title", 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats'),
			"taxonomies"=>array("category"),
			"show_in_nav_menus" => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_rest' => true,
			'show_in_menu' => 'namaste_options',
			"register_meta_box_cb" => array(__CLASS__,"meta_boxes")
		);
		register_post_type( 'namaste_module', $args );
		register_taxonomy_for_object_type('category', 'namaste_module');
	}
	
	// add modules to the homepage and archive listings
	static function query_post_type($query) {
		if(!get_option('namaste_show_modules_in_blog')) return $query;
		
		if ( (is_home() or is_archive()) and $query->is_main_query() ) {
			$post_types = $query->query_vars['post_type'] ?? null;
			
			// empty, so we'll have to create post_type setting			
			if(empty($post_types)) {
				if(is_home()) $post_types = array('post', 'namaste_module');
				else $post_types = array('post', 'namaste_module');
			}
			
			// not empty, so let's just add
			if(!empty($post_types) and is_array($post_types)) {
				$post_types[] = 'namaste_module';				
				$query->set( 'post_type', $post_types );
			}
		}		
		return $query;
	}
	
	static function meta_boxes() {
		add_meta_box("namaste_meta", __("Namaste! Settings", 'namaste'), 
							array(__CLASS__, "print_meta_box"), "namaste_module", 'normal', 'high');
		/*add_meta_box("namaste_advanced_reports_hint", __("Advanced Reports", 'namaste'), 
							array(__CLASS__, "print_reports_box"), "namaste_module", 'side', 'default');*/		
	}
	
	
	static function print_meta_box($post) {
			global $wpdb;
			
			// select courses
			$_course = new NamasteLMSCourseModel();
			$courses = $_course->select();
			
			// select lessons in this module
			$_lesson = new NamasteLMSLessonModel();
			$lessons = $_lesson -> select($post->ID, 'array', null, 'post_title', 'ASC', true);
			$lessons = apply_filters('namaste-reorder-lessons', $lessons);	
			//$lessons = apply_filters('namaste-reorder-lessons', $lessons); is this needed twice? doesn't make sense	
						
			// required lessons
			$required_lessons = get_post_meta($post->ID, 'namaste_required_lessons', true);	
			if(!is_array($required_lessons)) $required_lessons = array();
			
			$use_points_system = get_option('namaste_use_points_system');
			$award_points = get_post_meta($post->ID, 'namaste_award_points', true);
			if($award_points === '') $award_points = get_option('namaste_points_module');
						
			// other modules
			$other_modules = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP			
			WHERE post_type = 'namaste_module'  AND (post_status='publish' OR post_status='draft') 
			AND ID!=%d ORDER BY post_title", $post->ID));

			// module will be accessible after these module(s) are completed			
			$module_access = get_post_meta($post->ID, 'namaste_access', true);	
			if(!is_array($module_access)) $module_access = array();
			
			// which courses do this module belong to?
			$course_id = get_post_meta($post->ID, 'namaste_course', true);
			
			wp_nonce_field( plugin_basename( __FILE__ ), 'namaste_noncemeta' );			  
			if(@file_exists(get_stylesheet_directory().'/namaste/module-meta-box.php')) require get_stylesheet_directory().'/namaste/module-meta-box.php';
			else require(NAMASTE_PATH."/views/module-meta-box.php");
	}
	
	static function print_reports_box($post) {
			global $wpdb;
			
			// for now do nothing since we have no reports on modules
			return '';
			
			// for now simply remind there are reports
			// or hint to the plugin. In the future we'll allow some basic report to be shown right in the box
			if(is_plugin_active('namaste-reports/namaste-reports.php')) {
				echo "<p>".sprintf(__('For advanced reports on this module, <a href="%s">click here</a>.', 'namaste'), 'admin.php?page=namasterep&action=courses&course_id='.$post->ID)."</p>";
			} else {
				echo "<p>".sprintf(__('You can get <b>advanced reports</b> on this course if you install the <a href="%s" target="_blank">Namaste! Reports</a> plugin.', 'namaste'), 'http://namaste-lms.org/reports.php"')."</p>";
			}
	}
	
	static function save_module_meta($post_id) {
			global $wpdb;
			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;		
	  		if ( empty($_POST['namaste_noncemeta']) or !wp_verify_nonce( $_POST['namaste_noncemeta'], plugin_basename( __FILE__ ) ) ) return;  	  		
	  		if ( !current_user_can( 'edit_post', $post_id ) ) return;
	 	 	if ('namaste_module' != $_POST['post_type']) return;
			
			update_post_meta($post_id, "namaste_required_lessons", $_POST['namaste_required_lessons']);			
			update_post_meta($post_id, "namaste_access", $_POST['namaste_access']);
			update_post_meta($post_id, "namaste_course", intval($_POST['namaste_course']));
			if(isset($_POST['namaste_award_points'])) update_post_meta($post_id, "namaste_award_points", $_POST['namaste_award_points']);
	} // end save meta
	
	// select existing modules
	function select($id = null, $course_id = 0, $ob = 'post_title', $dir = 'ASC') {
		global $wpdb;

		$ob = sanitize_sql_orderby($ob);
		if(empty($ob)) $ob = 'post_title';
		if($dir != 'ASC' and $dir != 'DESC') $dir = 'ASC';		
				
		$id_sql = $id ? $wpdb->prepare(' AND ID = %d ', $id) : '';
		$course_id_sql = $course_id ? $wpdb->prepare("JOIN {$wpdb->postmeta} tM ON tM.meta_key = 'namaste_course' 
			AND tM.meta_value=%d AND tM.post_id = tP.ID", $course_id) : '';
		
		$modules = $wpdb->get_results("SELECT tP.*, tP.ID as post_id FROM {$wpdb->posts} tP
		$course_id_sql
		WHERE post_type = 'namaste_module'  AND (post_status='publish' OR post_status='draft')
		$id_sql ORDER BY $ob $dir");
				
		if($id) return $modules[0];
		
		// allow filtering
		$modules = apply_filters( 'namaste_course_modules', $modules, $course_id );
		
		return $modules;	
	} // end select()
	
	// checks if all requirements for completion are satisfied
	function is_ready($module_id, $student_id) {
		$required_lessons = get_post_meta($module_id, 'namaste_required_lessons', true);	
		if(!is_array($required_lessons)) $required_lessons = array();
		
		foreach($required_lessons as $lesson) {
			if(!NamasteLMSLessonModel::is_completed($lesson, $student_id)) return false;
		}	
		
		// all completed, so it's ready
		return true;
	} // end is_ready()
	
	// actually marks module as completed
	function complete($module_id, $student_id) {
		global $wpdb;
		
		$student_module = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_MODULES."
			WHERE module_id=%d AND student_id=%d", $module_id, $student_id));
		
		if(empty($student_module->id)) return false;
		
		// if the course is already completed, don't mark it again
		if($student_module->status == 'completed') return false;
		
		$module = get_post($module_id);
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_MODULES." SET status = 'completed',
			completion_date = %s WHERE id=%d", 
			date("Y-m-d", current_time('timestamp')), $student_module->id));
			
		// award points?
		$use_points_system = get_option('namaste_use_points_system');
		if($use_points_system) {
			$award_points = get_post_meta($module_id, 'namaste_award_points', true);
			if($award_points === '') $award_points = get_option('namaste_points_module');
			if($award_points) {				
				NamastePoint :: award($student_id, $award_points, sprintf(__('Received %d points for completing module "%s".', 'namaste'), 
					$award_points, $module->post_title, 'module', $module_id));
			}
		}
			
		// add custom action
		do_action('namaste_completed_module', $student_id, $module_id);	
		
		// insert in history
	  $course_id = get_post_meta($module_id, 'namaste_course', true);	
	  $wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='completed_module', value=%s, num_value=%d, module_id=%d, course_id=%d",
			$student_id, sprintf(__('Completed module "%s"', 'namaste'), $module->post_title), $module_id, $module_id, $course_id));
	} // end complete()
	
	// returns all the required lessons along with mark whether they are completed or not
	function required_lessons($module_id, $student_id) {
		global $wpdb;
		
		$required_lessons_ids = get_post_meta($module_id, 'namaste_required_lessons', true);	
		if(!is_array($required_lessons_ids)) return array();
		
		$required_lessons = $wpdb->get_results("SELECT * FROM {$wpdb->posts} 
			WHERE ID IN (".implode(",", $required_lessons_ids).") 
			AND (post_status='publish' OR post_status='private') ORDER BY ID");
		
		foreach($required_lessons as $cnt => $lesson) {
			$required_lessons[$cnt]->namaste_completed = 0;
			if(NamasteLMSLessonModel::is_completed($lesson->ID, $student_id)) $required_lessons[$cnt]->namaste_completed = 1;
		}	
		return $required_lessons;
	} // end required_lessons()
	
	// show filter modules by Course in admin
	static function restrict_manage_posts() {
		 global $typenow;		
	    global $wp_query;
	    
	    if ($typenow == 'namaste_module') {
	        $_course = new NamasteLMSCourseModel();
	        $courses = $_course->select();
	        echo '<select name="namaste_course_id" id="namaste_course_id">';
	        echo '<option value="">'.__('All Courses', 'namaste').'</option>';
	        foreach($courses as $course) {
	        	  $selected = (!empty($_GET['namaste_course_id']) and $_GET['namaste_course_id'] == $course->ID) ? ' selected' : '';
	        	  echo '<option value="'.$course->ID.'"'.$selected.'>'.stripslashes($course->post_title).'</option>';
	        }
	        echo '</select>';
	    }
	} // end restrict manage posts
	
	// actually filter the lessons by course
	static function parse_admin_query($query) {
		 global $pagenow;
    	$type = 'namaste_module';
	    if (isset($_GET['post_type'])) {
	        $type = $_GET['post_type'];
	    }
	    if ( 'namaste_module' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['namaste_course_id']) && $_GET['namaste_course_id'] != '') {
	        $query->query_vars['meta_key'] = 'namaste_course';
	        $query->query_vars['meta_value'] = $_GET['namaste_course_id'];
	    }
	} // end parse_admin_query
	
	// add "Manage lessons" link in admin
	static function post_row_actions($actions, $post) {
		if($post->post_type == 'namaste_module') {			
			$course_id = get_post_meta($post->ID, 'namaste_course', true);
			$url = admin_url( 'edit.php?s&post_status=all&post_type=namaste_lesson&namaste_course_id='.$course_id.'&namaste_module_id='.$post->ID );
			$actions['namaste_manage_lessons'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( __( 'Manage Lessons', 'namaste' ) ) );
		}
		
		 return $actions;
	} // end post_row_actions

	// regroups lessons by module. First use the lessons order to define the modules order. Then regroup and return array of module objects
	// where each module contains lessons while having the same order inside the module
	static function regroup_lessons($lessons) {
		global $wpdb;
		$modules = $module_ids = array();
		
		// fill arrays
		foreach($lessons as $cnt => $lesson) {
			$module_id = get_post_meta($lesson->ID, 'namaste_module', true);			
			if( empty( $module_id )) continue;
			$lessons[$cnt]->module_id = $module_id;
			
			if(!in_array($module_id, $module_ids)) {
				$module_ids[] = intval($module_id);
				
				if(!empty($module_id)) $module = get_post($module_id);
				if(empty($module->ID)) $module = (object)array('ID' => 0, 'post_title' => '');
				$modules[] = $module;
			}
		}
	
		// now do the regrouping
		foreach($modules as $cnt => $module) {
			$module_lessons = array();
			
			foreach($lessons as $lesson) {
				if($lesson->module_id == $module->ID) $module_lessons[] = $lesson;
			}
			
			$modules[$cnt]->lessons = $module_lessons;
		} // end foreach module
        
		return $modules;
	} // end regroup_lessons
	
	// check if user can access the module
	static function access_module($content) {
		global $wpdb, $post, $user_ID, $comments_flat, $wp_query;		
		if(empty($post->post_type) or $post->post_type != 'namaste_module') return $content;		
		$_course = new NamasteLMSCourseModel();
				
		if(!is_user_logged_in()) {
			//echo $post->ID.'<br>';
			// post excerpt?			
			add_filter( 'comments_array', '__return_empty_array' );
			if(!empty($post->post_excerpt)) return wpautop($post->post_excerpt);
			
			/** 
            * IMPORTANT: This content MUST allow HTML and JavaScript. 
            * This is not a vulnerability.
            **/
			return NAMASTE_NEED_LOGIN_TEXT_MODULE;
		}
		
		// track visit
		NamasteTrack::visit('module', $post->ID, $user_ID);
		
		$course_id = get_post_meta($post->ID, 'namaste_course', true);
		$course = $_course -> select($course_id);
		
		// add link to parent course?
		if(get_option('namaste_link_to_course') == 1) {			
			$link = stripslashes(get_option('namaste_link_to_course_text'));
			$course_permalink = get_permalink($course_id);
			$link = str_replace('{{{course-link}}}', '<a href="'.$course_permalink.'">'.stripslashes($course->post_name).'</a>', $link);
			
			$content = $link . $content;
		}		
		
		// manager will always access module
		if(current_user_can('namaste_manage')) {
			// class or other restrictions apply?
			list($no_access, $message) = apply_filters('namaste-manager-module-access', array(false, ''), $user_ID, $course_id);
			if(!empty($no_access) and !current_user_can('manage_options')) {
				if(!empty($post->post_excerpt)) $message .= wpautop($post->post_excerpt);
				return $message;
			} 
			//self :: mark_accessed(); return $content; for now modules do not need mark_accessed 
			 return $content; 
		}
		
		// enrolled in the course?		
		$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES.
			" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status='completed')", $user_ID, $course_id));	
		if(!$enrolled) {
			if(!empty($post->post_excerpt)) $content = wpautop($post->post_excerpt);
			else $content = __('In order to see this module you first have to be enrolled in the course', 'namaste').' <b>"'.$course->post_title.'"</b>';
			// self :: mark_accessed();
			add_filter('comments_array', array('NamasteLMSLessonModel', 'fix_comments_array'), 10, 2);
			return $content; // no need to run further queries
		}		
		
		// no access due to filters? (Classes from Namaste PRO etc)
		list($no_access, $message) = apply_filters('namaste-course-access', array(false, ''), $user_ID, $course_id);
		if(!empty($no_access)) {
			if(!empty($post->post_excerpt)) $message .= wpautop($post->post_excerpt);
			return $message;
		}		
			 
		// no access due to other lesson restrictions based on filters from other plugins		
		list($no_access, $message) = apply_filters('namaste-module-access', array(false, ''), $user_ID, $post->ID);
		if(!empty($no_access)) {
			if(!empty($post->post_excerpt)) $message .= wpautop($post->post_excerpt);
			return $message;
		}		
		
		$module_access = get_post_meta($post->ID, 'namaste_access', true);
		   
      // any not completed?
      $not_completed_ids = null;
      if(!empty($module_access)) {
         foreach($module_access as $mid) {
             $is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_MODULES." WHERE
               module_id=%d AND student_id=%d AND status='completed'", $mid, $user_ID));
               
             if(!$is_completed) {
             	// check on the fly, maybe lessons are completed but there are no requirements
             	$_module = new NamasteLMSModuleModel();
             	if($_module -> is_ready($mid, $user_ID)) {
						// insert relation here
						$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_MODULES." SET
							module_id=%d, student_id=%d, status='completed', enrollment_date=%s, completion_date=%s",
							$mid, $user_ID, date('Y-m-d', current_time('timestamp')), date('Y-m-d', current_time('timestamp')) ));                		
             		
             		$mid = 0; // unset $mid so it's not inserted as not completed
             	}
             	if($mid) $not_completed_ids[] = $mid;
             }  
         }
      }		   
	   
	   if(!empty($not_completed_ids)) {     
	       if(!empty($post->post_excerpt)) $content = wpautop($post->post_excerpt); 
	       $content = '<p>'.__('Before accessing this module you must complete the following modules:','namaste').'</p>';	
	       foreach($not_completed_ids as $id) {
			 		$not_completed = get_post($id);   			 		
			 		$content .= '<li><a href="'.get_permalink($id).'">'.stripslashes($not_completed->post_title).'</a></li>';
			 }			 
		    $content	.= '<ul>';

		    return $content;
      }
		
		return $content;
	} // end access_module
	
	// adds course column in manage modules page
	static function manage_post_columns($columns) {
		// add this after title column 
		$final_columns = array();
		foreach($columns as $key=>$column) {			
			$final_columns[$key] = $column;
			if($key == 'title') {
				$final_columns['namaste_course'] =  __( 'Course', 'namaste' );
			}
		}
		return $final_columns;
	}
	
	// actually displaying the course column value
	static function custom_columns($column, $post_id) {
        $post = get_post( $post_id );
        if( $post->post_type != 'namaste_module' ) return;
		switch($column) {
			case 'namaste_course':
				$course_id = get_post_meta($post_id, "namaste_course", true);
				$course = get_post($course_id);
				echo '<a href="post.php?post='.$course_id.'&action=edit">'.stripslashes($course->post_title).'</a>';
			break;
		}
	}
}
