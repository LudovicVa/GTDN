<?php
/**
 * User Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * Include Front Model for inheritance
 */
include_once APPS_DIR.'user'.DS.'admin'.DS.'model.php';

/**
 * UserAdminModel is the Admin Model of the User Application.
 * 
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-15-02-2013
 */
class MerchantManagementAdminModel extends UserAdminModel {	
	public $GROUP = 1;
		
	public function __construct() {
		parent::__construct();
		
		$this->db->declareTable('merchants');
		$this->db->declareTable('merchants_addresses');
		$this->db->declareTable('merchants_emails');
	}
	
	public function getMerchants($from, $number, $order = 'nickname', $sens='ASC') {
		$prep = $this->db->prepare('
			SELECT users.id, nickname, email, firstname, lastname, country, lang, groupe, 
				users.access, valid, ip, last_activity, users.created_date, id_merchant, name, contact_email 
			FROM users, merchants
			WHERE id_user = users.id
			AND groupe = ' . $this->GROUP . '
			ORDER BY users.'.$order.' '.$sens.'
			'.($number > 0 ? 'LIMIT :start, :number' : '')
		);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		$merchants = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($merchants as $key=>$merchant) {
			//get addresses
			$prep = $this->db->prepare('
				SELECT id_address,address_name, address, opening_hours, tel
				FROM merchants_addresses
				WHERE id_merchant = :id'
			);
			$prep->bindParam(':id', $id);
			$id = $merchant['id_merchant'];
			$prep->execute();
			$merchants[$key]['addresses'] = $prep->fetchAll(PDO::FETCH_ASSOC);
			
			//contact email
			$prep = $this->db->prepare('
				SELECT id_email, email_name, email
				FROM merchants_emails
				WHERE id_merchant = :id'
			);
			$prep->bindParam(':id', $id);
			$id = $merchant['id_merchant'];
			$prep->execute();
			$merchants[$key]['contact_email'] = $prep->fetchAll(PDO::FETCH_ASSOC);
		}
		
		return $merchants;
	}
	
	public function updateName($id_merchant, $name) {
		$prep = $this->db->prepare('UPDATE merchants SET name=:name WHERE id_merchant = :id');
		$prep->bindParam(':id', $id_merchant);
		$prep->bindParam(':name', $name);
		
		return $prep->execute();
	}
	
	public function updateMail($user_id, $email) {
		$prep = $this->db->prepare('UPDATE users SET email=:email WHERE id = :id');
		$prep->bindParam(':id', $user_id);
		$prep->bindParam(':email', $email);
		try {
			$prep->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}	
	}
	
//----------------------------------------------------------------------------------------------------------------------
//CONTACT OPERATIONS ---------------------------------------------------------------------------------------------------
	public function updateContactEmail($email_id, $email) {
		$prep = $this->db->prepare('UPDATE merchants_emails SET email=:email WHERE id_email = :id_email');
		$prep->bindParam(':id_email', $email_id);
		$prep->bindParam(':email', $email);
		
		return $prep->execute();
	}	
	
	public function updateContactName($email_id, $name) {
		$prep = $this->db->prepare('UPDATE merchants_emails SET email_name=:name WHERE id_email = :id_email');
		$prep->bindParam(':id_email', $email_id);
		$prep->bindParam(':name', $name);
		
		return $prep->execute();
	}
	
	/**
	* Add
	**/
	public function addContact($merchant_id, $name, $email) {
		$prep = $this->db->prepare('INSERT INTO merchants_emails(id_merchant, email_name, email) VALUES (:id, :name, :email)'); 
		$prep->bindParam(':id', $merchant_id);
		$prep->bindParam(':name', $name);
		$prep->bindParam(':email', $email);
		try {
			$prep->execute();
			$id = $this->db->prepare('SELECT LAST_INSERT_ID()')->execute();
			return $id;
		} catch (PDOException $e) {
			return -1;
		}
	}
	
//----------------------------------------------------------------------------------------------------------------------
//ADDRESS OPERATIONS ---------------------------------------------------------------------------------------------------
	public function updateAddressName($address_id, $name) {
		$prep = $this->db->prepare('UPDATE merchants_addresses SET address_name=:name WHERE id_address = :id_address');
		$prep->bindParam(':id_address', $address_id);
		$prep->bindParam(':name', $name);
		
		return $prep->execute();
	}
	
	public function updateAddress($address_id, $address) {
		$prep = $this->db->prepare('UPDATE merchants_addresses SET address=:address WHERE id_address = :id_address');
		$prep->bindParam(':id_address', $address_id);
		$prep->bindParam(':address', $address);
		
		return $prep->execute();
	}
	
	public function updateOpeningHours($address_id, $hours) {
		$prep = $this->db->prepare('UPDATE merchants_addresses SET opening_hours=:hours WHERE id_address = :id_address');
		$prep->bindParam(':id_address', $address_id);
		$prep->bindParam(':hours', $hours);
		return $prep->execute();			
	}
	
	public function updateTel($address_id, $tel) {
		$prep = $this->db->prepare('UPDATE merchants_addresses SET tel=:tel WHERE id_address = :id_address');
		$prep->bindParam(':id_address', $address_id);
		$prep->bindParam(':tel', $tel);
		
		return $prep->execute();
	}
	
	public function addAddress($merchant_id, $name, $address, $hours, $tel) {
		$prep = $this->db->prepare('INSERT INTO merchants_addresses(id_merchant, address_name, address, opening_hours, tel) VALUES (:id, :name, :address, :opening_hours, :tel)'); 
		$prep->bindParam(':id', $merchant_id);
		$prep->bindParam(':name', $name);
		$prep->bindParam(':address', $address);
		$prep->bindParam(':opening_hours', $hours);
		$prep->bindParam(':tel', $tel);
		
		if(!$prep->execute()) {
			return false;
		}
		
		$id = $this->db->prepare('SELECT LAST_INSERT_ID()')->execute();
		return $id;
	}
}

?>
