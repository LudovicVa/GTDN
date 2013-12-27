<?php
/**
 * News Application - Admin Model - /apps/news/admin/model.php
 */

defined('IN_WITY') or die('Access denied');

// Include Front Model for inheritance
include_once APPS_DIR.'news'.DS.'front'.DS.'model.php';

/**
 * NewsAdminModel is the Admin Model of the News Application
 *
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class ConfigAdminModel {
    protected $db;

	public function __construct() {
        $this->db = WSystem::getDB();
	}

}

?>
