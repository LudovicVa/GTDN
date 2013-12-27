<?php

$manifest = array (
  'name' => 'Contact',
  'version' => '0.4',
  'date' => '02-10-2013',
  'icone' => '',
  'actions' => 
  array (
    'form' => 
    array (
      'description' => 'form',
      'requires' => 
      array (
      ),
    ),
  ),
  'default' => 'form',
  'admin' => 
  array (
    'mail_history' => 
    array (
      'description' => 'mail_history',
      'menu' => true,
      'requires' => 
      array (
      ),
    ),
    'mail_detail' => 
    array (
      'description' => 'mail_detail',
      'menu' => false,
      'requires' => 
      array (
      ),
    ),
    'config' => 
    array (
      'description' => 'action_config',
      'menu' => true,
      'requires' => 
      array (
        0 => 'config',
      ),
    ),
  ),
  'admin_menu' => true,
  'default_admin' => 'mail_history',
  'permissions' => 
  array (
    0 => 'admin',
  ),
  'default_lang' => '',
);

?>