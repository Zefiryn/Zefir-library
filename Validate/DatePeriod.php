<?php
/**
 * @package Zefir_Validate_DatePeriod
 */

/** @see Zend_Validate_Abstract */
require_once 'Zend/Validate/Abstract.php';

/**
 */
class Zefir_Validate_DatePeriod extends Zend_Validate_Abstract
{
	/**
     * Error codes
     * @const string
     */
    const TO_EARLY		= 'dateToEarly';
    const TO_LATE		= 'dateToLate';
    const WRONG_DATE	= 'wrongDate';
    
	/**
	* Error messages
	* @var array
	*/
	protected $_messageTemplates = array(
		self::TO_EARLY 	=> 'Given date is earlier than the initial date',
		self::TO_LATE	=> 'Given date is later than the expiration date',
		self::WRONG_DATE => 'This is not a valid date',
    );
    
    protected $_startDate;
    protected $_endDate;
    protected $_format = 'd-m-Y';
    
	/**
	* Sets validator options
	*
	* @param  int $startDate
	* @param  int $endDate
	* @param string $format
	* @return void
	*/
	public function __construct($startDate, $endDate, $format = null)
	{
		$this->_setStartDate($startDate);
		$this->_setEndDate($endDate);
		if ($format != null)
			$this->_setFormat($format);
	}
	
	/**
	 * Set the start date
	 * @param int $startDate
	 * @return void
	 */
	protected function _setStartDate($startDate)
	{
		$this->_startDate = $startDate;
	}
	
	/**
	 * Set the end date
	 * @param int $endDate
	 * @return void
	 */
	protected function _setEndDate($endDate)
	{
		$this->_endDate = $endDate;
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
	protected function _getStartDate()
	{
		return $this->_startDate;
	}
	
	/**
	 * Get the end date
	 * @return int
	 */
	protected function _getEndDate()
	{
		return $this->_endDate;
	}
	
	/**
	 * Get date format
	 * @return string
	 */
	protected function _getFormat()
	{
		return $this->_format;
	}
	
	
	/**
	* Check whether given date is between the start and the end
	* 
	* @param  mixed $value
	* @param  array $context
	* @return boolean
	*/
	public function isValid($value)
	{
		$this->_setValue($value);
		
		$startDate = $this->_getStartDate();
		$endDate = $this->_getEndDate();
		$date = strtotime($value);
		$dateData = date_parse_from_format($this->_getFormat(), $value);

		if ( !checkdate($dateData['month'], $dateData['day'], $dateData['year']))
		{
			$this->_error(self::WRONG_DATE);
			return FALSE;
		}
		
		if ($date < $startDate)
		{
			$this->_error(self::TO_EARLY);
			return FALSE;
		}
		
		if ($date > $endDate)
		{
			$this->_error(self::TO_LATE);
			return FALSE;
		}
		return TRUE;
    }
}