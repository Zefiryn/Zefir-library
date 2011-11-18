<?php
/**
 * @package Zefir_Validate_NotEmptyCombo
 */

/** @see Zend_Validate_Abstract */
require_once 'Zend/Validate/Abstract.php';

/**
 */
class Zefir_Validate_NotEmptyCombo extends Zend_Validate_Abstract
{
	/**
     * Error codes
     * @const string
     */
    const IS_EMPTY 		= 'isEmptyCombo';
    const MISSING_FIELD = 'missingField';
    
	/**
	* Error messages
	* @var array
	*/
	protected $_messageTemplates = array(
		self::IS_EMPTY 			=> 'One of the two fields must be given',
		self::MISSING_FIELD 	=> 'No field was provided to match against',
    );
    
    protected $_matchField;
    
	/**
	* Sets validator options
	*
	* @param  mixed $token
	* @return void
	*/
	public function __construct($field = null)
	{
		$this->_setMatchField($field);
	}
	
	protected function _setMatchField($field)
	{
		$this->_matchField = $field;
	}
	
	protected function _getMatchField()
	{
		return $this->_matchField;
	}
	
	/**
	*
	* @param  mixed $value
	* @param  array $context
	* @return boolean
	*/
	public function isValid($value, $context = null)
	{
		$this->_setValue($value);
		$field = $this->_getMatchField();
		
		if (!isset($context[$field]))
		{
			$this->_error(self::MISSING_FIELD);
			return FALSE;
		}
		
		elseif ($value == NULL && ($context[$field] == '0' || $context[$field] == NULL))
		{
			$this->_error(self::IS_EMPTY);
			return FALSE;
		}
		
		return TRUE;
    }
}