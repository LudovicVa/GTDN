<?php

$manifest = array (
  'name' => 'Merchant Manager',
  'version' => '0.1',
  'date' => '22-10-2013',
  'icone' => '',
  'actions' => 
  array (
    'login' => 
    array (
      'description' => 'login',
      'requires' => 
      array (
      ),
    ),
  ),
  'default' => 'login',
  'alias' => 
  array (
    'connexion' => 'login',
  ),
  'admin' => 
  array (
    'action_1' => 
    array (
      'description' => 'Action 1',
      'menu' => true,
      'requires' => 
      array (
      ),
    ),
  ),
  'admin_menu' => true,
  'default_admin' => 'action_1',
  'permissions' => 
  array (
    0 => 'admin',
    1 => 'add',
    2 => 'edit',
    3 => 'delete',
    4 => 'group_manager',
    5 => 'config',
  ),
  'default_lang' => '',
);

?>