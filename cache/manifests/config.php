<?php

$manifest = array (
  'name' => 'Configuration',
  'version' => '0.1',
  'date' => '10-01-2014',
  'icone' => '',
  'actions' => 
  array (
  ),
  'admin' => 
  array (
    'witycms' => 
    array (
      'description' => 'WityCMS Config',
      'menu' => true,
      'requires' => 
      array (
      ),
    ),
    'apps' => 
    array (
      'description' => 'Apps Config',
      'menu' => true,
      'requires' => 
      array (
      ),
    ),
  ),
  'admin_menu' => true,
  'default_admin' => 'witycms',
  'permissions' => 
  array (
    0 => 'admin',
    1 => 'config_cms',
    2 => 'config_app',
  ),
  'default_lang' => '',
);

?>