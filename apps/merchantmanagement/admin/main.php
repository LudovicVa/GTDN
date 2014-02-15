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
	* Editing
	**/	
	protected function edit(array $params) {
		header('Content-Type: application/json');
		$editor = WHelper::load('editor');		
		error_log(print_r($_POST, true));
		if(!isset($_POST['pk']) || !isset($_POST['name']) || !isset($_POST['value'])) {
			WNote::error('invalid_request', WLang::get('invalid_request'));
		} else {
			$merchant_id = $_POST['pk'];
			//$edit = $this->model->isMerchantId($merchant_id); TODO : CHECK IF IT IS A VALID ADDRESS ID...
			$edit = $merchant_id != null && is_numeric($merchant_id);
			if($edit) {
				$editor->edit($this, array(array('type'=>$_POST['name'], 'value'=>$_POST['value'])), $merchant_id);
			} else {
				$editor->edit($this, array(array('type'=>$_POST['name'], 'value'=>$_POST['value'])));
			}
		}
	}
	
	public function save($values, $merchant_id, $editor) {
		$errors = array();
		foreach($values as $data) {
			if(!isset($data['type']) || !isset($data['value'])) {
				array_push($errors, $editor->generateError('invalid_request'));
				return $errors;
			}
			$type = $data['type'];
			$value =  $data['value'];
			switch($type) {
				case 'name':
					if(!$this->model->updateName($merchant_id, $value)) {
						array_push($errors, $editor->generateError('merchant_name_not_saved'));
					}
					break;	
				case 'nickname':
					if(!$this->model->updateUser($merchant_id, array('nickname' => $value))) {
						array_push($errors, $editor->generateError('nickname_name_not_saved'));
					}
					break;
				case 'password':
					if(!$this->model->updateUser($merchant_id, array('password' => sha1($value['password'])))) {
						array_push($errors, $editor->generateError('password_not_saved'));
					}
					break;	
				case 'email':
					if(!$this->model->updateUser($merchant_id, array('email' => $value))) {
						array_push($errors, $editor->generateError('email_not_saved'));
					}
					break;
				case 'contacts_email':
					if(!$this->model->updateContactEmail($merchant_id, $value)) {
						array_push($errors, $editor->generateError('contact_email_not_saved'));
					}
					break;
				case 'contacts_name':
					if(!$this->model->updateContactName($merchant_id, $value)) {
						array_push($errors, $editor->generateError('contact_name_not_saved'));
					}
					break;
				case 'address_name':
					if(!$this->model->updateAddressName($merchant_id, $value)) {
						array_push($errors, $editor->generateError('address_name_not_saved'));
					}
					break;
				case 'address':
					if(!$this->model->updateAddress($merchant_id, $value)) {
						array_push($errors, $editor->generateError('address_not_saved'));
					}
					break;
				case 'opening_hours':
					if(!$this->model->updateOpeningHours($merchant_id, $value)) {
						array_push($errors, $editor->generateError('hours_not_saved'));
					}
					break;
				case 'tel':
					if(!$this->model->updateTel($merchant_id, $value)) {
						array_push($errors, $editor->generateError('tel_not_saved'));
					}
					break;
				case 'latlong':
					if(!$this->model->updateLatLng($merchant_id, $value)) {
						array_push($errors, $editor->generateError('lat_lng_not_saved'));
					} else {
						$value = explode(',', $value);
						array_push($errors, $editor->generateSuccess('lat', $value[0]));
						array_push($errors, $editor->generateSuccess('lng', $value[1]));
					}
					break;
				default:
					array_push($errors,$editor->generateError('unknown_field'));
					break;
			}
		}
		return $errors;
	}
	
	public function check($values, $editor) {
		$errors = array();	
		foreach($values as $data) {
			if(!isset($data['type']) || !isset($data['value'])) {
				array_push($errors, $editor->generateError('invalid_request'));
				return $errors;
			}
			$type = $_POST['name'];
			$value =  $_POST['value'];
			switch($type) {
				case 'name':
					if(!$this->isName($value)) {
						array_push($errors,$editor->generateError('invalid_name'));
					}
					break;	
				case 'nickname':
					if(is_string($e = $this->model->checkNickname($value))) {
						array_push($errors,$editor->generateError('invalid_nickname', WLang::get($e)));			
					}
					break;
				case 'password':
					if(!$this->isName($value) || ($value['password'] != $value['password_confirm'])) {
						array_push($errors,$editor->generateError('password_not_equals',  WLang::get('password_not_equals')));	
					}
					break;	
				case 'email':
					if(is_string($e = $this->model->checkEmail($value))) {
						array_push($errors,$editor->generateError('email',  WLang::get($e)));	
					}
					break;
				case 'contacts_email':
					if(!$this->isEmail($value)) {
						array_push($errors,$editor->generateError('email_invalid'));
					}
					break;
				case 'latlong':
					$array = explode(',',$value);
					if(count($array) != 2 || !is_numeric($array[0]) || !is_numeric($array[1])) {
						array_push($errors,$editor->generateError('coordinates_invalid'));
					}
					break;
				case 'contacts_name':
				case 'address_name':
				case 'address':
				case 'opening_hours':
				case 'tel':
					if(!$this->isName($value)) {
						array_push($errors,$editor->generateError('required_field'));
					}
					break;
				default:
					array_push($errors,$editor->generateError('unknown_field'));
					break;
			}
		}
		return $errors;
	}
	
	public function isValidRecordId($id, $editor) {
	//TODO case addresses, etc...
		/*if(!$this->model->isMerchantId($id)) {
			return array($editor->generateError('merchant_does_not_exist'));
		}*/
		return array();
	}
	
	protected function add(array $params) {
		header('Content-Type: application/json');
		$type = $params[0];
		switch($type) {
			case 'contacts':
				$merchant_id = $params[1];
				$name = $_POST['contacts_name'];
				$mail = $_POST['contacts_email'];
				if($this->isName($name) && $this->isEmail($mail)) {
					$id = $this->model->addContact($merchant_id, $name, $mail);
					if($id >= 0) {
						WNote::success('id', $id);
						WNote::success('success', WLang::get('contact_successfully_added'));
					} else {
						WNote::error('cannot_save_contacts',  WLang::get('cannot_save_contacts'));
					}
				} else {
					WNote::error('bad_contact_data', WLang::get('bad_contact_data'));
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
						WNote::success('id', $id);
						WNote::success('success', WLang::get('address_successfully_added'));
					} else {
						WNote::error('cannot_save_address',  WLang::get('cannot_save_address'));
					}
				} else {
					WNote::error('bad_address_data', WLang::get('bad_address_data'));
				}
				break;
			case 'merchant':
				$id = $this->addMerchant($_POST);
				if(isset($id)) {
					WNote::success('id', $id);
					WNote::success('success', WLang::get('merchant_successfully_added'));
				}
				break;
			default:
				WNote::error('command_unrecognised', 'command_unrecognised');
				break;
		}
	}
	
	protected function delete(array $params) {		
		header('Content-Type: application/json');
		$type = $_POST['name'];
		switch($type) {
			case 'address':
				$id = $_POST['pk'];
				if(($exist = $this->model->getAddress($id)) 
					&& $this->model->deleteAddress($id)) {
					WNote::success('success', WLang::get('address_successfully_deleted'));
				} else {
					if(!$exist) {
						WNote::error('unknown_address', WLang::get('unknown_address'));
					} else {
						WNote::error('cannot_delete_address', WLang::get('cannot_delete_address'));
					}
				}
				break;
			case 'email':
				$id = $_POST['pk'];
				if(($exist = $this->model->getContact($id))
					&& $this->model->deleteContact($id)) {
					WNote::success('success', WLang::get('email_successfully_deleted'));
				} else {
					if(!$exist) {
						WNote::error('unknown_contact', WLang::get('unknown_contact'));
					} else {
						WNote::error('cannot_delete_contact', WLang::get('cannot_delete_contact'));
					}
				}
				break;
			case 'merchant':
				$id = $_POST['pk'];
				if($this->model->deleteMerchant($id)) {
					WNote::success('success', WLang::get('merchant_successfully_deleted'));
				} else {
					WNote::error('cannot_delete_merchant', WLang::get('cannot_delete_merchant'));
				}
				break;
			default:
				WNote::error('command_unrecognised', 'command_unrecognised');
				break;
		}
	}
	
	private function isName($name) {
		return $name != null && $name != "";
	}
	
	private function isEmail($email) {
		return !empty($email) && preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email);
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
		$error = false;
		if(is_string($e = $this->model->checkNickname(isset($params['nickname']) ? $params['nickname'] : ''))) {
			WNote::error('nickname', WLang::get($e));
			$error = true;
		}
		
		if($params['password']['password'] != $params['password']['password_confirm']) {
			WNote::error('password_not_equals', 'password_not_equals');	
			$error = true;
		}
		$params['password'] = sha1($params['password']['password']);
		
		if(is_string($e = $this->model->checkEmail(isset($params['email']) ? $params['email'] : ''))) {
			WNote::error('email_not_valid', WLang::get($e));	
			$error = true;
		}
		
		if(!$error) {
			$params['valid'] = 1;
			$params['access'] = '';
			$params['confirm'] = '';
			
			return $this->model->createMerchant($params);
		}
		return false;
	}
	
}

?>
