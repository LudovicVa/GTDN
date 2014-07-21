<?php
/**
 * User Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserAdminModel is the Admin Model of the User Application.
 * 
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-15-02-2013
 */
class TransactionsModel {
	protected $db;
		
	public function __construct() {			
		$this->db = WSystem::getDB();
		
		$this->db->declareTable('merchants');
		$this->db->declareTable('merchants_emails');		
		$this->db->declareTable('merchants_addresses');	
		$this->db->declareTable('users');		
		$this->db->declareTable('deals');	
		$this->db->declareTable('deals_emails');		
		$this->db->declareTable('transactions');
		$this->db->declareTable('deal2address', false);
	}
	
	/**
	* Get transaction by token
	**/
	function getTransactionInfoByToken($token) {		
		$prep = $this->db->prepare('
			SELECT * FROM transactions
			WHERE token = :token');
		
		$prep->bindParam(':token', $token);
		$prep->execute();
		
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	* Get transaction token by id
	**/
	function getTransactionInfoById($id_transaction) {		
		$prep = $this->db->prepare('
			SELECT * FROM transactions
			WHERE id_transaction = :id_transaction');
		
		$prep->bindParam(':id_transaction', $id_transaction);
		$prep->execute();
		
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	* Save paypal init
	**/
	function savePaypalInit($id_deal, $token) {	
		//Compute new voucher
		$prep = $this->db->prepare("SELECT COUNT(*) FROM transactions WHERE receipt_id = :receipt_id");	
		$prep->bindParam(':receipt_id', $receipt_id);
		do {
			$receipt_id = $this->generateRef();
			$prep->execute();
			$count = intval($prep->fetchColumn());
		} while($count != 0);

		$statement = $this->db->prepare("INSERT INTO transactions(id_deal, token, receipt_id) VALUES (:id_deal, :token, :receipt_id)");
		$statement->bindParam(':id_deal', $id_deal);
		$statement->bindParam(':token', $token);
		$statement->bindParam(':receipt_id', $receipt_id);
		
		//Execute transaction DB insert
		if($statement->execute()) {
			return array('id' => $this->db->lastInsertId(), 'receipt_id' => $receipt_id, 'id_deal' => $id_deal);
		} else {
			return false;
		}
	}
	
	/**
	* Save paypal confirmation
	**/
	function savePaypalConfirm($id_transaction, $response) {
		$statement = $this->db->prepare("UPDATE transactions SET
			paypal_id = :paypal_id,
			paypal_status = :paypal_status,
			payal_order_time = :payal_order_time,
			internal_status = 'CONFIRMED'	
		WHERE id_transaction = :id_transaction");
		$statement->bindParam(':paypal_id', $paypal_id);
		$statement->bindParam(':paypal_status', $paypal_status);
		$statement->bindParam(':payal_order_time', $payal_order_time);	
		$statement->bindParam(':id_transaction', $id_transaction);
		
		$paypal_id 			= $response['PAYMENTINFO_0_TRANSACTIONID'];
		$paypal_status 		= $response['PAYMENTINFO_0_PAYMENTSTATUS'];
		$payal_order_time	= $response['PAYMENTINFO_0_ORDERTIME'];
		
		//Execute transaction DB insert
		return $statement->execute();
	}
	
	/**
	* Save paypal confirmation information
	**/
	function savePaypalDetails($id_transaction, $response) {
		$statement = $this->db->prepare("UPDATE transactions
			SET firstname = :firstname, 
					lastname = :lastname, 
					email = :email,
					paypal_status = :paypal_status,
					internal_status = 'DETAILED'
			WHERE id_transaction = :id_transaction");
		$statement->bindParam(':firstname', $firstname);
		$statement->bindParam(':lastname', $lastname);
		$statement->bindParam(':email', $email);		
		$statement->bindParam(':paypal_status', $paypal_status);
		$statement->bindParam(':id_transaction', $id_transaction);
		
		$paypal_status 		= $response['PAYMENTSTATUS'];
		$email 					= $response['EMAIL'];
		$firstname				= $response['FIRSTNAME'];
		$lastname				= $response['LASTNAME'];
		
		//Execute transaction DB insert
		if($statement->execute()) {
			return $this->db->lastInsertId();
		} else {
			return false;
		}
	}
	
	/**
	* Insert transaction into DB
	**/
	function insertTransaction($firstname, $lastname, $email, $id_deal, $paypal) {
		do {
			$receipt = $this->generateRef();
			$prep = $this->db->prepare("SELECT COUNT(*) FROM transactions WHERE receipt_id = :receipt_id");
			$prep->bindParam(':receipt_id', $receipt);
			$prep->execute();
			$count = intval($prep->fetchColumn());
		} while($count != 0);
		
		$statement = $this->db->prepare("INSERT INTO transactions(firstname, lastname, email, id_deal, receipt_id, paypal_id) 
		VALUES (:firstname, :lastname, :cmail, :id_deal, :receipt_id, :paypal_id)");
		$statement->bindParam(':firstname', $firstname);
		$statement->bindParam(':lastname', $lastname);
		$statement->bindParam(':cmail', $email);
		$statement->bindParam(':id_deal', $id_deal);
		$statement->bindParam(':paypal_id', $paypal);
		$statement->bindParam(':receipt_id', $receipt);
		
		//Execute transaction DB insert
		if($statement->execute()) {
			return $receipt;
		} else {
			return false;
		}
	}
	
	/**
	*	Get a specfic deal
	**/
	function getDealInfo($id_deal) {
		$prep = $this->db->prepare('
			SELECT deals.id_user, id_deal, name AS merchant_name, deal_name, DATE_FORMAT(start_time,\'%d/%m/%y %h:%i%p\') AS start_time, DATE_FORMAT(end_time,\'%d/%m/%y %h:%i%p\') AS end_time, price, original_price, description, images
			FROM deals
			LEFT JOIN merchants
			ON deals.id_user = merchants.id_user
			WHERE id_deal = :id_deal');
		
		$prep->bindParam(':id_deal', $id_deal, PDO::PARAM_INT);
		$prep->execute();
		
		$deal = $prep->fetchAll(PDO::FETCH_ASSOC);
		if(count($deal) == 0) {
			return 'deal_not_existing';
		} else {
			$deal = $deal[0];
		}
		
		$id = $deal['id_user'];
		
		//Get addresses
		$deal['addresses'] = $this->getAddresses($id_deal, $id);
		
		//Contact email
		if($deal['addresses'] !== 'false') {
			$prep = $this->db->prepare('
				SELECT id_email, email_name, email
				FROM merchants_emails
				WHERE id_user = :id'
			);
			$prep->bindParam(':id', $id);
			$success = $prep->execute();
			$deal['contact_email'] = $prep->fetchAll(PDO::FETCH_ASSOC);
		}
		
		//Email body and subject
		if($success) {
			$prep = $this->db->prepare('
				SELECT id_deal_email, subject, body AS email_body
				FROM deals_emails
				WHERE id_deal = :id'
			);
			$prep->bindParam(':id', $id_deal);
			$success = $prep->execute();
			$result = $prep->fetchAll(PDO::FETCH_ASSOC);
			
			if(count($result) == 0) {
				//no email exisiting, retrieving default email
				$deal['email2customer'] = $this->retrieveDefaultEmail();
			} elseif($success) {
				$deal['email2customer'] = $result[0];
			}			
			$deal['email2customer']['email_body'] = htmlspecialchars_decode ($deal['email2customer']['email_body']);
			$deal['email2customer']['email_body'] = str_replace("\'", "", $deal['email2customer']['email_body']); 
			$deal['email2customer']['email_body'] = str_replace("\\", "", $deal['email2customer']['email_body']); 
		}
		
		
		if(!$success) {
			return 'unknown_error ' .  print_r($prep->errorInfo(),true);
		} else {
			return $deal;
		}
	}
	
	/**
	* Get addresses of a specific deal
	**/
	public function getAddresses($id_deal, $id_user) {
		//try to find address by assoc
		$prep = $this->db->prepare('
			SELECT merchants_addresses.id_address,address_name, address, opening_hours, tel
			FROM merchants_addresses, deal2address
			WHERE merchants_addresses.id_user = :id_user 
			AND deal2address.id_address = merchants_addresses.id_address
			AND deal2address.id_deal = :id_deal'
		);
		$prep->bindParam(':id_deal', $id_deal);
		$prep->bindParam(':id_user', $id_user);
		$success = $prep->execute();
		if($success) {
			return $prep->fetchAll(PDO::FETCH_ASSOC);
		}
		return false;
	}
	
	/**
	* Return default email body email
	**/
	public function retrieveDefaultEmail() {
		return array('email_body' => WConfig::get('apps.transactions.mail2client'));
	}
	
	/**
	* Generate a Voucher
	**/
	public function generateRef() {
		$rand1 = rand(1000,9999);
		$rand2 = rand(1000,9999);
		return $rand1 . "-" . $rand2;
	}
	
	/**
	*	Get all deals
	**/
	public function getDealsList() {
		
		$prep = $this->db->prepare('
			SELECT id_deal, name AS merchant_name, deal_name
			FROM deals
			LEFT JOIN merchants
			ON deals.id_user = merchants.id_user
			');
		$prep->execute();
		
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	* Send transaction emails
	**/
	public function sendEmails(array $mail_info) {
		$website_mail 	= array('no-reply@getthedealnow.com', 'Get The Deal Now');
		$bcc_tmp 			= explode(',' , WConfig::getAppVar('transactions', 'bcc_email', 'ludovic.vanhove@getthedealnow.com'));
		$debug				= WConfig::getAppVar('transactions', 'use_sandbox ', 'true') == 'true';
		
		foreach($bcc_tmp as $key => $mail) {
			$bcc[$key] = array($mail);
		}
			
		$to = array();
		if(!$debug) {
			foreach($mail_info['contact_email'] as $email) {
				array_push($to, array($email['email']));
			}
		} else {
			$to = $bcc;
		}
		
		$mail = array(
			'origin' => array(
				'app' => 'transactions',
				'action' => 'send',
				'parameters' => array()
			),
			'defaults' => array(
				'from' => $website_mail,
				'params' => $mail_info
			),
			'specifics' => array(
				//To merchant
				array(
					'to' => $to,
					'subject' => 'New order from GetTheDealNow.com',
					'bcc' => $bcc,
					'body' => 'apps/transactions/front/templates/mail2merchant.html'
				),
				//To customer
				array(
					'to' => $mail_info['payer_email'],
					'subject' => 'GetTheDealNow - Your deal is ready!',
					'bcc' => $bcc,
					'body' => $mail_info['email2customer']['email_body']
				),
				//To admin
				array(
					'to' => 'me@gtdn.com',
					'bcc' => $bcc,
					'subject' => 'GetTheDealNow - A deal has been made',
					'body' => 'apps/transactions/front/templates/mail2admin.html'
				)
			)
		);
			
		$mail_app = WRetriever::getModel('mail', $mail);
			
		if (empty($mail_app['result']) || empty($mail_app['result']['success']) || $mail_app['result']['success'] != true) {
			return WNote::error('email_not_sent', WLang::get('email_not_sent'), 'email');
		}
	}

}

?>
