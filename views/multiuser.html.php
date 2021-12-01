<div class="wrap">
	<h1><?php _e('Manage multi-user configurations in Namaste! LMS', 'namaste')?></h1>
	
	<?php if(empty($enabled_roles)):?>
		<p><?php printf(__('To edit this page you need to enable some roles to manage LMS on the <a href="%s" target=_blank">Namaste Settings page</a>.', 'namaste'), 'admin.php?page=namaste_options')?></p>
		</div>
	<?php return false;
	endif;?>
	
	<form method="post">
		<div class="namaste">
		<p><?php _e('Please select role to configure:', 'namaste')?> <select name="role_key" onchange="this.form.submit();">
			<option value=""><?php _e('- Please select role -', 'namaste')?></option>
			<?php foreach($enabled_roles as $role):?>
				<option value="<?php echo $role?>" <?php if(!empty($_POST['role_key']) and $_POST['role_key'] == $role) echo 'selected'?>><?php echo $role?></option>
			<?php endforeach;?>
		</select></p>
		
		<?php if(!empty($_POST['role_key'])):
			$settings = @$role_settings[$_POST['role_key']];?>
				<p><b><?php _e('Note: courses and lesson access depend on the selected role "post" settings. For example the Editor role can create and edit courses while the Contributor role can not.', 'namaste')?></b></p>			
			
			<p><label><?php _e('Assignments access:', 'namaste')?></label> <select name="homework_access">
				<option value="all" <?php if(!empty($settings['homework_access']) and $settings['homework_access'] == 'all') echo "selected"?>><?php _e('Manage all assignments','namaste')?></option>
				<option value="own" <?php if(!empty($settings['homework_access']) and $settings['homework_access'] == 'own') echo "selected"?>><?php _e('Manage only assignments created by the user','namaste')?></option>
				<option value="no" <?php if(!empty($settings['homework_access']) and $settings['homework_access'] == 'no') echo "selected"?>><?php _e('No access to manage assignments','namaste')?></option>
			</select></p>
			
			<p><label><?php _e('Students page access:', 'namaste')?></label> <select name="students_access">
				<option value="all" <?php if(!empty($settings['students_access']) and $settings['students_access'] == 'all') echo "selected"?>><?php _e('Manage students','namaste')?></option>
				<option value="view" <?php if(!empty($settings['students_access']) and $settings['students_access'] == 'view') echo "selected"?>><?php _e('View only','namaste')?></option>
				<option value="no" <?php if(!empty($settings['students_access']) and $settings['students_access'] == 'no') echo "selected"?>><?php _e('No access to students page','namaste')?></option>
			</select></p>
			
			<p><label><?php _e('Mass enroll students:', 'namaste')?></label> <select name="mass_enroll_access">
				<option value="all" <?php if(!empty($settings['mass_enroll_access']) and $settings['mass_enroll_access'] == 'all') echo "selected"?>><?php _e('Can mass enroll','namaste')?></option>
				<option value="no" <?php if(!empty($settings['mass_enroll_access']) and $settings['mass_enroll_access'] == 'no') echo "selected"?>><?php _e('Can not mass enroll','namaste')?></option>		
			</select></p>
			
			<p><label><?php _e('Certificates access:', 'namaste')?></label> <select name="certificates_access">
				<option value="all" <?php if(!empty($settings['certificates_access']) and $settings['certificates_access'] == 'all') echo "selected"?>><?php _e('Manage all certificates','namaste')?></option>
				<option value="own" <?php if(!empty($settings['certificates_access']) and $settings['certificates_access'] == 'own') echo "selected"?>><?php _e('Manage only certificates created by the user','namaste')?></option>
				<option value="no" <?php if(!empty($settings['certificates_access']) and $settings['certificates_access'] == 'no') echo "selected"?>><?php _e('No access to manage certificates','namaste')?></option>
			</select></p>
			
			<p><label><?php _e('Gradebook access:', 'namaste')?></label> <select name="gradebook_access">
				<option value="all" <?php if(!empty($settings['gradebook_access']) and $settings['gradebook_access'] == 'all') echo "selected"?>><?php _e('Access gradebook','namaste')?></option>				
				<option value="no" <?php if(!empty($settings['gradebook_access']) and $settings['gradebook_access'] == 'no') echo "selected"?>><?php _e('No access to gradebook','namaste')?></option>
			</select></p>
			
			<p><label><?php _e('Settings page access:', 'namaste')?></label> <select name="settings_access">
				<option value="all" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'all') echo "selected"?>><?php _e('Manage settings','namaste')?></option>
				<option value="no" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'no') echo "selected"?>><?php _e('No access to manage settings','namaste')?></option>				
			</select></p>
			
			<p><label><?php _e('Help page access:', 'namaste')?></label> <select name="help_access">
				<option value="all" <?php if(!empty($settings['help_access']) and $settings['help_access'] == 'all') echo "selected"?>><?php _e('View Help page','namaste')?></option>
				<option value="no" <?php if(!empty($settings['help_access']) and $settings['help_access'] == 'no') echo "selected"?>><?php _e('No access to Help page','namaste')?></option>				
			</select></p>
			
			<p><label><?php _e('Plugins page access:', 'namaste')?></label> <select name="plugins_access">
				<option value="all" <?php if(!empty($settings['plugins_access']) and $settings['plugins_access'] == 'all') echo "selected"?>><?php _e('Access Plugins page','namaste')?></option>
				<option value="no" <?php if(!empty($settings['plugins_access']) and $settings['plugins_access'] == 'no') echo "selected"?>><?php _e('No access to Plugins page','namaste')?></option>				
			</select></p>
			
			<?php do_action('namaste-multiuser', $settings);?>
			
			<p><input type="checkbox" name="no_mycourses" value="1" <?php if(!empty($settings['no_mycourses'])) echo 'checked'?>> <?php _e("Don't display 'My Courses' link in the dashboard.", 'namaste');?></p>
			
			<p><?php printf(__('If you have <a href="%s" target="_blank">Namaste! PRO</a> all the above settings will also comply with any class (user group) limitations.', 'namaste'), 'http://namaste-lms.org/pro.php')?></p>
			
			<p><input type="submit" value="<?php _e('Save configuration for this role','namaste')?>" name="config_role"></p>
		<?php endif;?>
		</div>
		<?php wp_nonce_field('namaste_role_settings');?>
	</form>
</div>