<h1><?php _e('Manage Assignment in', 'namaste')?> <?php echo stripslashes($this_course->post_title.' / '.$this_lesson->post_title)?></h1>

<p><a href="admin.php?page=namaste_homeworks&course_id=<?php echo $this_course->ID?>&lesson_id=<?php echo $this_lesson->ID?>"><?php _e('Back to assignments', 'namaste');?></a></p>

<form method="post" onsubmit="return namasteValidateForm(this);">
	<div class="postbox namaste-form namaste-box">
		<p><label><?php _e('Title:', 'namaste')?></label> <input type="text" name="title" value="<?php echo stripslashes($homework->title ?? '')?>" size='80'></p>
		<p><label><?php _e('Description/Requirements:', 'namaste')?></label>
		<?php echo wp_editor(stripslashes(@$homework->description), 'description');?></p>

		<p><input type="checkbox" name="self_approving" value="1" <?php if(!empty($homework->self_approving)) echo 'checked'?> onclick="this.checked ? jQuery('.no-self-approving').hide() : jQuery('.no-self-approving').show();"> <?php _e('Students do not need to submit solutions on this homework. They will mark it as completed themselves.', 'namaste')?></p>		
		
		<p class="no-self-approving" style='display:<?php echo empty($homework->self_approving) ? 'block' : 'none';?>'><input type="checkbox" name="accept_files" value="1" <?php if(!empty($homework->accept_files)) echo 'checked'?>> <?php _e('Accept file upload as solution', 'namaste')?></p>
		
		<p class="no-self-approving" style='display:<?php echo empty($homework->self_approving) ? 'block' : 'none';?>'><input type="checkbox" name="auto_approve" value="1" <?php if(!empty($homework->auto_approve)) echo 'checked'?>> <?php _e('The assignment gets automatically approved upon submitting.', 'namaste')?></p>
		
		<?php if(get_option('namaste_use_points_system')):?>
			<p><?php _e('Reward', 'namaste')?> <input type="text" size="4" name="award_points" value="<?php echo $homework->award_points ?? ''?>"> <?php _e('points for successfully completing this assignment.', 'namaste')?></p>
		<?php endif;?>
		
		<p class="no-self-approving" style='display:<?php echo empty($homework->self_approving) ? 'block' : 'none';?>'><input type="checkbox" name="limit_by_date" value="1" <?php if(!empty($homework->limit_by_date)) echo 'checked'?> onclick="this.checked ? jQuery('#limitByDate').show() : jQuery('#limitByDate').hide();"> <?php _e('Solutions will be accepted only within date interval:', 'namaste');?>
			<span id="limitByDate" style='display:<?php echo empty($homework->limit_by_date) ? 'none' : 'inline';?>'>
			<?php _e('From:', 'namaste')?> <input type="text" name="accept_date_from" value="<?php echo empty($homework->accept_date_from) ? date('Y-m-d') : $homework->accept_date_from?>" class="namasteDatePicker">
			<?php _e('To:', 'namaste')?> <input type="text" name="accept_date_to" value="<?php echo empty($homework->accept_date_to) ? date('Y-m-d') : $homework->accept_date_to?>" class="namasteDatePicker">
		</p>
		
		<?php if(get_option('namaste_use_grading_system') == 'on'):?>
		   <p><input type="checkbox" name="auto_grade_lesson"value="1" <?php if(!empty($homework->auto_grade_lesson)) echo 'checked'?>> <?php _e('The grade of this homework automatically becomes lesson grade.', 'namaste');?></p>
      <?php endif;?>
		
		<?php do_action('namaste_homework_form', (empty($homework) ? null : $homework) );?>
		<p>
			<?php if(empty($homework->id)):?>
				<input type="submit" value="<?php _e('Create Assignment', 'namaste')?>" name="ok" class="button button-primary">
			<?php else:?>
				<input type="submit" value="<?php _e('Save Assignment', 'namaste')?>" name="ok" class="button button-primary">
				<input type="button" value="<?php _e('Delete Assignment', 'namaste')?>" onclick="namasteConfirmDelete(this.form, '<?php _e('Are you sure?','namaste')?>');" class="button">
				<input type="hidden" name="del" value="0">
			<?php endif;?>		
		</p>
	</div>
	<?php wp_nonce_field('namaste_homework');?>
</form>

<script type="text/javascript" >
jQuery(document).ready(function() {
    jQuery('.namasteDatePicker').datepicker({
		dateFormat : 'yy-mm-dd'    
    });
});	

function namasteValidateForm(frm) {
	if(frm.title.value == '') {
		alert("<?php _e('Please enter at least title for the assignment', 'namaste')?>");
		frm.title.focus();
		return false;
	}
	
	if( (new Date(frm.accept_date_from.value).getTime() > new Date(frm.accept_date_to.value).getTime())) {
		alert("<?php _e('The end date is before the start date.', 'namaste');?>");
		frm.accept_date_to.focus();
		return false;
	}
	
	return true;
}
</script>
