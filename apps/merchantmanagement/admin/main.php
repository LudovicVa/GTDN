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
			'created_date', 'DESC'
		));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		if($sort[0] == 'created_date') {
			$sort[0] = 'users.created_date';
		}
		$model = array(
			'users'         => $this->model->getMerchants(($page-1)*$n, $n, $sort[0], $sort[1]),
			'current_page'  => $page,
			'stats'         => array(),
			'per_page'      => $n,
			'sorting_tpl'   => $sortingHelper->getTplVars()
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
			case 'nickname':
				if($this->model->checkNickname($value)) {
					if($this->model->updateUser($merchant_id, array('nickname' => $value))) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('The nickname is not valid or already exists.');
				}
				break;
			case 'password':
				if($this->isName($value)) {
					if($value['password'] != $value['password_confirm']) {
						$this->view->error('Password must be equals.');
					} else {
						if($this->model->updateUser($merchant_id, array('password' => sha1($value['password'])))) {
							$this->view->success();
						} else {
							$this->view->error('Unknown error. Please retry.');
						}
					}
				} else {
					$this->view->error('The password can not be empty.');
				}
				break;	
			case 'email':
				if($this->model->checkEmail($value)) {
					if($this->model->updateUser($merchant_id, array('email' => $value))) {
						$this->view->success();
					} else {
						$this->view->error('Unknown error. Please retry');
					}
				} else {
					$this->view->error('The email is already existing or invalid.');
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
			case 'merchant':
				$this->addMerchant($_POST);
				break;
			default:
				$this->view->error("Command Unrecognised");
				break;
		}
		$this->view->respond();
	}
	
	protected function delete(array $params) {		
		$type = $_POST['name'];
		switch($type) {
			case 'address':
				$id = $_POST['pk'];
				if($this->model->getAddress($id) && $this->model->deleteAddress($id)) {
					$this->view->success();
				} else {
					$this->view->error("This address doesn't exist in the database");
				}
				break;
			case 'email':
				$id = $_POST['pk'];
				if($this->model->getContact($id) && $this->model->deleteContact($id)) {
					$this->view->success();
				} else {
					$this->view->error("This email doesn't exist in the database");
				}
				break;
			case 'merchant':
				$id = $_POST['pk'];
				if($this->model->deleteMerchant($id)) {
					$this->view->success();
				} else {
					$this->view->error("Unknown error. Please retry.");
				}
				break;
			default:
				$this->view->error("Command Unrecognised");
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
	
	private function addMerchant(array $params) {	
		if(!$this->model->checkNickname($params['nickname'])) {
			$this->view->error("Nickname invalid or already existing.");
			return;
		}
		print_r($params);
		
		if($params['password']['password'] != $params['password']['password_confirm']) {
			$this->view->error("Password and its confirmation must be equals.");
			return;
		}
		$params['password'] = sha1($params['password']['password']);
		
		if(!$this->model->checkEmail($params['email'])) {
			$this->view->error("Nickname invalid or already existing.");
			return;
		}
		
		$params['valid'] = 1;
		$params['access'] = '';
		$params['confirm'] = '';
		
		$this->model->createMerchant($params);
	}
}

?>
