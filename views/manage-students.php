<div class="wrap">
	<h1><?php _e('Student Enrollments', 'namaste')?></h1>
	
	<div class="postbox-container">
	<?php if(!sizeof($courses)):?>
	<p><?php _e('Nothing to do here as you have not created any courses yet!')?></p>
	<?php return true;
	endif;?>
	
	<?php if(!empty($error)):?>
		<div class="namaste-error"><?php echo $error?></div>
	<?php endif;?>
	
	<form method="get">
		<input type="hidden" name="page" value="namaste_students">
		<input type="hidden" name="offset" value="<?php echo $offset?>">
		<input type="hidden" name="ob" value="<?php echo $ob?>">
		<input type="hidden" name="dir" value="<?php echo $dir?>">
		<div class="wp-admin namaste-form">
			<p><label><?php _e('Select course:', 'namaste')?></label>
			<select name='course_id' onchange="this.form.submit();">
			<option value=""></option>
			<?php foreach($courses as $course):?>
				<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $course->ID == $_GET['course_id']) echo 'selected'?>><?php echo $course->post_title?></option>
			<?php endforeach;?>
			</select></p>
			<?php if(!empty($_GET['course_id'])):?>
				<p><b><?php _e('Enroll student in the course:', 'namaste')?></b>
				 <input type="text" name="email" size="30" placeholder="<?php _e('Enter email or user login', 'namaste')?>"> 
				 <b><?php _e('Tags (optional):', 'namaste');?></b>
				 <input type="text" name="tags" size="20" placeholder="<?php _e('Separate with comma: tag1, tag 2...', 'namaste');?>">
				<input type="submit" name="enroll" value="<?php _e('Enroll', 'namaste')?>" class="button button-primary">
				<?php if(NamasteLMSMultiUser :: check_access('mass_enroll_access', true) == 'all'):?>
				&nbsp; <a href="admin.php?page=namaste_mass_enroll&course_id=<?php echo (int)$_GET['course_id']?>"><?php _e('[Mass enroll students]', 'namaste');?></a>
				<?php endif;?>				
				</p>
			<?php endif;?>
		</div>
	
	
	<p><?php _e('Filter by student/course status:', 'namaste')?> <select name="status">
		<option value="" <?php if(empty($_GET['status'])) echo 'selected'?>><?php _e('Any status', 'namaste')?></option>
		<option value="pending" <?php if(!empty($_GET['status']) and $_GET['status']=='pending') echo 'selected'?>><?php _e('Pending', 'namaste')?></option>
		<option value="enrolled" <?php if(!empty($_GET['status']) and $_GET['status']=='enrolled') echo 'selected'?>><?php _e('Enrolled', 'namaste')?></option>
		<option value="rejected" <?php if(!empty($_GET['status']) and $_GET['status']=='rejected') echo 'selected'?>><?php _e('Rejected', 'namaste')?></option>
		<option value="completed" <?php if(!empty($_GET['status']) and $_GET['status']=='completed') echo 'selected'?>><?php _e('Completed', 'namaste')?></option>
	</select>
	<?php if(!empty($lessons) and count($lessons)):?>
		&nbsp; <a href="#" onclick="jQuery('#namasteLessonFilters').toggle();return false;"><?php _e('Show/hide status filters for each lesson in this course', 'namaste');?></a>
	<?php endif;?>	
	</p>
	
	<?php if(!empty($lessons) and count($lessons)):?>
		<p id="namasteLessonFilters" style='display:<?php echo empty($lesson_status_sql) ? 'none':'block;'?>'>
			<?php foreach($lessons as $lesson):
				echo stripslashes($lesson->post_title).':';?>
				<select name="lesson_status_<?php echo $lesson->ID;?>">
					<option value="" <?php if(empty($_GET['lesson_status_'.$lesson->ID])) echo 'selected'?>><?php _e('Any status', 'namaste')?></option>
					<option value="not_started" <?php if(!empty($_GET['lesson_status_'.$lesson->ID]) and $_GET['lesson_status_'.$lesson->ID] == 'not_started') echo 'selected'?>><?php _e('Not started', 'namaste')?></option>
					<option value="in_progress" <?php if(!empty($_GET['lesson_status_'.$lesson->ID]) and $_GET['lesson_status_'.$lesson->ID] == 'in_progress') echo 'selected'?>><?php _e('In progress', 'namaste')?></option>
					<option value="pending_approval" <?php if(!empty($_GET['lesson_status_'.$lesson->ID]) and $_GET['lesson_status_'.$lesson->ID] == 'pending_approval') echo 'selected'?>><?php _e('Completed but pending admin approval', 'namaste')?></option>
					<option value="completed" <?php if(!empty($_GET['lesson_status_'.$lesson->ID]) and $_GET['lesson_status_'.$lesson->ID] == 'completed') echo 'selected'?>><?php _e('Completed', 'namaste')?></option>
				</select> <br />
			<?php endforeach;?>	
		</p>
	<?php endif;?>
   
   <p><?php _e('Filter by user login:', 'namaste');?> <input type="text" name="user_login" value="<?php echo esc_attr($_GET['user_login'] ?? '')?>">
   <?php _e('Filter by email:', 'namaste');?> <input type="text" name="user_email" value="<?php echo esc_attr($_GET['user_email'] ?? '')?>">
   <?php _e('Filter by tag:', 'namaste');?> <input type="text" name="filter_tags" value="<?php echo esc_attr($_GET['filter_tags'] ?? '')?>">
   
   
   
   <input type="submit" name="filter" value="<?php _e('Filter students', 'namaste');?>" class="button button-primary">	
	</p>
	
	<?php do_action('namaste-show-students-filter');?>
	<p><?php _e('Per page:', 'namaste');?> <select name="page_limit" onchange="this.form.submit()">
		<option value="10" <?php selected($page_limit, 10)?>>10</option>
		<option value="20" <?php selected($page_limit, 20)?>>20</option>
		<option value="50" <?php selected($page_limit, 50)?>>50</option>
		<option value="100" <?php selected($page_limit, 100)?>>100</option>
		<option value="200" <?php selected($page_limit, 200)?>>200</option>
		<option value="500" <?php selected($page_limit, 500)?>>500</option>
	</select></p>
	</form>
	
	<?php if(!empty($_GET['course_id'])):?>
		<?php if(!count($students)):?>
		<p><?php _e('There are no students enrolled in this course yet.', 'namaste')?></p>
		<?php return false;
		endif;?>
		
		<p><?php _e('The below table shows all students enrolled in this course allong with the status for every lesson in it', 'namaste')?></p>
		<p><a href="<?php echo basename($_SERVER['REQUEST_URI']);?>&export=1&noheader=1"><?php echo _e('Export students table', 'namaste');?></a> <?php _e('(will export a comma delimited CSV file)', 'namaste');?></p>
		
		
		
		<form method="post" action="admin.php?page=namaste_students&course_id=<?php echo (int)$_GET['course_id']?>">
		<table class="widefat">
			<tr>
				<?php if($multiuser_access != 'view'):?>
					<th><input type="checkbox" onclick="namasteSelectAll(this.checked);"></th>
				<?php endif;?>
				<th><a href="admin.php?page=namaste_students&course_id=<?php echo intval($_GET['course_id'])?>&status=<?php echo empty($_GET['status']) ? '' : esc_attr($_GET['status'])?>&user_login=<?php echo empty($_GET['user_login']) ? '' : esc_attr($_GET['user_login'])?>&user_email=<?php echo empty($_GET['user_email']) ? '' : esc_attr($_GET['user_email'])?>&ob=display_name&dir=<?php echo $odir?>&page_limit=<?php echo $page_limit;?>&filter_tags=<?php echo empty($_GET['filter_tags']) ? '' : esc_attr($_GET['filter_tags']);?>"><?php _e('Student', 'namaste')?></a></th>
				<?php do_action('namaste_manage_students_extra_th');?>
				<?php foreach($lessons as $lesson):?>
					<th><?php echo stripslashes($lesson->post_title);?></th>					
				<?php endforeach;?>		
				<th><?php _e('Status in course', 'namaste')?></th>
				<?php	if($use_grading_system):?>
					<th><?php _e('Final grade', 'namaste');?></th>
				<?php endif;?>
			</tr>	
			<?php foreach($students as $student):
				// this page linked in the first cell will be the same for student - when student clicks on enrolled course, 
				// they'll see the same table as the admin will see here
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>">
				<?php if($multiuser_access != 'view'):?>
					<td><input type="checkbox" name="student_ids[]" value="<?php echo $student->ID?>" class="namaste_chk" onclick="namasteShowHideMassButton();"></td>
				<?php endif;?>
				<td><a href="admin.php?page=namaste_student_lessons&course_id=<?php echo intval($_GET['course_id'])?>&student_id=<?php echo $student->ID?>"><?php echo $student->user_login;
				echo '</a>';
				if($student->user_login != $student->display_name) echo "<br>" . $student->display_name;
				echo '<br>' . $student->user_email;?><br>
				<a href="#" onclick="jQuery('#studentEditTags<?php echo $student->scid?>').toggle();jQuery('#studentTags<?php echo $student->scid?>').toggle();return false;"><?php _e('Tags:', 'namaste')?></a>  
				<?php echo '<span id="studentTags'.$student->scid.'"><i>'.str_replace(',', ', ', ($student->tags ? $student->tags : __('None', 'namaste'))).'</i></span>';?>
				<div id="studentEditTags<?php echo $student->scid?>" style="display:none;"><input type="text" id="studentTagsFld<?php echo $student->scid?>" value="<?php echo str_replace(',', ', ', $student->tags);?>"><input type="button" value="<?php _e('Save', 'namaste');?>" onclick="namasteSaveTags(<?php echo $student->scid?>)"></div></td>
				<?php do_action('namaste_manage_students_extra_td', $student);?>
				<?php foreach($lessons as $lesson):?>
					<td><?php if(in_array($lesson->ID, $student->completed_lessons)): _e('Completed', 'namaste');
					elseif(in_array($lesson->ID, $student->incomplete_lessons)): echo "<a href='#' onclick='namasteInProgress(".$lesson->ID.", ".$student->ID."); return false;'>".__('In progress', 'namaste')."</a>";
					else: _e('Not started', 'namaste'); endif;?>
					<?php	if($use_grading_system and !empty($student->relations[$lesson->ID]->grade)):?><br><?php printf(__('Grade: %s', 'namaste'), $student->relations[$lesson->ID]->grade);?><?php endif;?></td>
				<?php endforeach;?>		
				<td><?php switch($student->namaste_status):
					case 'pending': _e('Pending', 'namaste');
						echo "<br>".sprintf(__('Since %s', 'namaste'), date_i18n($dateformat, strtotime($student->enrollment_date))).'<br>';  
					break;
					case 'enrolled': 
						_e('Enrolled', 'namaste');
						echo "<br>".sprintf(__('Since %s', 'namaste'), date_i18n($dateformat, strtotime($student->enrollment_date))).'<br>';
					break;
					case 'rejected': _e('Rejected', 'namaste'); break;
					case 'completed': 
						_e('Completed', 'namaste');
						echo "<br>".sprintf(__('On %s', 'namaste'), date_i18n($dateformat, strtotime($student->completion_date))).'<br>';
					break;
					case 'frozen': _e('Frozen', 'namaste'); break;
				endswitch;
				if($multiuser_access != 'view' and $student->namaste_status=='pending'):?>
					(<a href="#" onclick="namasteConfirmStatus('enrolled',<?php echo $student->ID?>);return false;"><?php _e('approve', 'namaste')?></a> | <a href="#" onclick="namasteConfirmStatus('rejected',<?php echo $student->ID?>);return false;"><?php _e('reject', 'namaste')?></a>)
				<?php endif;
				if($multiuser_access != 'view' and ($student->namaste_status == 'completed' or $student->namaste_status == 'rejected' 
					or $student->namaste_status == 'enrolled' or $student->namaste_status == 'frozen')):?>
				(<a href="#" onclick="namasteConfirmCleanup('<?php echo $student->ID?>');return false;"><?php _e('Cleanup', 'namaste')?></a>)
				<?php endif;?></td>
				<td><?php echo empty($student->grade) ? __('n/a', 'namaste') : $student->grade;?></td></tr>
			<?php endforeach;?>
		</table>
		
		<p align="center" id="namasteMassBtn" style="display:none;"><input type="button" value="<?php _e('Mass cleanup selected students', 'namaste');?>" onclick="namasteMassCleanup(this.form);">
			<?php if(!empty($any_pending)):?>
				<input type="button" value="<?php _e('Mass approve', 'namaste');?>" onclick="namasteMassProcess(this.form, true);">
				<input type="button" value="<?php _e('Mass reject', 'namaste');?>" onclick="namasteMassProcess(this.form, false);">
				<input type="hidden" name="mass_approve" value="0">
				<input type="hidden" name="mass_reject" value="0">
			<?php endif;?>		
		</p>
		<input type="hidden" name="mass_cleanup" value="0">

		<?php wp_nonce_field('namaste_manage_students');?>
		</form>
		
		<p align="center"><?php if($offset > 0):?>
			<a href="admin.php?page=namaste_students&course_id=<?php echo intval($_GET['course_id'])?>&status=<?php echo esc_attr($_GET['status'] ?? '')?>&offset=<?php echo $offset - $page_limit?>&user_login=<?php echo empty($_GET['user_login']) ? '' : esc_attr($_GET['user_login'])?>&user_email=<?php echo empty($_GET['user_email']) ? '' : esc_attr($_GET['user_email'])?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&page_limit=<?php echo $page_limit;?>&filter_tags=<?php echo empty($_GET['filter_tags']) ? '' : esc_attr($_GET['filter_tags']);?>"><?php _e('[previous page]', 'namaste')?></a>
		<?php endif;?> 
		<?php if($count > ($page_limit + $offset)):?>
			<a href="admin.php?page=namaste_students&course_id=<?php echo intval($_GET['course_id'])?>&status=<?php echo esc_attr($_GET['status'] ?? '')?>&offset=<?php echo $offset + $page_limit?>&user_login=<?php echo empty($_GET['user_login']) ? '' : esc_attr($_GET['user_login'])?>&user_email=<?php echo empty($_GET['user_email']) ? '' : esc_attr($_GET['user_email'])?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&page_limit=<?php echo $page_limit;?>&filter_tags=<?php echo empty($_GET['filter_tags']) ? '' : esc_attr($_GET['filter_tags']);?>"><?php _e('[next page]', 'namaste')?></a>
		<?php endif;?>	
		</p>
	<?php endif;?>
	
	<?php if(is_plugin_active('namaste-reports/namaste-reports.php')):?>
		<p><?php printf(__("Don't forget that you can get <a href='%s'>Advanced Reports</a> for your students performance.", 'namaste'), "admin.php?page=namasterep")?></p>
	<?php else:?>
		<p><?php printf(__("You can get advanced reports and ranking shortcodes with the <a href='%s' target='_blank'>Namaste! Reports</a> plugin.", 'namaste'), "http://namaste-lms.org/reports.php")?></p>
	<?php endif;?>
	
	</div>
