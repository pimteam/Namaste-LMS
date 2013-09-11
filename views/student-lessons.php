<h1><?php _e('Course Progress:', 'namaste')?> <?php echo $course->post_title?></h1>

<?php if(current_user_can('namaste_manage')):?>
	<p><a href="admin.php?page=namaste_students&course_id=<?php echo $course->ID?>"><?php _e('Back to students in this course')?></a></p>
<?php endif;?>

<?php if(!empty($error)):?>
	<p class="namaste-error"><?php echo $error;?></p>
<?php endif;?>

<div class="wrap">
	<p><?php _e('Student:', 'namaste')?> <strong><?php echo !empty($student->nice_name)?$student->nice_name:$student->user_login?></strong></p>

	<table class="widefat">
		<tr><th><?php _e('Lesson', 'namaste')?></th>
		<th><?php _e('Assignments', 'namaste')?></th>
		<?php if($use_exams):?>
			<th><?php _e('Test/Exam', 'namaste')?></th>
		<?php endif;?>
		<th><?php _e('Status', 'namaste')?></th></tr>
		
		<?php foreach($lessons as $lesson):?>
			<tr><td><a href="<?php echo get_permalink($lesson->ID)?>"><?php echo $lesson->post_title?></a></td>
			<td><?php if(!sizeof($lesson->homeworks)): echo __('None', 'namaste'); 
			else:?> <a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student_id?>"><?php echo sizeof($lesson->homeworks)?></a>
			<?php endif;?></td>
			<?php if($use_exams):?>
				<td><?php if(empty($lesson->exam->ID)): _e('None', 'namaste');
				else:?>
					<a href="<?php echo get_permalink($lesson->exam->post_id)?>" target="_blank"><?php echo $lesson->exam->name?></a>
				<?php endif;?></td>
			<?php endif;?>			
			<td><?php if($student->ID == $user_ID): echo $lesson->status;
			else: ?>
				<form method="post">
				<select name="status" onchange="this.form.submit();">
					<option value="-1"<?php if($lesson->statuscode == -1) echo ' selected'?>><?php _e('Not started', 'namaste')?></option>	
					<option value="0"<?php if($lesson->statuscode == 0) echo ' selected'?>><?php _e('In progress', 'namaste')?></option>			
					<option value="1"<?php if($lesson->statuscode == 1) echo ' selected'?>><?php _e('Completed', 'namaste')?></option>	
				</select>
				<?php if($lesson->statuscode == 0): echo " <a href='#' onclick='namasteInProgress(".$lesson->ID.",".$student->ID.");return false;'>".__('[todo]', 'namaste').'</a>'; endif;?>
				<input type="hidden" name="lesson_id" value="<?php echo $lesson->ID?>">
				<input type="hidden" name="change_status" value="1">
				</form>
			<?php endif;?></td></tr>
		<?php endforeach; ?>	
	</table>
	
	<?php do_action('namaste_course_progress_view', $_GET['student_id'], $_GET['course_id'])?>
</div>

<script type="text/javascript" >
function namasteInProgress(lessonID, studentID) {
	tb_show("<?php _e('Lesson progress', 'namaste')?>", 
		'<?php echo admin_url("admin-ajax.php?action=namaste_ajax&type=lesson_progress")?>&lesson_id=' + lessonID + 
		'&student_id=' + studentID);
}
</script>