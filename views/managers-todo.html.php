<div class="wrap">
	<h1><?php _e("Teacher's To-Do Items", 'namaste');?></h1>
	
	<h2><?php _e('Course enrollments', 'namaste');?></h2>
	
	<?php if(count($enrollments)):
		foreach($enrollments as $enrollment):?>
		<p><?php printf(__('There are <b>%d</b> pending enrollments in <b>"%s"</b>. <a href="%s" target="_blank">Manage</a>', 'namaste'),
			$enrollment->cnt, stripslashes($enrollment->course), 'admin.php?page=namaste_students&course_id='.$enrollment->course_id.'&email=&status=pending&watupro_group_id=0')?></p>
	<?php endforeach;
	else:?>
		<p><?php _e('There are no pending student enrollments.', 'namaste');?></p>
	<?php endif;?>
	
	<h2><?php _e('Lessons pending admin approval', 'namaste');?></h2>
	
	<?php if(count($approvals)):
		foreach($approvals as $approval):
			$course_id = get_post_meta($approval->lesson_id, 'namaste_course', true);?>
		<p><?php printf(__('<b>%s</b> is waiting for your approval to complete lesson <b>"%s"</b>. <a href="%s" target="_blank">Manage</a>', 'namaste'),
			$approval->user_login, stripslashes($approval->lesson), 'admin.php?page=namaste_student_lessons&course_id='.$course_id.'&student_id='.$approval->student_id)?></p>
	<?php endforeach;
	else:?>
		<p><?php _e('There are no pending lesson completions.', 'namaste');?></p>
	<?php endif;?>
	
	<h2><?php _e('Solutions to homework assignments, pending approval', 'namaste');?></h2>
	
	<?php if(count($homeworks)):
		foreach($homeworks as $homework):?>
		<p><?php printf(__('<b>%s</b> has submitted a solution on homework <b>"%s"</b>. <a href="%s" target="_blank">Manage</a>', 'namaste'),
			$homework->user_login, stripslashes($homework->title), 'admin.php?page=namaste_view_solutions&student_id='.$homework->student_id.'&id=' . $homework->homework_id)?></p>
	<?php endforeach;
	else:?>
		<p><?php _e('There are no pending solutions.', 'namaste');?></p>
	<?php endif;?>
</div>