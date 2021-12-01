<h4><?php _e('Assign to Course:', 'namaste')?></h4>
 
<?php if(!sizeof($courses)) echo "<p>".__('No courses have been created yet!', 'namaste')."</p>";?> 
<p><label><?php _e('Select course:', 'namaste')?></label>
<select name="namaste_course">
<?php foreach($courses as $course):?>
	<option value="<?php echo $course->ID?>"<?php if($course->ID == $course_id) echo 'selected'?>><?php echo stripslashes($course->post_title)?></option>
<?php endforeach;?>
</select></p>
		
<h4><?php _e('Module Access', 'namaste')?></h4>

<?php if(!sizeof($other_modules)):
	if(empty($post->post_title)):?>
		<p><?php _e('You will be able to set module access after you create the module.', 'namaste')?></p>
	<?php else:?>
		<p><?php _e('There are no other modules in this course. So this module will be accessible to anyone who enrolled the course.', 'namaste')?></p>
	<?php endif;
   else: 
echo '<p>'.__('This module will be accessible only after the following modules are completed:','namaste').'</p>'; 
foreach($other_modules as $module):?>
	<p><input type="checkbox" name="namaste_access[]" value="<?php echo $module->ID?>" <?php if(in_array($module->ID, $module_access)) echo "checked"?>> <?php echo stripslashes($module->post_title)?></p>
<?php endforeach;
endif;?>

<h4><?php _e('Module Completeness', 'namaste')?></h4>

<?php if(!count($lessons)):?>
	<p><?php _e('You need to assign some lessons to this module before you can define completion requirements. Assigning lessons is done individually from the Add / Edit Lesson page.', 'namaste');?></p>
<?php else:?>
	<p><?php _e('The following lessons must be completed in order to complete this module. Please select at least one.', 'namaste')?></p>
	<ul>
		<?php foreach($lessons as $lesson):?>
			<li><input type="checkbox" name="namaste_required_lessons[]" value="<?php echo $lesson->ID?>" <?php if(in_array($lesson->ID, $required_lessons)) echo 'checked'?>> <?php echo $lesson->post_title?></li>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php do_action('namaste-module-requirements', $post);?>

<?php if(!empty($use_points_system)):?>
	<p><?php _e('Reward', 'namaste')?> <input type="text" size="4" name="namaste_award_points" value="<?php echo $award_points?>"> <?php _e('points for completing this module.', 'namaste')?></p>
<?php endif;?>

<h3><?php _e('Shortcodes', 'namaste')?></h3>

<p><?php _e('You can use the shortcode', 'namaste')?> <input type="text" value="[namaste-todo]" readonly="readonly" onclick="this.select();"> <?php _e('inside the module content to display which lessons need to be completed to complete the module.', 'namaste')?>
<br> <?php printf(__('The same shortcode to use outside of the module page is %s', 'namaste'), '<b>[namaste-todo post_type="namaste_module" post_id="'.$post->ID.'"]</b>');?></p>

<p><?php _e('The shortcode', 'namaste')?> <input type="text" value="[namaste-module-lessons]" readonly="readonly" onclick="this.select();"> <?php printf(__('will display all the lessons included in this module. The shortcode accepts exactly the same arguments as %s. See the <a href="%s">internal Help page</a> for details.', 'namaste'), '[namaste-course-lessons]', 'admin.php?page=namaste_help');?></p>

<p><?php _e('The shortcode', 'namaste')?> <input type="text" value='[namaste-num-lessons module_id="<?php echo $post->ID?>"]' readonly="readonly" onclick="this.select();"> <?php _e('will display the number of lessons published in this module.', 'namaste');?></p>

<p><?php _e('The shortcode', 'namaste')?> <input type="text" value="[namaste-course-link]" readonly="readonly" onclick="this.select();"> <?php _e('will display a link to the course that this module belongs to. You can pass attribute "text" to set a clickable text for the link. Otherwise the course title will be used.', 'namaste')?></p>

<p><?php _e('The shortcode', 'namaste')?> <input type="text" value="[namaste-breadcrumb]" readonly="readonly" onclick="this.select();"> <?php _e('will display a breadcrumb style hierarchical navigation. You can pass the attribute separator to use a custom separator.', 'namaste')?></p>

<h4><?php _e('Use the Excerpt Box Below:', 'namaste');?></h4>

<p><?php _e('If you enter some content in the Excerpt box on this page, the content will be shown to users who cannot access the module  for some reason: non-logged in students, students who did not enroll the course, students with unsatisfied module access requirements etc. The content will be shown instead of or before the default text that is shown in these cases. You can also show these excerpts by passing the <b>show_excerpts=1</b> attribute to the <b>namaste-course-modules</b> shortcode.', 'namaste');?></p>