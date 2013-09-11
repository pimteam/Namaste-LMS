<h1><?php _e('Viewing Solutions for ', 'namaste')?> "<?php echo $homework->title?>"</h1>

<div class="wrap">
	<p><?php _e('Lesson:', 'namaste')?> <strong><?php echo $lesson->post_title?></strong></p>	
	<p><?php _e('Course:', 'namaste')?> <strong><?php echo $course->post_title?></strong></p>

	<?php if(!empty($show_everyone)):?>
		<p><a href="admin.php?page=namaste_homeworks&lesson_id=<?php echo $lesson->ID?>&course_id=<?php echo $course->ID?>"><?php printf(__('Back to assignments for "%s"', 'namaste'), $lesson->post_title);?></a></p>
		<p><strong><?php _e("Showing everyone's solutions on this assignment.", 'namaste')?></strong></p>
		<p><?php _e('Note: when one solution is approved, the assignment will be considered completed by this student and they will not be asked to submit more solutions for it.', 'namaste')?></p>
	<?php else: ?>
		<p><a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student->ID?>"><?php printf(__('Back to assignments for "%s"', 'namaste'), $lesson->post_title);?></a></p>
		<?php if($user_ID != $student->ID):?>
			<p><?php _e('Showing solutions submitted by', 'namaste')?> <strong><?php echo $student->user_login?></strong></p>
			<p><?php _e('Note: when one solution is approved, the assignment will be considered completed by this student and they will not be asked to submit more solutions for it.', 'namaste')?></p>
		<?php endif;		
	endif; // end if showing solutions for particular student
	if(!sizeof($solutions)):
			if(empty($show_everyone)) echo "<p>".__("The student has not submitted any solutions for this assignment yet.", 'namaste')."</p>";
			else echo "<p>".__("No one has submitted any solutions for this assignment yet.", 'namaste')."</p>";
			echo "</div>";
			return true;
	endif;?>
	
	<table class="widefat">
	<?php foreach($solutions as $solution):?>
		<tr><th><?php printf(__('Solution submitted at %s', 'namaste'), date(get_option('date_format'), strtotime($solution->date_submitted)));?>
		<?php if(!empty($show_everyone)):
		 echo __('by','namaste')." <a href='admin.php?page=namaste_lesson_homeworks&lesson_id=".$lesson->ID."&student_id=".$solution->student_id."' target='_blank'>".$solution->user_login."</a>";
		endif;?></th>
		<th><?php _e('Status');?></th>
		<?php if($use_grading_system):?>
			<th><?php _e('Grade', 'namaste')?></th>
		<?php endif;?></tr>
		<tr><td><?php echo apply_filters('the_content', $solution->content);?></td>
		<td><?php if(current_user_can('namaste_manage')):?>
		<form method="post">
			<select name="status" onchange="this.form.submit();">
				<option value="pending" <?php if($solution->status=='pending') echo 'selected'?>><?php _e('Pending', 'namaste')?></option>
				<option value="approved" <?php if($solution->status=='approved') echo 'selected'?>><?php _e('Approved', 'namaste')?></option>
				<option value="rejected" <?php if($solution->status=='rejected') echo 'selected'?>><?php _e('Rejected', 'namaste')?></option>
			</select>
			<input type="hidden" name="change_status" value="1">
			<input type="hidden" name="solution_id" value="<?php echo $solution->id?>">					
		</form>
		<?php else: echo $solution->status;
		endif;?></td>
		<?php if($use_grading_system):?>
			<td><?php if(current_user_can('namaste_manage') and empty($show_everyone)): ?>
				<form method="post">
				<input type="hidden" name="grade_solution" value="1">		
				<input type="hidden" name="id" value="<?php echo $solution->id?>">
				<select name="grade" onchange="this.form.submit();">
					<option value="">---------</option>
					<?php foreach($grades as $grade):
					 $grade = trim($grade);?>
					 	<option value="<?php echo $grade?>" <?php if($grade == $solution->grade) echo 'selected'?>><?php echo $grade;?></option>
					<?php endforeach;?> 
				</select>
				</form>			
			<?php else: echo $solution->grade ? $solution->grade : __('Not graded', 'namaste');
			endif;?></td>
		<?php endif;?>				
		</tr>
	<?php endforeach;?>
	</table>
</div>