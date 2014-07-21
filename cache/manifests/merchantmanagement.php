<?php

$manifest = array (
  'name' => 'Merchant Manager',
  'version' => '0.1',
  'date' => '22-10-2013',
  'icone' => '',
  'actions' => 
  array (
    'shops' => 
    array (
      'description' => 'shops',
      'requires' => 
      array (
        0 => 'merchant',
      ),
    ),
    'profile' => 
    array (
      'description' => 'profile',
      'requires' => 
      array (
        0 => 'merchant',
      ),
    ),
  ),
  'default' => 'shops',
  'admin' => 
  array (
    'listing' => 
    array (
      'description' => 'List of Merchants',
      'menu' => true,
      'requires' => 
      array (
      ),
    ),
    'edit' => 
    array (
      'description' => 'Edit',
      'menu' => false,
      'requires' => 
      array (
      ),
    ),
    'check' => 
    array (
      'description' => 'Check',
      'menu' => false,
      'requires' => 
      array (
      ),
    ),
    'add' => 
    array (
      'description' => 'Add',
      'menu' => false,
      'requires' => 
      array (
      ),
    ),
    'delete' => 
    array (
      'description' => 'Delete',
      'menu' => false,
      'requires' => 
      array (
      ),
    ),
  ),
  'admin_menu' => true,
  'default_admin' => 'listing',
  'permissions' => 
  array (
    0 => 'admin',
    1 => 'merchant',
  ),
  'default_lang' => '',
);

?>