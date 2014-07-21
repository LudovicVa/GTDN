<?php

defined('WITYCMS_VERSION') or die('Access denied');

include_once 'helpers'.DS.'WForm'.DS.'WForm.php';
include_once 'apps'.DS.'transactions'.DS.'front'.DS.'paypal.class.php';

class TransactionsController extends WController {
	const NO_TRANSACTION 														= 'NO_TRANSACTION';
	const TRANSACTION_SAVED 												= 'TRANSACTION_SAVED';
	const INIT_PAYMENT 																= 'INIT_PAYMENT';
	const ERROR_SAVING_TRANSACTION 								= 'ERROR_SAVING_TRANSACTION';
	const ERROR_INIT_PAYMENT 												= 'ERROR_INIT_PAYMENT';
	const ERROR_CONFIRMING_PAYMENT 								= 'ERROR_CONFIRMING_PAYMENT';
	const PAYMENT_PENDING 														= 'PAYMENT_PENDING';
	const PAYMENT_FAILURE 														= 'PAYMENT_FAILURE';
	const ERROR_RETRIEVING_TRANSACTION_DETAILS 	= 'ERROR_RETRIEVING_TRANSACTION_DETAILS';
	const EMAIL_SEND 																	= 'EMAIL_SEND';

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
			
			$deal_info['firstname'] 		= $_POST['first_name'];
			$deal_info['lastname'] 		= $_POST['last_name'];
			$deal_info['voucher'] 			= $voucher;
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
		$paypalmode = WConfig::getAppVar('transactions', 'use_sandbox', 'true') == 'true';
		//---------------------------------
		$data = WRequest::getAssoc(array('id', 'token', 'PayerID'));
		$status = self::NO_TRANSACTION;
		$paypal = new Paypal();
		
		if((is_null($data['token']) || is_null($data['PayerID'])) && !is_null($data['id'])) {
			//Init transaction		
			$id_deal = $data['id'];
			$deal_info = $this->model->getDealInfo($id_deal);
			
			$response = $paypal->initTransaction($id_deal, $deal_info);
			
			if("SUCCESS" == strtoupper($response["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($response["ACK"]))
			{
				// If successful set some session variable we need later when user is redirected back to page from paypal. 
				$paypal->storeTransactionAndRedirect($id_deal, $deal_info);
				$status = self::INIT_PAYMENT;
			}else{
				//Show error message
				$status = self::ERROR_INIT_PAYMENT;
				WNote::error("error_on_init_payment", print_r($response, true), "email");
			}
		} else if(!is_null($data['token']) && !is_null($data['PayerID'])) {
			//we will be using these two variables to execute the "DoExpressCheckoutPayment"
			//Note: we haven't received any payment yet.
			
			$token = $data["token"];
			$payerID = $data["PayerID"];
			
			$save = $this->model->getTransactionInfoByToken($token);
			if(count($save) != 0) {
				//Token already saved in DB and thus maybe already treated
				switch($save[0]['internal_status']) {
					case 'CONFIRMED':				
						return array('status' => self::TRANSACTION_SAVED);
					case 'DETAILED':
						return array('status' => self::EMAIL_SEND);
					default:
						return array('status' => self::ERROR_CONFIRMING_PAYMENT);
				}	
			}
			$trans_data = $this->model->savePaypalInit($_SESSION['id_deal'], $token);
			
			if($trans_data) {
				$response = $paypal->confirmPayment($token, $payerID);
				
				if("SUCCESS" == strtoupper($response["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($response["ACK"])) 	{
						$req = $this->model->savePaypalConfirm($trans_data['id'], $response);
						if('Completed' == $response["PAYMENTINFO_0_PAYMENTSTATUS"])	{
							$status = self::TRANSACTION_SAVED;
							
							//Retrieving Transaction detail
							$response = $paypal->getTransactionDetail($response["PAYMENTINFO_0_TRANSACTIONID"]);
							if("SUCCESS" == strtoupper($response["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($response["ACK"])) {
								$data = $this->model->savePaypalDetails($trans_data['id'], $response);			

								//Send email
								$mail_info 							= $this->model->getDealInfo($trans_data['id_deal']); 
								$mail_info['firstname'] 		= $response['FIRSTNAME'];
								$mail_info['lastname'] 		= $response['LASTNAME'];
								$mail_info['voucher'] 			= $trans_data['receipt_id'];
								$mail_info['payer_email'] 	= array($response['EMAIL'], $response['FIRSTNAME'] . ' ' . $response['LASTNAME']);						
								$this->model->sendEmails($mail_info);
								$status = self::EMAIL_SEND;
							} else {
								$status = self::ERROR_RETRIEVING_TRANSACTION_DETAILS;
								WNote::error("error_retrieving_transaction_detail", '<pre>' . print_r($response, true) . '</pre>', "email");
							}
						} elseif('Pending' == $response["PAYMENTINFO_0_PAYMENTSTATUS"])	{
							$status = self::PAYMENT_PENDING;
							WNote::error("payment_pending", '<pre>' . print_r($response, true) . '</pre>', "email");
						} else {
							$status = self::PAYMENT_FAILURE;
							WNote::error("error_on_payment_status", '<pre>' . print_r($response, true) . '</pre>', "email");
						}			
					
				} else {
					$status = self::ERROR_CONFIRMING_PAYMENT;
					WNote::error("error_while_confirming", '<pre>' . print_r($response, true) . '</pre>', "email");
				}
			} else {
				$status = self::ERROR_SAVING_TRANSACTION;
				WNote::error("error_saving_transaction", '<pre>' . $token . ' ' . $payerID .'</pre>', "email");
			}
		}
		return array('status' => $status, 'data' => $data);
	}
		
	/**
	* Insert Transaction
	**/
	private function insertTransaction(array $params) {
		return $this->model->insertTransaction($params['first_name'], $params['last_name'],  $params['payer_email'],  $params['item_number'], $params['txn_id']);
	}
}

?>