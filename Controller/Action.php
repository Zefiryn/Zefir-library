<?php
/**
 * @package Zefir_Controller_Action
 */
/**
 * An extension to the default Zend_Controller_Action class
 * @author zefiryn
 */
class Zefir_Controller_Action extends Zend_Controller_Action
{
	protected $_role;
	protected $_cache;
	/**
	 * init function
	 * @access public
	 * @return void
	 */
	public function init()
	{
		/**
    	 * set the base url for path in the view
    	 */
    	$options = Zend_Registry::get('options');
    	$this->view->baseUrl = $options['resources']['frontController']['baseUrl'];

    	/**
    	 * Check if there is any post data in session from redirect from login
    	 */
    	if ($this->getRequest()->getActionName() != 'login')
    	{
	    	$postSession = Zend_Session::namespaceGet('post');
	    	if (isset($postSession['post']) && $postSession['post'] != NULL)
	    	{
	    		foreach($postSession['post'] as $key => $value)
					$_POST[$key] = $value;
	    	}	
    	}
    	
    	/**
    	 * Run cache if there is one
    	 */
    	if (Zend_Registry::isRegistered('cache'))
    	{
	    	$cache = Zend_Registry::get('cache');
	    	if ($cache)
	    	{
	    		$this->_cache = $cache;
	    	}
    	}
    	
    	$this->view->link = $this->_getCurrentPage();
    	
	}
	
	/**
	 * Check if given action exists in the controller
	 * @access public
	 * @param string $name
	 * @return boolean 
	 */
	public function actionExists($name)
	{
		return method_exists($this, $name.'Action');
	}
	
	/**
	 * Store the flash message
	 * @access private
	 * @param string $msg
	 * @return void
	 */
	public function flashMe($msg, $bg = 'SUCCESS')
	{
		$flash = new Zend_Session_Namespace('flash');
		$flash->message = $msg;
		$flash->message_bg = $bg == 'SUCCESS' ? 'flash_success' : 'flash_failure';
		
	}
	
	/**
	 * Select new name for an existing file
	 * @access private
	 * @param string $dir
	 * @param string $file
	 * @return string $name
	 */
	protected function _getNewName($dir, $file)
	{
		$ext = substr($file, strrpos($file, '.'));
		$rawname = substr($file, 0, strrpos($file, '.'));
		
		//cut filename to 20 characters
		if (strlen($rawname) > 18)
			$rawname = substr($rawname, 0, 18);
		
		$name = '';
		
		if (file_exists($dir.$file))
		{
			for($i = 1; $name == ''; $i++)
			{
				$tryname = $rawname.'_'.$i.$ext;
				if (!file_exists($dir.$tryname))
					$name = $tryname;
			}
		}
		else 
			$name = $file;
		
		return $name;
	}
	
	/**
     * Set configuration for receiving uploaded file
     * 
     * @access private
     * @param string $cache_folder
     * @param Application_Book_Form $form
     * @param string $element
     * @return Zend_Form $form 
     */
    protected function _cacheFile($cache_folder, $form, $element)
    {
    	//get the file element
		$upload = $form->getElement($element);

		if(count($upload->getErrors()) == 0 && !is_array($upload->getFileName()))
		{
			$file = basename($upload->getFileName());
			$dir = APPLICATION_PATH.'/../public'.$cache_folder.'/';
			
			//rename uploaded file if there is another with the same name
			$name = $this->_getNewName($dir, $file);
			$upload->addFilter('Rename', $dir.$name);
			$upload->receive();
			
			if ($form->getElement($element.'Cache'))
			{
				$oldCache = $form->getElement($element.'Cache')->getValue();
				$form->getElement($element.'Cache')->setValue('cache/'.$name);
				
				//remove old cached file
				if (strstr($oldCache, 'cache/'))
					$this->_removeOldCache(APPLICATION_PATH.'/../public/assets/', $oldCache);
			}
			$form->$element = $upload;
		}

		return $form;
    }
    
    /**
     * Remove file
     * 
     * @access private
     * @param string $dir
     * @param string $file
     * @return void
     */
    protected function _removeOldCache($dir, $file)
    {
    	if (is_file($dir.$file))
    		unlink($dir.$file);
    }
    
    /**
     * Assemble route an redirect there
     * 
     * @access private
     * @param array $options
     * @param string $route
     * @return void
     */
    protected function _redirectToRoute($options, $route)
    {
    	$this->_redirector = $this->_helper->getHelper('Redirector');
		$this->_redirector->gotoRoute($options, $route);
    }
    
    /**
     * Check whether file has been cached
     * 
     * @access private
     * @param Zefir_Form $form
     * @param string $field
     * @return Zefir_Form $form
     */
	protected function _checkFileCache(Zefir_Form $form, $field)
    {
    	$request = $this->getRequest();
    	$options = Zend_Registry::get('options');
    	
    	$fileCache = $request->getParam($field.'Cache', '');
		$filePath = APPLICATION_PATH.'/../public/assets/'.$fileCache;

		if ($fileCache != null	&& file_exists($filePath))
			$form->getElement($field)->setRequired(FALSE);
		else
			$form->getElement($field.'Cache')->setValue(null);
		
		return $form;
    }
    
    protected function _clearCache(Zend_Form $form)
    {
    	$elements = $form->getElements();
    	
    	foreach($elements as $element)
    	{
    		if (strstr($element->getName(), 'Cache'))
    		{
    			$file = $element->getValue();
    			if ($file)
    			{
    				$path = APPLICATION_PATH.'/../public/assets/'.$file;
    				@unlink($path);
    			}
    		}
    	}
    }
    
    /**
     * Get the privileges levels of current user
     */
    protected function _getRole()
    {
    	if ($this->_role == null)
    		$this->_role = Zend_Registry::get('role');
    	
    	return $this->_role;    	
    }
    
    /**
     * Check whether current user is an admin 
     */
    protected function _isAdmin()
    {
    	return $this->_getRole() == 'admin';
    }
    
    /**
     * Clear cache according to given tag
     * 
     * @param string $tags
     */
    protected function _clearZendCache($tags)
    {
    	$cache = Zend_Registry::get('cache');
		$cache->clean(
			Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
			$tags
		);
    }
    
    /**
     * Get current page link
     * 
     * @return string $link;
     */
	protected function _getCurrentPage()
	{
		$link = $this->getRequest()->getRequestUri();
		
		if ($link == '/')
			$link = 'index';
		
		elseif (in_array(substr($link, 1, 2), array('pl', 'cs', 'sk', 'hu', 'en')))
		{
			$link = substr($link, 4);
		}
		
		else 
			$link = substr($link, 1);
		
		return $link;
	}
}