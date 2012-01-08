<?php
/**
 * @package Zefir_Action_Helper_UserSession
 */

/**
 * Handle user's related init procedures
 * @author Zefiryn
 * @since Jan 2011
 */
class Zefir_Action_Helper_UserSession extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * Zend_View object
	 * @var Zend_View
	 */
	protected $_view;
	
	/**
	 * @see Zend_Controller_Action_Helper_Abstract::preDispatch()
	 */
	public function preDispatch()
	{
		$this->_setUsersRole();
		$this->_checkUsersPrivileges();		
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
	
	/**
	 * Sets user's role according to his identity
	 * @access private
	 * @return void
	 */
	protected function _setUsersRole()
	{
		$auth = Zend_Auth::getInstance();
		$view = $this->_getView();
		$user = new Application_Model_Users();
		
		if (!$auth->hasIdentity())
			$user->_role = 'guest';

    	else
    		$user->getUser($auth->getIdentity(), TRUE);

    	Zend_Registry::set('role', $user->_role);
    	Zend_Registry::set('user', $user);
    	$view->user= $user;
    	$view->logged = $auth->hasIdentity();
	}
	
	/**
	 * Check user privilege to the current action
	 * @access private
	 * @return void
	 */
	protected function _checkUsersPrivileges()
	{
		$role =	Zend_Registry::get('role');
		$auth = Zend_Auth::getInstance();
		$acl = Zend_Registry::get('acl');
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$request = $this->getActionController()->getRequest();
		
		if (!$acl->isAllowed($role, $request->getControllerName(), $request->getActionName())
			&& !$auth->hasIdentity())
		{
			if ($request->getActionName() != 'login')
			{
				//save uri which user tried to get
				$authNamespace = new Zend_Session_Namespace('auth');
				$options = Zend_Registry::get('options');
				$authNamespace->redirect = 
					str_replace($options['resources']['frontController']['baseUrl'], '/', $request->getRequestUri());				
				$this->getActionController()->flashMe('access_denied', 'FAILURE');
				
				//save post data
				$request = $this->getRequest();
				if ($request->isPost())
				{
					$post = new Zend_Session_Namespace('post');
					$post->post = $request->getPost();
				}
				$redirect->gotoUrlAndExit('/auth/login');
			}
			else 
			{
				$this->getActionController()->flashMe('not_allowed', 'FAILURE');
				$redirect->gotoUrlAndExit('/index');
			}
		}
		elseif (!$acl->isAllowed($role, $request->getControllerName(), $request->getActionName())
				&& $auth->hasIdentity())
		{//insufficent rights
			$this->getActionController()->flashMe('not_allowed', 'FAILURE');
			$redirect->gotoUrlAndExit('/index');
		}
		
	}
}