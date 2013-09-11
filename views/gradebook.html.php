<div class="wrap">
	<h1><?php _e('Gradebook','namaste');?></h1>
	
	<form method="get" action="admin.php">
	<input type="hidden" name="page" value="namaste_gradebook">
	<p><?php _e('Select course:', 'namaste')?> <select name="course_id" onchange="this.form.submit();">
	<option value=""><?php _e('- please select -', 'namaste')?></option>
	<?php foreach($courses as $course):?>
		<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $_GET['course_id']==$course->ID) echo 'selected'?>><?php echo $course->post_title?></option>
	<?php endforeach;?>
	</select>		
	</p>
	</form>

	<?php if(!empty($this_course->ID)):?>
		<h2><?php printf(__('Grades of the students in %', 'namaste'), $this_course->post_title)?></h2>
		
		<table class="widefat">
			<tr><th rowspan="2"><?php _e('Student', 'namaste');?></th><th colspan="<?php echo sizeof($lessons);?>"><?php _e('Lessons Grades (Shows also grades from assignments)', 'namaste');?></th><th rowspan="2"><?php _e('Final grade for the course', 'namaste')?></th></tr>
			<tr><?php foreach($lessons as $lesson):?><th><?php echo $lesson->post_title?></th><?php endforeach;?></tr>
			<?php foreach($students as $student):?>
				<tr><td><a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $_GET['course_id']?>&student_id=<?php echo $student->ID?>" target="_blank"><?php echo $student->user_nicename?></a></td>
				<?php foreach($lessons as $lesson):?><td><!-- display homework grades if any -->
					<?php $final_lesson_grade = ''; 
					foreach($student->lesson_grades as $lesson_grade):						
					   if($lesson_grade['lesson_id'] != $lesson->ID) continue;
						echo implode(",", $lesson_grade['homework_grades'])." ";
						$final_lesson_grade = $lesson_grade['final_grade'];
					endforeach;?>
					<!-- display lesson grade dropdown -->
					<?php if(in_array($lesson->ID, $student->lesson_ids)):
						 _e('| Final:', 'namaste')?>
						<select name="grade" onchange="NamasteGrade(this, 'lesson', <?php echo $student->ID?>, <?php echo $lesson->ID?>);">
						<option value="">---------</option>
						<?php foreach($grades as $grade):
						 $grade = trim($grade);?>
						 	<option value="<?php echo $grade?>" <?php if($grade == $final_lesson_grade) echo 'selected'?>><?php echo $grade;?></option>
						<?php endforeach;?> 
						</select>
					<?php else: _e('Lesson not started', 'namaste');
					endif;?>
				</td><?php endforeach; // end foreach lesson?>
				<td><!-- course grade dropdown --><select name="grade" onchange="NamasteGrade(this, 'course', <?php echo $student->ID?>, <?php echo $this_course->ID?>);">
					<option value="">---------</option>
					<?php foreach($grades as $grade):
					 $grade = trim($grade);?>
					 	<option value="<?php echo $grade?>" <?php if($grade == $student->course_grade) echo 'selected'?>><?php echo $grade;?></option>
					<?php endforeach;?> 
					</select></td>				
				</tr>
			<?php endforeach;?>
		</table>
	<?php endif;?>
</div>

<script type="text/javascript" >
function NamasteGrade(elt, grType, studentID, itemID) {
	var url = "<?php echo admin_url("admin-ajax.php");?>";
	var data = {'action': 'namaste_ajax', 'type': 'set_grade', 'grade' : elt.value, 'grade_what': grType, 'student_id': studentID, 'item_id': itemID};
	jQuery.post(url, data);
}
</script>