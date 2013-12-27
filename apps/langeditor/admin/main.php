<?php
/**
 * News Application - Admin Controller - /apps/news/admin/main.php
 */

defined('WITYCMS_VERSION') or die('Access denied');

/**
 * NewsAdminController is the Admin Controller of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class LangEditorAdminController extends WController {
	public function __construct() {
		include 'model.php';
		$this->model = new LangEditorAdminModel();
		
		include 'view.php';
		$this->setView(new LangEditorAdminView());
	}
	
	/**
	 * Handle Lang Edit action
	 */
	protected function lang_edit(array $params) {
        //Check params
        if(sizeof($params) == 0 || !WRetriever::isApp($params[0])) {
            WNote::error("notAnApp", "The paramater is not an app");
            return array();
        }

        $app = $params[0];
        //Retrieve default lang

		//Return model
		return array();
	}
}

?>
