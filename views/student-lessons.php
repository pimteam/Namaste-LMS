<?php if(!$simplified):?>
	<h1><?php _e('Course Progress:', 'namaste')?> <?php echo $course->post_title?></h1>
	
	<?php if(current_user_can('namaste_manage')):?>
		<p><a href="admin.php?page=namaste_students&course_id=<?php echo $course->ID?>"><?php _e('Back to students in this course', 'namaste')?></a></p>
	<?php endif;?>
	
	<?php if(!empty($error)):?>
		<p class="namaste-error"><?php echo $error;?></p>
	<?php endif;?>
<?php endif;?>	

<div class="wrap">
	<?php if(!$simplified):?><p><?php _e('Student:', 'namaste')?> <strong><?php echo !empty($student->nice_name)?$student->nice_name:$student->user_login?></strong></p><?php endif;?>
	
	<?php	if(!empty($modules)) :
		// this happens only if namaste_use_modules is true, otherwise $modules will be empty
		foreach($modules as $module):
			$lessons = $module->lessons;
			if(!empty($module->post_title)): echo '<h3 class="namaste-module-title">'.stripslashes($module->post_title).'</h3>'; endif;
			if(@file_exists(get_stylesheet_directory().'/namaste/student-lessons-table.html.php')) require get_stylesheet_directory().'/namaste/student-lessons-table.html.php';
			else require(NAMASTE_PATH."/views/student-lessons-table.html.php");		
		endforeach;
	else:
		if(@file_exists(get_stylesheet_directory().'/namaste/student-lessons-table.html.php')) require get_stylesheet_directory().'/namaste/student-lessons-table.html.php';
		else require(NAMASTE_PATH."/views/student-lessons-table.html.php");
	endif;	
	?>

	<?php if(!$simplified) do_action('namaste_course_progress_view', $student->ID, $_GET['course_id'])?>
</div>

<script type="text/javascript" >
function namasteInProgress(lessonID, studentID) {
	tb_show("<?php _e('Lesson progress', 'namaste')?>", 
		'<?php echo admin_url("admin-ajax.php?action=namaste_ajax&type=lesson_progress")?>&lesson_id=' + lessonID + 
		'&student_id=' + studentID);
}
</script>