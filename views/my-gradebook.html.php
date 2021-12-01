<div class="wrap">
	<?php if(!$course_id):?>
		<h1><?php _e('My Gradebook', 'namaste');?></h1>
	<?php if(count($courses)):?>
		<form method="get" action="<?php echo empty($in_shortcode)? 'admin.php' : $current_url;?>">
		<input type="hidden" name="page" value="namaste_my_gradebook">
		<p><?php _e('Select course:', 'namaste')?> <select name="course_id" onchange="this.form.submit();">
		<option value=""><?php _e('- please select -', 'namaste')?></option>
		<?php foreach($courses as $course):?>
			<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $_GET['course_id']==$course->ID) echo 'selected'?>><?php echo $course->post_title?></option>
		<?php endforeach;?>
		</select>		
		</p>
		</form>
	<?php else:?>
		<p><?php _e('You need to enroll some courses first.', 'namaste')?></p>
	<?php endif;
	endif; // end if not $course_id?>	
	
	<?php if(!empty($this_course->ID)):?>
		<h2><?php printf(__("My grades in %s", 'namaste'), stripslashes($this_course->post_title));?></h2>
		
		<p><?php _e('Final grade for the whole course:', 'namaste');?></p>
		
		<table class="widefat">
			<tr><th><?php _e('Lesson', 'namaste')?></th><th><?php _e('Grades from assignments', 'namaste')?></th><th><?php _e('Final grade', 'namaste')?></th></tr>
			<?php foreach($lessons as $lesson):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo $lesson->post_title?></td>
				<td><?php if(sizeof($lesson->homeworks)):?><table class="widefat">
					<?php foreach($lesson->homeworks as $homework):?>
						<tr class="alternate"><th colspan="2"><?php echo __('Assignment:', 'namaste').' '.stripslashes($homework->title)?></th></tr>
						<?php if(sizeof($homework->solutions)): 
							echo "<tr><th>".__("Solution", 'namaste')."</td><td>".__('Grade', 'namaste')."</td></tr>";
							foreach($homework->solutions as $solution):?>
								<tr><td><?php printf(__('Solution submitted at %s', 'namaste'), date(get_option('date_format'),strtotime($solution->date_submitted)));?></td>
								<td><?php echo $solution->grade ? $solution->grade : __('Not graded', 'namaste')?></td></tr>
							<?php endforeach; // end foreach solution
							else:?>
							<tr><td colspan="2"><?php _e('No solutions submitted yet.', 'namaste')?></td></tr>
					<?php endif; // end if no solutions 
					endforeach; // end foreach homework?>					
				</table><?php else: _e('No assignments','namaste'); endif;?></td>				
				<td><?php echo $lesson->grade ? $lesson->grade : __('Not graded', 'namaste');?></td></tr>
			<?php endforeach; ?>
		</table>
	<?php endif;?>
</div>