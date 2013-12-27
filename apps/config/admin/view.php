<?php
/**
 * News Application - Admin View - /apps/news/admin/view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * NewsAdminView is the Admin View of the News Application
 * 
 * @package Apps
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3-19-04-2013
 */
class ConfigAdminView extends WView {
    public function __construct() {
        parent::__construct();

        // CSS for all views
        //$this->assign('css', '/apps/user/admin/css/user.css');
    }

    public function site_data($model) {
        $this->assign('js', '/apps/config/admin/js/form.js');
    }
}

?>
