<?php
class NamastePayment {
	static $pdt_mode = false;	
	static $pdt_response = '';	
	
	// handle Paypal IPN request
	static function parse_request($wp) {
		// only process requests with "namaste=paypal"
	   if (array_key_exists('namaste', $wp->query_vars) 
	            && $wp->query_vars['namaste'] == 'paypal') {
	        self::paypal_ipn($wp);
	   }	
	}
	
	// process paypal IPN
	static function paypal_ipn($wp = null) {
		global $wpdb;
		echo "<!-- NAMASTECOMMENT paypal IPN -->";
		
		// print_r($_GET);
		// read the post from PayPal system and add 'cmd'
		$pdt_mode = get_option('namaste_use_pdt');
		if(!empty($_GET['tx']) and !empty($_GET['namaste_pdt']) and get_option('namaste_use_pdt')==1) {
			// PDT			
			$req = 'cmd=_notify-synch';
			$tx_token = strtoupper($_GET['tx']);
			$auth_token = get_option('namaste_pdt_token');
			$req .= "&tx=$tx_token&at=$auth_token";
			$pdt_mode = true;
			$success_responce = "SUCCESS";
		}
		else {	
			// IPN		
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) { 
			  $value = urlencode(stripslashes($value)); 
			  $req .= "&$key=$value";
			}
			$success_responce = "VERIFIED";
		}		
		
		self :: $pdt_mode = $pdt_mode;	
		
		$paypal_host = "ipnpb.paypal.com";
		$paypal_sandbox = get_option('namaste_paypal_sandbox');
		if($paypal_sandbox == '1') $paypal_host = 'ipnpb.sandbox.paypal.com';
		
		// post back to PayPal system to validate
		$paypal_host = "https://".$paypal_host;
		
		// wp_remote_post
		$response = wp_remote_post($paypal_host, array(
			    'method'      => 'POST',
			    'timeout'     => 45,
			    'redirection' => 5,
			    'httpversion' => '1.0',
			    'blocking'    => true,
			    'headers'     => array(),
			    'body'        => $req,
			    'cookies'     => array()
		    ));
		
		if ( is_wp_error( $response ) ) {
		    $error_message = $response->get_error_message();
			 return self::log_and_exit("Can't connect to Paypal: $error_message");
		} 
		
		if (strstr ($response['body'], $success_responce) or $paypal_sandbox == '1') self :: paypal_ipn_verify($response['body']);
		else return self::log_and_exit("Paypal result is not VERIFIED: ".$response['body']);			
		
