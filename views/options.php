<div class="wrap">
	<h1><?php _e("Namaste! LMS Options", 'namaste')?></h1>
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('WordPress roles with access to the learning material', 'namaste')?></h2>
			
			<p><?php _e('By default Namaste! LMS creates a role "student" which is the only role allowed to work with the learning material. The idea behind this is to allow the admin have better control over which users can access it. However, you can enable the other existing user roles here. Note that this setting is regarding consuming content, and not creating it.', 'namaste')?></p>
			
			<p><?php foreach($roles as $key=>$r):
				if($key=='administrator') continue;
				$role = get_role($key);?>
				<input type="checkbox" name="use_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('namaste')) echo 'checked';?>> <?php _e($role->name, 'namaste')?> &nbsp;
			<?php endforeach;?></p>
			<?php if($is_admin):?>
				<h2><?php _e('WordPress roles that can administrate the LMS', 'namaste')?></h2>
				
				<p><?php _e('By default this is only the blog administrator. Here you can enable any of the other roles as well', 'namaste')?></p>
				
				<p><?php foreach($roles as $key=>$r):
					if($key=='administrator') continue;
					$role = get_role($key);?>
					<input type="checkbox" name="manage_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('namaste_manage')) echo 'checked';?>> <?php _e($role->name, 'namaste')?> &nbsp;
				<?php endforeach;?></p>
				<?php if(current_user_can('manage_options')):?>
					<p><a href="admin.php?page=namaste_multiuser" target="_blank"><?php _e('Fine-tune these settings.', 'namaste')?></a></p>
				<?php endif;?>
				
				<h2><?php _e('Using Modules', 'namaste');?></h2>
				
				<p><?php _e('You may want to use Modules between Courses and Lessons for better organization of the learning material.', 'namaste');?></p>
				
				<p><input type="checkbox" name="use_modules" value="1" <?php if(!empty($use_modules)) echo 'checked'?> onclick="this.checked ? jQuery('#namasteModulesSlug').show() : jQuery('#namasteModulesSlug').hide();"> <?php _e('Enable modules between courses and lessons. (Modules are in beta version and not yet fully covered in the whole LMS and addons).', 'namaste');?></p>
				
				<h2><?php _e('URL identificators for Namaste courses, lessons, and modules', 'namaste')?></h2>
				
				<p><?php _e('These are the parts of the URLs that identify a post as Namaste! LMS lesson or course. These URL slugs are shown at the browser address bar and are parts of all links to courses and lessons. By default they are "namaste-course" and "namaste-lesson". You can change them here.', 'namaste')?></p>
				
				<p><label><?php _e('Course URL slug:', 'namaste')?></label> <input type="text" name="course_slug" value="<?php echo $course_slug?>"></p>
				<p id="namasteModulesSlug" style='display:<?php echo $use_modules ? 'block' : 'none';?>'>
					<label><?php _e('Module URL slug:', 'namaste')?></label> <input type="text" name="module_slug" value="<?php echo $module_slug?>">				
				</p>
				<p><label><?php _e('Lesson URL slug:', 'namaste')?></label> <input type="text" name="lesson_slug" value="<?php echo $lesson_slug?>"></p>
				
				<p><?php _e('These slugs can contain only numbers, letters, dashes, and underscores. It is your responsibility to ensure they do not overlap with the URL identificators of another custom post type.', 'namaste')?></p>
				
				<p><input type="checkbox" name="link_to_course" value="1" <?php if($link_to_course == 1) echo 'checked'?> onclick="this.checked ? jQuery('#linkToCourseText').show() : jQuery('#linkToCourseText').hide();"> <?php _e('Automatically link to the course page from each lesson.', 'namaste');?></p>
				<div style='display:<?php echo $link_to_course ? 'block' : 'none';?>;margin-left:50px;' id="linkToCourseText">
					<p><?php _e('Link HTML:', 'namaste');?> <textarea rows="2" cols="30" name="link_to_course_text"><?php echo $link_to_course_text?></textarea>
					<?php printf(__('The tag %s will automatically be replaced with the hyperlinked course title.','namaste'), '{{{course-link}}}');?></p>
				</div>
			<?php endif;?>
			
			<h2><?php _e('Default "You need to be logged in" texts', 'namaste')?></h2>
			
			<p><?php _e('These are shown on lesson / course pages when a non logged in visitors visits them. For lessons the text is shown only when there is no lesson excerpt.', 'namaste');?></p>

			<p><?php _e('Text on course pages:', 'namaste');?> <textarea rows="3" cols="60" name="need_login_text_course"><?php echo stripslashes(get_option('namaste_need_login_text_course'));?></textarea></p>			
			<p><?php _e('Text on lesson pages:', 'namaste');?> <textarea rows="3" cols="60" name="need_login_text_lesson"><?php echo stripslashes(get_option('namaste_need_login_text_lesson'));?></textarea></p>
			<p><?php _e('Text on module pages:', 'namaste');?> <textarea rows="3" cols="60" name="need_login_text_module"><?php echo stripslashes(get_option('namaste_need_login_text_module'));?></textarea></p>

				
			<h2><?php _e('Blog / Archive Pages Behavior', 'namaste')?></h2>
			
			<p><input type="checkbox" name="show_courses_in_blog" value="1" <?php if(get_option('namaste_show_courses_in_blog')) echo 'checked'?>> <?php _e('Show courses as blog posts in home and archive pages', 'namaste')?></p>		
			<?php if($use_modules):?>
				<p><input type="checkbox" name="show_modules_in_blog" value="1" <?php if(get_option('namaste_show_modules_in_blog')) echo 'checked'?>> <?php _e('Show modules as blog posts in home and archive pages', 'namaste')?></p>		
			<?php endif;?>
			<p><input type="checkbox" name="show_lessons_in_blog" value="1" <?php if(get_option('namaste_show_lessons_in_blog')) echo 'checked'?>> <?php _e('Show lessons as blog posts in home and archive pages', 'namaste')?></p>		
			
			
			<h2><?php _e('My Courses Page Behavior', 'namaste')?></h2>
			
			<p><input type="checkbox" name="mycourses_only_enrolled" value="1" <?php if(get_option('namaste_mycourses_only_enrolled') == 1) echo 'checked'?>> <?php _e('Show only the courses student is enrolled in, completed, or pending enrollment.', 'namaste');?></p>
			
			<p><?php _e('Open courses and lessons in', 'namaste');?> <select name="links_target">
				<option value="_blank" <?php if($links_target == '_blank') echo 'selected';?>><?php _e('a new window/tab', 'namaste');?></option>
				<option value="_self" <?php if($links_target == '_self') echo 'selected';?>><?php _e('the same window/tab', 'namaste');?></option>
			</select></p>

			<p>&nbsp;</p>
			<?php echo do_action('namaste-options-main');?>			
			
			<p><input type="submit" value="<?php _e('Save Options', 'namaste')?>" name="namaste_options" class="button button-primary"></p>
		</div>
		<?php echo wp_nonce_field('save_options', 'nonce_options');?>
	</form>
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Grade and Point Systems', 'namaste')?></h2>
			
			<p><input type="checkbox" name="use_grading_system" <?php if($use_grading_system) echo 'checked'?> onclick="this.checked ? jQuery('#gradeSystem').show() : jQuery('#gradeSystem').hide();"> <?php _e('Use grading system*', 'namaste');?></p>
			<p><?php _e('* Using a grading system allows you to rate student performance in courses, lessons, and assignments, and keeping a gradebook. Grading individual lessons is optional.', 'namaste')?> </p>
			
			<div id="gradeSystem" style='display:<?php echo $use_grading_system ? 'block' : 'none'?>'>
				<p><?php _e('Enter your grades in the box, separated by comma. Start with the best possible grade and go right to the worst:', 'namaste')?>
				<input type="text" name="grading_system" value="<?php echo $grading_system;?>" size="40"></p>
			</div>
			
			<hr>
			
			<p><input type="checkbox" name="use_points_system" <?php if($use_points_system) echo 'checked'?> onclick="this.checked ? jQuery('#pointsSystem').show() : jQuery('#pointsSystem').hide();"> <?php _e('Use points system*', 'namaste');?></p>
			<p><?php _e('* Points system can be used alone or together with a grading system. It lets you reward your students with points for completing lessons, courses, or assignments. These points will be displayed, and in the future (and in additional plugins) used to create leaderboards, redeem rewards, etc.', 'namaste')?> </p>
			
			<div id="pointsSystem" style='display:<?php echo $use_points_system ? 'block' : 'none'?>'>
				<p><?php _e('Default reward values. They can be overridden for every individual course, lesson, or assignment.', 'namaste')?> <br />
				<strong><?php _e('When you change the numbers here, it affects courses, lessons and homework you create after the change. The change will not affect already created courses, lessons, and assignments.', 'namaste');?></strong></p>
				
				<p><?php _e('Reward', 'namaste')?> <input type="text" name="points_course" size="4" value="<?php echo get_option('namaste_points_course')?>"> <?php _e('points for completing a course', 'namaste')?></p>
				
				<p><?php _e('Reward', 'namaste')?> <input type="text" name="points_lesson" size="4" value="<?php echo get_option('namaste_points_lesson')?>"> <?php _e('points for completing a lesson', 'namaste')?></p>
				
				<p><?php _e('Reward', 'namaste')?> <input type="text" name="points_homework" size="4" value="<?php echo get_option('namaste_points_homework')?>"> <?php _e('points for successfully completing a homework / assignment', 'namaste')?></p>
				
				<p><input type="checkbox" name="moolamojo_points" value="1" <?php if(get_option('namaste_moolamojo_points')) echo "checked"?>> <?php printf(__('Connect to <a href="%s" target="_blank">MoolaMojo</a> so when points are awarded in Namaste! LMS the same number of virtual credits is earned. The MoolaMojo plugin must be installed and active.', 'namaste'), 'https://wordpress.org/plugins/moolamojo/');?></p>
				
				<h3><?php _e('Shortcodes enabled by using points system', 'namaste');?></h3>
				<p><?php _e('If you activate points system the following shortcodes become available:', 'namaste');?></p>
				
				<ol>
					<li><input type="text" size="12" readonly onclick="this.select();" value="[namaste-points]"> <?php _e('and', 'namaste');?> <input type="text" size="14" readonly onclick="this.select();" value="[namaste-points x]"> <?php _e('(where "x" is given user ID) outputs the total number of points the user has earned.', 'namaste')?> </li>
					<li><input type="text" size="18" readonly onclick="this.select();" value="[namaste-leaderboard x]"> <?php _e('and', 'namaste');?> <input type="text" size="24" readonly onclick="this.select();" value="[namaste-leaderboard x points]"> <?php _e('displays a leaderboard based on collected points. Replace "x" with the number of users you want to show. When you use the second shortcode the usernames will be shown in a table with the points collected in front of them.', 'namaste');?> </li>
				</ol>
			</div>

			<?php echo do_action('namaste-options-grading');?>			
			
			<input type="hidden" name="namaste_grade_options" value="1">
			<?php wp_nonce_field('namaste_grade_options');?>
			<p><input type="submit" value="<?php _e('Save grade and points settings', 'namaste')?>" class="button button-primary"></p>
		</div>
	</form>		
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Assignments / Homework', 'namaste')?></h2>
			
			<p><?php _e('Allowed file extensions for assignments that accept file uploads:', 'namaste');?>
			<input type="text" size="30" name="allowed_file_types" value="<?php echo get_option('namaste_allowed_file_types');?>" placeholder="Example: zip, doc, pdf">
			<?php _e('Separate allowed extensions by comma. Do not start with dots. Example format: zip, doc, pdf', 'namaste');?><br>
			<?php _e("If you leave this empty all file types will be accepted.", 'namaste');?></p>
			
			<p><input type="checkbox" name="store_filesystem" value="1" <?php if(get_option('namaste_store_files_filesystem') == '1') echo 'checked';?>> <?php printf(__('Store the files in the filesystem instead of the database. This is not secure: these files are accessible to everyone. At the very least we suggest installing <a href="%s" target="_blank">Protect uploads</a> to disallow browsing the uploads folder. Better option is to set custom folder below.', 'namaste'), 'https://wordpress.org/plugins/protect-uploads/');?></p>
			
			<p><input type="checkbox" name="file_upload_progress" value="1" <?php if(get_option('namaste_file_upload_progress') == '1') echo 'checked';?>> <?php _e('Use ajax-based file upload with a progress bar.', 'namaste');?> <b><?php _e('Note: this will not work in Internet Explorer 7-9 and other old browsers.', 'namaste');?></b></p>
			
			<?php if(!empty($upload_error)):?>
				<p class="namaste-error error"><?php _e('The directory is not writable. You may have to create it manually and set proper permissions.', 'namaste');?></p>
			<?php endif;?>
			
			<p><?php _e('Use custom protected folder:', 'namaste');?> <input type="text" name="protected_folder" value="<?php echo $protected_folder;?>" placeholder="<?php _e('Example: namaste_homework', 'namaste');?>">
			<?php _e('Name only, not full path. Use only letters, numbers and underscore. The folder will be created under folder uploads.', 'namaste');?></p>
			
			<p><?php _e('Total file size limit per submitted solution:', 'namaste');?> <input type="text" name="homework_size_total" size="4" value="<?php echo get_option('namaste_homework_size_total');?>"> <?php _e('KB', 'namaste');?>
			&nbsp;<?php _e('Maximum size of each uploded file:', 'namaste');?> <input type="text" name="homework_size_per_file" size="4" value="<?php echo get_option('namaste_homework_size_per_file');?>"> <?php _e('KB', 'namaste');?>
			<?php _e('(Leave empty or enter 0 in any of the file size limit boxes to set unlimited size.)', 'namaste');?></p>
			
			<p><input type="submit" name="save_homework_options" value="<?php _e('Save homework settings', 'namaste')?>" class="button button-primary"></p>
		</div>
		<?php wp_nonce_field('namaste_homework_options');?>
	</form>	
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Payment Settings', 'namaste')?></h2>
			
			<p><?php printf(__('If you have <a href="%s" target="_blank">Namaste! PRO</a> you can enable shopping cart.', 'namaste'), 'http://namaste-lms.org/pro.php');?></p>
			
			<p><label><?php _e('Payment currency:', 'namaste')?></label> <select name="currency" onchange="this.value ? jQuery('#customCurrency').hide() : jQuery('#customCurrency').show(); ">
			<?php foreach($currencies as $key=>$val):
            if($key==$currency) $selected='selected';
            else $selected='';?>
        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
         <?php endforeach; ?>
			<option value="" <?php if(!in_array($currency, $currency_keys)) echo 'selected'?>><?php _e('Custom', 'namaste')?></option>
			</select>
			<input type="text" id="customCurrency" name="custom_currency" style='display:<?php echo in_array($currency, $currency_keys) ? 'none' : 'inline';?>' value="<?php echo $currency?>"></p>
			
			<p><?php _e('Here you can specify payment methods that you will accept to give access to courses. When a course requires payment, the enrollment (pending or active - depends on your other course settings) will be entered after the payment is completed.', 'namaste')?></p>
			
			<p><input type="checkbox" name="accept_paypal" value="1" <?php if($accept_paypal) echo 'checked'?> onclick="this.checked?jQuery('#paypalDiv').show():jQuery('#paypalDiv').hide()"> <?php _e('Accept PayPal', 'namaste')?></p>
			
			<div id="paypalDiv" style='display:<?php echo $accept_paypal?'block':'none'?>;'>
				<p><input type="checkbox" name="paypal_sandbox" value="1" <?php if(get_option('namaste_paypal_sandbox')=='1') echo 'checked'?>> <?php _e('Use Paypal in sandbox mode', 'namaste')?></p>
				<p><label><?php _e('Your Paypal ID:', 'namaste')?></label> <input type="text" name="paypal_id" value="<?php echo get_option('namaste_paypal_id')?>"></p>
				<p><label><?php _e('After payment go to:', 'namaste')?></label> <input type="text" name="paypal_return" value="<?php echo get_option('namaste_paypal_return');?>" size="40"> <br />
				<?php _e('When left blank it goes to the course page. If you enter specific full URL, the user will be returned to that URL.', 'namaste')?> </p>
				
				<?php if(empty($use_pdt)):?>
				<p><b><?php _e('Note: Paypal IPN will not work if your site is behind a "htaccess" login box or running on localhost. Your site must be accessible from the internet for the IPN to work. In cases when IPN cannot work you need to use Paypal PDT.', 'namaste')?></b></p>
				<?php endif;
				if(!namaste_is_secure() and empty($use_pdt)):?>
					<p style="color:red;font-weight:bold;"><?php _e('Your site is not running on SSL so Paypal IPN will typicall not work. You MUST use the PDT option below.', 'namaste');?></p>
				<?php endif;?>				
			
				<p><input type="checkbox" name="use_pdt" value="1" <?php if($use_pdt == 1) echo 'checked'?> onclick="this.checked ? jQuery('#paypalPDTToken').show() : jQuery('#paypalPDTToken').hide();"> <?php printf(__('Use Paypal PDT instead of IPN (<a href="%s" target="_blank">Why and how</a>)', 'namaste'), 'http://blog.calendarscripts.info/watupro-intelligence-module-using-paypal-data-transfer-pdt-instead-of-ipn/');?></p>
				
				<div id="paypalPDTToken" style='display:<?php echo ($use_pdt == 1) ? 'block' : 'none';?>'>
					<p><label><?php _e('Paypal PDT Token:', 'namaste');?></label> <input type="text" name="pdt_token" value="<?php echo get_option('namaste_pdt_token');?>" size="60"></p>
				</div>
			</div>
			
			<p><input type="checkbox" name="namaste_woocommerce" value="1" <?php if(get_option('namaste_woocommerce') == 1) echo 'checked';?>> <?php printf(__('Allow accepting WooCommerce as a primary selling method. <a href="%s" target="_blank">Learn more</a>.', 'namaste'), 'https://blog.calendarscripts.info/namaste-lms-bridge-for-woocommerce/');?></p>
			
			<p><input type="checkbox" name="accept_stripe" value="1" <?php if($accept_stripe) echo 'checked'?> onclick="this.checked?jQuery('#stripeDiv').show():jQuery('#stripeDiv').hide()"> <?php _e('Accept Stripe', 'namaste')?></p>
			
			<div id="stripeDiv" style='display:<?php echo $accept_stripe?'block':'none'?>;'>
				<p><span style="color:red;"><b><?php _e('The built-in Stripe integration is deprecated.', 'namaste');?></b></span> 
				<?php printf(__('We strongly recommend using the built-in <a href="%s" target="_blank">WooCommerce integration</a> instead. WooCommerce flawlessly supports Stripe and many other payment methods.', 'namaste'), 'https://blog.calendarscripts.info/namaste-lms-bridge-for-woocommerce/');?></p>
				<p><label><?php _e('Your Public Key:', 'namaste')?></label> <input type="text" name="stripe_public" value="<?php echo get_option('namaste_stripe_public')?>"></p>
				<p><label><?php _e('Your Secret Key:', 'namaste')?></label> <input type="text" name="stripe_secret" value="<?php echo get_option('namaste_stripe_secret')?>"></p>
			</div>
			
			<p><input type="checkbox" name="accept_moolamojo" <?php if($accept_moolamojo) echo 'checked';?> value="1" onclick="this.checked ? jQuery('#namastePayMoola').show() : jQuery('#namastePayMoola').hide();"> <?php printf(__('Accept virtual credits from <a href="%s" target="_blank">MoolaMojo</a> (The plugin must be installed and active).', 'namaste'), 'https://moolamojo.com')?></p>

			<div id="namastePayMoola" style='display:<?php echo $accept_moolamojo ? 'block' : 'none';?>'>
				<p><label><?php printf(__('Cost of 1 %s in virtual credits:', 'namaste'), $currency)?></label> <input type="text" name="moolamojo_price" value="<?php echo get_option('namaste_moolamojo_price')?>" size="6"></p>
				<p><b><?php _e('Design of the payment button.', 'namaste')?></b>
				<?php _e('You can use HTML and the following codes:', 'namaste')?> {{{credits}}} <?php _e('for the price in virtual credits,', 'namaste')?> {{{button}}} <?php _e('for the payment button itself and', 'namaste')?> [moolamojo-balance] <?php _e('to display the currently logged user virtual credits balance.', 'namaste')?></p>
				<p><textarea name="moolamojo_button" rows="7" cols="50"><?php echo stripslashes($moolamojo_button)?></textarea></p>
				<hr>	
			</div>
			
			<p><input type="checkbox" name="accept_other_payment_methods" value="1" <?php if($accept_other_payment_methods) echo 'checked'?> onclick="this.checked?jQuery('#otherPayments').show():jQuery('#otherPayments').hide()"> <?php _e('Accept other payment methods.', 'namaste')?> 
				<span class="namaste_help"><?php _e('This option lets you paste your own button HTML code or other manual instructions, for example bank wire. These payments will have to be processed manually unless you can build your own script to verify them.','namaste')?></span></p>
				
			<div id="otherPayments" style='display:<?php echo $accept_other_payment_methods?'block':'none'?>;'>
				<p><?php _e('Enter text or HTML code for payment button(s). You can use the following variables: {{course-id}}, {{course-name}}, {{user-id}}, {{amount}}.', 'namaste')?></p>
				<textarea name="other_payment_methods" rows="8" cols="80"><?php echo stripslashes(get_option('namaste_other_payment_methods'))?></textarea>
				<p><?php printf(__('If you want to use Instamojo we have a <a href="%s" target="_blank">free plugin</a> for integration with the service.', 'namaste'), 'http://blog.calendarscripts.info/instamojo-integration-for-namaste-lms/');?></p>			
			</div>	
			
			<?php echo do_action('namaste-options-payments');?>
			
			<p><input type="submit" value="<?php _e('Save payment settings', 'namaste')?>" class="button button-primary"></p>
			
			<?php if(!empty($payment_errors)):?>
				<p><a href="#" onclick="jQuery('#namasteErrorlog').toggle();return false;"><?php _e('View payments errorlog', 'namaste')?></a></p>
				<div id="namasteErrorlog" style="display:none;"><?php echo nl2br($payment_errors)?></div>
			<?php endif;?>	
		</div>
		
		<input type="hidden" name="namaste_payment_options" value="1">
		<?php echo wp_nonce_field('save_payment_options', 'nonce_payment_options');?>	
	</form>
	
	<form method="post" class="namaste-form">
		<div class="postbox wp-admin namaste-box">
			<h2><?php _e('Exam/Test Related Settings', 'namaste')?></h2>
			
			<p><?php _e('Namaste LMS utilizes the power of existing WordPress plugins to handle exams, tests and quizzes. At this moment it can connect with two plugins:', 'namaste')?> <a href="http://wordpress.org/extend/plugins/watu/">Watu</a> <?php _e('(Free) and ', 'namaste')?> <a href="http://calendarscripts.info/watupro/?r=namaste">WatuPRO</a> <?php _e('(Premium)', 'namaste')?></p>
			
			<p><?php _e('If you have any of these plugins installed and activated, please choose which one to use for handling tests below:', 'namaste')?></p>
			
			<p><input type="radio" name='use_exams' <?php if(empty($use_exams)) echo 'checked'?> value="0"> <?php _e('I don not need to create any exams or tests.', 'namaste')?></p>
			
			<?php if($watu_active):?>
				<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watu')) echo 'checked'?> value="watu"> <?php _e('I will create exams with Watu.', 'namaste')?></p>
			<?php endif;?>
			
			<?php if($watupro_active):?>
				<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watupro')) echo 'checked'?> value="watupro"> <?php _e('I will create exams with WatuPRO.', 'namaste')?></p>
			<?php endif;?>
			
			<?php if($chained_active):?>
				<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'chained')) echo 'checked'?> value="chained"> <?php _e('I will create exams with Chained Quiz.', 'namaste')?></p>
			<?php endif;?>
			
			<?php if($watu_active or $watupro_active or $chained_active):?>
				<p><input type="checkbox" name="access_exam_started_lesson" value="1" <?php if(get_option('namaste_access_exam_started_lesson') == '1') echo 'checked'?>> <?php _e('Exams that are required by lessons will be accessible only after the associated lesson has been started.', 'namaste')?> </p>
				<p><input type="checkbox" name="cleanup_exams" value="yes" <?php if(get_option('namaste_cleanup_exams') == 'yes') echo 'checked'?>> <?php _e('When I cleanup student course data from the "Manage Students" page I want any related exam data for this student also to be REMOVED.', 'namaste')?> </p>
			<?php endif;?>
			
			<p><input type="submit" value="<?php _e('Save Exam Options', 'namaste')?>" name="namaste_exam_options" class="button button-primary"></p>
		</div>
		
		<?php echo do_action('namaste-options-exams');?>		
		
		<?php echo wp_nonce_field('save_exam_options', 'nonce_exam_options');?>
	</form>	
</div>	