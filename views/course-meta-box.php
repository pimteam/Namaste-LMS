<h3><?php _e('Enrollment mode:', 'namaste')?></h3>

<p><b><?php _e('You can use the shortcode', 'namaste')?></b> <input type="text" value="[namaste-enroll]" onclick="this.select();" readonly size="14"> <b><?php _e('to display enrollment button (or enrolled/pending message) in the course content', 'namaste')?></b></p>

<p><b><?php _e('The shortcode', 'namaste')?></b> <input type="text" value="[namaste-enroll course_id=<?php echo $post->ID?>]" onclick="this.select();" readonly size="24"> <b><?php _e('needs to be used if you want to display enroll button for this course on a different page. See the internal Help page for more parameters in this shortcode.', 'namaste')?></b></p>
 
<p><input type="radio" name="namaste_enroll_mode" value="free" <?php if(empty($enroll_mode) or $enroll_mode == 'free') echo 'checked'?>> <?php _e('Logged in users can enroll this course themselves.', 'namaste')?></p>

<p><input type="radio" name="namaste_enroll_mode" value="manual" <?php if(!empty($enroll_mode) and $enroll_mode == 'manual') echo 'checked'?>> <?php _e('Admin manually approves/enrolls students in courses', 'namaste')?></p>

<p><input type="checkbox" id="namasteUnEnrollChk" name="namaste_unenroll" value="1" <?php if($unenroll_allowed) echo 'checked'?>> <?php _e('Allow students to unenroll from this course (this will cleanup any stats)', 'namaste');?></p>

<?php if(!empty($currency)):?>
	<p><?php _e('Students need to pay a fee of', 'namaste')?> <?php echo $currency?> <input type="text" size="6" name="namaste_fee" value="<?php echo $fee?>" onkeyup="this.value > 0 ? jQuery('#woocomProduct').show() : jQuery('#woocomProduct').hide();"> <?php _e('to enroll this course. (Leave it 0 for no fee.)', 'namaste')?></p>
	<p id="woocomProduct" style='display: <?php ($fee > 0) ? 'block':'none';?>'>
		<?php if(class_exists('woocommerce') and get_option('namaste_woocommerce') == 1):
			_e('Sell as a WooCommerce product:', 'namaste');?>
			<select name="namastewoo_id">
				<option value=""><?php _e('- Select product -', 'namaste');?></option>
				<?php while($products->have_posts()):
					$products->the_post();?>
					<option value="<?php echo $products->post->ID?>"<?php if(!empty($namastewoo_id) and $namastewoo_id == $products->post->ID) echo ' selected';?>><?php echo stripslashes(get_the_title());?></option>
				<?php endwhile;?>
			</select> <br />
			<?php _e('Only Virtual and Downloadable products can be used. If you create the connection here, any enroll or pay buttons for this course will send the user to the WooCommerce product page.', 'namaste');?>	
		<?php endif;?>	
	</p>
<?php else:?>
	<p><?php printf(__('You can charge students for course enrollments. To do this you must first select currency in the "Payment Settings" section <a href="%s" target="_blank">here</a>.', 'namaste'), 'admin.php?page=namaste_options')?></p>	
<?php endif;?>

<p><input type="checkbox" name="namaste_register_enroll" value="1" <?php if(!empty($register_enroll)) echo 'checked'?>> <?php printf(__('Automatically enroll in this course everyone who registers in the site with an <a href="%s" target="_blank">enabled user role</a>. Selecting this will ignore any course access prerequisites, payment requirements and any other enrollment restrictions.', 'namaste'), 'admin.php?page=namaste_options');?></p>

<h3><?php _e('Course Access / Pre-requisites', 'namaste')?></h3>

<?php if(!count($other_courses)):?>
	<p><?php _e('There are no other courses so every student can enroll in this course.', 'namaste')?></p>
<?php else: 
echo '<p>'.__('This course will be accessible only after the following courses are completed:','namaste').'</p>'; 
foreach($other_courses as $course):?>
	<p><input type="checkbox" name="namaste_access[]" value="<?php echo $course->ID?>" <?php if(in_array($course->ID, $course_access)) echo "checked"?>> <?php echo $course->post_title?></p>
<?php endforeach;
endif;?>

<h3><?php _e('Restrict by role:', 'namaste');?></h3>