		/*
		// see CURL or fsockopen
		if(function_exists('curl_version')) {
			$ch = curl_init('https://'.$paypal_host.'/cgi-bin/webscr');
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
			
			if( !($res = curl_exec($ch)) ) {
			   self::log_and_exit("Got " . curl_error($ch) . " when processing IPN data");
			   curl_close($ch);
			   exit;
			}
			curl_close($ch);			
			if (strstr ($res, $success_responce) or $paypal_sandbox == '1') self :: paypal_ipn_verify($res);
			else return self::log_and_exit("Paypal result is not VERIFIED: $res");
		}
		else {
			$header="";
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n";
			$header .="Host: $paypal_host\r\n"; 
			$header .="Connection: close\r\n\r\n";		
			$fp = fsockopen ($paypal_host, 80, $errno, $errstr, 30);
			
			if($fp) {
				fputs ($fp, $header . $req);
				$pp_response = '';
			   while (!feof($fp)) {
			      $res = fgets ($fp, 1024);	
			      $pp_response .= $res;	     
			      if (strstr ($res, $success_responce) or $paypal_sandbox == '1') {
			      	self :: paypal_ipn_verify($pp_response);
			      	exit;
			     	}			     	
			   }  
			   fclose($fp);
			   return self::log_and_exit("Paypal result is not VERIFIED: $pp_response");  
			} 
			else return self::log_and_exit("Can't connect to Paypal via fsockopen");
		}
		*/
		exit;
	}
	
	// process paypal IPN
	static function paypal_ipn_verify($pp_response) {
		global $wpdb, $user_ID, $post;
		echo "<!-- NAMASTECOMMENT paypal IPN -->";
				
		// when we are in PDT mode let's assign all lines as POST variables
		if(self :: $pdt_mode) {
			 $lines = explode("\n", $pp_response);	
				if (strcmp ($lines[0], "SUCCESS") == 0) {
				for ($i=1; $i<count($lines);$i++){
					if(strstr($lines[$i], '=')) list($key,$val) = explode("=", $lines[$i]);
					$_POST[urldecode($key)] = urldecode($val);
				}
			 }
			 
			 $_GET['user_id'] = $user_ID;
			 self :: $pdt_response = $pp_response;
		} // end PDT mode transfer from lines to $_POST	 		
		
	   $paypal_email = get_option("namaste_paypal_id");
		
		
   	// check the payment_status is Completed
      // check that txn_id has not been previously processed
      // check that receiver_email is your Primary PayPal email
      // process payment
	   $payment_completed = false;
	   $txn_id_okay = false;
	   $receiver_okay = false;
	   $payment_currency_okay = false;
	   $payment_amount_okay = false;
	   
	   if(@$_POST['payment_status']=="Completed") {
	   	$payment_completed = true;
	   } 
	   else self::log_and_exit("Payment status: $_POST[payment_status]");
	   
	   // check txn_id
	   $txn_exists = $wpdb->get_var($wpdb->prepare("SELECT paycode FROM ".NAMASTE_PAYMENTS."
		   WHERE paytype='paypal' AND paycode=%s", $_POST['txn_id']));
		if(empty($txn_id)) $txn_id_okay = true; 
		else {
			// in PDT mode just redirect to the post because existing txn_id isn't a problem.
			// but of course we shouldn't insert second payment
			if( self :: $pdt_mode) namaste_redirect(get_permalink($_GET['course_id']));
			return self::log_and_exit("TXN ID exists: $txn_exists");
		}  
			
		// check receiver email
		if(strtolower($_POST['business']) == strtolower($paypal_email)) {
			$receiver_okay = true;
		}
		else self::log_and_exit("Business email is wrong: $_POST[business]");
		
		// check payment currency
		if($_POST['mc_currency']==get_option("namaste_currency")) {
			$payment_currency_okay = true;
		}
		else self::log_and_exit("Currency is $_POST[mc_currency]"); 
		
		// check amount
		if(empty($_GET['course_id'])) $_GET['course_id'] = @$_GET['item_number']; // in case of PDT
		$fee = get_post_meta($_GET['course_id'], 'namaste_fee', true);
      $course = get_post($_GET['course_id']);		
		
		// $fee = apply_filters('namaste-coupon-applied', $fee, $_GET['course_id'], 'course');	// coupon code from other plugin?	
		// Coupon code from Namaste! PRO?
		if(class_exists('NamastePROCoupons')) {		   
		   $fee = NamastePROCoupons :: coupon_applied($fee, $_GET['course_id'], 'course');
		}
		// school price?
		$is_school = 0;
	   if(class_exists('NamastePROSchool') and !empty($_GET['is_school'])) {
	      $fee = NamastePROSchool :: school_price('course', $course);
	      $is_school = 1;
	   }	
		
		if($_POST['mc_gross']>=$fee) {
			$payment_amount_okay = true;
		}
		else self::log_and_exit("Wrong amount: $_POST[mc_gross] when price is $fee"); 
		
		// everything OK, insert payment and enroll
		if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
				and $payment_amount_okay) {					
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_PAYMENTS." SET 
				course_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, paytype='paypal'", 
				$_GET['course_id'], $_GET['user_id'], $fee, $_POST['txn_id']));
			
			do_action('namaste-paid', $_GET['user_id'], $fee, "course", $_GET['course_id'], $is_school);	
				
			// enroll accordingly to course settings - this will be placed in a method once we 
			// have more payment options
			$enroll_mode = get_post_meta($_GET['course_id'], 'namaste_enroll_mode', true);	
			if(!NamasteLMSStudentModel :: is_enrolled($_GET['user_id'], $_GET['course_id']))  {
				$_course = new NamasteLMSCourseModel();
				$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';				
				$_course->enroll($_GET['user_id'], $_GET['course_id'], $status);
			}	

			if(!self :: $pdt_mode) exit;
			else namaste_redirect(get_permalink($_GET['course_id']));
		}  
		
		exit;
	}
	
	// log paypal errors
	static function log_and_exit($msg) {
		// log
		$msg = "Paypal payment attempt failed at ".date(get_option('date_format').' '.get_option('time_format')).": ".$msg;
		$errorlog=get_option("namaste_errorlog");
		$errorlog = $msg."\n".$errorlog;
		update_option("namaste_errorlog",$errorlog);
		
		// if we are in Paypal PDT mode just echo and don't exit
		if(self :: $pdt_mode) {
			echo $msg;
			if(get_option('namaste_debug_mode')) echo "<br>Full response: ".self :: $pdt_response;
			return true;
		}
		// throw exception as there's no need to contninue
		exit;
	}
}