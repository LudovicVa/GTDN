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
class MerchantManagementView extends WView {
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Setting up the users listing view.
	 *
	 * @param array $model
	 */
	public function shops(array $model) {
		$this->assign($model);
		$this->assign('require', 'apps!merchantmanagement/maps_front');
	}
	
	/**
	**/
	public function profile(array $model) {
		$this->assign($model);
		$this->assign('require', 'apps!merchantmanagement/multiple');
	}
}

?>