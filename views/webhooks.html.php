<div class="wrap">
	<h1><?php _e('Manage Webhooks', 'namaste');?></h1>
	
	<p><?php printf(__('Here you can define webhooks which to be notified when an user subscribes to or unsubscribes from a mailing list. Webhooks are most often used with Zapier but can also be useful for integrations to other apps. Learn more about Zapier webhooks <a href="%1$s" target="_blank">here</a>.', 'namaste'), 'https://zapier.com/blog/what-are-webhooks/');?></p>
	
	<p><a href="https://blog.calendarscripts.info/zapier-webhooks-in-namaste-lms/" target="_blank"><?php _e('Learn more how to use these webhooks with Namaste! LMS', 'namaste');?></a></p>
	
	<p><a href="admin.php?page=namaste_webhooks&action=add"><?php _e('Set up a new webhook', 'namaste');?></a></p>
	
	<?php if(count($hooks)):?>
		<table class="widefat">
			<thead>
				<tr><th><?php _e('Item', 'namaste');?></th>
				<th><?php _e('Action', 'namaste');?></th>
				<th><?php _e('Notifies hook URL', 'namaste');?></th>
				<th><?php _e('View/Edit', 'namaste');?></th>
				<th><?php _e('Delete', 'namaste');?></th></tr>
			</thead>
			<tbody>
				<?php foreach($hooks as $hook):
				if(empty($class)) $class = 'alternate';
				else $class = '';?>
					<tr class="<?php echo $class;?>">
						<td><?php echo apply_filters('namaste_filter_hook_item', $hook->course, $hook); ?></td>
						<td><?php switch($hook->action):
							case 'enroll': _e('Enrolls', 'namaste'); break;
							case 'complete': _e('Completes', 'namaste'); break;
							case 'complete_lesson': _e('Completes', 'namaste'); break;
							case 'start_lesson': _e('Starts', 'namaste'); break;
							case 'complete_homework': _e('Completes', 'namaste'); break;
							case 'achieve_certificate': _e('Achieves', 'namaste'); break;
						endswitch;?></td>
						<td><?php echo $hook->hook_url;?></td>
						<td><a href="admin.php?page=namaste_webhooks&action=edit&id=<?php echo $hook->id;?>"><?php _e('View/Edit', 'namaste');?></a></td>
						<td><a href="<?php echo wp_nonce_url('admin.php?page=namaste_webhooks&delete=1&id='.$hook->id, 'delete_hook', 'namaste_hook_nonce')?>" class="delete_link"><?php _e('Delete', 'namaste');?></a></td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	<?php endif;?>
	
	<p align="center">
		<?php if($offset > 0):?>
			<a href="admin.php?page=namaste_webhooks&offset=<?php echo $offset - $limit;?>">&lt;&lt;&lt;</a>
		<?php endif;?>
		&nbsp;
		<?php if($count > $offset + $limit):?>
			<a href="admin.php?page=namaste_webhooks&offset=<?php echo $offset + $limit;?>">&gt;&gt;&gt;</a>
		<?php endif;?>
	</p>
</div>

<script type="text/javascript" >
jQuery('.delete_link').click(function(){
    return confirm("<?php _e('Are you sure you want to delete the hook?', 'namaste')?>");
});
</script>
