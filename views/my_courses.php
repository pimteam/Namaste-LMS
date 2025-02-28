<h1><?php _e('My Courses', 'namaste')?></h1>

<?php if(!count($courses)) :?>
	<p><?php _e('No courses are available at this time.', 'namaste')?></p>
<?php return false;
endif;?>

<div class="wrap">
	<?php if(!empty($message)):?>
		<p class="namaste-note"><?php echo $message?></p>
	<?php endif;?>	

	<table class="widefat">
		<thead>
			<tr><th <?php if(!empty($column_widths[0])):?>style="width:<?php echo trim($column_widths[0])?>;"<?php endif;?>><?php _e('Course title &amp; description', 'namaste')?></th>
			<?php if(!$simplified):?><th><?php _e('Lessons', 'namaste')?></th><?php endif;?>		
			<th <?php if(!empty($column_widths[1])):?>style="width:<?php echo trim($column_widths[1])?>;"<?php endif;?>><?php _e('Status', 'namaste')?></th></tr>
		</thead>
		<tbody>
		<?php foreach($courses as $course):
			$unenroll_allowed = get_post_meta($course->post_id, 'namaste_unenroll', true);
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><a href="<?php echo get_permalink($course->post_id)?>" target="<?php echo $links_target;?>"><?php echo stripslashes($course->post_title)?></a>
			<?php if(!empty($course->post_excerpt)): echo apply_filters('namaste_content', stripslashes($course->post_excerpt)); endif;?></td>
			<?php if(!$simplified):?>
			<td><?php if(empty($course->status) or $course->status == 'pending'): 
				_e('Enroll to get access to the lessons', 'namaste');
				else: ?>
					<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course->post_id?>&student_id=<?php echo $user_ID?>"><?php _e('View lessons', 'namaste')?></a>
				<?php endif;?></td>
			<?php endif; // end if not simplified ?>	
			<td>
			<?php if(empty($course->status)): // not enrolled
			  // Konniciwa integration: can't enroll if the content is protected
			   $can_enroll = true;
			   if(!$is_manager): $can_enroll = NamasteLMSCourseModel :: konnichiwa_access($course->post_id); endif;
				if($can_enroll): echo $_course->enroll_buttons($course, $is_manager);  else: _e('No access', 'namaste'); endif;
			else: // enrolled
				if($course->status == 'pending'): _e('Pending enrollment', 'namaste'); endif;
				if($course->status == 'rejected'): _e('Application rejected', 'namaste'); endif;
				if($course->status == 'frozen'): _e('Frozen / no access', 'namaste'); endif;
				if($course->status == 'enrolled'): 
					printf(__('Enrolled on %s', 'namaste'), date_i18n(get_option('date_format'), strtotime($course->enrollment_date)));
					if($unenroll_allowed):?>
						<p><a href="#" onclick="namasteUnenrollCourse(<?php echo $course->post_id?>);return false;"><?php _e('Un-enroll from this course', 'namaste');?></a></p>
					<?php endif;
					do_action('namaste-course-status', $course->post_id, $user_ID); 
				endif;
				if($course->status == 'completed'): printf(__('Completed on %s', 'namaste'), 
					date_i18n(get_option('date_format'), strtotime($course->completion_date))); endif;
			endif;?>			
			</td></tr>
		<?php endforeach;?>
		</tbody>
	</table>
</div>

<script type="text/javascript" >
function namasteUnenrollCourse(id) {
	if(confirm("<?php _e('Are you sure? This will blank out all your progress in this course', 'namaste')?>")) {
		var unenrollUrl = '<?php echo esc_url(add_query_arg(['unenroll' => 'ID_REPLACE', 'namaste_unenroll_nonce' => wp_create_nonce('namaste_unenroll_action')], $target_url)); ?>';
		window.location = unenrollUrl.replace('ID_REPLACE', id);
	}
}

</script>
