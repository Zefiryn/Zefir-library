<?php

class Zefir_Decorator_PasswordField extends Zend_Form_Decorator_Abstract
{
	/**
	 * Render an password type element
	 * @param  string $content
	 * @return string
	 */
	public function render($content)
	{
		$element = $this->getElement();

		$value         = $element->getValue($element);
		$attribs       = $element->getAttribs();
		$name          = $element->getFullyQualifiedName();
		$id            = $element->getId();
		$attribs['id'] = $id;
		$class = $element->hasErrors() ? $attribs['class'].' '.'error' : $attribs['class'];


		$elementContent  =
        '<input type="password" name="'.$name.'"	id="'.$id.'" value="" 
        	class="'.$class.'" size="'.$attribs['size'].'" 
        	maxlength="'.$attribs['maxlength'].'" />';

		return $elementContent;
	}
}