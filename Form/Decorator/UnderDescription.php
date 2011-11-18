<?php

class Zefir_Decorator_UnderDescription extends Zend_Form_Decorator_Abstract
{ 
	
	/**
     * Render description
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
     	$element = $this->getElement();
     	
     	$attribs		= $element->getAttribs();
     	$name 			= $element->getFullyQualifiedName();
     	$description	= $element->getDescription();
		$tag			= isset($attribs['tag']) ?  $attribs['tag'] : 'p';
		$class			= $this->getOption('class') != null ?  $this->getOption('class') : 'form-description';
		
		
		if (!empty($description) && null !== ($translator = $element->getTranslator()))
		{
			$description = $translator->translate($description);
		}
		
		if ($description != NULL)
        {		        	
	    	switch ($this->getPlacement()) {
	            case self::APPEND:
	                return $content . $this->getSeparator() . $this->_createDescription($tag, $class, $name, $description);
	            case self::PREPEND:
	                return $this->_createDescription($tag, $class, $name, $description). $this->getSeparator() . $content;
	        }
        }
        else
        	return $content;
    }
    
    protected function _createDescription($tag, $class, $name, $description)
    {
    	return '<'.$tag.' id="description-'.$name.'" class="'.$class.'">'.$description.'</'.$tag.'>';
    }
}