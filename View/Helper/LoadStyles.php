<?php
class Zefir_View_Helper_LoadStyles extends Zend_View_Helper_Abstract
{
	protected static $_styles = array(
		'files' => array(),
		'media' => 'screen',
	);
	
	public function loadStyles($styles)
	{
		$styles = is_array($styles) ? $styles : array($styles);
		
		foreach($styles as $style)
		{
			if (!in_array($style, self::$_styles['files']))
			{
				self::$_styles['files'][] = $style;
			}
		} 
		
		return $this;
	}
	
	public function setMedia($name)
	{
		self::$_styles['media'] = $name;
		return $this;
	}
	
	public function setLink($url)
	{
		self::$_styles['url'] = $url;
		
		return $this;
	}
	
	public function setSeparator($separator)
	{
		self::$_styles['separator'] = $separator;
		return $this;
	}
	
	public function render()
	{
		$value = '';
		foreach (self::$_styles['files'] as $css)
		{
			$value .= $css.self::$_styles['separator'];
		}
		$link = '<link href="' . self::$_styles['url'] . $value .'" media="' . self::$_styles['media'] . '" rel="stylesheet" type="text/css" >';
		return $link;
	}
	
	public function __toString()
	{
		return $this->render();
	}
}