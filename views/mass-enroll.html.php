<div class="wrap">
	<h1><?php printf(__('Mass enroll students in course "%s"', 'namaste'), stripslashes($course->post_title));?></h1>
	
	<p><a href="admin.php?page=namaste_students&course_id=<?php echo intval($_GET['course_id'])?>"><?php _e('Back to Manage Students', 'namaste');?></a></p>
	
	<form method="get" action="admin.php">
	<input type="hidden" name="page" value="namaste_mass_enroll">
		<input type="hidden" name="offset" value="<?php echo $offset?>">
		<input type="hidden" name="ob" value="<?php echo $ob?>">
		<input type="hidden" name="dir" value="<?php echo $dir?>">
	<input type="hidden" name="course_id" value="<?php echo $course->ID	?>">
	<p><?php _e('Per page:', 'namaste');?> <select name="page_limit" onchange="this.form.submit()">
		<option value="10" <?php selected($page_limit, 10)?>>10</option>
		<option value="20" <?php selected($page_limit, 20)?>>20</option>
		<option value="50" <?php selected($page_limit, 50)?>>50</option>
		<option value="100" <?php selected($page_limit, 100)?>>100</option>
		<option value="200" <?php selected($page_limit, 200)?>>200</option>
		<option value="500" <?php selected($page_limit, 500)?>>500</option>
	</select></p>
	</form>
	
	<form method="post">	
	<table class="widefat">
		<tr><th><input type="checkbox" onclick="namasteSelectAll(this);"></th>
		<th><a href="admin.php?page=namaste_mass_enroll&course_id=<?php echo $course->ID?>&ob=ID&dir=<?php echo $odir?>&page_limit=<?php echo $page_limit;?>"><?php _e('ID', 'namaste');?></a></th>
		<th><a href="admin.php?page=namaste_mass_enroll&course_id=<?php echo $course->ID?>&ob=user_login&dir=<?php echo $odir?>&page_limit=<?php echo $page_limit;?>"><?php _e('User Login', 'namaste');?></a></th>
		<th><a href="admin.php?page=namaste_mass_enroll&course_id=<?php echo $course->ID?>&ob=display_name&dir=<?php echo $odir?>&page_limit=<?php echo $page_limit;?>"><?php _e('User Name', 'namaste');?></a></th>
		<th><a href="admin.php?page=namaste_mass_enroll&course_id=<?php echo $course->ID?>&ob=user_email&dir=<?php echo $odir?>&page_limit=<?php echo $page_limit;?>"><?php _e('User Email', 'namaste');?></a></th></tr>
		<?php foreach($users as $user):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>">
				<td><input type="checkbox" name="uids[]" value="<?php echo $user->ID?>" class="namaste_uid"></td>
				<td><?php echo $user->ID?></td>
				<td><?php echo $user->user_login;?></td>
				<td><?php echo $user->display_name;?></td>
				<td><?php echo $user->user_email;?></td>
			</tr>
		<?php endforeach;?>
	</table>
	
	<p align="center"><b><?php _e('Tags (optional):', 'namaste');?></b>
				 <input type="text" name="tags" size="20" placeholder="<?php _e('Separate with comma: tag1, tag 2...', 'namaste');?>"><br />
	<input type="submit" class="btn btn-primary" value="<?php _e('Enroll Selected Users', 'namaste');?>" name="mass_enroll"></p>
	<?php wp_nonce_field('namaste_mass_enroll');?>
	</form>
	
	<p align="center">
		<?php if($offset > 0):?>
			<a href="admin.php?page=namaste_mass_enroll&course_id=<?php echo intval($_GET['course_id'])?>&offset=<?php echo $offset-$page_limit;?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&page_limit=<?php echo $page_limit;?>"><?php _e('Previous page', 'namaste');?></a>
		<?php endif;?>
		&nbsp;
		<?php if($total_users > $offset + $page_limit):?>
			<a href="admin.php?page=namaste_mass_enroll&course_id=<?php echo intval($_GET['course_id'])?>&offset=<?php echo $offset+$page_limit;?>&ob=<?php echo $ob?>&dir=<?php echo $dir?>&page_limit=<?php echo $page_limit;?>"><?php _e('Next page', 'namaste');?></a>
		<?php endif;?>			
	</p>
</div>

<script type="text/javascript" >
function namasteSelectAll(chk) {
	if(chk.checked) jQuery('.namaste_uid').attr('checked', true);
	else jQuery('.namaste_uid').removeAttr('checked');
}
</script>