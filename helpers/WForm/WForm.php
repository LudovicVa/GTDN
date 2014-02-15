<?php

defined('WITYCMS_VERSION') or die('Access denied');


class WForm {
	
	/**
	 * @var WTemplate WTemplate instance
	 */
	private static $tpl;
	
	
	/**
	* Array (
		'action' => url,
		'change' => url,
		'method' => POST || GET,
		'submit_text' => name,
		'nodes' => array(name_of_input => array('label' => name, 'type' => text, 'value' => value), ...)
		)
	**/
	public static function assignForm($view, $name, array $form_data) {	
		WTemplateCompiler::registerCompiler('WForm', array('WForm', 'compile_form'));			
		WTemplateCompiler::registerCompiler('WFormFields', array('WForm', 'compile_nodes'));		
		
		$view->assign('WForm_' . $name ,$form_data);
		$view->assign('WForm_' . $name ,$form_data);
	}
	
	private static $form_name;
	
	public static function compile_form($args) {	
		$body = "";
		if (!empty($args)) {
			$form_name = $args;
			$class = "form-horizontal wform";
			
			//Prepare Template compiler
			if (empty(self::$tpl)) {
				self::$tpl = WSystem::getTemplate();
			}		
			
			self::$form_name = $form_name;			
						
			$href = 'apps/config/admin/templates/form.html';
			$body = '<?php $this->tpl_vars[\'WForm\'] = $this->tpl_vars[\'WForm_' . $form_name . '\']; ?>' . "\n";
			$body .= self::$tpl->compile($href);
			
			
		}

		return $body;
	}
	
	private static $current_node_name;
	private static $current_form;
	
	public static function compile_nodes($args) {	
		//Prepare Template compiler
		if (empty(self::$tpl)) {
			self::$tpl = WSystem::getTemplate();
		}
			
		//In case it is included in the default template file, we use the ugly global var
		$current_form = self::$form_name;		
		if (!empty($args)) {
			$current_form = $args;
		}
		self::$current_form = $current_form;
		
		$body_by_type['text'] 			=	self::$tpl->compile( 'helpers/WForm/templates/input_text.html');
		$body_by_type['password'] 	=	$body_by_type['text'];
		$body_by_type['hidden'] 		=	self::$tpl->compile( 'helpers/WForm/templates/input_hidden.html');
		$body_by_type['select'] 			=	self::$tpl->compile( 'helpers/WForm/templates/input_select.html');
		$body_by_type['radio'] 			=	self::$tpl->compile( 'helpers/WForm/templates/input_radio.html');
		$body_by_type['checkbox'] 	=	self::$tpl->compile( 'helpers/WForm/templates/input_checkbox.html');
		
		$code = "\n". '<?php' ."\n";
		$code .= '$form_name = \'' . $current_form . "';\n";
		$code .= 'foreach($this->tpl_vars [\'WForm_'. $current_form . '\'][\'nodes\'] as $name=>$node):'."\n";
		$code .= '$node[\'name\'] = $name;'."\n";
		$code .= 'switch($node[\'type\']):'."\n";
		foreach($body_by_type as $key => $value) {
			$code .= 'case "'. $key . "\":\n";
			$code .= '$this->tpl_vars [\'WFormField\'] = $node;' . "?>\n";
			$code .= $value . "\n";
			$code .= "<?php break;\n";
		}
		$code .= "endswitch;\n";
		$code .= "endforeach; ?>\n";
		return $code;
	}
}

?>