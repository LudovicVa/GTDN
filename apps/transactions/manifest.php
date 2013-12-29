<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Transactions</name>
	
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
		<action default="default" description="List of transactions">listing</action>
		<action description="Edit" menu="false">edit</action>
		<action description="Check" menu="false">check</action>
		<action description="Add" menu="false">add</action>
		<action description="Delete" menu="false">delete</action>
	</admin>
</app>
