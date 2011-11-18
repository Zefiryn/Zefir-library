<?php

class Zefir_Decorator_ErrorMsg extends Zend_Form_Decorator_Abstract
{ 
	
	protected $_errorContener;
	
	/**
     * Render errors
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
     	$form = $this->getElement();
     	
     	$elementType 	= $form->getType();
     	$attribs		= $form->getAttribs();
     	$name 			= $form->getFullyQualifiedName();
     	$errors 		= $form->getErrors();
		$messages 		= $form->getMessages();
		$tag			= $this->getOption('tag') ?  $this->getOption('tag') : 'div';
		$errorMsgTag 	= $this->getOption('errorMsgTag') ?  $this->getOption('errorMsgTag') : 'p';
		$image 			= $this->getOption('image') ?  TRUE : FALSE;
		$class			= 'error-div';
		$errorMsgClass	= 'error';
		$errorImgClass 	= $elementType == 'Zend_Form_Element_Textarea' ? 'textarea-error' : 'error';
		
		//create contener element where errors will be placed
		$this->_errorContener = $this->_createErrorContener($tag, $class, $name);

		if ($form->hasErrors())
        {
			$content = str_replace('class="width1"', 'class="width1 error"', $content);
			$content = str_replace('class="width2"', 'class="width2 error"', $content);
			$content = str_replace('class="file"', 'class="file error"', $content);
        	$errorContent = '';
			
        	foreach($messages as $error => $errorMsg)
        	{
        		$errorContent .= $this->_createErrorMsg($errorMsgTag, $errorMsgClass, $errorMsg);
        	}
        	
        	$errorContent = sprintf($this->_errorContener, $errorContent);
        	
        	$imageContent =  ($image) ? $this->_createImage($errorImgClass) : NULL;
        	
	    	switch ($this->getPlacement()) {
	            case self::APPEND:
	                if ($elementType == 'Zend_Form_Element_Textarea')
	                	return $content . $this->getSeparator() . $errorContent;
	                else
	                	return $content . $imageContent . $this->getSeparator() . $errorContent;
	            case self::PREPEND:
	                return $errorContent . $this->getSeparator() . $content . $imageContent;
	        }
        }
        else
        	return $content;
    }
    
    protected function _createErrorContener($tag, $class, $name)
    {
    	return '<'.$tag.' id="error-'.$name.'" class="'.$class.'">%s</'.$tag.'>';
    }
    
	protected function _createErrorMsg($tag, $class, $msg)
    {
    	return '<'.$tag.' class="'.$class.'"><span>'.$msg.'</span></'.$tag.'>';
    }
    
    protected function _createImage($errorImgClass)
    {
    	$session = new Zend_Session_Namespace('template');
    	$options = Zend_Registry::get('options');
    	$baseUrl = $options['resources']['frontController']['baseUrl'];
    	
		return '<img src="'.$baseUrl.'../img/'.$session->template_name.'/error_mark.png" class="'.$errorImgClass.'">';    
    }
}