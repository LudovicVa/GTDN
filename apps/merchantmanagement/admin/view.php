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
		$this->assign('require', '{$base_url}/apps/merchantmanagement/admin/js/script.js');
		$this->assign('users', $model['users']);
		
		$pagination = WHelper::load('pagination', array(
			$model['stats']['request'],
			$model['per_page'],
			$model['current_page'],
			'/admin/user/listing/')
		);
		$this->assign('pagination', $pagination->getHTML());
	}
	
		/**
	 * @var array Stores the response which will be sent in json to the client 
	 */
	private $response = array();
	
	public function error($message) {
		$this->push_content('success', false);
		$this->push_content('msg', $message);
	}
	
	public function success($id = null) {
		$this->push_content('success', true);
		if($id == null) {
			$this->push_content('id', $id);
		}
	}
	
	private function push_message($state, $level, $id, $head_message, $message) {
		if(empty($this->response) || !is_array($this->response)) {
			$this->response = array();
		}
		
		if(empty($this->response[$level]) || !is_array($this->response[$level])) {
			$this->response[$level] = array();
		}
		
		if(empty($this->response[$level][$id]) || !is_array($this->response[$level][$id])) {
			$this->response[$level][$id] = array();
		}
		
		$this->response[$level][$id][$state][] = array('head_message' => $head_message, 'message' => $message);
	}
	
	public function push_content($id, $data) {
		if(empty($this->response) || !is_array($this->response)) {
			$this->response = array();
		}
		
		$this->response[$id] = $data;
	}
	
	public function respond() {
		header('Content-Type: application/json');
		$final_response = array_map('self::prepare_array', $this->response);
		$final_response = html_entity_decode(json_encode($final_response));
		echo $final_response;		
		exit(0);
	}
	
	private function prepare_array($val = '') {
		if(is_numeric($val)) {
			$val = strval($val);
		}
		
		if(is_string($val)) {
			$val = htmlentities($val);
		}
		
		return $val;
	}
}

?>
