<h1><?php _e('Manage Assignment in', 'namaste')?> <?php echo $this_course->post_title.' / '.$this_lesson->post_title?></h1>

<form method="post" onsubmit="return namasteValidateForm(this);">
	<div class="postbox namaste-form namaste-box">
		<div><label><?php _e('Title:', 'namaste')?></label> <input type="text" name="title" value="<?php echo stripslashes($homework->title)?>" size='80'></div>
		<div><label><?php _e('Description/Requirements:')?></label>
		<?php echo wp_editor(stripslashes(@$homework->description), 'description');?></div>
		<div><input type="checkbox" name="accept_files" value="1" <?php if(!empty($homework->accept_files)) echo 'checked'?>> <?php _e('Accept file upload as solution')?></div>
		<?php do_action('namaste_homework_form', @$homework);?>
		<div>
			<?php if(empty($homework->id)):?>
				<input type="submit" value="<?php _e('Create Assignment', 'namaste')?>" name="ok">
			<?php else:?>
				<input type="submit" value="<?php _e('Save Assignment', 'namaste')?>" name="ok">
				<input type="button" value="<?php _e('Delete Assignment', 'namaste')?>" onclick="namasteConfirmDelete(this.form, '<?php _e('Are you sure?','namaste')?>');">
				<input type="hidden" name="del" value="0">
			<?php endif;?>		
		</div>
	</div>
</form>

<script type="text/javascript" >
function namasteValidateForm(frm) {
	if(frm.title.value=='') {
		alert("<?php _e('Please enter at least title for the assignment', 'namaste')?>");
		frm.title.focus();
		return false;
	}
	
	return true;
}
</script>