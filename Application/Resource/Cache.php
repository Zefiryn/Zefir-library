<?php
/**
 * @package Zefir_Application_Resource_Cache
 */
/**
 * Resource class for handling cache
 * @author zefiryn
 * @since Jan 2011
 *
 */

class Zefir_Application_Resource_Cache extends Zend_Application_Resource_ResourceAbstract {

	public function init () 
	{
		$options = $this->getOptions();
		$cache = Zend_Cache::factory(
		$options['frontEnd'],
		$options['backEnd'],
		$options['frontEndOptions'],
		$options['backEndOptions']);
		Zend_Registry::set('cache', $cache);
		return $cache;
	}
	
}