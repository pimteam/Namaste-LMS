<h1><?php _e('My Courses', 'namaste')?></h1>

<?php if(!sizeof($courses)) :?>
	<p><?php _e('No courses are available at this time.', 'namaste')?></p>
<?php return false;
endif;?>

<div class="wrap">
	<?php if(!empty($message)):?>
		<p class="namaste-note"><?php echo $message?></p>
	<?php endif;?>	

	<table class="widefat">
		<tr><th><?php _e('Course title &amp; description', 'namaste')?></th>
		<th><?php _e('Lessons', 'namaste')?></th>		
		<th><?php _e('Status', 'namaste')?></th></tr>
		<?php foreach($courses as $course):?>
			<tr><td><a href="<?php echo get_permalink($course->post_id)?>" target="_blank"><?php echo $course->post_title?></a>
			<?php if(!empty($course->post_excerpt)): echo apply_filters('the_content', stripslashes($course->post_excerpt)); endif;?></td>
			<td><?php if(empty($course->status) or $course->status == 'pending'): 
				_e('Enroll to get access to the lessons', 'namaste');
				else: ?>
					<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course->post_id?>&student_id=<?php echo $user_ID?>"><?php _e('View lessons', 'namaste')?></a>
				<?php endif;?></td>
			<td>
			<?php if(empty($course->status)): // not enrolled
				echo $_course->enroll_buttons($course, $is_manager);
			else: // enrolled
				if($course->status == 'pending'): _e('Pending enrollment', 'namaste'); endif;
				if($course->status == 'rejected'): _e('Application rejected', 'namaste'); endif;
				if($course->status == 'enrolled'): printf(__('Enrolled on %s', 'namaste'), 
					date(get_option('date_format'), strtotime($course->enrollment_date))); endif;
				if($course->status == 'completed'): printf(__('Completed on %s', 'namaste'), 
					date(get_option('date_format'), strtotime($course->completion_date))); endif;
			endif;?>			
			</td></tr>
		<?php endforeach;?>
	</table>
</div>