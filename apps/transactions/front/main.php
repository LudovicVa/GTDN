<?php

defined('WITYCMS_VERSION') or die('Access denied');

include_once 'helpers'.DS.'WForm'.DS.'WForm.php';
include_once 'apps'.DS.'transactions'.DS.'front'.DS.'paypal.class.php';

class Paypal {
    	
	public static function PPHttpPost($methodName_, $nvpStr_, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode) {
			// Set up your API credentials, PayPal end point, and API version.
			$API_UserName = urlencode($PayPalApiUsername);
			$API_Password = urlencode($PayPalApiPassword);
			$API_Signature = urlencode($PayPalApiSignature);
			
			if($PayPalMode == 'true')
			{
				$paypalmode 	=	'.sandbox';
			}
			else
			{
				$paypalmode 	=	'';
			}
	
			$API_Endpoint = "https://api-3t".$paypalmode.".paypal.com/nvp";
			$version = urlencode('76.0');
		
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
			$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
		
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
		
		return $httpParsedResponseAr;
	}
		
}

class TransactionsController extends WController {
	/**
	* IPN Listener stuff
	**/	
	protected function ipn(array $params) {

		// instantiate the IpnListener class
		$ipn = WHelper::load("ipn");
		
		//---------------------------------
		$ipn->use_sandbox = WConfig::getAppVar('transactions', 'use_sandbox ', 'true') == 'true';
		//---------------------------------

		try {
			$ipn->requirePostMethod();
			$verified = $ipn->processIpn();
		} catch (Exception $e) {
			WNote::error('error_getting_data', '<pre>' . $e . ' \n '. $ipn->getTextReport() .'</pre>' , 'email');
			return;
		}
		
		if ($verified) {			
			if($_POST['payment_status'] != "Completed") {
				//ignored as it not completed
				return;
			}
			
			//To Be Changed !!!
			if($_POST['receiver_email'] != WConfig::getAppVar('transactions', 'receiver_email', 'seller@paypalsandbox.com')) {
				/*ignored as we are not the receiveer*/
				WNote::error('payment_from_someone_else', '<pre>'. $_POST['receiver_email'] . '
				' . serialize($_POST) . '
				' . $ipn->getTextReport() .  '<pre>', 'email');
				return;
			}
			
			//Check that the id product exists and get product info
			$id_deal = $_POST['item_number'];
			$deal_info = $this->model->getDealInfo($id_deal);
			
			if(!is_array($deal_info)) {
				WNote::error('error_retrieving_deal_data', '<p><pre>'. $deal_info . '</pre></p><p><pre>'. print_r($_POST,true) . '</pre></p><p><pre>' . $ipn->getTextReport() . '</pre></p>', 'email');
				return;
			}
			
			//Check price
			if(floatval($_POST['mc_gross']) != floatval($deal_info['price'])) {
				WNote::error('payment_amount_wrong', 'Received ' . $_POST['mc_gross']. ', expected ' . $deal_info['price'] . '<p><pre>' .  print_r($_POST,true) . '</pre></p><p><pre>' . $ipn->getTextReport(). '</pre></p>', 'email');
				return;
			}
			
			//Insert transaction into transaction DB
			$voucher = $this->insertTransaction($_POST);
			
			if(!is_string($voucher)) {
				WNote::error('error_while_inserting', print_r($_POST,true) . ' ' . $ipn->getTextReport(), 'email');
				return;
			}
			
			$deal_info['firstname'] 	= $_POST['first_name'];
			$deal_info['lastname'] 		= $_POST['last_name'];
			$deal_info['voucher'] 		= $voucher;
			$deal_info['payer_email'] 	= array($_POST['first_name'] . ' ' . $_POST['last_name'], $_POST['payer_email']);
				
			//send the email
			$this->model->sendEmails($deal_info);
			
		} else {
			/*!
			An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
			a good idea to have a developer or sys admin manually investigate any 
			invalid IPN.
			*/
			WNote::error('ipn_invalid', $ipn->getTextReport(), 'email');
			return;
		}
	}
	
	/**
	* Process using classic Api
	**/	
	protected function process(array $params) {

		//---------------------------------
		$paypalmode = WConfig::getAppVar('transactions', 'use_sandbox ', 'true') == 'true';
		//---------------------------------
		$data = WRequest::getAssoc(array('id', 'token', 'PayerID'));
		
		 $PayPalApiUsername = WConfig::get("apps.transactions.paypal_username"); 
		 $PayPalApiPassword = WConfig::get("apps.transactions.paypal_password");
		 $PayPalApiSignature =  WConfig::get("apps.transactions.paypal_signature");
		$PayPalMode = WConfig::getAppVar("transactions", "use_sandbox", "true");
		
		$PayPalCurrencyCode = urlencode(WConfig::get("apps.transactions.paypal_currency_code"));
		$PayPalReturnURL = urlencode(WConfig::get("apps.transactions.paypal_return"));
		$PayPalCancelURL = urlencode(WConfig::get("apps.transactions.paypal_cancel"));
		
		
		if((is_null($data['token']) || is_null($data['PayerID'])) && !is_null($data['id'])) {
			$id_deal = $data['id'];
			$deal_info = $this->model->getDealInfo($id_deal);
		
			//Request for paying
						
			$padata = '&CURRENCYCODE='.urlencode($PayPalCurrencyCode).
				'&PAYMENTACTION=Sale'.
				'&ALLOWNOTE=1'.
				'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode).
				'&PAYMENTREQUEST_0_AMT='.urlencode($deal_info['price']).
				'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($deal_info['price']). 
				'&L_PAYMENTREQUEST_0_QTY0='. urlencode(1).
				'&L_PAYMENTREQUEST_0_AMT0='.urlencode($deal_info['price']).
				'&L_PAYMENTREQUEST_0_NAME0='.urlencode($deal_info['deal_name']).
				'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($id_deal).
				'&AMT='.urlencode($deal_info['price']).				
				'&RETURNURL='.$PayPalReturnURL.
				'&CANCELURL='.$PayPalCancelURL;
			
			//echo "<pre>";
			//print_r($padata);
			//We need to execute the "SetExpressCheckOut" method to obtain paypal token
			$httpParsedResponseAr = Paypal::PPHttpPost('SetExpressCheckout', $padata,  $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
			//print_r($httpParsedResponseAr);
			//echo "</pre>";
			//Respond according to message we receive from Paypal
			if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
			{
					// If successful set some session variable we need later when user is redirected back to page from paypal. 
					$_SESSION['itemprice'] =  $deal_info['price'];
					$_SESSION['totalamount'] = $deal_info['price'];
					$_SESSION['itemName'] =  $deal_info['deal_name'];
					$_SESSION['itemNo'] =  $id_deal;
					$_SESSION['itemQTY'] = 1;
					
					if($paypalmode)
					{
						$paypalmode 	=	'.sandbox';
					}
					else
					{
						$paypalmode 	=	'';
					}
					//Redirect user to PayPal store with Token received.
					$paypalurl ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpParsedResponseAr["TOKEN"].'';
					header('Location: '.$paypalurl);
					exit(0);
			}else{
				//Show error message
				WNote::error("sorry", "trouble_while_checkout");
			}
		} else if(!is_null($data['token']) && !is_null($data['PayerID'])) {
			//we will be using these two variables to execute the "DoExpressCheckoutPayment"
			//Note: we haven't received any payment yet.
			
			$token = $data["token"];
			$playerid = $data["PayerID"];
			
			//get session variables
			$ItemPrice 		= $_SESSION['itemprice'];
			$ItemTotalPrice = $_SESSION['totalamount'];
			$ItemName 		= $_SESSION['itemName'];
			$ItemNumber 	= $_SESSION['itemNo'];
			$ItemQTY 		=	$_SESSION['itemQTY'];
			
			$padata = 	'&TOKEN='.urlencode($token).
								'&PAYERID='.urlencode($playerid).
								'&PAYMENTACTION='.urlencode("SALE").
								'&AMT='.urlencode($ItemTotalPrice).
								'&CURRENCYCODE='.urlencode($PayPalCurrencyCode);
			
			//We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
			$httpParsedResponseAr = Paypal::PPHttpPost('DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
			
			//Check if everything went ok..
			if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
			{				
				
				/*
				//Sometimes Payment are kept pending even when transaction is complete. 
				//May be because of Currency change, or user choose to review each payment etc.
				//hence we need to notify user about it and ask him manually approve the transiction
				*/
				
				if('Completed' == $httpParsedResponseAr["PAYMENTSTATUS"])
				{
					unset($_SESSION['itemprice']);
					unset($_SESSION['totalamount']);
					unset($_SESSION['itemName']);
					unset($_SESSION['itemNo']);
					unset($_SESSION['itemQTY']);	
					WNote::success("transaction _successfully_saved", "transaction _successfully_saved");
				}
				elseif('Pending' == $httpParsedResponseAr["PAYMENTSTATUS"])
				{
					WNote::error("sorry", "trouble_while_checkout");
				}
				echo "<pre>";
				print_r($httpParsedResponseAr);
				
				$transactionID = urlencode($httpParsedResponseAr["TRANSACTIONID"]);
				$nvpStr = "&TRANSACTIONID=".$transactionID;
				$httpParsedResponseAr = Paypal::PPHttpPost('GetTransactionDetails', $nvpStr, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

				print_r($httpParsedResponseAr);
				echo "</pre>";
				
				if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
					
					/* 
					#### SAVE BUYER INFORMATION IN DATABASE ###
					$buyerName = $httpParsedResponseAr["FIRSTNAME"].' '.$httpParsedResponseAr["LASTNAME"];
					$buyerEmail = $httpParsedResponseAr["EMAIL"];
					
					$conn = mysql_connect("localhost","MySQLUsername","MySQLPassword");
					if (!$conn)
					{
					 die('Could not connect: ' . mysql_error());
					}
					
					mysql_select_db("Database_Name", $conn);
					
					mysql_query("INSERT INTO BuyerTable 
					(BuyerName,BuyerEmail,TransactionID,ItemName,ItemNumber, ItemAmount,ItemQTY)
					VALUES 
					('$buyerName','$buyerEmail','$transactionID','$ItemName',$ItemNumber, $ItemTotalPrice,$ItemQTY)");
					
					mysql_close($con);
					*/
				} else  {
					echo '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
					echo '<pre>';
					print_r($httpParsedResponseAr);
					echo '</pre>';

				}
	
			}else{
					echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
					echo '<pre>';
					print_r($httpParsedResponseAr);
					echo '</pre>';
			}
		}
	}
	
	/**
	* Insert Transaction
	**/
	private function insertTransaction(array $params) {
		return $this->model->insertTransaction($params['first_name'], $params['last_name'],  $params['payer_email'],  $params['item_number'], $params['txn_id']);
	}
}

?>
