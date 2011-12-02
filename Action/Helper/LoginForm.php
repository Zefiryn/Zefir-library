<?php
/**
 * @package Zefir_Action_Helper_LoginForm
 */

/**
 * Create login form for not loogged user
 * @author Zefiryn
 * @since Apr 2011
 */
class Zefir_Action_Helper_LoginForm extends Zend_Controller_Action_Helper_Abstract
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
		$role = Zend_Registry::get('role');
		 
		if ($role == 'guest')
		{
			$form = new Application_Form_Login();
			$view->loginForm = $form;
		}

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