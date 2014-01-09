<?php
/**
 * User Application - Admin View
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * UserAdminView is the Admin View of the User Application.
 *
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-04-2013
 */
class TransactionsAdminView extends WView {
	public function __construct() {
		parent::__construct();
		
	}

	/**
	 * Setting up the users listing view.
	 *
	 * @param array $model
	 */
	public function listing(array $model) {
		$this->assign('transactions', $model['transactions']);
		$this->assign($model['sorting_tpl']);
		
		$pagination = WHelper::load('pagination', array(
			$model['stats']['request'],
			$model['per_page'],
			$model['current_page'],
			'/admin/transactions/listing/%d/')
		);
		$this->assign('pagination', $pagination->getHTML());
	}
	
	/**
	* Test system
	**/		
	public function manualTransaction(array $model) {
		$this->assign($model);
		
	}
}

?>
