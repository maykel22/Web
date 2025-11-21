<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	#	Quickteller Payment module for Sendroid Ultimate
	#	location: gateway/quickteller/index.php
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
		$amount_converted = round($amount_converted*100);
		$quickteller_payment_code = trim(paymentGatewayData($gateway,'param1'));	//This is saved in param1 column
		
		//build HTML form
		$out = "<form action='https://paywith.quickteller.com/' method='post' >
		  <input type='hidden' name='amount' value='$amount_converted' />
		  <input type='hidden' name='customerId' value='$transaction_reference' />
		  <input type='hidden' name='redirectUrl' value='".home_base_url().'gateway/gtpay/callback.php?tx_reference='.$transaction_reference."' />
		  <input type='hidden' name='paymentCode' value='$quickteller_payment_code' /> 
		  <input type='hidden' name='mobileNumber' value='".userData(getUser(),'phone')."' /> 
		  <input type='hidden' name='emailAddress' value='".userData(getUser(),'email')."' /> "; 
		$out .= $button;
		$out .= '</form>';	
		return $out;	  
	}
	function validatePayment($transaction_reference,$amount,$user_id,$postdata='') {
		//Not required for gtpay
	}	
} 
?>