<h1><?php _e('Send Note', 'namaste')?></h1>

<div class="wrap">
	<p><?php _e('Use this page to send a note about assignments', 'namaste')?> "<strong><?php echo $homework->title?></strong>" <?php _e('for student', 'namaste')?> <strong><?php echo $student->user_login?></strong>
	</p>
	
	<p><?php _e('Course:', 'namaste')?> <strong><?php echo $course->post_title?></strong></p>
	<p><?php _e('Lesson:', 'namaste')?> <strong><?php echo $lesson->post_title?></strong></p>
	
	<p><a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student->ID?>"><?php _e('Back to assignments for the student on this lesson', 'namaste')?></a></p>

	<div class="namaste-form">
		<form method="post">
			<p align="center"><?php echo wp_editor('', 'note')?></p>
			<p align="center"><input type="submit" name='ok' value="<?php _e('Send note', 'namaste')?>"></p>	
		</form>
	</div>
</div>