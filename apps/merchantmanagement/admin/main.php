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
class MerchantManagementAdminController extends WController {
	/**
	* Listing
	**/	
	protected function listing(array $params) {
		$n = 30;
		$page = 1;
		if (!empty($params[0])) {
			$count = sscanf(str_replace('-', ' ', $params[0]), '%s %s %d', $sort_by, $sens, $page_input);
			if ($page_input > 1) {
				$page = $page_input;
			}
		}
		
		$model = array(
			'users'         => $this->model->getMerchants(($page-1)*$n, $n),
			'current_page'  => $page,
			'stats'         => array(),
			'per_page'      => $n
		);
		
		// Users count
		$model['stats']['total'] = $this->model->countUsers();
		$model['stats']['request'] = $this->model->countUsers(array('groupe' => 1));
		
		return $model;
	}
	
	/**
	* Listing
	**/	
	protected function edit(array $params) {
		$merchant_id = $_POST['pk'];
		$type = $_POST['name'];
		$value =  $_POST['value'];
		switch($type) {
			case 'name':
				if($this->isName($value)) {
					if($this->model->updateName($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Required');
				}
				break;	
			case 'email':
				if($this->isEmail($value)) {
					if($this->model->updateMail($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Does not look like an email...');
				}
				break;
			case 'contacts_email':
				if($this->isEmail($value)) {
					if($this->model->updateContactEmail($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Doesn\'t look like an email...');
				}
				break;
			case 'contacts_name':
				if($this->isName($value)) {
					if($this->model->updateContactName($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Required');
				}
				break;
			case 'address_name':
				if($this->isName($value)) {
					if($this->model->updateAddressName($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Required');
				}
				break;
			case 'address':
				if($this->isName($value)) {
					if($this->model->updateAddress($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Required');
				}
				break;
			case 'opening_hours':
				if($this->isName($value)) {
					if($this->model->updateOpeningHours($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Required');
				}
				break;
			case 'tel':
				if($this->isName($value)) {
					if($this->model->updateTel($merchant_id, $value)) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Required');
				}
				break;
			default:
				$this->view->error('Unknown field');
				break;
		}		
		$this->view->respond();
	}
	
	protected function check(array $params) {	
		$type = $_POST['name'];
		$value =  $_POST['value'];
		switch($type) {
			case 'name':
				if($this->isName($value)) {
					$this->view->success();
				} else {
					$this->view->error('Required');
				}
				break;	
			case 'email':
				if($this->isEmail($value)) {
					$this->view->success();
				} else {
					$this->view->error('Does not look like an email...');
				}
				break;
			case 'contacts_email':
				if($this->isEmail($value)) {
					$this->view->success();
				} else {
					$this->view->error('Doesn\'t look like an email...');
				}
				break;
			case 'contacts_name':
				if($this->isName($value)) {
					$this->view->success();
				} else {
					$this->view->error('Required');
				}
				break;
		}		
		$this->view->respond();
	}
	
	protected function add(array $params) {
		$type = $params[0];
		switch($type) {
			case 'contacts':
				$merchant_id = $params[1];
				$name = $_POST['contacts_name'];
				$mail = $_POST['contacts_email'];
				if($this->isName($name) && $this->isEmail($mail)) {
					$id = $this->model->addContact($merchant_id, $name, $mail);
					if($id >= 0) {
						$this->view->success($id);
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Invalid data');
				}
			break;
			case 'address':
				$merchant_id = $params[1];
				error_log(serialize($_POST));
				$name = $_POST['address_name'];
				$address = $_POST['address'];
				$hours = $_POST['opening_hours'];
				$tel = $_POST['tel'];
				if($this->isName($name) && $this->isName($address) && $this->isName($hours) && $this->isName($tel)) {
					$id = $this->model->addAddress($merchant_id, $name, $address, $hours, $tel);
					if($id >= 0) {
						$this->view->success($id);
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('Invalid data');
				}
			break;
		}
		$this->view->respond();
	}
	
	private function isName($name) {
		return $name != null && $name != "";
	}
	
	private function isEmail($email) {
		return !empty($email) && preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email);
	}
	
	private function areEmails($data) {
		if(!is_array($data)) {
			return isEmail($email);
		}
		foreach($data as $email) {
			if(!$this->isEmail($email)) {	
				return false;
			}
		}
		return true;
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
