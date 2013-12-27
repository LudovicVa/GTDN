<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Language Editor</name>
	
	<version>0.1</version>
	
	<!-- Last update date -->
	<date>20-10-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>
	
	<default_lang>fr</default_lang>
	
	<!-- Permissions
	TODO: add proper permission
	<permission name="" />-->
	
	<!-- Front pages
	<action default="default">listing</action>
	<action>detail</action>-->
	
	<!-- Admin pages -->
	<admin>
		<action desc="lang_edit" default="1">lang_edit</action>
	</admin>
</app>