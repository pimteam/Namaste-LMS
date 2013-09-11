<h1><?php _e("Assignments / Homeworks", 'namaste')?></h1>

<form method="get" action="admin.php">
	<input type="hidden" name="page" value="namaste_homeworks">
	<p><?php _e('Select course:', 'namaste')?> <select name="course_id" onchange="namasteSelectCourse(this.value);">
	<option value=""><?php _e('- please select -', 'namaste')?></option>
	<?php foreach($courses as $course):?>
		<option value="<?php echo $course->ID?>" <?php if(!empty($_GET['course_id']) and $_GET['course_id']==$course->ID) echo 'selected'?>><?php echo $course->post_title?></option>
	<?php endforeach;?>
	</select>
	
	<span id="namasteLessonID">
	<?php if(!empty($_GET['course_id'])):?>
		<?php _e('Select lesson:','namaste')?> <select name='lesson_id' onchange="this.form.submit();">
			<?php foreach($lessons as $lesson):?>
				<option value="<?php echo $lesson->ID?>"<?php if($lesson->ID == $_GET['lesson_id']) echo ' selected'?>><?php echo $lesson->post_title?></option>
			<?php endforeach;?>
		</select>
	<?php endif;?>	
	</span>
	</p>
</form>

<?php if(!empty($_GET['course_id']) and !empty($_GET['lesson_id'])):?>
<p><a href="admin.php?page=namaste_homeworks&course_id=<?php echo $_GET['course_id']?>&lesson_id=<?php echo $_GET['lesson_id']?>&do=add"><?php _e('Click here to create new assignment', 'namaste')?></a></p>
	<?php if(sizeof($homeworks)):?>
		<table class="widefat">
			<tr><th><?php _e('Title', 'namaste')?></th><th><?php _e('Edit','namaste')?></th><th><?php _e('View solutions','namaste')?></th>
			<?php do_action('namaste_extra_th', 'homeworks');?>				
			</tr>
			<?php foreach($homeworks as $homework):?>
				<tr><td><?php echo stripslashes($homework->title)?></td><td><a href="admin.php?page=namaste_homeworks&course_id=<?php echo $_GET['course_id']?>&lesson_id=<?php echo $_GET['lesson_id']?>&do=edit&id=<?php echo $homework->id?>"><?php _e('Edit', 'namaste')?></a></td>
				<td>
				<?php if($homework->solutions):?>
					<a href="admin.php?page=namaste_view_all_solutions&id=<?php echo $homework->id?>"><?php echo $homework->solutions?> <?php _e('solutions', 'namaste')?></a>
				<?php else: _e('No solutions', 'namaste'); endif;?></td>
				<?php do_action('namaste_extra_td', 'homeworks', $homework);?></tr>
			<?php endforeach;?>
		</table>	
	<?php endif;?>
<?php else: echo '<p>'.__('You have to select course and lesson before you can create assignments', 'namaste').'</p>'; endif;?>

<script type="text/javascript" >
function namasteSelectCourse(id) {
	if(!id) {
		jQuery('#namasteLessonID').html('');
		return false;
	}	
	data = {'action' : 'namaste_ajax', 'type': 'lessons_for_course', 'course_id' : id};
	jQuery.post("<?php echo admin_url('admin-ajax.php?')?>", data, function(msg){
		results = jQuery.parseJSON(msg);	
		html = "<?php _e('Select lesson:','namaste')?> <select name='lesson_id' onchange='this.form.submit();'>";
		html += '<option value=""><?php _e('- please select -')?></option>';
		for(i=0; i<results.length; i++) {
			html += '<option value="' + results[i].ID + '">' + results[i].post_title + '</option>';
		} 
		html += '</select>';
		jQuery('#namasteLessonID').html(html);
	});
}
</script>