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
		
		return $prep_address->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function isValidId($id_address, $user_id) {
		$prep_address = $this->db->prepare('
				SELECT COUNT(*)
				FROM merchants_addresses
				WHERE id_address = :id_address AND id_user = :id_user'
			);
		$prep_address->bindParam(':id_address', $id_address);
		$prep_address->bindParam(':id_user', $id_user);
		$prep_address->execute();
		
		return intval($prep_address->fetchColumn()) == 1;
	}
	
		
	public function update($id_address, $data) {
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
	
	public function createAddress($id_user, array $data) {			
		$prep = $this->db->prepare('
			INSERT INTO deals(id_user, address_name, address, lat, lng, opening_hours, tel)
			VALUES (:id_user, :address_name, :address, :lat, :lng, :opening_hours, :tel);
		');
		
		$prep->bindParam(':id_user', $id_user);
		$prep->bindParam(':address_name', $address_name);
		$prep->bindParam(':address', $start_time);
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
}

?>
