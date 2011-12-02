<?php


class Zefir_View_Helper_Date extends Zend_View_Helper_Abstract
{
	/**
	 * Default format
	 *
	 * @var string
	 */
	protected $_default_format = "d M Y, H:i";

	/**
	 * Default Locale
	 *
	 * @var string
	 */
	protected $_default_locale = 'pl';

	/**
	 * Months names for the default locale
	 *
	 * @var array
	 */
	protected $_months = array(
		'January' => 'Styczeń', 'February' => 'Luty', 'March' => 'Marzec', 'April' => 'Kwiecień', 
		'May' => 'Maj', 'June' => 'Czerwiec', 'July' => 'Lipiec', 'August' => 'Sierpień', 
		'September' => 'Wrzesień', 'October' => 'Październik', 'November' => 'Listopad', 
		'December' => 'Grudzień',
		'Jan' => 'Sty', 'Feb' => 'Lut', 'Mar' => 'Mar', 'Apr' => 'Kwi', 'May' => 'Maj', 'Jun' => 'Cze',
		'Jul' => 'Lip', 'Aug' => 'Sie', 'Sep' => 'Wrz', 'Oct' => 'Paź', 'Nov' => 'Lis', 'Dec' => 'Gru'
	);

	public function date($string, $format = "")
	{
		if (!$this->_isTimestamp($string))
		{
			$string = strtotime($string);
		}

		$format = $format != NULL ? $format : $this->_default_format;
		$date = $this->_localize(date($format, $string));

		return $date;
	}

	protected function _isTimestamp($string)
	{
		return (preg_match('/^[0-9]{10-12}$/', $string));
	}

	protected function _localize($string)
	{
		if (Zend_Registry::isRegistered('Zend_Translate'))
		{
			$translation = Zend_Registry::get('Zend_Translate');
			if (is_array($translation))
			$months = $translation['date']['months'];
			else
			$months = $this->_months;
		}
		else
		$months = $this->_months;
			
		foreach($months as $search => $replace)
		{
			if (strstr($string, $search))
			{
				$string = str_replace($search, $replace, $string);
			}
		}

		return $string;
	}
}
