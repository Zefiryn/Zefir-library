<?php


class Zefir_View_Helper_Bbcode extends Zend_View_Helper_Abstract
{
	public function bbcode($string)
	{
		$bbcode = Zend_Markup::factory('Bbcode');

		//strip quotations from urls
		$string = str_replace('&quot;', null, $string);

		$string = $bbcode->render($string);



		return ($string);
	}

}
