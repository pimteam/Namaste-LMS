<div class="wrap namaste-wrap">
	<h1><?php _e('Add/Edit a Webhook', 'namaste');?></h1>
	
	<p><a href="admin.php?page=namaste_webhooks"><?php _e('Back to webhooks', 'namaste');?></a></p>

	<div class="postbox">
	<form method="post" class="namaste-form wrap" onsubmit="return validateHookForm(this);">
		<div class="wrap">
			<p><label><?php _e('When a student:', 'namaste');?></label> <select name="action" onchange="changeHookAction(this);">
				<option value="enroll" <?php if(!empty($hook->action) and $hook->action == 'enroll') echo 'selected';?>><?php _e('Enrolls in a course', 'namaste');?></option>
				<option value="complete" <?php if(!empty($hook->action) and $hook->action == 'complete') echo 'selected';?>><?php _e('Completes a course', 'namaste');?></option>
				<?php do_action('namaste_hook_actions', $hook ?? null);?>
			</select></p>
			
			<p><label id="hookItemLabel"><?php _e('Course:', 'namaste');?></label> <select name="item_id" id="hookItemId">
				<?php foreach($courses as $course):?>
					<option value="<?php echo $course->ID?>" <?php if(!empty($hook->item_id) and $hook->item_id == $course->ID) echo 'selected';?>><?php echo stripslashes($course->post_title);?></option>
				<?php endforeach;?>
			</select></p>
									
			<p><label><?php _e('Webhook URL:', 'namaste');?></label> <input type="text" name="hook_url" value="<?php echo empty($hook->id) ? '' : $hook->hook_url;?>" class="namaste-url-field" size="40"></p>
			
			<p><?php _e("The following data variables can be passed as a JSON array from Namaste! LMS to the webhook if they are available. You can set your names for each variable. If a variable has no name, it will not be included in the JSON array.", 'namaste');?></p>
			
			<p><?php _e('You can include several custom attributes with a predefined value in the request. You can use them for an API key, authorization keys, and so on.', 'namaste');?></p>
			
			<table>
				<thead>
					<tr><th><?php _e('Field / Data', 'namaste');?></th><th><?php _e('Variable name', 'namaste');?></th><th><?php _e('Variable value', 'namaste');?></th></tr>
				</thead>
				<tbody>
					<tr><td><b><?php _e('Student Username', 'namaste');?></b></td>
					<td><input type="text" name="user_login_name" value="<?php echo empty($payload_config['user_login']) ? '' : $payload_config['user_login']['name'];?>"></td>
					<td><?php _e('Dynamic / provided by user', 'namaste');?></td></tr>
					<tr><td><b><?php _e('Email address', 'namaste');?></b></td>
					<td><input type="text" name="email_name" value="<?php echo empty($payload_config['email']) ? '' : $payload_config['email']['name'];?>"></td>
					<td><?php _e('Dynamic / provided by user', 'namaste');?></td></tr>
					<tr><td><b><?php _e('Student Display Name', 'namaste');?></b></td>
					<td><input type="text" name="display_name_name" value="<?php echo empty($payload_config['display_name']) ? '' : $payload_config['display_name']['name'];?>"></td>
					<td><?php _e('Dynamic / provided by user', 'namaste');?></td></tr>
				
					<tr><td><b><?php _e('Custom parameter 1', 'namaste')?></b><br />
					<i><?php _e('Predefined variable 1', 'namaste');?></i></td>
					<td><input type="text" name="custom_key1_name" value="<?php echo empty($payload_config['custom_key1']) ? '' : $payload_config['custom_key1']['name'];?>"></td>
					<td><input type="text" name="custom_key1_value" value="<?php echo empty($payload_config['custom_key1']) ? '' : $payload_config['custom_key1']['value'];?>"></td></tr>
					<tr><td><b><?php _e('Custom parameter 2', 'namaste');?></b><br />
					<i><?php _e('Predefined variable 2', 'namaste');?></i></td>
					<td><input type="text" name="custom_key2_name" value="<?php echo empty($payload_config['custom_key2']) ? '' : $payload_config['custom_key2']['name'];?>"></td>
					<td><input type="text" name="custom_key2_value" value="<?php echo empty($payload_config['custom_key2']) ? '' : $payload_config['custom_key2']['value'];?>"></td></tr>
					<tr><td><b><?php _e('Custom parameter 3', 'namaste');?></b><br />
					<i><?php _e('Predefined variable 3', 'namaste');?></i></td>
					<td><input type="text" name="custom_key3_name" value="<?php echo empty($payload_config['custom_key3']) ? '' : $payload_config['custom_key3']['name'];?>"></td>
					<td><input type="text" name="custom_key3_value" value="<?php echo empty($payload_config['custom_key3']) ? '' : $payload_config['custom_key3']['value'];?>"></td></tr>
				</tbody>
			</table>
			
			<p><input type="submit" value="<?php _e('Save Webhook', 'namaste');?>" class="button button-primary">
			<input type="submit" name="test" value="<?php _e('Test Webhook', 'namaste');?>" class="button button-primary"></p>
		</div>
		<?php wp_nonce_field('namaste_webhooks');?>
		<input type="hidden" name="ok" value="1">
	</form>
	</div>
	
	<?php if(!empty($_POST['test'])):?>
	<div>
		<h2><?php _e('Data sent', 'namaste');?></h2>
			<p><?php echo '<pre>' . var_export($data, true) . '</pre>';;?></p>
		<h2><?php _e('Response from the hook', 'namaste');?></h2>
		<p>
			<?php echo '<pre>' . var_export($result, true) . '</pre>';?>
		</p>
	</div>
	<?php endif;?>
</div>

<script type="text/javascript" >
function validateHookForm(frm) {
	var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
    '(\\#[-a-z\\d_]*)?$','i'); // fragment locator	
	
	if(frm.hook_url.value == '' || !pattern.test(frm.hook_url.value)) {
		alert("<?php _e('Provide a valid Webhook URL', 'namaste');?>");
		frm.hook_url.focus();
		return false;
	}
	
	return true;
}

// change the item drop-down & info in case of a change in the hook action
function changeHookAction(sel) {
	let hookItems = {
      courses: [<?php foreach($courses as $course):?>
			[<?php echo $course->ID?>, "<?php echo stripslashes($course->post_title);?>"],      
      <?php endforeach;?>],	
      <?php do_action('namaste_hook_items');?>
	};
	
	// figure out which items to load
	items = [];
	switch(sel.value) {
		case 'enroll':
		case 'complete':
		   items = hookItems.courses;
		   label = '<?php _e('Course:', 'namaste');?>';
		break;
		<?php do_action('namaste_hook_switch');?>
	}
	
	// refill item type & options
	jQuery('#hookItemLabel').text(label);
	let hookOptions = '';
	items.forEach((elt, index) => {
		console.log(elt[1]);
		hookOptions += '<option value="'+elt[0]+'">'+elt[1]+'</option>' + "\n";
	} );
	
	jQuery('#hookItemId').html(hookOptions);
} // end changeHookAction
</script>