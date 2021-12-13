<div class="wrap">
	<h1><?php _e('Manage Webhooks', 'namaste');?></h1>
	
	<p><?php printf(__('Here you can define webhooks which to be notified when an user subscribes to or unsubscribes from a mailing list. Webhooks are most often used with Zapier but can also be useful for integrations to other apps. Learn more about Zapier webhooks <a href="%1$s" target="_blank">here</a>.', 'namaste'), 'https://zapier.com/blog/what-are-webhooks/');?></p>
	
	<p><a href="admin.php?page=namaste_webhooks&action=add"><?php _e('Set up a new webhook', 'namaste');?></a></p>
	
	<?php if(count($hooks)):?>
		<table class="widefat">
			<thead>
				<tr><th><?php _e('Course', 'namaste');?></th>
				<th><?php _e('Action', 'bftpro');?></th>
				<th><?php _e('Notifies hook URL', 'namaste');?></th>
				<th><?php _e('View/Edit', 'namaste');?></th>
				<th><?php _e('Delete', 'namaste');?></th></tr>
			</thead>
			<tbody>
				<?php foreach($hooks as $hook):
				if(empty($class)) $class = 'alternate';
				else $class = '';?>
					<tr class="<?php echo $class;?>">
						<td><?php echo stripslashes($hook->course);?></td>
						<td><?php echo $hook->action;?></td>
						<td><?php echo $hook->hook_url;?></td>
						<td><a href="admin.php?page=namaste_webhooks&action=edit&id=<?php echo $hook->id;?>"><?php _e('View/Edit', 'namaste');?></a></td>
						<td><a href="<?php echo wp_nonce_url('admin.php?page=namaste_webhooks&delete=1&id='.$hook->id, 'delete_hook', 'namaste_hook_nonce')?>" class="delete_link"><?php _e('Delete', 'namaste');?></a></td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	<?php endif;?>
</div>

<script type="text/javascript" >
jQuery('.delete_link').click(function(){
    return confirm("<?php _e('Are you sure you want to delete the hook?', 'namaste')?>");
});
</script>