<?php
/**
 * User Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * Include Front Model for inheritance
 */
include_once APPS_DIR.'user'.DS.'front'.DS.'model.php';

/**
 * UserAdminModel is the Admin Model of the User Application.
 * 
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-15-02-2013
 */
class MerchantManagementModel extends UserModel {
	public function __construct() {
		parent::__construct();
		
		$this->db->declareTable('merchants_addresses');
		$this->db->declareTable('merchants');
		$this->db->declareTable('merchants_emails');
	}
	
	/**
	*	Get shops from merchant
	**/
	public function getShops($id_user) {
		$prep_address = $this->db->prepare('
				SELECT id_address, address_name, address, lat, lng, opening_hours, tel
				FROM merchants_addresses
				WHERE id_user = :id'
			);
		$prep_address->bindParam(':id', $id);
		$id = $id_user;
		$prep_address->execute();
		
		return $prep_address->fetchAll(PDO::FETCH_UNIQUE);
	}
	
	/**
	* Is a valid adress id
	**/
	public function isValidId($id_address, $user_id) {
		$prep_address = $this->db->prepare('
				SELECT COUNT(*)
				FROM merchants_addresses
				WHERE id_address = :id_address AND id_user = :id_user'
			);
		$prep_address->bindParam(':id_address', $id_address);
		$prep_address->bindParam(':id_user', $user_id);
		$prep_address->execute();
		
		return intval($prep_address->fetchColumn()) == 1;
	}
	
	/**
	*	Update merchant address
	**/ 
	public function updateMerchant($id_merchant, $data) {
		$alloweds['merchants'] = array('name');
		$alloweds['users'] = array('nickname', 'email', 'firstname', 'lastname');		
		$alloweds['merchants_emails'] = array('contact_email');
		$id_cols = array('users' => 'id', 'merchants' => 'id_user', 'merchants_emails' => 'id_user');
		
		$cols = array();
		foreach($data as $name => $value) {
			foreach($alloweds as $db => $allowed) {
				if(in_array($name, $allowed)) {
					if($db != 'merchants_emails') {
						if(!isset($cols[$db])) { $cols[$db] = array(); }
						array_push($cols[$db],  $name . " = ". $this->db->quote($value));
					} else {
						$cols[$db] = $value;
					}
				}
			}
		}
		if(count($cols) != 0) {
			foreach($cols as $db => $cols) {
				if($db == 'merchants_emails') {
					$sql = "DELETE FROM merchants_emails WHERE $id_cols[$db] = :id;
					INSERT INTO merchants_emails(id_user, email, email_name) VALUES ";
					$values = array();
					foreach($cols as $email) {
						$values[] = '(' . $this->db->quote($id_merchant, PDO::PARAM_INT) . ',' . $this->db->quote($email) .  ',' . $this->db->quote($email) . ')';
					}
					echo $sql . implode(',', $values);
					$prep = $this->db->prepare($sql . implode(',', $values));
					$prep->bindParam(':id', $id_merchant);
					echo $prep->execute();
				} else {
					$sql = "UPDATE $db SET " .implode(', ', $cols) ." WHERE $id_cols[$db] = :id";
					$prep = $this->db->prepare($sql);
					$prep->bindParam(':id', $id_merchant);
					$prep->execute();
				}	
			}				
		}
	}	
	
	/**
	*	Update existing address
	**/ 
	public function updateAddress($id_address, $data) {
		$allowed = array('address_name', 'address', 'lat', 'lng', 'opening_hours', 'tel');
		
		$cols = array();
		foreach($data as $name => $value) {
			if(in_array($name, $allowed)) {
				array_push($cols,  $name . " = ". $this->db->quote($value));
			}
		}
		if(count($cols) != 0) {
			$sql = "UPDATE merchants_addresses SET " .implode(', ', $cols) ." WHERE id_address = :id";
			$prep = $this->db->prepare($sql);
			$prep->bindParam(':id', $id_address);
			$prep->execute();
		}
	}	
	
	/**
	*	Create adress
	**/
	public function createAddress($id_user, array $data) {			
		$prep = $this->db->prepare('
			INSERT INTO merchants_addresses(id_user, address_name, address, lat, lng, opening_hours, tel)
			VALUES (:id_user, :address_name, :address, :lat, :lng, :opening_hours, :tel);
		');
		
		$prep->bindParam(':id_user', $id_user);
		$prep->bindParam(':address_name', $address_name);
		$prep->bindParam(':address', $address);
		$prep->bindParam(':lat', $lat);
		$prep->bindParam(':lng', $lng);
		$prep->bindParam(':opening_hours', $opening_hours);
		$prep->bindParam(':tel', $tel);
		
		$address_name	= $data['address_name'];
		$address 			= $data['address'];
		$lat 					= $data['lat'];
		$lng 					= $data['lng'];
		$opening_hours = $data['opening_hours'];
		$tel 					= $data['tel'];
		
		if($prep->execute()) {	
			return $this->db->lastInsertId();
		}
		return false;
	}
	
	/**
	*	Get a specfic merchant
	**/
	function getMerchant($id_user) {
		$prep = $this->db->prepare('
			SELECT merchants.id_user, name AS merchant_name, nickname, email, firstname, lastname
			FROM merchants
			LEFT JOIN users
			ON merchants.id_user = users.id
			WHERE id_user = :id_user');
		
		$prep->bindParam(':id_user', $id_user, PDO::PARAM_INT);
		$prep->execute();
		
		$merchant = $prep->fetch(PDO::FETCH_ASSOC);
				
		//Contact email
		$prep = $this->db->prepare('
			SELECT email
			FROM merchants_emails
			WHERE id_user = :id_user'
		);
		$prep->bindParam(':id_user', $id_user);
		$success = $prep->execute();
		$merchant['contact_email'] = $prep->fetchAll(PDO::FETCH_COLUMN);
				
		return $merchant;
	}
}

?>
