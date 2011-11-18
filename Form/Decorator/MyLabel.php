<?php

class Zefir_Decorator_MyLabel extends Zend_Form_Decorator_Abstract
{ 
	
	protected $_errorContener;
	
	/**
     * Render lable
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
     	$element = $this->getElement();
     	
     	$attribs		= $element->getAttribs();
     	$name 			= $element->getFullyQualifiedName();
     	$label 			= $element->getLabel();
     	$type			= $element->getType();
		$tag			= $this->getOption('tag') != null ?  $this->getOption('tag') : 'p';
		$class			= $this->getOption('class') != null ?  $this->getOption('class') : 'label';
		$separator		= $this->getOption('separator') != null ? $this->getOption('separator') : $this->getSeparator();
		
		if ($type != 'Zend_Form_Element_Checkbox')
		{
			$labelContent = '<'.$tag.' class="'.$class.'">'.$label.'</'.$tag.'>';
			
			switch ($this->getPlacement()) {
				case self::APPEND:
					$content = $content . $separator . $labelContent;
					break;
				case self::PREPEND:
					$content = $labelContent . $separator . $content;
					break;
			}
		}
		else 
		{
			
			$content = $content.' <label for="'.$name.'" class="checkbox_option">'.$label.'</label>';
			$content = '<p>'.$content.'</p>';
			
		}

		return $content;

    }

}