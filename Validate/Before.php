<?php
/**
 * @package Zefir_Validate_Before
 */

/** @see Zend_Validate_Abstract */
require_once 'Zend/Validate/Abstract.php';

/**
 */
class Zefir_Validate_Before extends Zend_Validate_Abstract
{
	/**
     * Error codes
     * @const string
     */
    const NOT_BEFORE	= 'notBefore';
    const WRONG_DATE	= 'wrongDate';
    
    protected $_field;
    protected $_date;
    protected $_format = 'd-m-Y';
    
    
    protected $_messageVariables = array(
          'date' => '_date'
        );
    
	/**
	* Error messages
	* @var array
	*/
	protected $_messageTemplates = array(
		self::NOT_BEFORE => 'The date is later than %date%',
		self::WRONG_DATE => 'This is not a valid date',
    );
    
    
	/**
	* Sets validator options
	*
	* @param  int $startDate
	* @param  int $endDate
	* @param string $format
	* @return void
	*/
	public function __construct($field, $format = null)
	{
		$this->_setReferenceField($field);
		if ($format != null)
			$this->_setFormat($format);
	}
	
	/**
	 * Set the start date
	 * @param int $startDate
	 * @return void
	 */
	protected function _setReferenceField($field)
	{
		$this->_field = $field;
	}
	
	/**
	 * Set date format
	 * @param string $format
	 * @return void
	 */
	protected function _setFormat($format)
	{
		$this->_format = $format;
	}
	
	/**
	 * Get the start date
	 * @return int
	 */
	protected function _getReferenceField()
	{
		return $this->_field;
	}
	
	/**
	 * Get date format
	 * @return string
	 */
	protected function _getFormat()
	{
		return $this->_format;
	}
	
	protected function _getReferenceFieldValue($context)
	{
		$field = $this->_getReferenceField();
		
		return strtotime($context[$field]);
	}
	
	/**
	* Check whether given date is between the start and the end
	* 
	* @param  mixed $value
	* @param  array $context
	* @return boolean
	*/
	public function isValid($value, $context = null)
	{
		$this->_setValue($value);
		
		$date = $this->_getReferenceFieldValue($context);
		$this->_date = date($this->_getFormat(), $date);
		
		$dateData = date_parse_from_format($this->_getFormat(), $value);
		if ( !checkdate($dateData['month'], $dateData['day'], $dateData['year']))
		{
			$this->_error(self::WRONG_DATE);
			return FALSE;
		}
		
		if (strtotime($value) > $date)
		{
			$this->_error(self::NOT_BEFORE);
			return FALSE;	
		}
		
		
		return TRUE;
    }
}