<h1><?php _e("Manage Certificates", 'namaste')?></h1>

<div class="wrap">
	<?php if(!empty($msg)):?>
		<div class="namaste-note"><?php echo $msg?></div>
	<?php endif;?>	

	<p><?php _e('Certificates can optionally be assigned to users upon completion of courses.', 'namaste')?></p>
	
	<p><a href="admin.php?page=namaste_certificates&action=add"><?php _e('Create new certificate', 'namaste')?></a></p>
	
	<?php if(sizeof($certificates)):?>
		<table class="widefat">
			<tr><th><?php _e('Certificate title', 'namaste')?></th><th><?php _e('Edit', 'namaste')?></th></tr>
			<?php foreach($certificates as $certificate):?>
				<tr><td><?php echo $certificate->title?></td><td><a href="admin.php?page=namaste_certificates&action=edit&id=<?php echo $certificate->id?>"><?php _e('Edit', 'namaste')?></a></td></tr>
			<?php endforeach;?>	
		</table>
	<?php else:?>
		<p><?php _e('You have not added any certificates yet.', 'namaste')?></p>
	<?php endif;?>
</div>