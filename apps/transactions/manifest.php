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
	
	<!-- Variable -->
	<app_config name="mail2client" default='apps/transactions/front/templates/mail2client.html'/>
	<app_config name="bcc_email" default='ludovic.vanhove@getthedealnow.com' />
	<app_config name="receiver_email" default='seller@paypalsandbox.com' />
	<app_config name="use_sandbox" default='true'/>
	<app_config name="paypal_username" default='test_api1.getthedealnow.com'/>
	<app_config name="paypal_password" default='1391347621'/>
	<app_config name="paypal_signature" default='A2IrunCdioRsLV6XIMPCj0qE48pKAMAcLUgdIPbI0l.ZglvqMbfiTcQc'/>
	<app_config name="paypal_currency_code" default='HKD'/>
	<app_config name="paypal_return" default='http://dev.getthedealnow.com/transactions/process/'/>
	<app_config name="paypal_cancel" default='http://dev.getthedealnow.com/transactions/cancel'/>
	
	<!-- Front actions -->
	<action default="default">ipn</action>
	<action default="default">process</action>
	
	<!-- Admin actions -->
	<admin>
		<action default="default" description="List of transactions">listing</action>
		<action default="default" description="Insert a transaction">manualTransaction</action>
		<action default="default" description="Edit">manualTransaction</action>
		<action description="Edit" menu="false">edit</action>
		<action description="Check" menu="false">check</action>
		<action description="Add" menu="false">add</action>
		<action description="Delete" menu="false">delete</action>
	</admin>
</app>
