<?php
/**
 * @package Zefir_Validate_Youtube
 */

/**
 * Confirms that a video id or youtube link is correct
 *
 * @package    Zefir_Validate_Youtubr
 */
class Zefir_Validate_Youtube extends Zend_Validate_Abstract
{
	/**
     * Error constants
     */
    const ERROR_VIDEO_INVALID 	= 'linkInvalid';
	
    protected $_primary;
    
    public function isValid($value)
    {
    	//set additional messages
    	$msgTemplates = array(
    		self::ERROR_VIDEO_INVALID => 'This link appears to be invalid', 
    	);
    	
    	$this->_messageTemplates += $msgTemplates;
    	
        $this->_setValue($value);
    	
        
        $link = preg_match('/^(http:\/\/)?www\.youtube\.com\/watch\?v=[^&]*(&.*)?$/', $value) ?
        	substr($value, strpos($value, 'v=') + 2, 11): $value;
        
        $link = 'http://gdata.youtube.com/feeds/api/videos/'.$link;
        
        $response = get_headers($link);
        
        if ($response[0] != 'HTTP/1.0 200 OK')
        {
        	$this->_error(self::ERROR_VIDEO_INVALID);
			return FALSE;	
        }
        return TRUE;
    }
}
