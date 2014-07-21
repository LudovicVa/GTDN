<?php
/**
 * User Application - Admin Controller
 */

defined('WITYCMS_VERSION') or die('Access denied');

include_once 'helpers'.DS.'WForm'.DS.'WForm.php';

/**
 * UserAdminController is the Admin Controller of the User Application.
 * 
 * @package Apps\User\Admin
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-04-2013
 */
class MerchantManagementController extends WController {
	/**
	* Shops listing and edition
	* If post data, try to create or update a shop 
	**/	
	protected function shops() {		
		$this->view->setTheme('gtdn_merchant');
		$merchant_id = $_SESSION['userid'];
		
		$model['shops'] = array();
		if(WRequest::hasData()) {
			$data = WRequest::getAssoc(array('id_address', 'address_name', 'address', 'lat', 'lng', 'opening_hours', 'tel'));
			
			if(is_null($data['id_address']) && empty($data['id_address'])) {
				//Create new address
				$any_error = $this->checkAddress($data);
				if(!$any_error) {
					$id = $this->model->createAddress($merchant_id, $data);
					$model['selected'] = $id;
					WNote::success('shop_created', WLang::get('shop_created'));
				} else {
					$model = $model + $data;
					$model['selected'] = 'new';
				}
			} else {
				//Update
				$any_error = $this->checkAddress($data);
				$model['selected'] = $data['id_address'];
				$model['shops'][$data['id_address']] = $data;
				if(!$any_error) {
					$this->model->updateAddress($data['id_address'], $data);
					WNote::success('new_info_saved', WLang::get('new_info_saved'));
				}
			}
		}	
		$model['shops'] = $model['shops'] + $this->model->getShops($merchant_id);
		ksort($model['shops']);
		
		if(!isset($model['selected'])) {
			if(count($model['shops']) == 0) {
				$model['selected'] = 'new';
			} else {
				$model['selected'] = key($model['shops']);
			}
		}
		return $model;
	}
	
	/**
	* Profile edition
	* If post data, try to create or update a shop 
	**/	
	protected function profile() {		
		$this->view->setTheme('gtdn_merchant');
		$merchant_id = $_SESSION['userid'];
		
		$model = $this->model->getMerchant($merchant_id);
		if(WRequest::hasData()) {
			$data = WRequest::getAssocCheck(array(
									'nickname'=>array('type'=>'checkNickname', 'data' => $this->model), 
									'merchant_name' => WRequest::NOT_EMPTY, 
									'email' =>array('type'=>WRequest::REG_EXP, 'data' => '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i'), 
									'firstname' => WRequest::NOT_EMPTY, 
									'lastname' => WRequest::NOT_EMPTY));
			$contact_email = $this->retrieveEmail();
			if($contact_email === true) {
				$data['errors'][] = 'contact_email';
				$data['contact_email'] = array();
			} else {
				$data['contact_email'] = $contact_email;
			}
			$model = $data;
			if(!empty($data['errors'])) {
				foreach($data['errors'] as $name) {
					WNote::error('invalid_' . $name, WLang::get('invalid_' . $name));
				}
			} else {
				$this->model->updateMerchant($merchant_id, $data);
			}
		}	
		
		//Let's build the form !
		$base = array(
			'nickname' => array('label' => WLang::get('nickname_register'), 
							'type' => 'text', 
							'value' => $model['nickname']),
			'merchant_name' => array('label' => WLang::get('merchant_name'), 
							'type' => 'text', 
							'value' => $model['merchant_name']),
			'email' => array('label' => WLang::get('email'), 
							'type' => 'text', 
							'value' => $model['email']),
			'firstname' => array('label' => WLang::get('firstname'), 
							'type' => 'text', 
							'value' => $model['firstname']),
			'lastname' => array('label' => WLang::get('lastname'), 
							'type' => 'text', 
							'value' => $model['lastname'])
		);
			
		$form = array(
			'action' => '',
			'change' => '',
			'method' => 'POST',
			'submit_text' => 'Submit',
			'class' => 'form-horizontal wform',
			'nodes' => $base
		);
		
		WForm::assignForm($this->view, 'profile', $form);		
		
		return $model;
	}
	
	private function retrieveEmail() {
		$data = WRequest::getAssoc(array('contact_email'));
		$error = true;
		foreach($data['contact_email'] as $email) {	
			if(!empty($email) && !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) {
				$error = true;
			}
		}
		if($error || count($data['contact_email']) == 0) {
			return $error;
		}
		return $data['contact_email'];
	}
	
	/**
	* Check address
	**/
	private function checkAddress($data) {
		$error = false;
		if(!is_null($data['id_address']) && !empty($data['id_address']) && !$this->model->isValidId($data['id_address'], $_SESSION['userid'])) {
			//If address is set then it has to exist
			WNote::error('id_address_does_not_exist', WLang::get('id_address_does_not_exist'));
			$error = true;
		}
		
		if(is_null($data['address_name']) || empty($data['address_name'])) {
			//If address is set then it has to exist
			WNote::error('address_name_cannot_be_empty', WLang::get('address_name_cannot_be_empty'));
			$error = true;
		}
		
		if(is_null($data['address']) || empty($data['address'])) {
			//If address is set then it has to exist
			WNote::error('address_cannot_be_empty', WLang::get('address_cannot_be_empty'));
			$error = true;
		}
		
		if(is_null($data['lat']) || empty($data['lat']) || !is_numeric($data['lat']) ||
			is_null($data['lng']) || empty($data['lng']) || !is_numeric($data['lng'])) {
			//If address is set then it has to exist
			WNote::error('coords_invalid', WLang::get('coords_invalid'));
			$error = true;
		}
		
		if(is_null($data['opening_hours']) || empty($data['opening_hours'])) {
			//If address is set then it has to exist
			WNote::error('opening_hours_cannot_be_empty', WLang::get('opening_hours_cannot_be_empty'));
			$error = true;
		}
		
		if(is_null($data['tel']) || empty($data['tel'])) {
			//If address is set then it has to exist
			WNote::error('tel_cannot_be_empty', WLang::get('tel_cannot_be_empty'));
			$error = true;
		}
		
		return $error;
	}
	
	public function checkEmails(array $emails){
		return true;
	}
}

?>
