<?php
/**
 * Deals Management Application - Admin Model
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * Include Transactions Model for inheritance of getDealInfo
 */
include_once APPS_DIR.'transactions'.DS.'front'.DS.'model.php';

/**
 * DealsManagementAdminModel is the Admin Model of the deals Application.
 * 
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-15-02-2013
 */
class DealsManagementModel extends TransactionsModel {	
	public $GROUP = 1;
		
	public function __construct() {
		parent::__construct();
	}
	
	const FORMAT   = '%d/%m/%Y %H:%i';
//----------------------------------------------------------------------------------------
//LIST OPERATION	
	public function getDeals($from, $number, $order = 'deal_name', $sens = 'ASC', array $filters = array()) {
		if (strtoupper($sens) != 'ASC') {
			$sens = 'DESC';
		}
		
		// Add filters
		$cond = '';
		if (!empty($filters)) {
			$allowed = array('name', 'deal_name', 'lastname', 'id_user');
			foreach ($filters as $name => $value) {
				if (in_array($name, $allowed)) {
					if (strpos($value, '%') === false) {
						$value = '%'.$value.'%';
					}
					if($name == 'id_user') {
						$name = 'deals.id_user';
					}
					$cond .= $name." LIKE ".$this->db->quote($value)." AND ";
				}
			}
			
			if (!empty($cond)) {
				$cond = 'WHERE '.substr($cond, 0, -5);
			}
		}
		
		$prep = $this->db->prepare('
			SELECT deals.id_user, id_deal, name AS merchant_name, deal_name, DATE_FORMAT(start_time,\''. self::FORMAT .'\') AS start_time, DATE_FORMAT(end_time,\''. self::FORMAT .'\') AS end_time, price, original_price, description, images
			FROM deals
			LEFT JOIN merchants
			ON deals.id_user = merchants.id_user
			'.$cond.'
			ORDER BY '.$order.' '.$sens.'
			'.($number > 0 ? 'LIMIT :start, :number' : '')
		);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function countDeals(array $filters = array()) {
		$cond = "";
		if (!empty($filters)) {
			$allowed = array('name', 'deal_name', 'lastname', 'id_user');
			foreach ($filters as $name => $value) {
				if (in_array($name, $allowed)) {
					if (strpos($value, '%') === false) {
						$value = '%'.$value.'%';
					}
					if($name == 'id_user') {
						$name = 'deals.id_user';
					}
					$cond .= $name." LIKE ".$this->db->quote($value)." AND ";
				}
			}
						
			if (!empty($cond)) {
				$cond = 'WHERE '.substr($cond, 0, -5);
			}
		}
		
		$prep = $this->db->prepare('
			SELECT COUNT(*) FROM deals '.$cond);
		
		$prep->execute();
		
		return intval($prep->fetchColumn());
	}

	public function getDealWithin($lat = null, $long =null, $dist = null) {
		//Latitude	Longitude
		//48.88980	2.33803
		if($lat != null && $long !=null && $dist != null) {
			$prep = $this->db->prepare('
				SELECT id_deal, name AS merchant_name, 
				deal_name, price, original_price, 
				DATE_FORMAT(start_time,\''. self::FORMAT .'\') AS start_time, DATE_FORMAT(end_time,\''. self::FORMAT .'\') AS end_time, images, 
				address_name, address, lat, lng, get_distance_metres(:lat, :lng, lat, lng) AS proximite
				FROM deals, merchants, merchants_addresses
				WHERE deals.id_user = merchants.id_user 
				AND merchants_addresses.id_user = deals.id_user
				AND get_distance_metres(:lat, :lng, lat, lng) < :dist
				ORDER BY proximite ASC'
			);
			$prep->bindParam(':lat', $lat);
			$prep->bindParam(':lng', $long);
			$prep->bindParam(':dist', $dist);
		} else {
		$prep = $this->db->prepare('
				SELECT id_deal, name AS merchant_name, 
				deal_name, price, original_price, 
				DATE_FORMAT(start_time,\''. self::FORMAT .'\') AS start_time, DATE_FORMAT(end_time,\''. self::FORMAT .'\') AS end_time, images, 
				address_name, address, lat, lng, 0 AS proximite
				FROM deals, merchants, merchants_addresses
				WHERE deals.id_user = merchants.id_user 
				AND merchants_addresses.id_user = deals.id_user'
			);
		}
		$prep->execute();
		
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/** Get a specific deal **/
	public function getDealFromMerchant($id_deal, $id_merchant) {
		
		$prep = $this->db->prepare('
			SELECT deals.id_user, id_deal, name AS merchant_name, deal_name, DATE_FORMAT(start_time,\''. self::FORMAT .'\') AS start_time, DATE_FORMAT(end_time,\''. self::FORMAT .'\') AS end_time, price, original_price, description, images
			FROM deals
			LEFT JOIN merchants
			ON deals.id_user = merchants.id_user
			WHERE deals.id_user = :id_user AND deals.id_deal = :id_deal
		');
		$prep->bindParam(':id_user', $id_merchant, PDO::PARAM_INT);
		$prep->bindParam(':id_deal', $id_deal, PDO::PARAM_INT);
		$prep->execute();
		
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	
	/*
	*	Check if a deals exist
	*/
	public function isDealId($id_deal, $id_merchant) {
		if(!is_numeric($id_deal)) {
			return false;
		} 		
		$prep = $this->db->prepare('SELECT id_deal FROM deals WHERE id_deal = :id AND deals.id_user = :id_user');
		$prep->bindParam(':id', $id_deal);
		$prep->bindParam(':id_user', $id_merchant);
		$prep->execute();
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		return count($result) == 1;
	}
	
	
	public function update($id_deal, $data) {
		$allowed = array('deal_name', 'price', 'original_price', 'start_time', 'end_time', 'images', 'description');
		
		$cols = array();
		$vals = array();
		foreach($data as $name => $value) {
			if(in_array($name, $allowed)) {
				if($name == 'start_time' || $name == 'end_time') {
					$value = 'STR_TO_DATE('. $this->db->quote($value). ', \''. self::FORMAT .'\')';
					array_push($cols,  $name . " = ".$value);
				} else {
					array_push($cols,  $name . " = ". $this->db->quote($value));
				}
			}			
		}
		if(count($cols) != 0) {
			$sql = "UPDATE deals SET " .implode(', ', $cols) ." WHERE id_deal = :id";
			$prep = $this->db->prepare($sql);
			$prep->bindParam(':id', $id_deal);
			$prep->execute();
		}
	}	
	/*
	*	Check if a merchant id exist
	*/
	public function isMerchantId($id_user) {
		if(!is_numeric($id_user)) {
			return false;
		} 		
		$prep = $this->db->prepare('SELECT id_user FROM merchants WHERE id_user = :id');
		$prep->bindParam(':id', $id_user);
		$prep->execute();
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		return count($result) == 1;
	}
	
	/**
	*	Create a new deal
	**/
	public function createDeal(array $data) {			
		$prep = $this->db->prepare('
			INSERT INTO deals(id_user, deal_name, start_time, end_time, price, original_price,images, description)
			VALUES (:id_user, :deal_name, :start_time, :end_time, :price, :original_price, :images, :desc);
		');
		
		$prep->bindParam(':id_user', $id_user);
		$prep->bindParam(':deal_name', $deal_name);
		$prep->bindParam(':start_time', $start_time);
		$prep->bindParam(':end_time', $end_time);
		$prep->bindParam(':price', $price);
		$prep->bindParam(':original_price', $original_price);
		$prep->bindParam(':images', $images);
		$prep->bindParam(':desc', $desc);
		
		$id_user 			= $data['id_user'];
		$deal_name		= $data['deal_name'];
		$start_time 		= $data['start_time'];
		$end_time 		= $data['end_time'];
		$price 				= $data['price'];
		$original_price 	= $data['original_price'];
		$desc 				= $data['description'];
		$images 			= $data['images'];
		
		if($prep->execute()) {	
			return $this->db->lastInsertId();
		}
		return false;
	}
	
	/**
	*	Update deal email
	**/
	public function updateEmail2Customer($id_deal, $subject, $body) {
		$prep = $this->db->prepare('UPDATE deals_emails SET subject = :subject, body = :body WHERE id_deal = :id_deal');
		$prep->bindParam(':id_deal', $id_deal);
		$prep->bindParam(':subject', $subject);
		$prep->bindParam(':body', $body);
		
		return $prep->execute();
	}
}

?>
