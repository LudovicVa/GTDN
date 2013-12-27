<?php

$manifest = array (
  'name' => 'User',
  'version' => '0.4.0',
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
    'logout' => 
    array (
      'description' => 'logout',
      'requires' => 
      array (
        0 => 'connected',
      ),
    ),
    'register' => 
    array (
      'description' => 'register',
      'requires' => 
      array (
        0 => 'not-connected',
      ),
    ),
    'confirm' => 
    array (
      'description' => 'confirm',
      'requires' => 
      array (
        0 => 'not-connected',
      ),
    ),
    'password_lost' => 
    array (
      'description' => 'password_lost',
      'requires' => 
      array (
        0 => 'not-connected',
      ),
    ),
  ),
  'default' => 'login',
  'alias' => 
  array (
    'connexion' => 'login',
    'deconnexion' => 'logout',
    'password-lost' => 'password_lost',
  ),
  'admin' => 
  array (
    'listing' => 
    array (
      'description' => 'action_listing',
      'menu' => true,
      'requires' => 
      array (
      ),
    ),
    'add' => 
    array (
      'description' => 'action_add',
      'menu' => true,
      'requires' => 
      array (
        0 => 'add',
      ),
    ),
    'edit' => 
    array (
      'description' => 'action_edit',
      'menu' => false,
      'requires' => 
      array (
        0 => 'edit',
      ),
    ),
    'delete' => 
    array (
      'description' => 'action_delete',
      'menu' => false,
      'requires' => 
      array (
        0 => 'delete',
      ),
    ),
    'groups' => 
    array (
      'description' => 'action_groups',
      'menu' => true,
      'requires' => 
      array (
        0 => 'group_manager',
      ),
    ),
    'group_del' => 
    array (
      'description' => 'action_group_del',
      'menu' => false,
      'requires' => 
      array (
        0 => 'group_manager',
        1 => 'delete',
      ),
    ),
    'group_diff' => 
    array (
      'description' => 'group_diff',
      'menu' => false,
      'requires' => 
      array (
        0 => 'group_manager',
        1 => 'edit',
      ),
    ),
    'load_users_with_letter' => 
    array (
      'description' => 'load_users_with_letter',
      'menu' => false,
      'requires' => 
      array (
        0 => 'group_manager',
        1 => 'edit',
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
  'default_admin' => 'listing',
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