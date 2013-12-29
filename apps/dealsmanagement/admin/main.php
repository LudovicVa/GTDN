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
class DealsManagementAdminController extends WController {
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
			array('id_deal', 'deal_name', 'merchant_name', 'start_time', 'end_time', 'price', 'original_price'), 
			'id_deal', 'ASC'
		));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		
		$model = array(
			'deals'         => $this->model->getDeals(($page-1)*$n, $n, $sort[0], $sort[1]),
			'current_page'  => $page,
			'stats'         => array(),
			'per_page'      => $n,
			'sorting_tpl'   => $sortingHelper->getTplVars()
		);
		
		// Users count
		$model['stats']['total'] = $this->model->countDeals();
		$model['stats']['request'] = $this->model->countDeals();
		
		return $model;
	}
	
	/**
	 * Get the merchants for lists
	 * 
	 * @return list of merchants
	 */	
	protected function merchants(array $params) {
		$merchants = $this->model->getMerchants();
		
		foreach($merchants as $key=>$merchant) {
			$value = $merchant['id_merchant'];
			$text = $merchant['name'];
			$this->view->push_content($key, array('value' => $value, 'text' => $text));
		}
		$this->view->respond();
	}
	
	/**
	* Editing
	**/	
	protected function edit(array $params) {
		$editor = WHelper::load('Editor');
		if(!isset($_POST['pk']) || !isset($_POST['name']) || !$value =  $_POST['value']) {
			WNote::error('invalid_request', WLang::get('invalid_request'));
		} else {
			$deal_id = $_POST['pk'];
			$edit = $this->model->isDealId($deal_id);
			if($edit) {
				$editor->edit($this, array(array('type'=>$_POST['name'], 'value'=>$_POST['value'])), $deal_id);
			} else {
				$editor->edit($this, array(array('type'=>$_POST['name'], 'value'=>$_POST['value'])));
			}
		}
	}
	
	public function save($values, $deal_id, $editor) {
		$errors = array();
		foreach($values as $data) {
			if(!isset($data['type']) || !isset($data['value'])) {
				array_push($errors, $editor->generateError('invalid_request'));
				return $errors;
			}
			$type = $data['type'];
			$value =  $data['value'];
			switch($type) {
				case 'deal_name':
					if(!$this->model->updateName($deal_id, $value)) {
						array_push($errors, $editor->generateError('deal_name_not_saved'));
					}					
					break;	
				case 'merchant':
					if(!$this->model->updateMerchant($deal_id, $value)) {
						array_push($errors, $editor->generateError('merchant_not_saved'));
					}
					break;
				case 'start_time':
					if(!$this->model->updateStartTime($deal_id, $value)) {
						array_push($errors, $editor->generateError('start_time_not_saved'));
					}
					break;
				case 'end_time':
					if(!$this->model->updateEndTime($deal_id, $value)) {
						array_push($errors, $editor->generateError('end_time_not_saved'));
					}
					break;					
				case 'price':
					if(!$this->model->updatePrice($deal_id, $value)) {
						array_push($errors, $editor->generateError('price_not_saved'));
					}
					break;
				case 'original_price':
					if(!$this->model->updateOriginalPrice($deal_id, $value)) {
						array_push($errors, $editor->generateError('original_price_not_saved'));
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
			$type = $data['type'];
			$value =  $data['value'];
			switch($type) {
				case 'deal_name':
					if(!($e = $this->isName($value))) {
						array_push($errors,$editor->generateError('deal_name'));
					}
					break;	
				case 'merchant':
					if(!$this->model->isMerchantId($value)) {
						array_push($errors,$editor->generateError('merchant_unknown'));
					}
					break;
				case 'start_time':
					//TODO
					break;
				case 'end_time':
					//TODO
					break;					
				case 'price':
					if(!is_numeric($value)) {
						array_push($errors,$editor->generateError('invalid_price'));
					}
					break;
				case 'original_price':
					if(!is_numeric($value)) {
						array_push($errors,$editor->generateError('invalid_price'));
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
		if(!$this->model->isDealId($id)) {
			return array($editor->generateError('deal_does_not_exist'));
		}
		return array();
	}
	
	protected function add(array $params) {
		$type = $params[0];
		switch($type) {
			case 'deal':
				$id = $this->addDeal($_POST);
				if(isset($id)) {
					$this->view->success($id);
				}
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
			case 'deal':
				$id = $_POST['pk'];
				if($this->model->deleteDeal($id)) {
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
	
	private function addDeal(array $params) {
		error_log(serialize($params));
		if(!$this->isName($params['deal_name'])) {
			$this->view->error(WLang::get('deal_name_required'));
			return;
		}
		
		if(!$this->model->isMerchantId($params['merchant']))	 {
			$this->view->error(WLang::get('merchant_unknown'));
			return;
		}
		
		if(!is_numeric($params['price']) || !is_numeric($params['original_price'])) {
			$this->view->error(WLang::get('price_not_a_number'));
			return;
		}
		
		return $this->model->createDeal($params);
	}
}

?>
