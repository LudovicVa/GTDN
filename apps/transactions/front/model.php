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
	}
	
	/**
	* Insert transaction
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
			SELECT deals.id_merchant, id_deal, name AS merchant_name, deal_name, UNIX_TIMESTAMP(start_time) AS start_time, UNIX_TIMESTAMP(end_time) AS end_time, price, original_price, description, images
			FROM deals
			LEFT JOIN merchants
			ON deals.id_merchant = merchants.id_merchant
			WHERE id_deal = :id_deal');
		
		$prep->bindParam(':id_deal', $id_deal, PDO::PARAM_INT);
		$prep->execute();
		
		$deal = $prep->fetchAll(PDO::FETCH_ASSOC);
		if(count($deal) == 0) {
			return 'deal_not_existing';
		} else {
			$deal = $deal[0];
		}
		
		$id = $deal['id_merchant'];
		
		//Get addresses
		$prep = $this->db->prepare('
			SELECT id_address,address_name, address, opening_hours, tel
			FROM merchants_addresses
			WHERE id_merchant = :id'
		);
		$prep->bindParam(':id', $id);
		$success = $prep->execute();
		$deal['addresses'] = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		//Contact email
		if($success) {
			$prep = $this->db->prepare('
				SELECT id_email, email_name, email
				FROM merchants_emails
				WHERE id_merchant = :id'
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
	
	public function retrieveDefaultEmail() {
		$prep = $this->db->prepare('
			SELECT id_deal_email, subject, body AS email_body
			FROM deals_emails
			WHERE id_deal = :id'
		);
		$prep->bindParam(':id', $default);
		$default = "default_email";
		$success = $prep->execute();
		return $prep->fetch();
	}
	
	
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
			ON deals.id_merchant = merchants.id_merchant
			');
		$prep->execute();
		
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	* Send transaction
	**/
	public function sendEmails(array $mail_info) {
		$website_mail = array('no-reply@getthedealnow.com', 'Get The Deal Now');
		$bcc = array(array('ludovic-vanhove@orange.fr')/*, array('contact@getthedealnow.com')*/);
			
		$to = array();
		foreach($mail_info['contact_email'] as $email) {
			array_push($to, array($email['email']));
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
					'to' => $bcc,
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
