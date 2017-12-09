<?php
/**
 * No need to modify these unless you know what ur doing 
 */
$debug     = false; // Enable if you want debug log 
$debug_log = "tlaulli_debug.log";
$sandbox   = false; // Enable sandbox testing

if($sandbox){
    // URL of directory where script is stored ( include trailing slash )
    $script_location = 'http://localhost/Resources/Libs/'; 

    //URL of page from where script is called 
    $script_page     = 'http://localhost/'; 

    //URL of page Thank you page ( once payment is made , payee is sent to this page )
    $script_thankyou = 'http://localhost/Views/Checkout/ThanksChkout.php'; 
    $paypal_email = 'Rec_1352397082_biz@gmail.com';   // Paypal Email
    $paypal_server = "https://www.sandbox.paypal.com/cgi-bin/webscr";
} else {
    // URL of directory where script is stored ( include trailing slash )
    $script_location = 'http://www.tlaulli.com/Resources/Libs/'; 

    //URL of page from where script is called 
    $script_page     = 'http://www.tlaulli.com/'; 

    //URL of page Thank you page ( once payment is made , payee is sent to this page )
    $script_thankyou = 'http://www.tlaulli.com/Views/Checkout/ThanksChkout.php';
    
    //$paypal_email = 'joe.carcamo@gmail.com';   // Paypal Email
    $paypal_email = 'sales@tlaulli.com';   // Paypal Email
    $paypal_server = "https://www.paypal.com/cgi-bin/webscr";
}

// Please change thing to anything random 
$secret = 'DFJ#*#$NSD)Oc32j908u43jhh893';
$currency = 'USD';

// Success Email Messages 
$notify_subject = "Failed to process order {txn_id} - {first_name} {last_name}";
$notify_body = "Failed to update product {product_id} for quantity {quantity}.";

// Tags
$tags = array("{first_name}", "{last_name}", "{txn_id}", "{product_id}", "{quantity}");

// Simple PHP Mail 
$email_config['protocol'] = 'mail';
$notify_email ='ipn@tlaulli.com';    // Email which will recive notification
$tax_rate_cfg = 0.0875;
?>
