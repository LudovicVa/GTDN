<?php

defined('WITYCMS_VERSION') or die('Access denied');


class WForm {
	private static $forms = array();
	
	/**
	* Array (
		'action' => url,
		'change' => url,
		'method' => POST || GET,
		'submit_text' => name,
		'nodes' => array(name_of_input => array('label' => name, 'type' => text, 'value' => value), ...)
		)
	**/
	public static function assignForm($name, array $form_nodes) {	
		WTemplateCompiler::registerCompiler('WForm', array('WForm', 'compile_form'));
		
		if(!empty($name)) {
			self::$forms[$name] = &$form_nodes;
		}
	}
	private static $form_name;
	public static function compile_form($args) {	
		if (!empty($args)) {
			$form_name = $args;
			$class = "form-horizontal wform";
						
			if(!isset(self::$forms[$form_name])) {
				throw new Exception("WForm::compile_form(): no form exists for  {" .$form_name. "}." . print_r(self::$forms, true));
			}
			
			WTemplateCompiler::registerCompiler('WNodes', array('WForm', 'compile_nodes'));
			self::$form_name = $form_name;
			
			$form = self::$forms[$form_name];
			$nodes = $form['nodes'];
			
			//Prepare Template compiler
			if (empty(self::$tpl)) {
				self::$tpl = WSystem::getTemplate();
			}
			
			self::$tpl->pushContext();

			self::$tpl->assign($form);
			self::$tpl->assign('form_name', $form_name);
			self::$tpl->assign('class', $class);
			
			$body = 'apps/config/admin/templates/form.html';
			$body = self::$tpl->parse($body);
			
			self::$tpl->popContext();
			
			WTemplateCompiler::unregisterCompiler('WNodes');
			// Replace the template variables in the string
			/*$str = '<form method="' . $form['method'] . '" enctype="multipart/form-data" action="' . (isset($form['action'])?$form['action']:'') . '" class="'. $class .'" data-url="'. $form['change'] . '">';

			
			foreach($nodes as $name => $node) {
				$str .= self::node2html($name, $node);
			}
			
			$str .= '<button type="submit" class="btn btn-default">'. $form['submit_text'] .'</button>';
			 
			$str .= '</form>';*/
		}

		return $body;
	}
	
	public static function compile_nodes($args) {	
		$form_name = self::$form_name;		
		
		$form = self::$forms[$form_name];
		$nodes = $form['nodes'];
		$str = "";
		foreach($nodes as $name => $node) {
			$str .= self::node2html($name, $node);
		}

		return $str;
	}
	
	/**
	 * @var WTemplate WTemplate instance
	 */
	private static $tpl;
	
	
	private static function node2html($name, array $node) {
		//Prepare Template compiler
		if (empty(self::$tpl)) {
			self::$tpl = WSystem::getTemplate();
		}

		self::$tpl->pushContext();

		self::$tpl->assign($node);
		self::$tpl->assign('name', $name);
		
		$body = 'apps/config/admin/templates/input_text.html';
			
		switch($node['type']) {
			case 'text':
			case 'password':
				$body = self::$tpl->parse($body);
				break;
			case 'select':			
				break;
			default:
				$str .= '<input type="text" class="form-control" name="'. $name. '" id="'. $name. '" '. (isset($node['value'])?'value="' .$node['value'] . '"':'') . '>';
				break;
		}
		
		self::$tpl->popContext();
		return $body;
	}
}

?>