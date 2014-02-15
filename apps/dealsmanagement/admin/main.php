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
		
		$super_admin = $_SESSION['access'] == 'all';
		if(!$super_admin) {
			$merchant_id = $this->model->getMerchantIdFromUser($_SESSION['userid']);
		} else {
			$merchant_id = false;
		}
		
		//Sorting data
		$sortingHelper = WHelper::load('SortingHelper', array(
			array('id_deal', 'deal_name', 'merchant_name', 'start_time', 'end_time', 'price', 'original_price'), 
			'id_deal', 'ASC'
		));
		$sort = $sortingHelper->findSorting($sort_by, $sens);
		
		$model = array(
			'deals'         => $this->model->getDeals(($page-1)*$n, $n, $sort[0], $sort[1], $merchant_id?array('id_user' => $merchant_id):array()),
			'current_page'  => $page,
			'stats'         => array(),
			'per_page'      => $n,
			'sorting_tpl'   => $sortingHelper->getTplVars(),
			'super_admin' => $super_admin, //$this->getAdminContext()
			'merchant_id' => $merchant_id,
		);
		
		// Users count
		$model['stats']['total'] = $this->model->countDeals();
		$model['stats']['request'] = $model['stats']['total'];
		
		return $model;
	}
	
	/**
	 * Get the merchants for lists
	 * 
	 * @return list of merchants
	 */	
	protected function merchants(array $params) {
		$merchants = $this->model->getMerchants();
		//header('Content-Type: application/json');
		$result = array();
		foreach($merchants as $key=>$merchant) {
			$value = $merchant['id_user'];
			$text = $merchant['name'];
			$result[$value] = $text;
		}
		echo json_encode($result);
		exit(0);
	}
	
	/**
	* Editing
	**/	
	protected function edit(array $params) {
		header('Content-Type: application/json');
		$editor = WHelper::load('editor');
		if(!isset($_POST['pk']) || !isset($_POST['name']) || !isset($_POST['value'])) {
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
		foreach($values as $key=>$data) {
		
			if(is_array($data)) {
				if(!isset($data['type']) || !isset($data['value'])) {
					array_push($errors, $editor->generateError('invalid_request'));
					return $errors;			
				} else {
					$type = $data['type'];
					$value = $data['value'];
				}
			} else {
				$type = $key;
				$value = $data;
			}
			
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
		foreach($values as $key=>$data) {
		
			if(is_array($data)) {
				if(!isset($data['type']) || !isset($data['value'])) {
					array_push($errors, $editor->generateError('invalid_request'));
					return $errors;			
				} else {
					$type = $data['type'];
					$value = $data['value'];
				}
			} else {
				$type = $key;
				$value = $data;
			}
			
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
	
	/**
	*
	**/
	public function isValidRecordId($id, $editor) {
		if(!$this->model->isDealId($id)) {
			return array($editor->generateError('deal_does_not_exist'));
		}
		return array();
	}
	
	/**
	* Add action
	**/
	protected function add(array $params) {
		header('Content-Type: application/json');
		$type = $params[0];
		switch($type) {
			case 'deal':
				$result = $this->addDeal($_POST);
				
				if(isset($result) && is_array($result)) {
					WNote::success('id', $result['id']);
					WNote::success('paypal_button', $result['paypal_id']);
					WNote::success('success', WLang::get('deal_successfully_added'));
				} else {
					WNote::error('problem_while_validating', 'problem_while_validating');
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
			case 'deal':
				$id = $_POST['pk'];
				if($this->model->deleteDeal($id)) {			
					WNote::success('success', WLang::get('deal_successfully_delete'));
				} else {
					WNote::error('unknown_error', WLang::get('unknown_error'));
				}
				break;
			default:
				WNote::error('unrecognized_command', WLang::get('unrecognized_command'));
				break;
		}
	}
	
	private function isName($name) {
		return $name != null && $name != "";
	}
	
	/**
	* Add a deal
	**/
	private function addDeal(array $params) {
		$errors = $this->check($params,  WHelper::load('editor'));
		
		if(count($errors) != 0) {
			foreach($errors as $note) {
				WNote::raise($note);
			}
			return false;
		}
		
		return $this->model->createDeal($params);
	}
	
//Email
	/**
	 * @var WTemplate WTemplate instance
	 */
	private static $tpl;
	
	public function email_edit(array $params) {
		if (WRequest::hasData()) {
			$received = WRequest::getAssoc(array('id', 'html_body'));
			
			//If not empty id and numeric, edit or display specific email
			if(!empty($received['id']) && is_numeric($received['id'])) {				
				$model = $this->retrieveDealInfo($received['id']);
				$model['id'] = $received['id'];
				
				$model['title'] = 'Customize email for the deal : ' . $model['deal_name'];	
				
				//If new body submitted, treat it
				if(!empty($received['html_body'])) {	
					//first thing first, put back the body into the form
					$body = $received['html_body'];	
					
					//Check if body contains {$voucher}
					$pos = strpos($received['html_body'], "{\$voucher}");
					if($pos === false) {
						//if note, don't save
						WNote::error("voucher_not_present", WLang::get("voucher_not_present"));
					} else {
						$subject = "Get The Deal Now - Get The Deal Now!";
						//alright, we can save !
						if($this->model->updateEmail2Customer($model['id'], $subject, $received['html_body'])) {
							WNote::success("successfully_saved", WLang::get("successfully_saved"));
						} else {
							WNote::error("unknown_error_during_mail_edtion", WLang::get("unknown_error_during_mail_edtion"));
						}
					}
				} else {					
					$body = $model['email2customer']['email_body'];
				}
				
				//Prepare Template compiler
				if (empty(self::$tpl)) {
					self::$tpl = WSystem::getTemplate();
				}

				self::$tpl->pushContext();

				// Assign View variables
				$model['voucher'] = "<b>{\$voucher}</b>";
				$model['firstname'] = "<b>{\$firstname}</b>";
				$model['lastname'] = "<b>{\$lastname}</b>";
				self::$tpl->assign($model);
				
				if (substr($body, -5) === '.html' && file_exists(WITY_PATH.$body)) {
					// Use system directory separator
					if (DS != '/') {
						$body = str_replace('/', DS, $body);
					}
					$body = self::$tpl->parse($body);
				} else {
					$body = self::$tpl->parseString($body);
				}
				
				$body = trim(preg_replace('/\s+/', ' ', $body));
				$body = preg_replace('#(<script.*?>).*?(</script>)#', '', $body);
				$model['email_body'] = $body;
			
				self::$tpl->popContext();
			}
			
			return $model;
		} else {				
			//Default email
			$model['title'] = 'Edit default email';				
			$model = $this->model->retrieveDefaultEmail();
			
			return $model;
		}
	}
	
	private function retrieveDealInfo($id) {
		return $this->model->getDealInfo($id);
	}
}

?>
