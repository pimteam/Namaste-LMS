<table class="widefat namaste-table">
		<thead>
		<tr><th><?php _e('Lesson', 'namaste')?></th>
		<?php if(!$simplified):?>
			<th><?php _e('Assignments', 'namaste')?></th>
			<?php if($use_exams):?>
				<th><?php _e('Test/Exam', 'namaste')?></th>
			<?php endif;
		endif; // end if not simplified ?>	
		<th><?php _e('Status', 'namaste')?></th>		
		<?php if(!empty($use_grading_system) and !empty($atts['show_grade'])):?>
			<th><?php _e('Grade', 'namaste')?></th>
		<?php endif;?>
		</tr>
		</thead>
		<tbody>
		<?php foreach($lessons as $lesson):
			if(empty($class)) $class = 'alternate';
			else $class = ''; ?>
			<tr class="<?php echo $class?>"><td><a href="<?php echo get_permalink($lesson->ID)?>" target="<?php echo $links_target;?>"><?php echo stripslashes($lesson->post_title)?></a>
			<?php if(!empty($show_excerpts) and !empty($lesson->post_excerpt)) echo wpautop($lesson->post_excerpt);?></td>
			<?php if(!$simplified):?>
				<td><?php if(!count($lesson->homeworks)): echo __('None', 'namaste'); 
				else:?> <a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student_id?>"><?php echo count($lesson->homeworks)?></a>
				<?php endif;?></td>
				<?php if($use_exams):?>
					<td><?php if(empty($lesson->exam->ID)): _e('None', 'namaste');
					else:?>
						<a href="<?php echo get_permalink($lesson->exam->post_id)?>" target="<?php echo $links_target;?>"><?php echo stripslashes($lesson->exam->name)?></a>
					<?php endif;?></td>
				<?php endif; // end if $use_exams
			endif; // end if not simplified?>
			<td><?php if($student->ID == $user_ID or @$multiuser_access == 'view'): echo $lesson->status;
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
			<?php endif;?></td>
			<?php if(!empty($use_grading_system) and !empty($atts['show_grade'])):?>
				<th><?php echo !empty($lesson->grade) ? $lesson->grade : __('N/a', 'namaste');?></th>
			<?php endif;?>
			</tr>
		<?php endforeach; ?>	
		</tbody>
	</table>