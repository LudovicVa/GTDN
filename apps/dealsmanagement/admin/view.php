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
class DealsManagementAdminView extends WView {
	public function __construct() {
		parent::__construct();

		// CSS for all views
		$this->assign('css', '/apps/dealsmanagement/admin/css/style.css');
	}

	/**
	 * Setting up the users listing view.
	 *
	 * @param array $model
	 */
	public function listing(array $model) {
		$this->assign('css', '/libraries/bootstrap3-editable/css/bootstrap-editable.css');
		
		$this->assign('require', 'gtdn/moment.min');
		$this->assign('require', 'gtdn/editable-table');
		$this->assign('require', '{$base_url}/apps/dealsmanagement/admin/js/script.js');
		
		$this->assign('css', '/libraries/bootstrap-switch/css/bootstrap-switch.min.css');
		$this->assign('deals', $model['deals']);
		
		$this->assign('base_url', WRoute::getBase());
		
		$pagination = WHelper::load('pagination', array(
			$model['stats']['request'],
			$model['per_page'],
			$model['current_page'],
			'/admin/dealsmanagement/listing/%d/')
		);
		$this->assign('pagination', $pagination->getHTML());
	}
	
	public function email_edit(array $model) {
		$this->assign('css', "/libraries/wysihtml5-bootstrap/bootstrap-wysihtml5-0.0.2.css");
		//$this->assign('js', '/apps/dealsmanagement/admin/js/script.js');
		//$this->assign('js', "/libraries/ckeditor/ckeditor.js");
		//$this->assign('require', 'wysihtml5');
		
		//$this->assign('require', '{$base_url}/apps/dealsmanagement/admin/js/script.js');
		$this->assign($model);
	}
}

?>
