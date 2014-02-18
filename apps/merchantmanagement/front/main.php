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
		
		
		if(WRequest::hasData()) {
			$data = WRequest::getAssoc(array('id_address', 'address_name', 'address', 'lat', 'lng', 'opening_hours', 'tel'));
			$any_error = $this->check($data);
			
			if(is_null($data['id_address']) && empty($data['id_address'])) {
				$any_error = $this->check($data);
				if(!$any_error) {
					$id = $this->model->createAddress($data['id_address']);
					$model['selected'] = $id;
					WNote::success('shop_created', WLang::get('shop_created'));
				} else {
					$model = $data;
					$model['selected'] = 'new';
				}
			} else {
				//update
				$any_error = $this->check($data);
				if(!$any_error) {
					$this->model->update($data['id_address'], $data);
					$model['selected'] = $data['id_address'];
					WNote::success('new_info_saved', WLang::get('new_info_saved'));
				}
			}
		}	
		
		$model['shops'] = $this->model->getShops($merchant_id);
		if(!isset($model['selected'])) {
			if(count($model['shops']) == 0) {
				$model['selected'] = 'new';
			} else {
				$model['selected'] = $model['shops'][0]['id_address'];
			}
		}
		return $model;
	}
	
	private function check($data) {
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
		
		if(is_null($data['lat']) || empty($data['lat']) || !is_numeric($data['lat'])) {
			//If address is set then it has to exist
			WNote::error('lat_invalid', WLang::get('lat_invalid'));
			$error = true;
		}
		
		if(is_null($data['lng']) || empty($data['lng']) || !is_numeric($data['lng'])) {
			//If address is set then it has to exist
			WNote::error('lng_invalid', WLang::get('lng_invalid'));
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
}

?>
