<h1><?php _e('Submitting a solution to assignment', 'namaste');?></h1>

<div class="wrap">
		<?php if($in_shortcode):$permalink = get_permalink($post->ID);
		$params = array('lesson_id' => (int)$_GET['lesson_id']);
		$target_url = add_query_arg( $params, $permalink );?>
		<p><a href="<?php echo $target_url;?>"><?php _e('Back to the assignments', 'namaste')?></a> | <a href="<?php echo get_permalink($lesson->ID);?>"><?php printf(__('Back to lesson "%s"', 'namaste'), stripslashes($lesson->post_title));?></a></p>
		<?php else:?>
		<p><a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $user_ID?>"><?php _e('Back to assignments in', 'namaste')?> "<?php echo $lesson->post_title?>"</a> 
	<?php _e('from course','namaste')?> "<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course->ID?>&student_id=<?php echo $user_ID?>"><?php echo $course->post_title?></a>"
	| <a href="<?php echo get_permalink($lesson->ID);?>"><?php printf(__('View lesson "%s"', 'namaste'), stripslashes($lesson->post_title));?></a>		
	</p>
	<?php endif;?>

	<h2><?php echo $homework->title?></h2>
	
	<div><?php echo apply_filters('namaste_content', stripslashes($homework->description))?></div>

	<p><b><?php _e('Submit your solution below:','namaste')?></b></p>
	
	<form method="post" enctype="multipart/form-data" id="namasteSolutionForm">
	<?php if($homework->accept_files):?>
		<div><label><?php _e('Upload file(s):', 'namaste')?></label> <input type="file" name="files[]" multiple="multiple" id="namasteSolutionFiles"> <?php _e('(You can upload multiple files by holding the control key when seleting them.)', 'namaste');?> </div>
		<?php if($file_upload_progress == 1):?><progress></progress><?php endif;?>
	<?php endif;?>
	<div><?php if($in_shortcode):?>
	<textarea name="content" rows="10" cols="50" class="namaste-submit-solution"></textarea>
	<?php else: wp_editor('', 'content');
	endif;?></div>
	<p align="center">
		<input id="namasteSubmitSolition" type="submit" value="<?php _e('Submit your solution', 'namaste')?>" class="button button-primary">
		<input type="hidden" name="ok" value="1">
		<input type="hidden" name="solution_files_uploaded" id="namasteSolutionFilesUploaded" value="0">
	</p>
	</form>
</div>
<?php if($file_upload_progress == 1):?>
<script type="text/javascript" >
jQuery('#namasteSubmitSolition').on('click', function(e) {
	 if (e.preventDefault) e.preventDefault();

		var submitURL = namaste_i18n.ajax_url + "?action=namaste_ajax&type=submit_solution_files&id=<?php echo intval($_GET['id']);?>";
    jQuery.ajax({
        // Your server script to process the upload
        url: submitURL,
        type: 'POST',

        // Form data
        data: new FormData(jQuery('#namasteSolutionForm')[0]),

        // Tell jQuery not to process data or worry about content-type
        // You *must* include these options!
        cache: false,
        contentType: false,
        processData: false,
        
        // Custom XMLHttpRequest
        xhr: function() {
            var myXhr = jQuery.ajaxSettings.xhr();
            if (myXhr.upload) {
                // For handling the progress of the upload
                myXhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {                    		
                        jQuery('progress').attr({
                            value: e.loaded,
                            max: e.total,
                        });
                        if(e.loaded >= e.total) {
                        	if(jQuery('#namasteSolutionFiles').val() != '') {                        		
                        		alert("<?php _e('Files uploaded!', 'namaste')?>");	                        	
	                        	jQuery('#namasteSolutionFiles').val('');
	                        	jQuery('#namasteSolutionFilesUploaded').val('1');
                        	}
                        	
                        	// submit form 
                           jQuery('#namasteSolutionForm').submit();
                        }
                    }
                } , false);
            }
            return myXhr;
        }
    });
});
</script>
<?php endif;?>
