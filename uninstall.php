<?php
global $wpdb;

if(!defined('WP_UNINSTALL_PLUGIN') or !WP_UNINSTALL_PLUGIN) exit;
    
// clenaup all data
if(get_option('namaste_cleanup_db')==1)
{
	// now drop tables	
	$wpdb->query("DROP TABLE `".NAMASTE_STUDENT_COURSES."`");
	$wpdb->query("DROP TABLE `".NAMASTE_LESSON_COURSES."`");
	$wpdb->query("DROP TABLE `".NAMASTE_HOMEWORKS."`");
	$wpdb->query("DROP TABLE `".NAMASTE_STUDENT_HOMEWORKS."`");
	$wpdb->query("DROP TABLE `".NAMASTE_HOMEWORK_NOTES."`");
	$wpdb->query("DROP TABLE `".NAMASTE_STUDENT_LESSONS."`");
	    
	// clean options
	// NYI
}