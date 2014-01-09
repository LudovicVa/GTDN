<?php

defined('WITYCMS_VERSION') or die('Access denied');

class TransactionsController extends WController {
	/**
	* IPN Listener stuff
	**/	
	protected function ipn(array $params) {

		// instantiate the IpnListener class
		$ipn = WHelper::load("ipn");
		
		//---------------------------------
		//$ipn->use_sandbox = true;
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
			if($_POST['receiver_email'] != "contact@BIGGER-stronger.com" && $_POST['receiver_email'] != "contact@bigger-stronger.com" && $_POST['receiver_email'] != "seller@paypalsandbox.com") {
				//ignored as we are not the receiver
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
	* Insert Transaction
	**/
	private function insertTransaction(array $params) {
		return $this->model->insertTransaction($params['first_name'], $params['last_name'],  $params['payer_email'],  $params['item_number'], $params['txn_id']);
	}
}

?>
