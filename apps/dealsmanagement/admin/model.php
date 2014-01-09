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
class DealsManagementAdminModel extends TransactionsModel {	
	public $GROUP = 1;
		
	public function __construct() {
		parent::__construct();
	}
	
//----------------------------------------------------------------------------------------
//LIST OPERATION
	public function getDeals($from, $number, $order = 'deal_name', $sens = 'ASC', array $filters = array()) {
		if (strtoupper($sens) != 'ASC') {
			$sens = 'DESC';
		}
		
		// Add filters
		$cond = '';
		if (!empty($filters)) {
			$allowed = array('name', 'deal_name', 'lastname');
			foreach ($filters as $name => $value) {
				if (in_array($name, $allowed)) {
					if (strpos($value, '%') === false) {
						$value = '%'.$value.'%';
					}
					$cond .= $name." LIKE ".$this->db->quote($value)." AND ";
				}
			}
			
			if (isset($filters['time'])) {
				//check if in-between
				$cond .= 'valid = '.intval($filters['valid']).' AND ';
			} else {
				$cond .= 'valid = 1 AND ';
			}
			
			if (!empty($filters['groupe'])) {
				$cond .= 'groupe = '.intval($filters['groupe']).' AND ';
			}
			
			if (!empty($cond)) {
				$cond = 'WHERE '.substr($cond, 0, -5);
			}
		}
		
		$prep = $this->db->prepare('
			SELECT deals.id_merchant, id_deal, name AS merchant_name, deal_name, DATE_FORMAT(start_time,\'%d/%m/%y %k:%i\') AS start_time, DATE_FORMAT(end_time,\'%d/%m/%y %k:%i\') AS end_time, price, original_price, description, images
			FROM deals
			LEFT JOIN merchants
			ON deals.id_merchant = merchants.id_merchant
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
		$prep = $this->db->prepare('
			SELECT COUNT(*) FROM deals
		');
		
		$prep->execute();
		
		return intval($prep->fetchColumn());
	}
	
	/*
	*	Check if a deals exist
	*/
	public function isDealId($id_deal) {
		if(!is_numeric($id_deal)) {
			return false;
		} 		
		$prep = $this->db->prepare('SELECT id_deal FROM deals WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		$prep->execute();
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		return count($result) == 1;
	}
		
	/*
	*	Get all the merchants id and name
	*/
	public function getMerchants() {
		$prep = $this->db->prepare('SELECT id_merchant, name FROM merchants');
		$prep->execute();
		return $prep->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/*
	*	Check if a merchant id exist
	*/
	public function isMerchantId($id_merchant) {
		if(!is_numeric($id_merchant)) {
			return false;
		} 		
		$prep = $this->db->prepare('SELECT id_merchant FROM merchants WHERE id_merchant = :id');
		$prep->bindParam(':id', $id_merchant);
		$prep->execute();
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		return count($result) == 1;
	}
	
	/**
	*	Update deal name
	**/
	public function updateName($id_deal, $name) {
		$prep = $this->db->prepare('UPDATE deals SET deal_name=:name WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		$prep->bindParam(':name', $name);
		
		return $prep->execute();
	}
	
	/**
	*	Update merchant
	**/
	public function updateMerchant($id_deal, $id_merchant) {
		$prep = $this->db->prepare('UPDATE deals SET id_merchant=:id_merchant WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		$prep->bindParam(':id_merchant', $id_merchant);
		
		return $prep->execute();
	}
	
	/**
	*	Update start time
	**/
	public function updateStartTime($id_deal, $start_time) {
		$prep = $this->db->prepare('UPDATE deals SET start_time = :start_time WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		$prep->bindParam(':start_time', $start_time);
		
		return $prep->execute();
	}
	
	/**
	*	Update end time
	**/
	public function updateEndTime($id_deal, $end_time) {
		$prep = $this->db->prepare('UPDATE deals SET end_time = :end_time WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		$prep->bindParam(':end_time', $end_time);
		
		return $prep->execute();
	}
	
	/**
	*	Update price
	**/
	public function updatePrice($id_deal, $price) {
		$prep = $this->db->prepare('UPDATE deals SET price = :price WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		$prep->bindParam(':price', $price);
		
		return $prep->execute();
	}
	
	/**
	*	Update original price
	**/
	public function updateOriginalPrice($id_deal, $price) {
		$prep = $this->db->prepare('UPDATE deals SET original_price = :price WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		$prep->bindParam(':price', $price);
		
		return $prep->execute();
	}
	
	/*
	* Delete Deal
	*/
	public function deleteDeal($id_deal) {	
		$prep = $this->db->prepare('DELETE FROM deals WHERE id_deal = :id');
		$prep->bindParam(':id', $id_deal);
		
		return $prep->execute();
	}
	
	public function createDeal(array $data) {			
		$prep = $this->db->prepare('
			INSERT INTO deals(id_merchant, deal_name, start_time, end_time, price, original_price)
			VALUES (:id_merchant, :deal_name, :start_time, :end_time, :price, :original_price);
		');
		$prep->bindParam(':id_merchant', $id_merchant);
		$prep->bindParam(':deal_name', $deal_name);
		$prep->bindParam(':start_time', $start_time);
		$prep->bindParam(':end_time', $end_time);
		$prep->bindParam(':price', $price);
		$prep->bindParam(':original_price', $original_price);
		
		$id_merchant = $data['merchant'];
		$deal_name = $data['deal_name'];
		$start_time = $data['start_time'];
		$end_time = $data['end_time'];
		$price = $data['price'];
		$original_price = $data['original_price'];
		
		if($prep->execute()) {	
			return $this->db->lastInsertId(); 
		}
		return false;
	}
	
	
	
	/**
	*	Update deal email
	**/
	public function updateEmail2Customer($id_deal, $subject, $body) {
		$prep = $this->db->prepare('REPLACE INTO deals_emails SET id_deal = :id_deal, subject = :subject, body = :body');
		$prep->bindParam(':id_deal', $id_deal);
		$prep->bindParam(':subject', $subject);
		$prep->bindParam(':body', $body);
		
		return $prep->execute();
	}
}

?>
