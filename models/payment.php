<?php
class NamastePayment {
	// handle Paypal IPN request
	static function parse_request($wp) {
		// only process requests with "namaste=paypal"
	   if (array_key_exists('namaste', $wp->query_vars) 
	            && $wp->query_vars['namaste'] == 'paypal') {
	        self::paypal_ipn($wp);
	   }	
	}
	
	// process paypal IPN
	static function paypal_ipn($wp) {
		global $wpdb;
		echo "<!-- NAMASTECOMMENT paypal IPN -->";
		
	   $paypal_email = get_option("namaste_paypal_id");
		
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) { 
		  $value = urlencode(stripslashes($value)); 
		  $req .= "&$key=$value";
		}		
		
		// post back to PayPal system to validate
		$header="";
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
		
		
		if($fp) {			
			fputs ($fp, $header . $req);
		   while (!feof($fp)) {
		      $res = fgets ($fp, 1024);
		     
		      if (strstr ($res, "200 OK")) {
		      	// check the payment_status is Completed
			      // check that txn_id has not been previously processed
			      // check that receiver_email is your Primary PayPal email
			      // process payment
				   $payment_completed = false;
				   $txn_id_okay = false;
				   $receiver_okay = false;
				   $payment_currency_okay = false;
				   $payment_amount_okay = false;
				   
				   if($_POST['payment_status']=="Completed") {
				   	$payment_completed = true;
				   } 
				   else self::log_and_exit("Payment status: $_POST[payment_status]");
				   
				   // check txn_id
				   $txn_exists = $wpdb->get_var($wpdb->prepare("SELECT paycode FROM ".NAMASTE_PAYMENTS."
					   WHERE paytype='paypal' AND paycode=%s", $_POST['txn_id']));
					if(empty($txn_id)) $txn_id_okay = true; 
					else self::log_and_exit("TXN ID exists: $txn_id");  
					
					// check receiver email
					if($_POST['business']==$paypal_email) {
						$receiver_okay = true;
					}
					else self::log_and_exit("Business email is wrong: $_POST[business]");
					
					// check payment currency
					if($_POST['mc_currency']==get_option("namaste_currency")) {
						$payment_currency_okay = true;
					}
					else self::log_and_exit("Currency is $_POST[mc_currency]"); 
					
					// check amount
					$fee = get_post_meta($_POST['item_id'], 'namaste_fee', true);
					if($_POST['mc_gross']>=$fee) {
						$payment_amount_okay = true;
					}
					else self::log_and_exit("Wrong amount: $_POST[mc_gross] when price is $fee"); 
					
					// everything OK, insert payment and enroll
					if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
							and $payment_amount_okay) {						
						$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_PAYMENTS." SET 
							course_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, paytype='paypal'", 
							$_POST['item_id'], $_GET['user_id'], $fee, $_POST['txn_id']));
							
						// enroll accordingly to course settings - this will be placed in a method once we 
						// have more payment options
						$enroll_mode = get_post_meta($_POST['item_id'], 'namaste_enroll_mode', true);	
						if(!NamasteLMSStudentModel :: is_enrolled($_GET['user_id'], $_POST['item_id']))  {
							$_course = new NamasteLMSCourseModel();
							$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';				
							$_course->enroll($_GET['user_id'], $_POST['item_id'], $status);
						}	
						exit;
					}
		     	}
		     	else self::log_and_exit("Paypal result is not 200 OK: $res");
		   }  
		   fclose($fp);  
		} 
		else self::log_and_exit("Can't connect to Paypal");
		
		exit;
	}
	
	// log paypal errors
	static function log_and_exit($msg) {
		// log
		$errorlog=get_option("namaste_errorlog");
		$errorlog = $msg."\n".$errorlog;
		update_option("namaste_errorlog",$errorlog);
		
		// throw exception as there's no need to contninue
		exit;
	}
}