<div class="wrap">
   <?php if(!$in_shortcode):?>
	<h1><?php _e('Gradebook','namaste');?></h1>
	<form method="get" action="admin.php">
	<input type="hidden" name="page" value="namaste_gradebook">
	<p><?php _e('Select course:', 'namaste')?> <select name="course_id" onchange="this.form.submit();">
	<option value=""><?php _e('- please select -', 'namaste')?></option>
	<?php foreach($courses as $course):?>
		<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $_GET['course_id']==$course->ID) echo 'selected'?>><?php echo stripslashes($course->post_title)?></option>
	<?php endforeach;?>
	</select>		
	</p>
	</form>
   <?php endif;?>
   
	<?php if(!empty($this_course->ID)):?>
		<?php if(!$in_shortcode):?>
		<p><?php _e('View-only shortcode for this gradebook:', 'namaste');?> <input type="text" value='[namaste-gradebook course_id="<?php echo $this_course->ID?>" public_view="true" compact=1]' size="50" onclick="this.select();" readonly="readonly"><br />
		<?php _e('When the parameter "public_view" is set to "true" or entirely missing, the gradebook is visible for everyone. Set the parameter to "false" to apply user-access permissions so teachers / managers see only students in their groups (if any), students see only their own gradebook, and non-logged in users see no gradebook at all.', 'namaste');?><br />
		<?php printf(__("Pass the parameter <b>%s</b> if the course has several lessons and they don't fit well info a horizontal table. Remove the parameter if you prefer to display the table like in the admin page.", 'namaste'), 'compact=1');?></p>
		<h2><?php printf(__('Grades of the students in %s', 'namaste'), stripslashes($this_course->post_title))?></h2><?php endif;?>
		
		<div role="region" aria-labelledby="caption" tabindex="0">
			<table class="widefat namaste-table namaste-gradebook">
				<thead>
				<tr><th rowspan="<?php echo empty($atts['compact']) ? 2 : 1?>"><?php _e('Student', 'namaste');?></th><th colspan="<?php echo empty($atts['compact']) ? count($lessons) : 1;?>"><?php _e('Lessons Grades (Shows also grades from assignments)', 'namaste');?></th><th rowspan="2"><?php _e('Final grade for the course', 'namaste')?></th></tr>
				<?php if(empty($atts['compact'])):?><tr><?php foreach($lessons as $lesson):?><th><?php echo stripslashes($lesson->post_title)?></th><?php endforeach;?></tr><?php endif;?>
				</thead>
				<tbody>
				<?php foreach($students as $student):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>"><td>
					<?php if($in_shortcode): echo $student->user_nicename;
					else:?> 
					<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $_GET['course_id']?>&student_id=<?php echo $student->ID?>" target="_blank"><?php echo $student->user_nicename?></a>
					<?php endif;?>
					</td>
					<?php
					if(!empty($atts['compact'])) echo '<td>'; // display one table cell for all lessons
					foreach($lessons as $lesson):
						if(empty($atts['compact'])) echo '<td>';
						else echo '<h3>'.stripslashes($lesson->post_title).'</h3>'; ?><!-- display homework grades if any -->
						<?php $final_lesson_grade = ''; 
						foreach($student->lesson_grades as $lesson_grade):						
						   if($lesson_grade['lesson_id'] != $lesson->ID) continue;
							echo implode(",", $lesson_grade['homework_grades'])." ";
							$final_lesson_grade = $lesson_grade['final_grade'];
						endforeach;?>
						<!-- display lesson grade dropdown -->
						<?php if(in_array($lesson->ID, $student->lesson_ids)):
							 if(!empty($lesson_grade['homework_grades'])):_e('| ', 'namaste'); endif;
							 _e('Final:', 'namaste');
							 if(!$in_shortcode):?>
							<select name="grade" onchange="NamasteGrade(this, 'lesson', <?php echo $student->ID?>, <?php echo $lesson->ID?>);">
							<option value="">---------</option>
							<?php foreach($grades as $grade):
							 $grade = trim($grade);?>
							 	<option value="<?php echo $grade?>" <?php if($grade == $final_lesson_grade) echo 'selected'?>><?php echo $grade;?></option>
							<?php endforeach;?> 
							</select> <br>
							<input type="text" value="[namaste-grade lesson_id=<?php echo $lesson->ID?>]" size="20" readonly="true" onclick="this.select();">
						<?php else:
						       echo ' '; 
						       echo '<b>'.($final_lesson_grade ? $final_lesson_grade : __('N/a', 'namaste')).'</b>'; // in shortcode 
						    endif; // end if shortcode or not
						else: _e('Lesson not started', 'namaste');
						endif;
					if(empty($atts['compact'])) echo '</td>';
					else echo '<hr />'; ?>
					<?php endforeach; // end foreach lesson
					if(!empty($atts['compact'])) echo '</td>'; // close the lessons cell ?>
					<td><?php if($in_shortcode):
					  echo '<b>'. ($student->course_grade ? $student->course_grade : __('N/a', 'namaste')).'</b>';
					 else:?>
					<!-- course grade dropdown --><select name="grade" onchange="NamasteGrade(this, 'course', <?php echo $student->ID?>, <?php echo $this_course->ID?>);">
						<option value="">---------</option>
						<?php foreach($grades as $grade):
						 $grade = trim($grade);?>
						 	<option value="<?php echo $grade?>" <?php if($grade == $student->course_grade) echo 'selected'?>><?php echo $grade;?></option>
						<?php endforeach;?> 
						</select>
						<?php endif;?></td>				
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</div>	
		<?php if(!$in_shortcode):?>
		<p><?php _e('Shortcode to display final course grade for a student:', 'namaste')?> <input type="text" value="[namaste-grade course_id=<?php echo $this_course->ID?> userlogin='' whenempty='N/A']" size="50" readonly="true" onclick="this.select();"><br>
	<?php _e('When userlogin is left empty it will display the grade of the current user. If you pass username, it will display the grade of that user. The "whenempty" parameter lets you define text to show when there is no grade assigned yet.', 'namaste')?></p>
	<?php endif;?>
		
</div>

<script type="text/javascript" >
function NamasteGrade(elt, grType, studentID, itemID) {
	var url = "<?php echo admin_url("admin-ajax.php");?>";
	var data = {'action': 'namaste_ajax', 'type': 'set_grade', 'grade' : elt.value, 'grade_what': grType, 'student_id': studentID, 'item_id': itemID};
	jQuery.post(url, data);
}
</script>
   <?php endif;?>