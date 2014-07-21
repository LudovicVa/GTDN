<?php
/**
 * User Application - Front View
 */

defined('IN_WITY') or die('Access denied');

/**
 * UserView is the Front View of the User Application.
 * 
 * @package Apps\User\Front
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.4.0-26-02-2013
 */
class TransactionsView extends WView {
	public function __construct() {
		parent::__construct();
		
	}
	
	public function process(array $model) {
		$this->assign($model);		
		$this->setTheme('gtdn_simple');
	}
}

?>
