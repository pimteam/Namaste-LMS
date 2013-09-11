<h1><?php _e('My Certificates', 'namaste')?></h1>

<?php if(!sizeof($certificates)) :?>
	<p><?php _e('You have not achieved any certificates yet.', 'namaste')?></p>
<?php return false;
endif;?>

<div class="wrap">
	<table class="widefat">
		<tr><th><?php _e('Certificate', 'namaste')?></th><th><?php _e('Date', 'namaste')?></th><th><?php _e('Completed course(s)', 'namaste')?></th></tr>
		<?php foreach($certificates as $certificate):?>
			<tr><td><a href="admin.php?page=namaste_view_certificate&id=<?php echo $certificate->id?>&student_id=<?php echo $student_id?>&noheader=1" target="_blank"><?php echo $certificate->title?></a></td>
			<td><?php echo date(get_option('date_format'), strtotime($certificate->date))?></td>
			<td><?php echo $certificate->courses?></td></tr>
		<?php endforeach;?>
	</table>
</div>