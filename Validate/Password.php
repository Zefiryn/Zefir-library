<?php
/**
 * @package Zefir_Validate_Password
 */
/**
 * Validators for the password field
 * @author zefiryn
 * @since Jan 2011
 */
class Zefir_Validate_Password extends Zend_Validate_Abstract {

	/**
	 * @var string
	 */
	const MSG_INVALIDCHARACTERS =  'msgInvalidCharacters';
	/**
	 * @var string
	 */
	const MSG_MINIMUM = 'msgMinimum';
	/**
	 * @var string
	 */
	const MSG_MAXIMUM = 'msgMaximum';
	/**
	 * @var string
	 */
	const MSG_ONESMALLLETTER = 'msgOneSmallLetter';
	/**
	 * @var string
	 */
	const MSG_ONEBIGLETTER = 'msgOneBigLetter';
	/**
	 * @var string
	 */
	const MSG_ONEDIGIT = 'msgOneDigit';

	/**
	 * @var int
	 */
	protected $_minimum;
	/**
	 * @var int
	 */
	protected $_maximum;

	/**
	 * @var array
	 */
	protected $_messageVariables = array(
		'min' => '_minimum',
		'max' => '_maximum',
	);

	/**
	 * @var array
	 */
	protected $_messageTemplates = array(
	self::MSG_INVALIDCHARACTERS => "'%value%' contains invalid characters",
	self::MSG_MINIMUM => "'%value%' is less than '%min%' characters long",
	self::MSG_MAXIMUM => "'%value%' is more than '%min%' characters long",
	self::MSG_ONESMALLLETTER => "Value must contains at least one small letter",
	self::MSG_ONEBIGLETTER => "Value must contains at least one big letter",
	self::MSG_ONEDIGIT => "Value must contains at least one digit",
	);

	/**
	 * Constructor
	 * @access public
	 * @param int $minimum
	 * @param int $maximum
	 * @return void
	 */
	public function __construct($minimum=5, $maximum=16) {
		$this->_minimum = (integer) $minimum;
		$this->_maximum = (integer) $maximum;
	}

	/**
	 * (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {

		$this->_setValue($value);

		if ( !preg_match('/^\S*$/u', $value) ) {
			$this->_error(self::MSG_INVALIDCHARACTERS);
			return false;
		}

		if ( strlen($value) < $this->_minimum ) {
			$this->_error(self::MSG_MINIMUM);
			return false;
		}

		if ( strlen($value) > $this->_maximum ) {
			$this->_error(self::MSG_MAXIMUM);
			return false;
		}

		if ( !preg_match('/[a-z]/', $value) ) {
			$this->_error(self::MSG_ONESMALLLETTER);
			return false;
		}

		/*
		 if ( !preg_match('/[A-Z]/', $value) ) {
		$this->_error(self::MSG_ONEBIGLETTER);
		return false;
		}

		if ( !preg_match('/[0-9]/', $value) ) {
		$this->_error(self::MSG_ONEDIGIT);
		return false;
		}
		*/
		return true;

	}

}