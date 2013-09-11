<h1><?php _e('Student Enrollments', 'namaste')?></h1>

<?php if(!sizeof($courses)):?>
<p><?php _e('Nothing to do here as you have not created any courses yet!')?></p>
<?php return true;
endif;?>

<?php if(!empty($error)):?>
	<div class="namaste-error"><?php echo $error?></div>
<?php endif;?>

<form method="get">
	<input type="hidden" name="page" value="namaste_students">
	<div class="wp-admin namaste-form">
		<p><label><?php _e('Select course:', 'namaste')?></label>
		<select name='course_id' onchange="this.form.submit();">
		<option value=""></option>
		<?php foreach($courses as $course):?>
			<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $course->ID == $_GET['course_id']) echo 'selected'?>><?php echo $course->post_title?></option>
		<?php endforeach;?>
		</select></p>
		<?php if(!empty($_GET['course_id'])):?>
			<p><label><?php _e('Enroll student in the course:', 'namaste')?></label>
			 <input type="text" name="email" size="30" placeholder="<?php _e('Enter email', 'namaste')?>"> 
			<input type="submit" name="enroll" value="<?php _e('Enroll', 'namaste')?>"></p>
		<?php endif;?>
	</div>
</form>

<p><?php _e('Filter by student/course status:', 'namaste')?> <select name="status" onchange="window.location='admin.php?page=namaste_students&course_id=<?php echo $_GET['course_id']?>&status='+this.value;">
	<option value="" <?php if(empty($_GET['status'])) echo 'selected'?>><?php _e('Any status', 'namaste')?></option>
	<option value="pending" <?php if(!empty($_GET['status']) and $_GET['status']=='pending') echo 'selected'?>><?php _e('Pending', 'namaste')?></option>
	<option value="enrolled" <?php if(!empty($_GET['status']) and $_GET['status']=='enrolled') echo 'selected'?>><?php _e('Enrolled', 'namaste')?></option>
	<option value="rejected" <?php if(!empty($_GET['status']) and $_GET['status']=='rejected') echo 'selected'?>><?php _e('Rejected', 'namaste')?></option>
	<option value="completed" <?php if(!empty($_GET['status']) and $_GET['status']=='completed') echo 'selected'?>><?php _e('Completed', 'namaste')?></option>
</select></p>

<?php if(!empty($_GET['course_id'])):?>
	<?php if(!sizeof($students)):?>
	<p><?php _e('There are no students enrolled in this course yet.', 'namaste')?></p>
	<?php return false;
	endif;?>
	
	<p><?php _e('The below table shows all students enrolled in this course allong with the status for every lesson in it', 'namaste')?></p>
	<table class="widefat">
		<tr><th><?php _e('Student', 'namaste')?></th>
			<?php foreach($lessons as $lesson):?>
				<th><?php echo $lesson->post_title?></th>
			<?php endforeach;?>		
			<th><?php _e('Status', 'namaste')?></th>
		</tr>	
		<?php foreach($students as $student):
			// this page linked in the first cell will be the same for student - when student clicks on enrolled course, 
			// they'll see the same table as the admin will see here?>
			<tr><td><a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $_GET['course_id']?>&student_id=<?php echo $student->ID?>"><?php echo $student->user_login?></td>
			<?php foreach($lessons as $lesson):?>
				<td><?php if(in_array($lesson->ID, $student->completed_lessons)): _e('Completed', 'namaste');
				elseif(in_array($lesson->ID, $student->incomplete_lessons)): echo "<a href='#' onclick='namasteInProgress(".$lesson->ID.", ".$student->ID."); return false;'>".__('In progress', 'namaste')."</a>";
				else: _e('Not started', 'namaste'); endif;?></td>
			<?php endforeach;?>		
			<td><?php echo $student->namaste_status;
			if($student->namaste_status=='pending'):?>
				(<a href="#" onclick="namasteConfirmStatus('enrolled',<?php echo $student->ID?>);return false;"><?php _e('approve', 'namaste')?></a> | <a href="#" onclick="namasteConfirmStatus('rejected',<?php echo $student->ID?>);return false;"><?php _e('reject')?></a>)
			<?php endif;
			if($student->namaste_status == 'completed' or $student->namaste_status == 'rejected'):?>
			(<a href="#" onclick="namasteConfirmCleanup('<?php echo $student->ID?>');return false;"><?php _e('Cleanup', 'namaste')?></a>)
			<?php endif;?></td></tr>
		<?php endforeach;?>
	</table>
<?php endif;?>

<script type="text/javascript" >
function namasteConfirmStatus(status, id) {	
	if(!confirm("<?php _e('Are you sure?','namaste')?>")) return false;
	
	window.location="admin.php?page=namaste_students&course_id=<?php echo $_GET['course_id']?>&change_status=1&status="+status	
		+ "&student_id="+id;	
}

function namasteInProgress(lessonID, studentID) {
	tb_show("<?php _e('Lesson progress', 'namaste')?>", 
		'<?php echo admin_url("admin-ajax.php?action=namaste_ajax&type=lesson_progress")?>&lesson_id=' + lessonID + 
		'&student_id=' + studentID);
}

function namasteConfirmCleanup(studentID) {
	if(confirm("<?php _e('Are you sure to cleanup this record? It will be removed from the system and history and the user will be able to enroll or request enrollment again', 'namaste')?>")) {
		window.location = 'admin.php?page=namaste_students&course_id=<?php echo @$_GET["course_id"]?>&status=<?php echo @$_GET["status"]?>&cleanup=1&student_id='+studentID;
	}
}
</script>