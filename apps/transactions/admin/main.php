<?php
/**
 * User Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserAdminController is the Admin Controller of the User Application.
 * 
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-04-2013
 */
class TransactionsAdminController extends WController {
	/**
	* Listing
	**/	
	protected function listing(array $params) {
		$n = 30;
		$page = 1;
		$sort_by = '';
		$sens = '';
		
		if (!empty($params[0])) {
			$count = sscanf(str_replace('-', ' ', $params[0]), '%s %s %d', $sort_by, $sens, $page_input);
			if ($page_input > 1) {
				$page = $page_input;
			}
		}
		
		//Sorting data
		$sortingHelper = WHelper::load('SortingHelper', array(
			array('id', 'nickname', 'email', 'name', 'last_activity', 'created_date'), 
			'created_date', 'ASC'
		));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		if($sort[0] == 'created_date') {
			$sort[0] = 'transactions.created_date';
		}
		$model = array(
			'transactions'         => $this->model->getTransactions(($page-1)*$n, $n, $sort[0], $sort[1]),
			'current_page'  => $page,
			'stats'         => array(),
			'per_page'      => $n,
			'sorting_tpl'   => $sortingHelper->getTplVars()
		);
		
		// Users count
		$model['stats']['total'] = $this->model->countTransactions();
		$model['stats']['request'] = $this->model->countTransactions();
		
		return $model;
	}	
	
	/**
	* Test system
	**/		
	public function manualTransaction(array $params) {		
		$model = array();
		
		//treat data if any
		if (WRequest::hasData()) {
			$received = WRequest::getAssoc(array('id_deal', 'first_name', 'last_name'));
			$model = $received;
			
			//retrieve deal info
			$mail_info = $this->model->getDealInfo($received['id_deal']);
			
			if(!is_array($mail_info)) {
				WNote::error('error_retrieving_deal_data', '<p><pre>'. $mail_info . '</pre></p><p><pre>'. print_r($_POST,true) . '</pre></p>');
				return;
			}
			
			//Add firstname, etc...
			$mail_info['firstname'] = $_POST['first_name'];
			$mail_info['lastname'] = $_POST['last_name'];
			//Fake voucher
			$mail_info['voucher'] = "XXXX-XXXX";
			
			//IMPORTANT : ERASE EMAIL !
			$mail_info['contact_email'] = array(array('email' => 'fake_merchant@getthedealnow.com'));	
			$mail_info['payer_email'] 	= 'fake_customer@getthedealnow.com';	

			$this->model->sendEmails($mail_info);			
		}
		
		//assign variable for select
		$model['deals'] = $this->model->getDealsList();
		return $model;
	}
}

?>
