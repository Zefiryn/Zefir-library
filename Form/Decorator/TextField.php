<?php

class Zefir_Decorator_TextField extends Zend_Form_Decorator_Abstract
{ 
	/**
     * Render an text type element
     * 
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();

        $value         	= $element->getValue($element);
        $attribs       	= $element->getAttribs();
        $name          	= $element->getFullyQualifiedName();
        $type 			= $element->getType();
        
        $id            	= $element->getId() ;
        $id 			= $id != NULL ? 'id="'.$id.'"' : NULL;
        
        $attribs['id'] 	= $id;
        $class 			= isset($attribs['class']) ? $attribs['class'] : NULL; 
        $class 			.= $element->hasErrors() ? ' '.'error' : NULL;
		$class			= $class != NULL ? 'class="'.$class.'"' : NULL;
		
        //input field attribs
        $size 			= isset($attribs['size']) ? 'size="'.$attribs['size'].'" ' : NULL; 
        $maxLength		= isset($attribs['maxLength']) ? 'maxlength="'.$attribs['maxlength'].'" ' : NULL;
        
        //textarea attribs
        $cols			= isset($attribs['cols']) ? 'cols="'.$attribs['cols'].'" ' : NULL;
        $rows 			= isset($attribs['rows']) ? 'rows="'.$attribs['rows'].'" ' : NULL;
        
        if (isset($attribs['name_id']))
        {
        	$name = substr($name, 0, strrpos($name, '_'));
        	$name = $name.'['.$attribs['name_id'].']';
        }
        
        if ($type == 'Zend_Form_Element_Text')
        	return $this->_createInputField($name, $id, $value, $class, $size, $maxLength);
        	
        elseif($type == 'Zend_Form_Element_Textarea')
        {	
        	return $this->_createTextAreaField($name, $id, $value, $class, $rows, $cols);
        }
        
        elseif($type == 'Zend_Form_Element_Submit')
        	return $this->_createSubmitElement($name, $id, $element->getLabel(), $class);
        
    }
    
    protected function _createInputField($name, $id, $value, $class, $size, $maxLength)
    {
    	return '<input type="text" name="'.$name.'" '.$id.' value="'.$value.'" 
        	'.$class.' '.$size.$maxLength.' />';
    }
    
    protected function _createTextAreaField($name, $id, $value, $class, $rows, $cols)
    {
    	return '<textarea name="'.$name.'" '.$id.' '.$class.' '.$rows.$cols.'>'.$value.'</textarea>';
    }
    
    protected function _createSubmitElement($name, $id, $value, $class)
    {
    	$translations = Zend_Registry::get('translations');
    	$lang = new Zend_Session_Namespace('lang');
    	
    	$value = isset($translations[$lang->lang][$value]) ? $translations[$lang->lang][$value] : $value;
    	
    	$submit = '<input type="submit" '.$id.' '.$class.' name="'.$name.'" value="'.$value.'" />';
    	return $submit;
    }
}