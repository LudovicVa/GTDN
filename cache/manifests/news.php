<?php

$manifest = array (
  'name' => 'News',
  'version' => '0.4.0',
  'date' => '22-10-2013',
  'icone' => '',
  'actions' => 
  array (
    'listing' => 
    array (
      'description' => 'listing',
      'requires' => 
      array (
      ),
    ),
    'detail' => 
    array (
      'description' => 'detail',
      'requires' => 
      array (
      ),
    ),
  ),
  'default' => 'listing',
  'admin' => 
  array (
    'listing' => 
    array (
      'description' => 'articles_listing',
      'menu' => true,
      'requires' => 
      array (
      ),
    ),
    'add' => 
    array (
      'description' => 'article_add',
      'menu' => true,
      'requires' => 
      array (
        0 => 'writer',
      ),
    ),
    'edit' => 
    array (
      'description' => 'article_edit',
      'menu' => false,
      'requires' => 
      array (
        0 => 'writer',
      ),
    ),
    'news_delete' => 
    array (
      'description' => 'article_delete',
      'menu' => false,
      'requires' => 
      array (
        0 => 'moderator',
      ),
    ),
    'categories_manager' => 
    array (
      'description' => 'categories_management',
      'menu' => true,
      'requires' => 
      array (
        0 => 'category_manager',
      ),
    ),
    'category_delete' => 
    array (
      'description' => 'category_delete',
      'menu' => false,
      'requires' => 
      array (
        0 => 'category_manager',
        1 => 'moderator',
      ),
    ),
  ),
  'admin_menu' => true,
  'default_admin' => 'listing',
  'permissions' => 
  array (
    0 => 'admin',
    1 => 'writer',
    2 => 'category_manager',
    3 => 'moderator',
  ),
  'default_lang' => 'fr',
);

?>