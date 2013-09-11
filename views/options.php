<div class="wrap">
	<h1><?php _e("Namaste! LMS Options", 'namaste')?></h1>
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Wordpress roles with access to the learning material', 'namaste')?></h2>
			
			<p><?php _e('By default Namaste! LMS creates a role "student" which is the only role allowed to work with the learning material. The idea behind this is to allow the admin have better control over which users can access it. However, you can enable the other existing user roles here. Note that this setting is regarding consuming content, and not creating it.', 'namaste')?></p>
			
			<p><?php foreach($roles as $key=>$r):
				if($key=='administrator') continue;
				$role = get_role($key);?>
				<input type="checkbox" name="use_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('namaste')) echo 'checked';?>> <?php _e($role->name, 'namaste')?> &nbsp;
			<?php endforeach;?></p>
			
			<h2><?php _e('Wordpress roles that can administrate the LMS', 'namaste')?></h2>
			
			<p><?php _e('By default this is only the blog administrator. Here you can enable any of the other roles as well', 'namaste')?></p>
			
			<p><?php foreach($roles as $key=>$r):
				if($key=='administrator') continue;
				$role = get_role($key);?>
				<input type="checkbox" name="manage_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('namaste_manage')) echo 'checked';?>> <?php _e($role->name, 'namaste')?> &nbsp;
			<?php endforeach;?></p>
			
			<p></p>
			<p><input type="submit" value="<?php _e('Save Options', 'namaste')?>" name="namaste_options"></p>
		</div>
		<?php echo wp_nonce_field('save_options', 'nonce_options');?>
	</form>
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Grade and Point Systems', 'namaste')?></h2>
			
			<p><input type="checkbox" name="use_grading_system" <?php if($use_grading_system) echo 'checked'?> onclick="this.checked ? jQuery('#gradeSystem').show() : jQuery('#gradeSystem').hide();"> <?php _e('Use grading system*', 'namaste');?></p>
			<p><?php _e('* Using a grading system allows you to rate student performance in courses, lessons, and assignments, and keeping a gradebook. Grading individual lessons is optional.', 'namaste')?> </p>
			
			<div id="gradeSystem" style="display:<?php echo $use_grading_system ? 'block' : 'none'?>">
				<p><?php _e('Enter your grades in the box, separated by comma. Start with the best possible grade and go right to the worst:', 'namaste')?>
				<input type="text" name="grading_system" value="<?php echo $grading_system;?>" size="40"></p>
			</div>
			
			<hr>
			
			<p><input type="checkbox" name="use_points_system" <?php if($use_points_system) echo 'checked'?> onclick="this.checked ? jQuery('#pointsSystem').show() : jQuery('#pointsSystem').hide();"> <?php _e('Use points system* <b>(Not yet implemented)</b>', 'namaste');?></p>
			<p><?php _e('* Points system can be used alone or together with a grading system. It lets you reward your students with points for completing lessons, courses, or assignments. These points will be displayed, and in the future (and in additional plugins) used to create leaderboards, redeem rewards, etc.', 'namaste')?> </p>
			
			<div id="pointsSystem" style="display:<?php echo $use_points_system ? 'block' : 'none'?>">
				<p><?php _e('Default reward values. They can be overridden for every individual course, lesson, or assignment.', 'namaste')?></p>
				
				<p><?php _e('Reward', 'namaste')?> <input type="text" name="points_course" size="4" value="<?php echo get_option('namaste_points_course')?>"> <?php _e('points for completing a course', 'namaste')?></p>
				
				<p><?php _e('Reward', 'namaste')?> <input type="text" name="points_lesson" size="4" value="<?php echo get_option('namaste_points_lesson')?>"> <?php _e('points for completing a lesson', 'namaste')?></p>
				
				<p><?php _e('Reward', 'namaste')?> <input type="text" name="points_homework" size="4" value="<?php echo get_option('namaste_points_homework')?>"> <?php _e('points for successfully completing a homework', 'namaste')?></p>
			</div>
			<input type="hidden" name="namaste_grade_options" value="1">
			<p><input type="submit" value="<?php _e('Save grade and points settings', 'namaste')?>"></p>
		</div>
	</form>		
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Payment Settings', 'namaste')?></h2>
			
			<p><label><?php _e('Payment currency:', 'namaste')?></label> <select name="currency">
			<?php foreach($currencies as $key=>$val):
            if($key==$currency) $selected='selected';
            else $selected='';?>
        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
         <?php endforeach; ?>
			</select></p>
			
			<p><?php _e('Here you can specify payment methods that you will accept to give access to courses. When a course requires payment, the enrollment (pending or active - depends on your other course settings) will be entered after the payment is completed.', 'namaste')?></p>
			
			<p><input type="checkbox" name="accept_paypal" value="1" <?php if($accept_paypal) echo 'checked'?> onclick="this.checked?jQuery('#paypalDiv').show():jQuery('#paypalDiv').hide()"> <?php _e('Accept PayPal', 'namaste')?></p>
			
			<div id="paypalDiv" style="display:<?php echo $accept_paypal?'block':'none'?>;">
				<p><label><?php _e('Your Paypal ID:', 'namaste')?></label> <input type="text" name="paypal_id" value="<?php echo get_option('namaste_paypal_id')?>"></p>
			</div>
			
			<p><input type="checkbox" name="accept_stripe" value="1" <?php if($accept_stripe) echo 'checked'?> onclick="this.checked?jQuery('#stripeDiv').show():jQuery('#stripeDiv').hide()"> <?php _e('Accept Stripe', 'namaste')?></p>
			
			<div id="stripeDiv" style="display:<?php echo $accept_stripe?'block':'none'?>;">
				<p><label><?php _e('Your Public Key:', 'namaste')?></label> <input type="text" name="stripe_public" value="<?php echo get_option('namaste_stripe_public')?>"></p>
				<p><label><?php _e('Your Secret Key:', 'namaste')?></label> <input type="text" name="stripe_secret" value="<?php echo get_option('namaste_stripe_secret')?>"></p>
			</div>
			
			<p><input type="checkbox" name="accept_other_payment_methods" value="1" <?php if($accept_other_payment_methods) echo 'checked'?> onclick="this.checked?jQuery('#otherPayments').show():jQuery('#otherPayments').hide()"> <?php _e('Accept other payment methods', 'namaste')?> 
				<span class="namaste_help"><?php _e('This option lets you paste your own button HTML code or other manual instructions, for example bank wire. These payments will have to be processed manually unless you can build your own script to verify them.','namaste')?></span></p>
				
			<div id="otherPayments" style="display:<?php echo $accept_other_payment_methods?'block':'none'?>;">
				<p><?php _e('Enter text or HTML code for payment button(s). You can use the following variables: {{course-id}}, {{course-name}}, {{user-id}}, {{amount}}.', 'namaste')?></p>
				<textarea name="other_payment_methods" rows="8" cols="80"><?php echo stripslashes(get_option('namaste_other_payment_methods'))?></textarea>			
			</div>	
			
			<p><input type="submit" value="<?php _e('Save payment settings', 'namaste')?>"></p>
		</div>
		<input type="hidden" name="namaste_payment_options" value="1">
		<?php echo wp_nonce_field('save_payment_options', 'nonce_payment_options');?>
	</form>
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Exam/Test Related Settings')?></h2>
			
			<p><?php _e('Namaste LMS utilizes the power of existing Wordpress plugins to handle exams, tests and quizzes. At this moment it can connect with two plugins:', 'namaste')?> <a href="http://wordpress.org/extend/plugins/watu/">Watu</a> <?php _e('(Free) and ', 'namaste')?> <a href="http://calendarscripts.info/watupro/?r=namaste">WatuPRO</a> <?php _e('(Premium)', 'namaste')?></p>
			
			<p><?php _e('If you have any of these plugins installed and activated, please choose which one to use for handling tests below:', 'namaste')?></p>
			
			<p><input type="radio" name='use_exams' <?php if(empty($use_exams)) echo 'checked'?> value="0"> <?php _e('I don not need to create any exams or tests.', 'namaste')?></p>
			
			<?php if($watu_active):?>
				<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watu')) echo 'checked'?> value="watu"> <?php _e('I will create exams with Watu.', 'namaste')?></p>
			<?php endif;?>
			
			<?php if($watupro_active):?>
				<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watupro')) echo 'checked'?> value="watupro"> <?php _e('I will create exams with WatuPRO.', 'namaste')?></p>
			<?php endif;?>
			
			<p><input type="submit" value="<?php _e('Save Exam Options', 'namaste')?>" name="namaste_exam_options"></p>
		</div>
		<?php echo wp_nonce_field('save_exam_options', 'nonce_exam_options');?>
	</form>	
</div>	