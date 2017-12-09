<?php
/**
 * paypal_class provides a neat and simple method to interface with paypal and
 *      The paypal Instant Payment Notification (IPN) interface.
 * 
 *
 * @author Micah Carrick <email@micahcarrick.com> modified by Vikas Patial
 * @version v1.3.0, last update on 10.10.2005
 */
include_once "../Components/Settings.php";

class paypal_class {
   var $last_error;                 // holds the last error encountered
   
   var $ipn_log;                    // bool: log IPN results to text file?
   
   var $ipn_log_file;               // filename of the IPN log
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   
   var $fields = array();           // array holds the fields to submit to paypal

   
   function paypal_class() {
      // initialization constructor.  Called when class is created.
      $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
      
      $this->last_error = '';
      
      $this->ipn_log_file = 'tlaulli_debug.log';
      $this->ipn_response = '';
   }
   
   // Verifies various order details.
   function validate_order() {
      // Check product ID , Amount , Currency , Recivers email , 
      global $currency, $paypal_email;
   		
      // Ensure the order is actually for Tlaulli.
      if( $this->ipn_data['receiver_email'] != $paypal_email ) {
  	 $this->debug_log("Invalid Receiver E-Mail : {$this->ipn_data['receiver_email']}
             , paypal_email = {$paypal_email}", false);  
       	 return false;
      }
   		
      // Ensure the payment is valid.
      if($currency != $this->ipn_data["mc_currency"] ||
         $this->ipn_data["mc_gross"] <= 0 ||
         $this->ipn_data["tax"] <= 0 ||
         $this->ipn_data["num_cart_items"] <= 0 ||
         $this->ipn_data["mc_shipping"] <= 0) {
         $this->debug_log("Invalid payment items mc_currency = {$this->ipn_data['mc_currency']}, 
            mc_gross = {$this->ipn_data['mc_gross']}, tax = {$this->ipn_data['tax']}
            num_cart_items = {$this->ipn_data['num_cart_items']}, 
            mc_shipping = {$this->ipn_data['mc_shipping']}", false);
         return false;
      } 
      
      // Validate shipping.
      if($this->ipn_data["address_street"] == "" ||
         $this->ipn_data["address_zip"] == "" ||
         $this->ipn_data["address_country_code"] == "" ||
         $this->ipn_data["address_name"] == "") {
         $this->debug_log("Invalid address items address_street = {$this->ipn_data['address_street']}, 
            address_zip = {$this->ipn_data['address_zip']}, address_country_code = {$this->ipn_date['address_country_code']}
            address_name = {$this->ipn_data['address_name']}", false);
         return false;
      }
      
      return true;
   }
   
   // Ensures that the current payment notifications actuallys comes from Paypal and verifies the order details.
   function validate_ipn() {
      // Parse the paypal URL
      $url_parsed=parse_url($this->paypal_url);        

      // Re-generate POST string and store POSTed values.
      $post_string = '';    
      foreach ($_POST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;           
         $post_string .= $field.'='.urlencode(stripslashes($value)).'&'; 
      }
    	
      $this->post_string = $post_string;
      $this->debug_log('Post string : '. $this->post_string,true);  
      $post_string.="cmd=_notify-validate";

      // Open the connection to paypal
      $verify_url = "ssl://{$url_parsed['host']}";
      $fp = fsockopen($verify_url, "443", $err_num, $err_str, 30); 
      if(!$fp) {
         $this->debug_log('Connection to '.$url_parsed['host']." failed.fsockopen error no. $errnum: $errstr",false);  
         return false;
      }
      
      // Post the data back to paypal and receive response
      fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n"); 
      fputs($fp, "Host: $url_parsed[host]\r\n"); 
      fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
      fputs($fp, "Content-length: ".strlen($post_string)."\r\n"); 
      fputs($fp, "Connection: close\r\n\r\n"); 
      fputs($fp, $post_string . "\r\n\r\n");             
         
      while(!feof($fp)) { 
         $this->ipn_response .= fgets($fp, 1024); 
      } 

      fclose($fp); // close connection
      $this->debug_log("Connection to {$verify_url} successfuly completed.", true);  

      // Check whether the IPN is valid.
      if (!eregi("VERIFIED", $this->ipn_response)) {
         $this->debug_log("IPN validation failed. IPN response = {$this->ipn_response}", false);  
         return false;
      }
      
      // Validate the products purchased.
      $this->debug_log("IPN validation succeeded. IPN response = {$this->ipn_response}", true);  
      if(!$this->validate_order()) {
      	$this->debug_log('IPN product validation failed.',false);  
      	return false;
      }  
      
      return true;
   }
   
   function debug_log($message,$success,$end=false) {
  	   if (!$this->ipn_log){
         return;  // is logging turned off?
      }
      
      // Timestamp
      $text = '['. date('m/d/Y g:i A') . '] - ' . (($success) ? 'SUCCESS :' : 'FAILURE :') . $message . "\n"; 
      
      if ($end) {
      	$text .= "\n------------------------------------------------------------------\n\n";
      }
      
      // Write to log
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text ); 
      fclose($fp);  // close file  	
   }
}
?>