</div>
<script type="text/javascript" >
function namasteConfirmStatus(status, id) {	
	if(!confirm("<?php _e('Are you sure?','namaste')?>")) return false;
	
	window.location="admin.php?page=namaste_students&course_id=<?php echo intval($_GET['course_id'] ?? 0)?>&change_status=1&status="+status	
		+ "&student_id="+id;	
}

function namasteInProgress(lessonID, studentID) {
	tb_show("<?php _e('Lesson progress', 'namaste')?>", 
		'<?php echo admin_url("admin-ajax.php?action=namaste_ajax&type=lesson_progress")?>&lesson_id=' + lessonID + 
		'&student_id=' + studentID);
}

<?php
// Generate the nonce in PHP
$cleanup_nonce = wp_create_nonce('namaste_cleanup');
?>

function namasteConfirmCleanup(studentID) {
	let namaste_cleanup_nonce = "<?php echo esc_js($cleanup_nonce); ?>";

    if (confirm("<?php _e('Are you sure to cleanup this record? It will be removed from the system and history and the user will be able to enroll or request enrollment again', 'namaste'); ?>")) {
        // Append the nonce to the URL
        window.location = 'admin.php?page=namaste_students&course_id=<?php echo intval($_GET["course_id"] ?? 0); ?>&status=<?php echo esc_attr($_GET["status"] ?? ''); ?>&cleanup=1&student_id=' + studentID + '&_wpnonce=' + namaste_cleanup_nonce;
    }
}

