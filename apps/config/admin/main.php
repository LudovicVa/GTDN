<?php
/**
 * User Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

include_once APPS_DIR.'config'.DS.'admin'.DS.'WForm.php';

/**
 * UserAdminController is the Admin Controller of the User Application.
 * 
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-04-2013
 */
class ConfigAdminController extends WController {
	/**
	* Config WityCMS
	**/	
	protected function witycms(array $params) {
		if (WRequest::hasData()) {
			$received = WRequest::getAssoc(array('save', 'field', 'field_2'));
			
			if(isset($_POST['field']) && $received['field'] == '') {
				WNote::error('required', WLang::get('required'));
			}
			
			if(isset($_POST['field_2']) && $received['field_2'] == '') {
				WNote::error('required', WLang::get('required'));
			}
			
			if(isset($received['save']) && $received['save'] == 'true') {
			
			}
		}
		$nodes = array(
			'field' => array('label' => 'Field 1', 'type' => 'text', 'value' => 'default'),
			'field_2' => array('label' => 'Field 2', 'type' => 'text')
		);
			
		$form = array(
			'action' => '/admin/config/witycms/',
			'change' => '/m/admin/config/witycms/',
			'method' => 'POST',
			'submit_text' => 'name',
			'nodes' => $nodes
		);
		
		WForm::assignForm('test', $form);
	}
	
	/**
	* Config Apps
	**/	
	protected function apps(array $params) {
		
	}
}

?>
