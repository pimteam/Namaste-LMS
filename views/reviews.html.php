<h1><?php _e('Student Reviews on Courses', 'namaste');?></h1>

<div class="wrap">
	<form method="get"  action="admin.php">
		<div class="postbox namaste-form namaste-box">
			<p><label><?php _e('Filter by course', 'namaste');?></label> <select name="course_id">
				<option value=""><?php _e('All courses', 'namaste');?></option>
				<?php foreach($courses as $course):?>
					<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $_GET['course_id'] == $course->ID) echo 'selected';?>><?php echo stripslashes($course->post_title);?></option>
				<?php endforeach;?>
			</select></p>
			
			<p><label><?php _e('Filter by status', 'namaste');?></label> <select name="status">
				<option value=""><?php _e("Any status", 'namaste');?></option>
				<option value="1" <?php if(!empty($_GET['status']) and $_GET['status'] == 1) echo 'selected';?>><?php _e('Approved', 'namaste');?></option>
				<option value="0" <?php if(isset($_GET['status']) and $_GET['status'] === "0") echo 'selected';?>><?php _e('Pending approval', 'namaste');?></option>
			</select></p>
			
			<p><input type="submit" value="<?php _e('Filter courses', 'namaste');?>" class="button button-primary"></p>
		</div>
		
		<input type="hidden" name="page" value="namaste_reviews">
	</form>
	
	<?php if(count($reviews)):?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e('Course', 'namaste');?></th>
					<th><?php _e('Student', 'namaste');?></th>
					<th><?php _e('Rating and Review', 'namaste');?></th>
					<th><?php _e('Date', 'namaste');?></th>
					<th><?php _e('Action', 'namaste');?></th>
				</tr>			
			</thead>
			
			<tbody>
				<?php foreach($reviews as $review):
					if(empty($class)) $class = 'alternate';
					else $class = '';?>
					<tr class="<?php echo $class;?>">
					<td><?php echo stripslashes($review->course_name);?></td>
					<td><?php echo $review->user_name;?></td>
					<td><p><?php echo NamasteLMSReviews :: stars($review->rating);?><span class="screen-reader-text"><?php echo $review->rating;?></span></p>
					<?php echo wpautop(stripslashes($review->review));?></td>
					<td><?php echo date_i18n($date_format.' '.$time_format, strtotime($review->datetime));?></td>
					<td><form method="post">
						<?php if(!$review->is_approved):?><input type="submit" name="approve" value="<?php _e("Approve", 'namaste');?>" class="button button-primary"><?php endif;?>
						<input type="button" class="button" value="<?php _e('Delete', 'namaste');?>" onclick="confirmDelReview(this.form);">
						<input type="hidden" name="id" value="<?php echo $review->id;?>">
						<input type="hidden" name="del" value="0">
						<?php wp_nonce_field('namaste_reviews');?>
					</form></td>
					</tr>
				<?php endforeach;?>			
			</tbody>
		</table>	
		
		<p align="center"><?php if($offset > 0):?>
			<a href="admin.php?page=namaste_reviews&course_id=<?php echo empty($_GET['course_id']) ? '' : intval($_GET['course_id'])?>&status=<?php echo (!isset($_GET['status'])) ? '' : esc_attr($_GET['status'])?>&offset=<?php echo $offset - $page_limit;?>"><?php _e('[previous page]', 'namaste')?></a>
		<?php endif;?> 
		<?php if($count > ($page_limit + $offset)):?>
			<a href="admin.php?page=namaste_reviews&course_id=<?php echo empty($_GET['course_id']) ? '' : intval($_GET['course_id'])?>&status=<?php echo !isset($_GET['status']) ? '' : esc_attr($_GET['status'])?>&offset=<?php echo $offset + $page_limit;?>"><?php _e('[previous page]', 'namaste')?></a>
		<?php endif;?>	
		</p>
				
		<script>
		function confirmDelReview(frm) {
			if(confirm("<?php _e('Are you sure?', 'namaste');?>")) {
				frm.del.value = 1;
				frm.submit();
			}
		}
		</script>
	
	<?php else:?>
		<p><?php _e('No reviews found.', 'namaste');?></p>
	<?php endif;?>
</div>