function namasteSelectAll(status) {
	if(status) jQuery('.namaste_chk').attr('checked', true);
	else jQuery('.namaste_chk').removeAttr('checked');
	
	namasteShowHideMassButton();
}

function namasteShowHideMassButton() {	
	var anyChecked = false;
	jQuery('.namaste_chk').each(function(index){
		if(jQuery(this).is(':checked')) anyChecked = true;
	});
	
	if(anyChecked) jQuery('#namasteMassBtn').show();
	else jQuery('#namasteMassBtn').hide();
}

function namasteMassCleanup(frm) {
	if(confirm("<?php _e('Are you sure?', 'namaste');?>")) {
		frm.mass_cleanup.value = 1;
		frm.submit();
	}
}

// mass approve or reject
function namasteMassProcess(frm, approve) {
	if(confirm("<?php _e('Are you sure?', 'namaste');?>")) {
		if(approve) frm.mass_approve.value = 1;
		else frm.mass_reject.value = 1;
		frm.submit();
	}
}

// save tags
function namasteSaveTags(id) {
	// get tags
	var tags = jQuery('#studentTagsFld' + id).val();
	
	jQuery('#studentTags' + id).html(tags);
	jQuery('#studentEditTags' + id).toggle();
	jQuery('#studentTags' + id).toggle();

	var url = "<?php echo admin_url("admin-ajax.php");?>";
	var data = {'action': 'namaste_ajax', 'type': 'set_student_tags', 'tags' : tags, 'student_course_id': id};
	
	jQuery.post(url, data);
}
</script>