<p><input type="checkbox" name="namaste_require_roles" value="1" <?php if(!empty($require_roles)) echo 'checked'?> onclick="this.checked ? jQuery('#namasteRequiredRoles').show() : jQuery('#namasteRequiredRoles').hide();"> <?php _e('Require specific user roles to join this course', 'namaste');?></p>

<div id="namasteRequiredRoles" style='display:<?php echo empty($require_roles) ? 'none' : 'block';?>'>
	<?php foreach($roles as $key => $role):?>
			<span style="white-space:nowrap;"><input type="checkbox" name="namaste_required_roles[]" value="<?php echo $key?>" <?php if(is_array($required_roles) and in_array($key, $required_roles)) echo 'checked'?>> <?php echo $role['name']?> &nbsp;</span>
	<?php endforeach;?>
</div>

<h3><?php _e('Course completeness', 'namaste')?></h3>

<?php if(!count($lessons)):?>
	<p><?php _e('This course has no lessons assigned so it can never be completed. Please create and assign some lessons to this course.', 'namaste')?></p>
<?php else:?>
	<p><?php _e('The following lessons must be completed in order to complete this course. Please select at least one.', 'namaste')?></p>
	<ul>
		<?php foreach($lessons as $lesson):?>
			<li><input type="checkbox" name="namaste_required_lessons[]" value="<?php echo $lesson->ID?>" <?php if(in_array($lesson->ID, $required_lessons)) echo 'checked'?>> <?php echo $lesson->post_title?></li>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if(!empty($use_points_system)):?>
	<p><?php _e('Reward', 'namaste')?> <input type="text" size="4" name="namaste_award_points" value="<?php echo $award_points?>"> <?php _e('points for completing this course.', 'namaste')?></p>
<?php endif;?>

<?php if(!empty($use_grading_system)):?>
	<p><input type="checkbox" value="1" name="namaste_auto_grade" <?php if(!empty($auto_grade)) echo 'checked'?>> <?php printf(__('Automatically grade this course based on its lesson grades (<a href="%s" target="_blank">learn how this works</a>)', 'namaste'), 'http://demo.namaste-lms.org/grading/#courses');?></p>
<?php endif;?>

<?php if(!empty($bp_groups) and count($bp_groups)):?>
	<h3><?php _e('BuddyPress Integration', 'namaste')?></h3>
	
	<p><?php _e('When someone enrolls this course join them in the following BuddyPress group:', 'namaste');?> <select name="namaste_bp_enroll_group" id="namasteBPEnrollGroup">
		<option value="0"><?php _e('- No group -', 'namaste');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_enroll_group == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
	</select>
	<?php do_action('namaste-bp-tie-activity', $post, $bp_enroll_group);?>
	<?php _e('and remove them from the following group:', 'namaste');?>
		<select name="namaste_bp_enroll_group_remove">
		<option value="0"><?php _e('- No group -', 'namaste');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_enroll_group_remove == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
		</select>
	</p>
	<p><?php _e('When someone completes this course join them in the following BuddyPress group:', 'namaste');?> <select name="namaste_bp_complete_group">
		<option value="0"><?php _e('- No group -', 'namaste');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_complete_group == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
	</select>
	<?php _e('and remove them from the following group:', 'namaste');?>
		<select name="namaste_bp_complete_group_remove">
		<option value="0"><?php _e('- No group -', 'namaste');?></option>
		<?php foreach($bp_groups['groups'] as $bp_group):
			$selected = ($bp_complete_group_remove == $bp_group->id) ? 'selected' : ''; ?>
			<option value="<?php echo $bp_group->id?>" <?php echo $selected?>><?php echo stripslashes($bp_group->name);?></option>
		<?php endforeach;?>
		</select>	
	</p>
<?php endif;?>

<h3><?php _e('Student Feedback', 'namaste-lms');?></h3>

<p><input type="checkbox" name="namaste_accept_reviews" value="1" <?php if($accept_reviews) echo 'checked';?> onclick="this.checked ? jQuery('.namaste-review-features').show() : jQuery('.namaste-review-features').hide();"> <?php _e("Accept reviews from students after completing the course.", 'namaste');?>
 <span class="namaste-review-features" style='display: <?php echo $accept_reviews ? 'inline' : 'none';?>'>
 	<input type="checkbox" name="namaste_hold_reviews" value="1" <?php if($hold_reviews) echo 'checked';?>> <?php _e('Hold reviews for moderation', 'namaste');?> 
 </span></p>
 
