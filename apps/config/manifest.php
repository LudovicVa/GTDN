<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Configuration</name>
	
	<version>0.1</version>
	
	<!-- Last update date -->
	<date>16-10-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>
	
	<default_lang>fr</default_lang>
	
	<!-- Permissions
	TODO: add proper permission-->
	<permission name="??" />
	
	<!-- Front pages
	<action default="default">listing</action>
	<action>detail</action>-->
	
	<!-- Admin pages -->
	<admin>
		<action desc="site" default="1">site_data</action>
		<action desc="database" >database</action>
	</admin>
</app>