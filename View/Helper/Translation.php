<?php


class Zefir_View_Helper_Translation extends Zend_View_Helper_Abstract
{

	protected $_lang;
	protected $_translations;
	
	protected function _setLang()
	{
		if ($this->_lang == null)
		{
			$session = new Zend_Session_Namespace('lang');
			$this->_lang = $session->lang;
		}
	}
	
	protected function _setTranslations()
	{
		if ($this->_translations == null)
			$this->_translations = Zend_Registry::get('translations');
	}
	
	public function translation($key, $lang = NULL)
	{
		$this->_setLang();
		$this->_setTranslations();
		
		//set the language
		$lang = $lang != NULL && array_key_exists($lang, $this->_translations) ? 
			$lang : $this->_lang; 
		
		//check if $key exist
		$text = isset($this->_translations[$lang]) && array_key_exists($key, $this->_translations[$lang]) ?
			$this->_translations[$lang][$key] : $key;
		
		return $text;
	}
}
