<?php
/**
 * @package Zefir_View_Template
 */
/**
 * An extension to the default Zend_View class
 * @author zefiryn
 */
class Zefir_View_Template extends Zend_View
{
	/**
	 * CONSTRUCT THE OBJECT AND SET ALL THE VARIABLES
	 * 
	 * @param array $config
	 */
	
	public function __construct($config = array())
	{
		//construct a Zend_View object
		parent::__construct($config);
		
		//get the chosen template
		$template = $this->_getTemplateChoice();
		
		//set path to the template view files
		$this->setBasePath(APPLICATION_PATH.'/views/'.$template);
		
		//start the layout
		$layout = Zend_Layout::startMvc();
		$layout->setLayout($template.'_layout');
		
		//set the template name variable in the template
		$this->template = $template;
		$this->addHelperPath(APPLICATION_PATH.'/views/'.$template.'/helpers/', 'Helper');
		
		$options = Zend_Registry::get('options');
		//add basic js script for the current template
        if (isset($options['js'][$template]))
        {
        	foreach ($options['js'][$template] as $name)
        		$this->headScript()->prependFile($options['resources']['frontController']['baseUrl'].'js/'.$template.'/'.$name);
        }
        
	}
	
	/**
	 * GET THE TEMPLATE NAME AND CHECK IF IT IS CORRECT
	 * The template name is search in the following order:
	 * POST, GET, COOKIE, SESSION, USER_SETTINGS, DEFAULT (in application.ini)
	 * @access private
	 * @return string template name
	 */
	private function _getTemplateChoice()
	{
		$options = Zend_Registry::get('options');
		$auth = Zend_Auth::getInstance();
		$templateSession = new Zend_Session_Namespace('template');
		
		//set template name
		if (isset($_POST['template_name']))
			$send_tpl = $_POST['template_name'];
		
		elseif (isset($_GET['template_name']))
			$send_tpl = $_GET['template_name'];
			
		elseif (isset($_COOKIE['template_name']))
			$send_tpl = $_COOKIE['template_name'];
		
		elseif (isset($templateSession->template_name))
			$send_tpl = $templateSession->template_name;

		else
			$send_tpl = $options['view']['default_template'];
		
		//check if the template is correct
		if ($this->_templateExists($send_tpl))
			$template = $send_tpl;
		else
			$template = $options['view']['default_template'];

		//set current value in cookie and in session
		setcookie("template_name",$template, time() + (60*60*24*14) , '/');
		$templateSession->template_name = $template;
		
		return $template;
	}
	
	/**
	 * Check if there is a layout file and folder for the given template
	 * @access private
	 * @param string $name
	 * @return boolean whether template layout and template view folder exists
	 */
	private function _templateExists($name)
	{
		
		$template = new Application_Model_TemplateSettings();
		//$template = $template->getDbTable()->findTemplateByName($template, $name);
				
		return (is_readable(APPLICATION_PATH.'/views/'.$name) 
			&& file_exists(APPLICATION_PATH.'/layouts/scripts/'.$name.'_layout.tpl')
			//&& $template->_template_name != NULL
			);
		 
	}
}