<div class="namaste-review-features" style='display: <?php echo $accept_reviews ? 'block' : 'none';?>'>
	<p><?php _e('Use the following shortcode to include the review feature on the course page:','namaste');?> 
	<input type="text" value='[namaste-review course_id="<?php echo $post->ID;?>"]' readonly onclick="this.select()"><br>
	<?php _e('The feature will be shown only when appropriate - i.e. after completing the course and only when the student has not already rated it.', 'namaste');?></p>
	<p><?php _e('You can enclose content / text in the shortcode. In that case the text will be shown conditionally when the whole rating form is displayed. Example:', 'namaste');?><br>
	[namaste-review course_id="<?php echo $post->ID;?>"]<?php _e('Thank you for competing this course! Please rate it now!', 'namaste');?>[/namaste-review]</p>
	
	<p><?php printf(__('Use the shortcode %s to show the submitted reviews on the course:', 'namaste'), '<input type="text" value="[namaste-reviews course_id='.$post->ID.']" readonly onclick="this.select();">');?></p>
</div>

<p>&nbsp;</p>
<?php do_action('namaste-course-meta-box', $post);?>

<h3><?php _e('Shortcodes', 'namaste')?></h3>

<p><?php _e('You can use the shortcode', 'namaste')?> <input type="text" value="[namaste-todo]" readonly="readonly" onclick="this.select();"> <?php _e('inside the course content to display what the student needs to do to complete the course.', 'namaste')?> 
<br> <?php printf(__('The same shortcode to use outside of the course page is %s', 'namaste'), '<b>[namaste-todo post_type="namaste_course" post_id="'.$post->ID.'"]</b>');?></p>
<p><?php _e('The shortcode', 'namaste')?> <input type="text" value="[namaste-course-lessons]" readonly="readonly" onclick="this.select();"> <?php _e('will display the lessons in the course.','namaste');?> <?php _e('It allows more advanced configurations explained on the ', 'namaste');?> <a href="admin.php?page=namaste_help"><?php _e('help page.', 'namaste')?></a><br>
<?php printf(__('The shortcode <b>%s</b> on the other hand will display just the number of lessons in the course.', 'namaste'), '[namaste-num-lessons course_id="'.$post->ID.'"]');?></p>

<?php if(get_option('namaste_use_modules') == 1):?>
<p><?php printf(__('Similar to the above, the shortcode %1$s will display the modules in this course and the shortcode <b>%2$s</b> will display the number of modules in it.', 'namaste'), '<input type="text" value="[namaste-course-modules]" readonly="readonly" onclick="this.select();">', '[namaste-num-modules course_id="'.$post->ID.'"]');?></p>
<?php endif;?>

<p><?php _e('You can use the shortcode', 'namaste')?> <input type="text" value="[namaste-course-status]" readonly="readonly" onclick="this.select();"> <?php _e('inside the course content to display its current status for the logged in user.', 'namaste')?>
<br> <?php printf(__('The same shortcode to use outside of the course page is %s', 'namaste'), '<b>[namaste-course-status course_id="'.$post->ID.'"]</b>');?></p>

<p><?php printf(__('The shortcode %s can be used to display conditional content to logged in or non-logged in users like this: %s. To use it outside of the course page pass also the attribute <b>%s</b>', 'namaste'), '<input type="text" value="[namaste-condition]" readonly="readonly" onclick="this.select();">', '[namaste-condition is_enrolled=1]'.__('Content enrolled users', 'namaste').'[/namaste-condition] [namaste-condition is_enrolled=0]'.__('Content for not enrolled users', 'namaste').'[/namaste-condition]', 'course_id='.$post->ID);?> </p>

<h4><?php _e('Did you know?', 'namaste')?></h4>

<?php if(is_plugin_active('namaste-pro/namaste-pro.php')):?>
	<p><?php printf(__('You can limit the access to this course also by <a href="%s" target="_blank">class / group</a>.', 'namaste'), 'admin.php?page=namastepro_classes')?></p>
<?php else:?>
	<p><?php printf(__('If you <a href="%s" target="_blank">upgrade to PRO</a> you will be able to assign courses to classes and restrict access based on class, have different managers for different classes, and a lot more.', 'namaste'),'http://namaste-lms.org/pro.php')?></p>
<?php endif;?>