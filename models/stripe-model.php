<?php
// stripe integration model
class NamasteStripe {
	static function load() {
		require_once(NAMASTE_PATH.'/lib/Stripe.php');
 
		$stripe = array(
		  'secret_key'      => get_option('namaste_stripe_secret'),
		  'publishable_key' => get_option('namaste_stripe_public')
		);
		 
		Stripe::setApiKey($stripe['secret_key']);
		
		return $stripe;
	}
	
	static function pay($currency) {
		global $wpdb, $user_ID, $user_email;
		$_course = new NamasteLMSCourseModel();
		
		$token  = $_POST['stripeToken'];
		$course = get_post($_POST['course_id']);
		$fee = get_post_meta($course->ID, 'namaste_fee', true);
		 
		try {
			 $customer = Stripe_Customer::create(array(
		      'email' => $user_email,
		      'card'  => $token
		    ));				
			
			  $charge = Stripe_Charge::create(array(
			      'customer' => $customer->id,
			      'amount'   => $fee*100,
			      'currency' => $currency
			  ));
		} 
		catch (Exception $e) {
			wp_die($e->getMessage());
		}	  
		 
		// !!!!in the next version avoid this copy-paste
		// almost the same code is in models/payment.php for the paypal payments
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_PAYMENTS." SET 
						course_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, paytype='paypal'", 
						$_POST['course_id'], $user_ID, $fee, $token));
						
		// enroll accordingly to course settings - this will be placed in a method once we 
		// have more payment options
		$enroll_mode = get_post_meta($course->ID, 'namaste_enroll_mode', true);	
		if(!NamasteLMSStudentModel :: is_enrolled($user_ID, $course->ID))  {
			$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';				
			$_course->enroll($user_ID, $course->ID, $status);
		}	
	}
}