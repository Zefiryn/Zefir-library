<?php
/**
 * @package Zefir_Action_Helper_LoginForm
 */

/**
 * Perform application migration
 * @author Zefiryn
 * @since Apr 2011
 */
include_once(APPLICATION_PATH . '/migrations/version.php');

class Zefir_Action_Helper_Migration extends Zend_Controller_Action_Helper_Abstract
{
	protected $_migrationDir;
	
	protected $_options;
	/**
	 * @see Zend_Controller_Action_Helper_Abstract::preDispatch()
	 */
	public function preDispatch()
	{
		$settings = Zend_Registry::get('appSettings');
		if (APP_VERSION > $settings->version) {
			$filename = 'migration-' . APP_VERSION . '.php';
			include($this->getMigrationDir().$filename);
			$settings->version = APP_VERSION;
			$settings->save();
			$this->clearCache();
		}
		
		return $this;
	}
	
	public function getMIgrationDir(){
		if ($this->_migrationDir == null) {
			$this->_migrationDir = APPLICATION_PATH . '/migrations/sql/';
		}
		
		return $this->_migrationDir;
	}
	
	protected function getAdapter() {
		return Zend_Db_Table::getDefaultAdapter();
	}
	
	protected function getOptions() {
		if ($this->_options == null) {
			Zend_Registry::get('options');
		}
		
		return $this->_options;
	}
	
	protected function getTable($model) {
		$object = new $model;
		
		return $object->getDbTable()->getTableName();
	}
	
	protected function clearCache() {
		$cache = Zend_Registry::get('cache');
		$cache->clean(Zend_Cache::CLEANING_MODE_ALL);
		
		return $this;
	}
}