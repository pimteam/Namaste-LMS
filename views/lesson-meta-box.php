<h4><?php _e('Assign to Courses:', 'namaste')?></h4>
 
<?php if(!sizeof($courses)) echo "<p>".__('No courses have been created yet!', 'namaste')."</p>";?> 
<p><label><?php _e('Select course:', 'namaste')?></label>
<select name="namaste_course">
<?php foreach($courses as $course):?>
	<option value="<?php echo $course->ID?>"<?php if($course->ID == $course_id) echo 'selected'?>><?php echo $course->post_title?></option>
<?php endforeach;?>
</select></p>
		
<h4><?php _e('Lesson Access', 'namaste')?></h4>

<?php if(!sizeof($other_lessons)):?>
	<p><?php _e('There are no other lessons in this course. So this lesson will be accessible to anyone who enrolled the course.', 'namaste')?></p>
<?php else: 
echo '<p>'.__('This lesson will be accessible only after the following lessons are completed:','namaste').'</p>'; 
foreach($other_lessons as $lesson):?>
	<p><input type="checkbox" name="namaste_access[]" value="<?php echo $lesson->ID?>" <?php if(in_array($lesson->ID, $lesson_access)) echo "checked"?>> <?php echo $lesson->post_title?></p>
<?php endforeach;
endif;?>

<h4><?php _e('Lesson Completeness', 'namaste')?></h4>

<p><?php _e('The minimum requirement for a lesson to be completed is to be visited by the student. However you can add some extra requirements here:', 'namaste')?></p>

<p><input type="checkbox" name="namaste_completion[]" value="admin_approval" <?php if(in_array('admin_approval', $lesson_completion)) echo 'checked'?>> <?php _e('Lesson completion will be manually verified and approved by the admin for every student.', 'namaste')?></p>

<?php if(!empty($homeworks) and sizeof($homeworks)):?>
<p><?php _e('The following assignments/homeworks must be completed:', 'namaste')?></p>
<ul>
	<?php foreach($homeworks as $homework):?>
		<li><input type="checkbox" name="namaste_required_homeworks[]" value="<?php echo $homework->id?>"<?php if(in_array($homework->id, $required_homeworks)) echo 'checked'?>> <?php echo $homework->title?></li>
	<?php endforeach;?>
</ul>
<?php endif;?>

<?php if($use_exams and sizeof($exams)):?>
	<p><?php _e('The following quiz must be completed:', 'namaste')?></p>
	<p><select name="namaste_required_exam" onchange="namasteLoadGrades(this.value);">
	<option value=""><?php _e('- No quiz required -', 'namaste')?></option>
	<?php foreach($exams as $exam):?>
		<option value="<?php echo $exam->ID?>" <?php if($exam->ID == $required_exam) echo 'selected'?>><?php echo $exam->name?></option>
	<?php endforeach;?>
	</select> 
	<span id='namasteGradeRequirement' style="display:<?php echo $required_exam?'inline':'none'?>">
	<?php _e('with the following grade achieved:', 'namaste')?>
		<span id="namasteGradeSelection">
			<?php if($required_exam):?>
				<select name="namaste_required_grade">
					<option value=""><?php _e('- Any grade -')?></option>
					<?php foreach($required_grades as $grade):?>
						<option value="<?php echo $grade->gtitle?>" <?php if($grade->gtitle == $required_grade) echo 'selected'?>><?php echo $grade->gtitle?></option>
					<?php endforeach;?>
				</select>
			<?php endif;?>
		</span>
	</span></p>	
	
	<script type="text/javascript" >
	function namasteLoadGrades(examID) {
		var exams = { <?php foreach($exams as $exam): echo $exam->ID.' : { ';
				foreach($exam->grades as $grade): echo $grade->ID.' : "'.str_replace('"','', $grade->gtitle).'", '; endforeach; 
			echo '}, '; endforeach;?>	
		}; // end exams object

		// construct grades dropdown
		if(!examID) {
			jQuery('#namasteGradeRequirement').hide();
			return false;
		}	
		
		html = '<select name="namaste_required_grade"> <option value=""><?php _e('- Any grade -')?></option>';
		if(!exams[examID]) return false;		
		exam = exams[examID];
		
		jQuery.each(exam, function(index, value){
			html += '<option value="'+index+'">' + value + '</option>';		
		});		
		
		jQuery('#namasteGradeSelection').html(html);
		jQuery('#namasteGradeRequirement').show();
	}
	</script>
<?php else: printf('<p style="font-weight:bold;">'.__('If you install %s or %s you can also require certain tests and quizzes to be completed.', 'namaste'), 
	"<a href='http://wordpress.org/extend/plugins/watu/' target='_blank'>Watu</a>", "<a href='http://calendarscripts.info/watupro/' target='_blank'>WatuPRO</a>").'</p>';
endif;?>

<h4><?php _e('Shortcodes', 'namaste')?></h4>

<p><?php _e('You can use the shortcode', 'namaste')?> <strong>[namaste-todo]</strong> <?php _e('inside the lesson content to display what the student needs to do to complete the lesson.', 'namaste')?></p>