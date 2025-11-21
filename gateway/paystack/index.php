<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	#	Paystack Payment module for Sendroid Ultimate
	#	location: gateway/paystack/index.php
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
		$amount_converted = $amount_converted*100;						//This is just because Paystack wants the amount in K
		$secret_key = trim(paymentGatewayData($gateway,'param2'));			//Paystack stores private key in param2 column
		$public_key = trim(paymentGatewayData($gateway,'param1'));			//Paystack stores public key in param1 column
		//build HTML form
		$out = '<form id="paystack" action="'.home_base_url().'gateway/paystack/callback.php?tx_reference='.$transaction_reference.'" method="post" >';
		$out .= $button;
		$out .=	'<script 
					src="https://js.paystack.co/v1/inline.js" 
					data-key="'.$public_key.'" 
					data-email="'.userData($user_id,'email').'"
					data-amount="'.round($amount_converted).'"
					data-ref="'.$transaction_reference.'"
					data-custom-button="inv_pay_b" 
				  >
				  </script>';
		$out .= '</form>';	
		return $out;	  
	}
	function validatePayment($transaction_reference,$amount,$user_id,$postdata='') {
		//Not required for paystack
	}	
}