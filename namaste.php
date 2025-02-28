<?php
/*
Plugin Name: Namaste! LMS
Plugin URI: http://namaste-lms.org
Description: Learning Management System for WordPress. Courses, modules, lessons, gradebook, and everything you need.
Author: Kiboko Labs
Version: 2.6.5.1
Author URI: http://calendarscripts.info/
License: GPLv2 or later
Text Domain: namaste
*/

define( 'NAMASTE_PATH', dirname( __FILE__ ) );
define( 'NAMASTE_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'NAMASTE_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
require_once(NAMASTE_PATH."/helpers/htmlhelper.php");
require(NAMASTE_PATH."/models/namaste-model.php");
require(NAMASTE_PATH."/models/lesson-model.php");
require(NAMASTE_PATH."/models/course-model.php");
require(NAMASTE_PATH."/models/module-model.php");
require(NAMASTE_PATH."/models/homework-model.php");
require(NAMASTE_PATH."/models/student-model.php");
require(NAMASTE_PATH."/models/note-model.php");
require(NAMASTE_PATH."/models/certificate-model.php");
require(NAMASTE_PATH."/models/payment.php");
require(NAMASTE_PATH."/models/stripe-model.php");
require(NAMASTE_PATH."/models/track.php");
require(NAMASTE_PATH."/models/point.php");
require(NAMASTE_PATH."/controllers/ajax.php");
require(NAMASTE_PATH."/controllers/courses.php");
require(NAMASTE_PATH."/controllers/homeworks.php");
require(NAMASTE_PATH."/controllers/certificates.php");
require(NAMASTE_PATH."/controllers/shortcodes.php");
require(NAMASTE_PATH."/controllers/gradebook.php");
require(NAMASTE_PATH."/controllers/multiuser.php");
require(NAMASTE_PATH."/controllers/search.php");
require(NAMASTE_PATH."/controllers/todo.php");
require(NAMASTE_PATH."/controllers/xapi.php");
require(NAMASTE_PATH."/controllers/woocommerce.php");
require(NAMASTE_PATH."/controllers/reviews.php");
require(NAMASTE_PATH."/controllers/webhooks.php");
require(NAMASTE_PATH."/controllers/lessons.php");

add_action('init', array("NamasteLMSCourseModel", "register_course_type"));
add_action('init', array("NamasteLMSModuleModel", "register_module_type"));
add_action('init', array("NamasteLMSHomeworkModel", "register_homework_type"));
add_action('init', array("NamasteLMSLessonModel", "register_lesson_type"));
add_action('init', array("NamasteLMS", "init"));

register_activation_hook(__FILE__, array("NamasteLMS", "install"));
add_action('admin_menu', array("NamasteLMS", "menu"));

add_action('admin_enqueue_scripts', array("NamasteLMS", "scripts"));

// show the things on the front-end
add_action( 'wp_enqueue_scripts', array("NamasteLMS", "scripts"));

// widgets
add_action( 'widgets_init', array("NamasteLMS", "register_widgets") );

// other actions
add_action('save_post', array('NamasteLMSLessonModel', 'save_lesson_meta'));
add_action('save_post', array('NamasteLMSCourseModel', 'save_course_meta'));
add_action('save_post', array('NamasteLMSModuleModel', 'save_module_meta'));
add_filter('pre_get_posts', array('NamasteLMSCourseModel', 'query_post_type'));
add_filter('pre_get_posts', array('NamasteLMSLessonModel', 'query_post_type'));
add_filter('pre_get_posts', array('NamasteLMSModuleModel', 'query_post_type'));
add_filter('pre_get_posts', array('NamasteLMSSearchController', 'pre_get_posts'));
add_action('wp_ajax_namaste_ajax', 'namaste_ajax');
add_action('wp_ajax_nopriv_namaste_ajax', 'namaste_ajax');
add_filter('the_content', array('NamasteLMSLessonModel', 'access_lesson'));
add_filter('the_content', array('NamasteLMSModuleModel', 'access_module'));
add_filter('the_content', array('NamasteLMSCourseModel', 'enroll_text'));

// erase personal data?
add_filter('wp_privacy_personal_data_erasers', array('NamasteLMS', 'register_eraser'), 10);
