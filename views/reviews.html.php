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
					<th><?php _e('Action', 'namaste');?></th>
				</tr>			
			</thead>
			
			<tbody>
					NYI			
			</tbody>
		</table>	
	
	<?php else:?>
		<p><?php _e('No reviews found.', 'namaste');?></p>
	<?php endif;?>
</div>