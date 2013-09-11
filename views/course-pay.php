<div class="wrap">
	<h2><?php _e('Enrolling in course', 'namaste')?> "<?php echo $course->post_title?>"</h2>

	<p><?php printf(__('This is a premium course. There is a fee of <strong>%s %d</strong> to enroll it.', 'namaste'), $currency, $fee)?></p>
	
	<?php if($accept_paypal and $paypal_id): // generate Paypal button ?>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<p align="center">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="<?php echo $paypal_id?>">
		<input type="hidden" name="item_name" value="<?php echo __('Subscribe for', 'namaste').' '.$course->post_title?>">
		<input type="hidden" name="item_number" value="<?php echo $course->ID?>">
		<input type="hidden" name="amount" value="<?php echo number_format($fee,2,".","")?>">
		<input type="hidden" name="return" value="<?php echo admin_url('admin.php?page=namaste_my_courses');?>">
		<input type="hidden" name="notify_url" value="<?php echo site_url('?namaste=paypal&user_id='.$user_ID);?>">
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="<?php echo $currency;?>">
		<input type="hidden" name="lc" value="US">
		<input type="hidden" name="bn" value="PP-BuyNowBF">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</p>
	</form> 
	<?php endif;?>
	
	<?php if($accept_other_payment_methods):?>
		<div><?php echo $other_payment_methods?></div>
	<?php endif;?>
</div>