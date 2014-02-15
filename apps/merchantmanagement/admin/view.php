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
class MerchantManagementAdminView extends WView {
	public function __construct() {
		parent::__construct();

		// CSS for all views
		$this->assign('css', '/libraries/bootstrap3-editable/css/bootstrap-editable.css');
		$this->assign('css', '/apps/merchantmanagement/admin/css/style.css');
	}

	/**
	 * Setting up the users listing view.
	 *
	 * @param array $model
	 */
	public function listing(array $model) {
		$this->assign('require', '{$base_url}/libraries/bootstrap3-editable/inputs-ext/password/password.js');		
		$this->assign('require', '{$base_url}/libraries/bootstrap3-editable/inputs-ext/wysihtml5/wysihtml5.js');		
		$this->assign('require', '{$base_url}/libraries/gtdn/editable-table.js');
		$this->assign('require', 'wity_ajax');
		$this->assign('require', 'apps!merchantmanagement/maps');
		$this->assign('users', $model['users']);
		$this->assign($model['sorting_tpl']);
		
		$pagination = WHelper::load('pagination', array(
			$model['stats']['request'],
			$model['per_page'],
			$model['current_page'],
			'/admin/merchantmanagement/listing/%d/')
		);
		$this->assign('pagination', $pagination->getHTML());
	}
}

?>
