<?php
/**
 * @package Zefir_Action_Helper_Flash
 */

/**
 * Get flash message after redirection
 * @author Zefiryn
 * @since Jan 2011
 */
class Zefir_Action_Helper_Flash extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * Zend_View object
	 * @var Zend_View
	 */
	protected $_view;


	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action_Helper_Abstract::preDispatch()
	 */
	public function preDispatch()
	{
		$view = $this->_getView();
		$flash = Zend_Session::namespaceGet('flash');
		if (isset($flash['message']) && $flash['message'] != NULL)
		{
			$view->flash = $flash['message'];
			$view->flash_bg = $flash['message_bg'];
		}
		else
		$view->flash = '';

		$newflash = new Zend_Session_Namespace('flash');
		$newflash->message = NULL;
		$newflash->message_bg = NULL;

	}

	/**
	 * Get the view property of Zend_Action_Controller object
	 * @access private
	 * @return Zend_View an instance of Zend_View class
	 */
	protected function _getView()
	{
		if (null === $this->_view)
		{
			$controller = $this->getActionController();
			$this->_view = $controller->view;
		}
		return $this->_view;

	}
}