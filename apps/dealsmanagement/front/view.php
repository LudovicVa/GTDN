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
class DealsManagementView extends WView {
	public function __construct() {
		parent::__construct();
	}
	
	public function dealslisting(array $model) {		
		$this->assign($model);		
	}

	public function editdeals(array $model) {
		$this->assign('css', '/apps/dealsmanagement/front/css/edit.css');
		$this->assign('css', '/libraries/datepicker/css/bootstrap-datetimepicker.min.css');
		
		$this->assign($model);		
	}
	
	public function editdeal(array $model) {
		$this->assign('require', 'apps!dealsmanagement/edit');
		$this->assign('require', 'moment');
		$this->assign('require', 'date_picker');
		$this->assign('require', 'extend');
		$this->assign('require', 'multiselect');
		$this->assign('css', '/apps/dealsmanagement/front/css/edit.css');
		$this->assign('css', '/libraries/datepicker/css/bootstrap-datetimepicker.min.css');
		$this->assign('css', '/libraries/extend/css/jasny-bootstrap.min.css');
		
		$this->assign($model);
		
	}
}

?>
