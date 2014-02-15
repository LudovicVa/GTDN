<?php
defined('WITYCMS_VERSION') or die('Access denied');
/**
 * Include Transactions Model for inheritance of getDealInfo
 */
include_once APPS_DIR.'transactions'.DS.'front'.DS.'model.php';

class TransactionsAdminModel extends TransactionsModel {	
		
	public function __construct() {	
		parent::__construct();
		
		$this->db->declareTable('merchants');
		$this->db->declareTable('deals');
		$this->db->declareTable('transactions');
	}
	
//----------------------------------------------------------------------------------------
//MERCHANTS OPERATION
	public function getTransactions($from, $number, $order = 'created_date', $sens='ASC') {
		$prep = $this->db->prepare('
			SELECT id_transaction, transactions.id_deal, email, firstname, lastname, receipt_id, code_used, transactions.created_date, deals.deal_name, deals.id_user, merchants.name
			FROM deals, merchants, transactions
			WHERE transactions.id_deal = deals.id_deal
			AND deals.id_user = merchants.id_user
			ORDER BY '.$order.' '.$sens.'
			'.($number > 0 ? 'LIMIT :start, :number' : '')
		);
		$prep->bindParam(':start', $from, PDO::PARAM_INT);
		$prep->bindParam(':number', $number, PDO::PARAM_INT);
		$prep->execute();
		$transactions = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		return $transactions;
	}
	
	public function countTransactions(array $filters = array()) {
		$prep = $this->db->prepare('
			SELECT COUNT(*) FROM transactions
		');
		
		$prep->execute();
		
		return intval($prep->fetchColumn());
	}
}

?>
