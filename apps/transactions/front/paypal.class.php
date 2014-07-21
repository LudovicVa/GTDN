<?php
function decode(&$item, $key) {
	$item = urldecode($item);
}

class Paypal {
    private $PayPalApiUsername; 
	private $PayPalApiPassword;
	private $PayPalApiSignature;
	private $PayPalMode;
	
	private $PayPalCurrencyCode;
	private $PayPalReturnURL;
	private $PayPalCancelURL;
	
	//State variable
	private $current_token;
	private $id_deal;
	private $deal_info;
	
	public function __construct() {			
		$this->PayPalApiUsername 	= WConfig::get("apps.transactions.paypal_username"); 
		$this->PayPalApiPassword 	= WConfig::get("apps.transactions.paypal_password");
		$this->PayPalApiSignature 	=  WConfig::get("apps.transactions.paypal_signature");
		$this->PayPalMode 				= WConfig::getAppVar("transactions", "use_sandbox", "true");
		if($this->PayPalMode == 'true') {
			$this->PayPalMode 			=	'.sandbox';
		} else {
			$this->PayPalMode 			=	'';
		}
		
		$this->PayPalCurrencyCode 	= WConfig::get("apps.transactions.paypal_currency_code");
		$this->PayPalReturnURL 		= WConfig::get('config.base') . WConfig::get("apps.transactions.paypal_return");
		$this->PayPalCancelURL 		= WConfig::get('config.base') . WConfig::get("apps.transactions.paypal_cancel");
	}
	
	/**
	* Initialiaze transaction
	**/
	public function initTransaction($id_deal, $deal_info) {	
		$this->id_deal = $id_deal;
		$this->deal_info = $deal_info;
	
		//Request for paying					
		$padata = '&CURRENCYCODE='.urlencode($this->PayPalCurrencyCode).
			'&PAYMENTACTION=Sale'.
			'&ALLOWNOTE=1'.
			'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($this->PayPalCurrencyCode).
			'&PAYMENTREQUEST_0_AMT='.urlencode($deal_info['price']).
			'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($deal_info['price']). 
			'&L_PAYMENTREQUEST_0_QTY0='. urlencode(1).
			'&L_PAYMENTREQUEST_0_AMT0='.urlencode($deal_info['price']).
			'&L_PAYMENTREQUEST_0_NAME0='.urlencode($deal_info['deal_name']).
			'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($id_deal).
			'&AMT='.urlencode($deal_info['price']).				
			'&RETURNURL='.urlencode($this->PayPalReturnURL).
			'&CANCELURL='.urlencode($this->PayPalCancelURL);
		
		//We need to execute the "SetExpressCheckOut" method to obtain paypal token
		$response =  $this->PPHttpPost('SetExpressCheckout', $padata);
		
		$this->current_token = $response["TOKEN"];
		return $response;
	}	
	
	/**
	* Store transaction after initialization
	**/
	public function storeTransactionAndRedirect() {
		$_SESSION['deal_price'] 	= $this->deal_info['price'];
		$_SESSION['id_deal']			=  $this->id_deal;
		
		//Redirect user to PayPal store with Token received.
		$paypalurl ='https://www'.$this->PayPalMode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$this->current_token.'';
		header('Location: '.$paypalurl);
		die;
	}
	
	/**
	* Confirm payment
	**/
	public function confirmPayment($token,  $payerID) {
		//get session variables
		$deal_price 	= $_SESSION['deal_price'];
		$id_deal 		= $_SESSION['id_deal'];
		
		$padata = 	'&TOKEN='.urlencode($token).
							'&PAYERID='.urlencode($payerID).
							'&PAYMENTACTION='.urlencode("SALE").
							'&PAYMENTREQUEST_0_AMT='.urlencode($deal_price).
							'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($this->PayPalCurrencyCode);
		
		//We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
		$response = $this->PPHttpPost('DoExpressCheckoutPayment', $padata);
		
		unset($_SESSION['id_deal']);
		unset($_SESSION['deal_price']);
		
		return $response;
	}		
	
	/**
	* Store transaction after initialization
	**/
	public function getTransactionDetail($payment_id) {		
		$transactionID = $payment_id;
		$nvpStr = "&TRANSACTIONID=".$transactionID;
		return $this->PPHttpPost('GetTransactionDetails', $nvpStr);
	}
	
	/**
	* Generic processing method
	**/
	private function PPHttpPost($methodName_, $nvpStr_) {
		$API_Endpoint = "https://api-3t".$this->PayPalMode.".paypal.com/nvp";
		$version = urlencode('109.0');
	
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$this->PayPalApiPassword&USER=$this->PayPalApiUsername&SIGNATURE=$this->PayPalApiSignature$nvpStr_";
	
		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
	
		// Get response from the server.
		$httpResponse = curl_exec($ch);
	
		if(!$httpResponse) {
			exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}
	
		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);
	
		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}
	
		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
			exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}
		
		array_walk($httpParsedResponseAr, 'decode');
		return $httpParsedResponseAr;
	}
		
}
?>