<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Merchant Manager</name>
	
	<version>0.1</version>
	
	<!-- Last update date -->
	<date>22-10-2013</date>
	
	<!-- Permissions -->
	<permission name="add" />
	<permission name="edit" />
	<permission name="delete" />
	<permission name="group_manager" />
	<permission name="config" />
	
	<!-- Front actions -->
	<action default="default" alias="connexion">login</action>
	
	<!-- Admin actions -->
	<admin>
		<action default="default" description="Action 1">action_1</action>
	</admin>
</app>
