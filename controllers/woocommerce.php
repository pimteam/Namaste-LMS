<?php
/* WooCommerce Integration */
// complete Woocommerce order
function namastewoo_bridge_order_complete($order_id) {
	global $wpdb, $user_ID;
		
	update_option('namastewoo_bridge_last_order_id', $order_id);
	
	// select line items
	$items = $wpdb->get_results($wpdb->prepare("SELECT tI.*, tM.meta_value as product_id 
			FROM {$wpdb->prefix}woocommerce_order_items tI JOIN {$wpdb->prefix}woocommerce_order_itemmeta tM
			ON tM.order_item_id = tI.order_item_id AND tM.meta_key='_product_id'
			WHERE tI.order_id = %d AND tI.order_item_type = 'line_item'", $order_id));
	$course_ids = $class_ids = array(); // quiz IDs to process
	$namaste_redirect = ""; // do we redirect anywhere?
	
	// now for each $item select the product, and check in the meta whether it's a namaste product
	foreach($items as $item) {
		$product = get_post($item->product_id);
		update_option('namastewoo_bridge_last_product_title', $product->post_title);		
		// get meta
		$atts = get_post_meta($product->ID, '_product_attributes', true);
		
		foreach($atts as $key=>$att) {		
			
			if($att['name'] == 'namaste-course' and !empty($att['value'])) {
				if(is_numeric($att['value'])) $course_ids[] =  $att['value'];
				else {
					$att['value'] = str_replace(' ', '', $att['value']);
					$att_cids = explode('|', $att['value']);
					$att_cids = array_filter($att_cids);
					$course_ids = array_merge($course_ids, $att_cids);
				}
			}
			
			if($att['name'] == 'namaste-class' and !empty($att['value'])) {
				if(is_numeric($att['value'])) $class_ids[] =  $att['value'];
				else {
					$att['value'] = str_replace(' ', '', $att['value']);
					$att_cids = explode('|', $att['value']);
					$att_cids = array_filter($att_cids);
					$class_ids = array_merge($class_ids, $att_cids);
				}
			}
			
			if($att['name'] == 'namaste-redirect' and empty($namaste_redirect)) $namaste_redirect = $att['value'];
		}
	}	// end foreach item	
	
	// if there are quiz ids we'll activate them but first need to ensure there is user ID
	if(!empty($course_ids) or !empty($class_ids)) {
		// select order  meta
		$user_id = get_post_meta($order_id, "_customer_user", true);
		
		if(empty($user_id)) {
			$password = wp_generate_password( 12, true );
			$user_email = get_post_meta($order_id, "_billing_email", true);
			
			// email exists?
			$user = get_user_by('email', $user_email);
			if(empty($user->ID)) {
				$user_id = wp_create_user( $user_email, $password, $user_email );
				wp_update_user( array ('ID' => $user_id, 'role' => 'student' ) ) ;
			}
			else $user_id = $user->ID;
		}
		
		// now insert payments for this user ID and the given quiz IDs
		foreach($course_ids as $course_id) {
			if(!is_numeric($course_id)) continue;
			
			$fee = get_post_meta($course_id, 'namaste_fee', true);	
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_PAYMENTS." SET course_id=%d, user_id=%d, date=CURDATE(),
				amount=%f, status='completed', paytype='woocommerce'", $course_id, $user_id, $fee));			
			
			do_action('namaste-paid', $user_id, $fee, "course", $course_id);	
			$enroll_mode = get_post_meta($course_id, 'namaste_enroll_mode', true);	
			if(!NamasteLMSStudentModel :: is_enrolled($user_id, $course_id))  {
				$_course = new NamasteLMSCourseModel();
				$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';				
				$_course->enroll($user_id, $course_id, $status);
			}
		}
		
		// handle classes
		foreach($class_ids as $class_id) {
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_PAYMENTS." SET class_id=%d, user_id=%d, date=CURDATE(),
				amount=1, status='completed', paytype='woocommerce'", $class_id, $user_id));
			do_action('namaste-paid', $user_id, 1, "class", $class_id);									
			NamastePROClass :: signup($user_id, $class_id);		
		}
		
		// any redirect defined?		
		if(!empty($namaste_redirect)) update_option('namaste-woocom-redirect', $namaste_redirect);
	}
} // end ww_bridge_order_complete

// this will handle redirects
function namastewoo_bridge_thankyou($order_id)  {
   $redirect = get_option('namaste-woocom-redirect');
   if(!empty($redirect)) {
      update_option('namaste-woocom-redirect', '');
      wp_redirect($redirect);
   }
}

// test it by just passing order ID
// if you want to test you have to change "and false" to "and true" in the code below
function namastewoo_bridge_template_redirect() {
	if(!empty($_GET['namwbridge_order_id']) and false) {
		namastewoo_bridge_order_complete($_GET['namwbridge_order_id']);
		exit;
	}	
}