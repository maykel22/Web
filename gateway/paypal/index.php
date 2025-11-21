<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	#	PayPal Payment module for Sendroid Ultimate
	#	location: gateway/2checkout/index.php
	#	Developed by: Ynet Interactive
	#	Special thanks: Mr. White

*/
global $LANG;
global $configverssion_id;
global $configapp_version;
global $server;
$userID = getUser();
$resellerID = userData($userID,'reseller');
global $userID;
global $resellerID;

class PaymentGateway {
	function initiatePayment($transaction_reference,$amount,$user_id,$button='') {
		global $LANG;
		$gateway = transactionData($transaction_reference,'method');	//Get the gateway ID from transaction
		$currency = paymentGatewayData($gateway,'currency_id');			//get the currency for this gateway
		$user_currency = userData($user_id,'currency_id');				//The users currency		
		$currency_code = currencyCode($user_currency);					//set user currency code if needed
		$amount_converted = $amount*currencyRate($currency);			//Convert amount to charge to gateway's currency
		if($currency_code==$currency) {
			$amount_converted = $amount;								// No conversion needed
		}
		$paypal_email = trim(paymentGatewayData($gateway,'param1'));	//This is saved in param1 column
		$amount_converted = round($amount_converted,2);
		//build HTML form
		$out = "<form action='https://www.paypal.com/cgi-bin/webscr' method='POST' >
        <input type='hidden' name='cmd' value='_xclick'>
        <input type='hidden' name='business' value='".$paypal_email."' />
        <input type='hidden' name='item_name' value='Payment for order #".$transaction_reference."' />
        <input type='hidden' name='item_number' value='".$transaction_reference."' />
        <input type='hidden' name='amount' value='".$amount_converted."' />
        <input type='hidden' name='cancel_return' value='".home_base_url().'gateway/paypal/callback.php?tx_reference='.$transaction_reference."' />
        <input type='hidden' name='custom' value='".$transaction_reference."' />
        <input type='hidden' name='currency_code' value='USD' />
        <input type='hidden' name='no_shipping' value='1' />
		<input type='hidden' name='no_note' value='1' />
		<input type='hidden' value='2' name='rm' />               
        <input type='hidden' name='lc' value='US' />
        <input type='hidden' name='return' value='".home_base_url().'gateway/paypal/callback.php?tx_reference='.$transaction_reference."' />
        <input type='hidden' name='notify_url' value='".home_base_url().'gateway/paypal/callback.php?tx_reference='.$transaction_reference."' />";
		$out .= $button;
		$out .= '</form>';	
		return $out;	  
	}
	function validatePayment($transaction_reference,$amount,$user_id,$postdata='') {
		//Not required for 2checkout
	}	
}

?>