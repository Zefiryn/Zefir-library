<?php
/**
 * @package Zefir_Validate_Login
 */

/**
 * Validators for the login field
 * @author zefiryn
 * @since Jan 2011
 */
class Zefir_Validate_Notdigit extends Zend_Validate_Abstract
{

    /**
     * @var string
     */
    const MSG_INVALIDCHARACTERS = 'msgInvalidCharacters';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::MSG_INVALIDCHARACTERS => "'%value%' contains invalid characters"
    );

    /**
     * Constructor
     * @access public
     */
    public function __construct()
    {
       return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Validate_Interface::isValid()
     */
    public function isValid($value)
    {

        $this->_setValue($value);

        if (ctype_digit($value)) {
            $this->_error(self::MSG_INVALIDCHARACTERS);
            return false;
        }

        return true;

    }

}