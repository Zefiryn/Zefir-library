<?php
class Zefir_View_Helper_LoadScripts extends Zend_View_Helper_Abstract
{
	protected static $_scripts = array(
		'files' => array(),
	);
	
	public function loadScripts($scripts = null)
	{
		if ($scripts)
		{
			$scripts = is_array($scripts) ? $scripts : array($scripts);
			
			foreach($scripts as $style)
			{
				if (!in_array($style, self::$_scripts['files']))
				{
					self::$_scripts['files'][] = $style;
				}
			} 
		}
		return $this;
	}
	
	public function setLink($url)
	{
		self::$_scripts['url'] = $url;
		
		return $this;
	}
	
	public function setSeparator($separator)
	{
		self::$_scripts['separator'] = $separator;
		return $this;
	}
	
	public function render()
	{
		$value = '';
		foreach (self::$_scripts['files'] as $css)
		{
			$value .= $css.self::$_scripts['separator'];
		}
		$link = '<script type="text/javascript" src="'. self::$_scripts['url'] . $value .'"></script>';
		return $link;
	}
	
	public function __toString()
	{
		return $this->render();
	}
}