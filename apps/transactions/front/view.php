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
class UserAdminView extends WView {
	public function __construct() {
		parent::__construct();

		// CSS for all views
		$this->assign('css', '/apps/user/admin/css/user.css');
	}

	/**
	 * Setting up the users listing view.
	 *
	 * @param array $model
	 */
	public function action_1(array $model) {
		
	}
}

?>
