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
class DealsManagementController extends WController {

	const FORMAT   = 'd/m/Y H:i';
	
	/**
	*	List all the deals
	**/	
	protected function dealslisting(array $params) {
		if(WRequest::hasData()) {
			$data = WRequest::getAssoc(array('lat', 'long', 'dist'));
			if(!is_null($data['lat']) && !is_null($data['long'])) {
				$deals = $this->model->getDealWithin($data['lat'], $data['long'], !is_null($data['dist'])?$data['dist']:5000);
			} else {
				$deals =  $this->model->getDealWithin();
			}
		} else {
			$deals =  $this->model->getDealWithin();
		}
		
		//$this->view->assign('deals', $deals);
		
		return $deals;
	}
	
	/**
	* Edit the deal of a specific merchants
	**/
	protected function editdeals(array $params) {
		$merchant_id = $_SESSION['userid'];
		$this->view->setTheme('gtdn_merchant');
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
			array('id_deal', 'deal_name', 'merchant_name', 'start_time', 'end_time', 'price', 'original_price'), 
			'id_deal', 'ASC'
		));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		
		$model = array(
			'deals'         => $this->model->getDeals(($page-1)*$n, $n, $sort[0], $sort[1], array('id_user' => $merchant_id)),
			'current_page'  => $page,
			'stats'         => array(),
			'per_page'      => $n,
			'sorting_tpl'   => $sortingHelper->getTplVars()
		);
		
		// Users count
		$model['stats']['request'] = $this->model->countDeals(array('id_user' => $merchant_id));
		$model['stats']['total'] = $this->model->countDeals();
		
		return $model;
	}
	
	/**
	* No param => add
	**/
	protected function editdeal(array $params) {
		$this->view->setTheme('gtdn_merchant');
		$merchant_id = $_SESSION['userid'];
				
		$model = array();		
		//Id find
		if(isset($params[0])) {
			$id = $params[0];		
		} else if(isset($_REQUEST['id'])) {
			$id = $_REQUEST['id'];
		} else {
			$id = null;
		}
		
		//Check validity of id
		if(!$this->model->isDealId($id, $merchant_id)) {
			$id = null;
		}
		
		//get deal info
		if (!empty($id)) {
			$model = $this->model->getDealFromMerchant($id, $merchant_id);
			$model['id'] = $id;
		}
		
		//Shops		
		$model['all_shop'] = $this->model->getShopsFromMerchant($merchant_id);
		
		if(WRequest::hasData()) {
			$data = WRequest::getAssoc(array('deal_name', 'price', 'original_price', 'start_time', 'end_time', 'description', 'shop'));

			if(!empty($id)) {
				//only updating
				$any_error = $this->check($data, $model);
				$image = $_FILES['image'];
				///upload
				$file_name = null;
				if(isset($image) && $image['error'] == 0) {
					$file_name = $this->upload($id, $image);
				}
				if(!$any_error && $file_name !== false) {
					if($file_name != null)  { $data['images'] = $file_name; }
					$this->model->update($id, $data);
					WNote::success('new_info_saved', WLang::get('new_info_saved'));
				}
			} else {
				//create new deal
				$any_error = $this->check($data);
				$image = $_FILES['image'];
				$file_name = null;
				if(isset($image) && $image['error'] == 0) {
					$file_name = $this->upload($id, $image);
					$data['images'] = $file_name;
				} else {
					WNote::error('no_images', WLang::get('no_images'));
				}
				if(!$any_error && $file_name !== false && $file_name != null) {
					$data['images'] 	= $file_name;
					$data['id_user'] 	= $merchant_id;
					$data['id'] 				= $this->model->createDeal($data);
					WNote::success('deal_created', WLang::get('deal_created'));
				}
			}
			$model = array_replace($model, $data);
		}	
		

		//echo "</pre>";
		return $model;
	}
	
	private function check(&$data, $model = null) {
		$error = false;
		if(is_null($data['deal_name']) || empty($data['deal_name'])) {
			WNote::error('invalid_deal_name', WLang::get('invalid_deal_name'));
			$error = true;
		}
		
		$error_price = false;
		if(is_null($data['price']) || empty($data['price']) || !is_numeric($data['price'])) {
			WNote::error('invalid_price', WLang::get('invalid_price'));
			$error = true;
			$error_price = true;
		}		
		if(is_null($data['original_price']) || empty($data['original_price']) || !is_numeric($data['price'])) {
			WNote::error('invalid_original_price', WLang::get('invalid_original_price'));
			$error = true;
			$error_price = true;
		}
		
		if(!$error_price) {
			if($data['original_price'] < $data['price']) {
				WNote::error('price_above_original', WLang::get('price_above_original'));
				$error = true;
			}
		}	
		
		$error_time = false;
		if(isset($model['start_time'])) {
			$old_start_time = date_create_from_format(self::FORMAT, $model['start_time']);
		} else {
			$old_start_time = false;
		}
		if($old_start_time && $old_start_time->getTimestamp() < time()) {
			if(!is_null($data['start_time']) && !empty($data['start_time'])) {
				WNote::error('change_start_after_deal_begin', WLang::get('change_start_after_deal_begin'));
				$error = true;
				$error_time = true;
			}
			//Anyway, set to model value
			$data['start_time'] = $model['start_time'];
			$start_time = $old_start_time;
		} elseif(is_null($data['start_time']) || empty($data['start_time']) || !(date_create_from_format(self::FORMAT, $data['start_time']))) {
			WNote::error('invalid_start_time', WLang::get('invalid_start_time'));
			$error = true;
			$error_time = true;
		} elseif (date_create_from_format(self::FORMAT, $data['start_time'])->getTimestamp() < time()) {
			WNote::error('start_time_before_now', WLang::get('start_time_before_now'));
			$error = true;
			$error_time = true;
		} else {
			$start_time = date_create_from_format(self::FORMAT, $data['start_time']);
		}
		
		//Time check
		if(isset($model['end_time'])) {
			$old_end_time = date_create_from_format(self::FORMAT, $model['end_time']);
		} else {
			$old_end_time = false;
		}
		if($old_end_time && $old_end_time->getTimestamp() < time()) {
			//End time WAS before now, cannot edit
			WNote::error('cannot_edit_finished_deal', WLang::get('cannot_edit_finished_deal'));
			$error = true;
			$error_time = true;
		} elseif(is_null($data['end_time']) || empty($data['end_time']) || !(date_create_from_format(self::FORMAT, $data['end_time']))) {
			WNote::error('invalid_end_time', WLang::get('invalid_end_time'));
			$error = true;
			$error_time = true;
		} elseif (date_create_from_format(self::FORMAT, $data['end_time'])->getTimestamp() < time()) {
			WNote::error('end_time_before_now', WLang::get('end_time_before_now'));
			$error = true;
			$error_time = true;
		} else {		
			$end_time = date_create_from_format(self::FORMAT, $data['end_time']);
		}
		
		if(!$error_time) {
			if($end_time->getTimestamp() < $start_time->getTimestamp()) {
				WNote::error('end_before_start', WLang::get('end_before_start'));
				$error = true;
			}
		}
		
		//Check shop validity
		if(is_null($data['shop']) || empty($data['shop']) || !is_array($data['shop'])) {
			WNote::error('invalid_shop', WLang::get('invalid_shop'));
			$error = true;
		} elseif(count($data['shop']) == 0) {
			WNote::error('at_least_one_shop', WLang::get('at_least_one_shop'));
			$error = true;
		} else {
			foreach($data['shop'] as $shop) {
				if($shop != 'multiselect-all' && !$this->model->isValidAddress($shop, $_SESSION['userid'])) {
					WNote::error('invalid_shop', WLang::get('invalid_shop'));
					$error = true;
				}
			}
		}
		return $error;
	}
	
	private function upload($id, $image) {
		$img_uploader =  WHelper::load('upload', array($image));
		if ($img_uploader->uploaded) {
			$img_uploader->file_new_name_body = $id;
			$img_uploader->image_resize = true;
			$img_uploader->image_convert = 'jpeg';
			$img_uploader->file_overwrite = true;
			$img_uploader->image_x = 350;
			$img_uploader->image_ratio_y = true;
			$img_uploader->Process(getcwd() . DS . "deals_images");
			if ($img_uploader->processed) {
				$full_path = $img_uploader->file_dst_name; 
				$img_uploader->Clean();
				return $full_path;
			  } else {
				WNote::error('upload_error', WLang::get('upload_error'));
				WNote::error('upload_error', "Error when upload a file " . print_r($image, true) ." " . $img_uploader->error, 'email');
				return false;
			  }
		} else {
			WNote::error('upload_error', WLang::get('upload_error'));
			WNote::error('upload_error', "Error when upload a file " . $img_uploader->error, 'email');
			return false;
		}
	}
}

?>