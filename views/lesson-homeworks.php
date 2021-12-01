<h1><?php _e('Assignments for lesson', 'namaste')?> "<?php echo $lesson->post_title?>"</h1>

<?php if($user_ID != $_GET['student_id']):?>
	<h3><?php _e('Showing assignments of', 'namaste')?> <strong><?php echo $student->user_login?>.</strong></h3>
<?php endif;?>

<?php if(!$in_shortcode):?>
	<p><a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course_id?>&student_id=<?php echo $_GET['student_id']?>"><?php _e('Back to the lessons page in this course', 'namaste')?></a></p>
<?php endif;?>	

<?php if(!sizeof($homeworks)):
	echo "<p>".__('There are no homeworks in this lesson', 'namaste').'</p>';
	return false;
endif;?>

<table class="widefat">
	<tr><th width="50%"><?php _e('Assignment title and description', 'namaste')?></th><th><?php _e('Solutions', 'namaste')?></th>		
		<th><?php _e('Notes / Feedback', 'namaste')?></th>
		<?php do_action('namaste_extra_th', 'lesson_homeworks');?>	
		</tr>
	<?php foreach($homeworks as $homework):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>"><td><h2><?php echo stripslashes($homework->title)?></h2>
		<?php echo apply_filters('namaste_content', stripslashes($homework->description))?></td>
		<td><p><?php if(!count($homework->solutions)): _e('None yet.', 'namaste');
		else: 
			if($in_shortcode):
				$permalink = get_permalink($post->ID);
			   $params = array('id' => $homework->id, 'view_solutions' => 1);
				$target_url = add_query_arg( $params, $permalink );
				echo "<a href='".$target_url."'>".sprintf(__('%d solutions', 'namaste'), sizeof($homework->solutions))."</a>";
			else: echo "<a href='admin.php?page=namaste_view_solutions&student_id=".$student->ID."&id=".$homework->id."'>".sprintf(__('%d solutions', 'namaste'), sizeof($homework->solutions))."</a>";
			endif; // end not in shortcode
		endif;?></p>
		<?php if(!$manager_mode):
			if($homework->status):?>
				<p><?php _e('A solution has been accepted and the assignment is completed.','namaste')?></p>
			<?php else:
				if($homework->limit_by_date and 
					(current_time('timestamp') < strtotime($homework->accept_date_from.' 00:00:00')
						or current_time('timestamp') > strtotime($homework->accept_date_to.' 23:59:59')) ):?>
					<p><?php printf(__('Solutions will be accepted between %s and %s.', 'namaste'),
						date_i18n($dateformat, strtotime($homework->accept_date_from)),
						date_i18n($dateformat, strtotime($homework->accept_date_to)));?></p>	
				<?php else:
					// Submit solution or mark completed part
					if($homework->self_approving):?>
					<form method="post"><p><input type="submit" class="button button-primary" value="<?php _e('Mark completed', 'namaste');?>"></p>
					<?php wp_nonce_field('namaste_mark_solution');?>
					<input type="hidden" name="mark_completed" value="<?php echo $homework->id?>"></form>	
					<?php else:	// end if self approving	
				   	if($in_shortcode):
					   	$permalink = get_permalink($post->ID);
					   	$params = array('id' => $homework->id, 'submit_solution' => 1);
							$target_url = add_query_arg( $params, $permalink );?>
					   	<p><a href="<?php echo $target_url ?>"><?php _e('Submit solution', 'namaste')?></a></p>
				  		 <?php else:?>
							<p><a href="admin.php?page=namaste_submit_solution&id=<?php echo $homework->id?>"><?php _e('Submit solution', 'namaste')?></a></p>
					<?php endif; // end if not in shrotcode 
					endif; // end if not self approving
			 	endif; // end if not restricted by time limit or OK with time limit
			endif; // end if no solution yet
		endif; // end if not manager's mode?></td>
		
		<td><p><?php if(!count($homework->notes)): _e('None yet.', 'namaste');
		else:?> <a href="#" onclick="Namaste.loadNotes('<?php echo $homework->id?>', '<?php echo $student->ID?>');return false;"><?php printf(__('%d notes', 'namaste'), count($homework->notes));?></a>
		<?php endif;?></p>		
		<?php if($manager_mode):
					if($in_shortcode):
				   	$permalink = get_permalink($post->ID);
				   	$params = array('id' => $homework->id, 'add_note' => 1, 'lesson_id' => $lesson->ID, 'student_id' => $_GET['student_id'], 'homework_id'=>$homework->id);
						$target_url = add_query_arg( $params, $permalink );?>
				   	<p><a href="<?php echo $target_url ?>"><?php _e('Add note / feedback', 'namaste')?></a></p>
			  		 <?php else:?>
						<p><a href="admin.php?page=namaste_add_note&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $_GET['student_id']?>&homework_id=<?php echo $homework->id?>"><?php _e('Add note / feedback', 'namaste')?></a></p>
 		<?php endif; // end if not in shortcode 
		endif; // end if manager mode?></td>
		<?php do_action('namaste_extra_td', 'lesson_homeworks', $homework, $in_shortcode);?></tr>
	<?php endforeach;?>
</table>

<script type="text/javascript" >
Namaste.loadNotes = function(homeworkID, studentID) {
	tb_show("<?php _e('Notes', 'namaste')?>", 
		'<?php echo admin_url("admin-ajax.php?action=namaste_ajax&type=load_notes")?>&homework_id=' + homeworkID + 
		'&student_id=' + studentID);
}

Namaste.deleteNote = function(studentID, noteID) {
	if(!confirm("<?php _e('Are you sure? There is no undo.', 'namaste');?>")) return false;
	var url = '<?php echo admin_url("admin-ajax.php");?>';
	var data = {'action' : 'namaste_ajax', 'type' : 'delete_note', 'id' : noteID, 'student_id' : studentID };
	jQuery.post(url, data, function(msg) {
		var parts = msg.split('|||');
		if(parts[0] == 'ERROR') alert(parts[1]);
		else jQuery('#homeworkNote-'+ noteID).hide();
	});
}
</script>