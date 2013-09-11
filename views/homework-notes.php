<h1><?php _e('Admin notes for assignment', 'namaste')?> "<?php echo $homework->title?>"</h1>

<?php if(!sizeof($notes)): echo "<p>".__("There aren't any notes yet.", 'namaste')."</p>"; endif;?>

<?php foreach($notes as $note):?>
	<div class="namaste-box namaste-dashed">
		<h3><?php _e(sprintf("Note by %s posted on %s", $note->username, date(get_option('date_format'), strtotime($note->datetime))), 'namaste');?></h3>	
		
		<?php echo apply_filters('the_content', stripslashes($note->note));?>
	</div>
<?php endforeach;?>