<?php
/**
 * @package Zefir_Validate_URL
 */

/** @see Zend_Validate_Abstract */
require_once 'Zend/Validate/Abstract.php';

class Zefir_Validate_URL extends Zend_Validate_Abstract
{
	const INVALID_URL = 'invalidUrl';

	protected $_messageTemplates = array(
			self::INVALID_URL => "'%value%' is not a valid URL",
	);

	public function isValid($value)
	{
		$valueString = (string) $value;
		$this->_setValue($valueString);

		if (!Zend_Uri::check($value)) {
			$this->_error(self::INVALID_URL);
			return false;
		}
		return true;
	}
}