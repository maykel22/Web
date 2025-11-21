<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
	#	2co Payment module for Sendroid Ultimate
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
		$checkout_seller_id = trim(paymentGatewayData($gateway,'param1'));	//This is saved in param1 column
		$amount_converted = round($amount_converted,2);
		//build HTML form
		$out = "<form id='2chechout' action='https://www.2checkout.com/checkout/purchase' method='post'>
        <input type='hidden' name='sid' value='$checkout_seller_id' />
        <input type='hidden' name='mode' value='2CO' />
        <input type='hidden' name='li_0_type' value='product' />
        <input type='hidden' name='li_0_name' value='Payment for order #$transaction_reference' />
        <input type='hidden' name='li_0_price' value='$amount_converted' />
        <input type='hidden' name='li_0_quantity' value='1' >
        <input type='hidden' name='card_holder_name' value='".userData(getUser(),'first_name')." ".userData(getUser(),'last_name')."' />
        <input type='hidden' name='street_address' value='".userData(getUser(),'address')."' />
        <input type='hidden' name='street_address2' value='' />
        <input type='hidden' name='city' value='".userData(getUser(),'city')."' />
        <input type='hidden' name='state' value='".userData(getUser(),'state')."' />
        <input type='hidden' name='zip' value='' />
        <input type='hidden' name='country' value='".countryName(userData(getUser(),'country_id'))."' />
        <input type='hidden' name='email' value='".userData(getUser(),'email')."' />
        <input type='hidden' name='phone' value='".userData(getUser(),'phone')."' />
        <input type='hidden' name='x_receipt_link_url' value='".home_base_url().'gateway/2checkout/callback.php?tx_reference='.$transaction_reference."'>";
		$out .= $button;
		$out .= '</form>';	
		return $out;	  
	}
	function validatePayment($transaction_reference,$amount,$user_id,$postdata='') {
		//Not required for 2checkout
	}	
}