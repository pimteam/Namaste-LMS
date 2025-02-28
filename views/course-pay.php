<div class="wrap">
	<h2><?php _e('Enrolling in course', 'namaste')?> "<?php echo stripslashes($course->post_title)?>"</h2>

	<p><?php printf(__('This is a premium course. There is a fee of <strong>%s %s</strong> to enroll in it.', 'namaste'), $currency, $fee)?></p>
	
	<?php if($accept_paypal and $paypal_id): 
		$paypal_host = "www.paypal.com";
		$paypal_sandbox = get_option('namaste_paypal_sandbox');
		if($paypal_sandbox == '1') $paypal_host = 'www.sandbox.paypal.com';// generate Paypal button ?>
	<form action="https://<?php echo $paypal_host?>/cgi-bin/webscr" method="post" class="namaste-payment">
	<p align="center">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="<?php echo $paypal_id?>">
		<input type="hidden" name="item_name" value="<?php echo __('Subscribe for', 'namaste').' '.$course->post_title?>">
		<input type="hidden" name="item_number" value="<?php echo $course->ID?>">
		<input type="hidden" name="amount" value="<?php echo number_format($fee,2,".","")?>">
		<input type="hidden" name="return" value="<?php echo (get_option('namaste_use_pdt') == 1) ? esc_url(add_query_arg(array('namaste_pdt' => 1), trim($paypal_return))) : trim($paypal_return);?>">
		<?php if(get_option('namaste_use_pdt') != 1):?><input type="hidden" name="notify_url" value="<?php echo site_url('?namaste=paypal&course_id='.$course->ID.'&user_id='.$user_ID);?>"><?php endif;?>
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="<?php echo $currency;?>">
		<input type="hidden" name="lc" value="US">
		<input type="hidden" name="bn" value="PP-BuyNowBF">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</p>
	<input type="hidden" name="charset" value="utf-8">
	</form> 
	<?php endif;?>
	
	<?php if($accept_moolamojo):
	if(is_user_logged_in()): echo do_shortcode($moola_button); 
	else: echo '<p>'.__('You can pay with your virtual credits balance but you must be logged in.', 'namaste').'</p>'; endif;
	endif;?>
	
	<?php if($accept_other_payment_methods):
        /** 
        * IMPORTANT: These buttons MUST allow HTML and JavaScript. 
        * This is not a vulnerability.
        **/
        ?>
		<div class="namaste-payment"><?php echo $other_payment_methods?></div>
	<?php endif;?>
</div>
