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
	
//----------------------------------------------------------------------------------------
//MERCHANTS OPERATION
	public function getMerchants($from, $number, $order = 'nickname', $sens='ASC') {
		$prep = $this->db->prepare('
			SELECT id, nickname, email, firstname, lastname, country, lang, groupe, 
				users.access, valid, ip, last_activity, users.created_date, name 
			FROM users, merchants
			WHERE id_user = users.id
			AND groupe = ' . $this->GROUP . '
			ORDER BY '.$order.' '.$sens.'
			'.($number > 0 ? 'LIMIT :start, :number' : '')
		);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		$merchants = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		$prep_address = $this->db->prepare('
				SELECT id_address, address_name, address, lat, lng, opening_hours, tel
				FROM merchants_addresses
				WHERE id_user = :id'
			);
		$prep_address->bindParam(':id', $id);
		$prep_email = $this->db->prepare('
				SELECT id_email, email_name, email
				FROM merchants_emails
				WHERE id_user = :id'
			);
		
		$prep_email->bindParam(':id', $id);
		foreach($merchants as $key=>$merchant) {
			//get addresses			
			$id = $merchant['id'];
			$prep_address->execute();
			$merchants[$key]['addresses'] = $prep_address->fetchAll(PDO::FETCH_ASSOC);
			
			//contact email			
			$prep_email->execute();
			$merchants[$key]['contact_email'] = $prep_email->fetchAll(PDO::FETCH_ASSOC);
		}
		
		return $merchants;
	}
	
	/*
	*	Check if a merchant id exist
	*/
	public function isMerchantId($id_user) {
		if(!is_numeric($id_user)) {
			return false;
		} 		
		$prep = $this->db->prepare('SELECT user_id FROM merchants WHERE user_id = :id');
		$prep->bindParam(':id', $id_user);
		$prep->execute();
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		return count($result) == 1;
	}
	
	public function updateName($id_user, $name) {
		$prep = $this->db->prepare('UPDATE merchants SET name=:name WHERE user_id = :id');
		$prep->bindParam(':id', $id_user);
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
	
	public function deleteMerchant($merchant_id) {
		$user_id = $merchant_id;
	
		$prep = $this->db->prepare('DELETE FROM merchants WHERE id_user = :id');
		$prep->bindParam(':id', $merchant_id);
		$prep_email = $this->db->prepare('DELETE FROM merchants_emails WHERE id_user = :id');
		$prep_email->bindParam(':id', $merchant_id);
		$prep_addresses = $this->db->prepare('DELETE FROM merchants_addresses WHERE id_user = :id');
		$prep_addresses->bindParam(':id', $merchant_id);
		
		return $prep_addresses->execute() && $prep_email->execute() && $prep->execute() && $this->deleteUser($user_id);
	}
	
	public function createMerchant($data) {	
		$data['groupe'] = $this->GROUP;
		$user_id = $this->createUser($data);
		
		$prep = $this->db->prepare('
			INSERT INTO merchants(id_user, name)
			VALUES (:id_user, :name);
		');
		$prep->bindParam(':id_user', $user_id);
		$name = $data['name'];
		$prep->bindParam(':name', $name);
		if($prep->execute()) {	
			return $this->db->lastInsertId(); 
		}
		return false;
	}
	
//----------------------------------------------------------------------------------------------------------------------
//CONTACT OPERATIONS ---------------------------------------------------------------------------------------------------
	public function getContact($contact_id) {
		$prep = $this->db->prepare('SELECT * FROM merchants_emails WHERE id_email = :id'); 
		$prep->bindParam(':id', $contact_id);
		
		if(!$prep->execute()) {
			return false;
		}
		
		$contact = $prep->fetchAll(PDO::FETCH_ASSOC);
		if(count($contact) == 1) {
			return $contact[0];
		}
		
		return false;
	}
	
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
		$prep = $this->db->prepare('INSERT INTO merchants_emails(id_user, email_name, email) VALUES (:id, :name, :email)'); 
		$prep->bindParam(':id', $merchant_id);
		$prep->bindParam(':name', $name);
		$prep->bindParam(':email', $email);
		try {
			$prep->execute();
			$id = $this->db->lastInsertId();
			return $id;
		} catch (PDOException $e) {
			return -1;
		}
	}
	
	public function deleteContact($email_id) {
		$prep = $this->db->prepare('DELETE FROM merchants_emails WHERE id_email = :id'); 
		$prep->bindParam(':id', $email_id);
		
		return $prep->execute();
	}
	
//----------------------------------------------------------------------------------------------------------------------
//ADDRESS OPERATIONS ---------------------------------------------------------------------------------------------------
	public function getAddress($address_id) {
		$prep = $this->db->prepare('SELECT * FROM merchants_addresses WHERE id_address = :id'); 
		$prep->bindParam(':id', $address_id);
		
		if(!$prep->execute()) {
			return false;
		}
		
		$address = $prep->fetchAll(PDO::FETCH_ASSOC);
		if(count($address) == 1) {
			return $address[0];
		}
		
		return false;
	}
	
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
	
	public function updateLatLng($address_id, $latlng) {
		$latlng = explode(',', $latlng);
		$prep = $this->db->prepare('UPDATE merchants_addresses SET lat=:lat, lng =:lng WHERE id_address = :id');
		$prep->bindParam(':id', $address_id);
		$prep->bindParam(':lat', $latlng[0]);
		$prep->bindParam(':lng', $latlng[1]);
		try {
			$prep->execute();
			return true;
		} catch (PDOException $e) {
			return false;
		}	
	}
	
	public function addAddress($merchant_id, $name, $address, $hours, $tel) {
		$prep = $this->db->prepare('INSERT INTO merchants_addresses(id_user, address_name, address, opening_hours, tel) VALUES (:id, :name, :address, :opening_hours, :tel)'); 
		$prep->bindParam(':id', $merchant_id);
		$prep->bindParam(':name', $name);
		$prep->bindParam(':address', $address);
		$prep->bindParam(':opening_hours', $hours);
		$prep->bindParam(':tel', $tel);
		
		if(!$prep->execute()) {
			return false;
		}
		
		$id = $this->db->lastInsertId();
		return $id;
	}
	
	public function deleteAddress($address_id) {
		$prep = $this->db->prepare('DELETE FROM merchants_addresses WHERE id_address = :id'); 
		$prep->bindParam(':id', $address_id);
		
		return $prep->execute();
	}
}

?>
