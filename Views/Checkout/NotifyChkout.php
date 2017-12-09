<?php
include_once "../../Resources/Components/Settings.php";
include_once "../../Resources/Libs/Paypal.php";
include_once "../../Resources/Libs/Rc4Crypt.php";
include_once "../../Resources/Libs/DbLib.php";
include_once "../../Resources/Libs/Email.php";

/* Global defines */
$p = new paypal_class();             // initiate an instance of the class

// testing system , comment on live version 
if ($debug) {
	$p->ipn_log = true; 
	$p->ipn_log_file = $debug_log;
}

if ($sandbox) {
	$p->paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
}

// Update database.
$db = new DbLib();
if ($p->validate_ipn()) {
   $db->autocommit(false);
   
   for($i = 1; $i <= $p->ipn_data["num_cart_items"]; ++$i) {
      $item_number = $p->ipn_data["item_number{$i}"];
      $item_qty = $p->ipn_data["quantity{$i}"];
      
      if(!$db->decrementItemQty($item_number, $item_qty)){
         // Send email indicating that product is not available.
         $vals = array($p->ipn_data["first_name"], $p->ipn_data["last_name"], $p->ipn_data["txn_id"],
             $item_number, $item_qty);
         $n_subject = str_replace($tags, $vals, $notify_subject);
         $n_body    = str_replace($tags, $vals, $notify_body) .
                  "\n\n-------Paypal Parameters---\n".
                  $p->post_string;
		 
         if(!@send_mail($notify_email, $n_body, $n_subject, $notify_email)) {
         	$p->debug_log("Error sending notify Email to {$notify_email}.\n$n_subject\n$n_body", false);
         }
         
         $db->rollback();
         break;
      }
   }
   
   $db->commit();
   $db->autoCommit(true);
} else {
   if(!@send_mail($notify_email, "Invalid IPN sent from {$_SERVER['REMOTE_ADDR']}. 
         Txn number {$p->ipn_data['txn_id']}", "Invalid IPN", $notify_email)) {
      $p->debug_log("Invalid IPN sent from {$_SERVER['REMOTE_ADDR']}. 
         Txn number {$p->ipn_data['txn_id']}", false);
   }
}
?>
