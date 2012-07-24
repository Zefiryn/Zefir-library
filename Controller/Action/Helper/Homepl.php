<?php
class Zefir_Controller_Action_Helper_Homepl extends Zend_Controller_Action_Helper_Abstract
{
	public function preDispatch() {
		$rdr = $this->getActionController()->getHelper('redirector');
		$rdr->setUseAbsoluteUri(true);
	}
}