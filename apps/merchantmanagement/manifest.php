<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Merchant Manager</name>
	
	<version>0.1</version>
	
	<!-- Last update date -->
	<date>22-10-2013</date>
	
	<!-- Permissions -->
	<permission name="merchant" />
	
	<!-- Front actions -->
	<action default="default" requires="merchant">shops</action>
	
	<!-- Admin actions -->
	<admin>
		<action default="default" description="List of Merchants">listing</action>
		<action description="Edit" menu="false">edit</action>
		<action description="Check" menu="false">check</action>
		<action description="Add" menu="false">add</action>
		<action description="Delete" menu="false">delete</action>
	</admin>
</app>
