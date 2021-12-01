<h1><?php _e('Admin notes for assignment', 'namaste')?> "<?php echo stripslashes($homework->title)?>"</h1>

<?php if(!sizeof($notes)): echo "<p>".__("There aren't any notes yet.", 'namaste')."</p>"; endif;?>

<?php foreach($notes as $note):?>
	<div class="namaste-box namaste-dashed">
		<div id="homeworkNote-<?php echo $note->id?>">
			<h3><?php printf(__("Note by %s posted on %s", 'namaste'), $note->username, date_i18n(get_option('date_format'), strtotime($note->datetime)));?></h3>	
			
			<?php echo apply_filters('namaste_content', stripslashes($note->note));?>
			<?php if(current_user_can('namaste_manage') and $multiuser_access == 'all'):?>
				<p><a href="#" onclick="Namaste.deleteNote(<?php echo $_GET['student_id']?>, <?php echo $note->id?>);return false;"><?php _e('Delete note', 'namaste');?></a></p>
			<?php endif;?>
		</div>
	</div>
<?php endforeach;?>