<?php
/**
 * @package Zefir_Action_Helper_Localization
 */

/**
 * Set and create translation array
 * @author Zefiryn
 * @since Feb 2011
 */
class Zefir_Action_Helper_Localization extends Zend_Controller_Action_Helper_Abstract
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
		$request = $this->getRequest();

		$this->_setLanguage($request);
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

	private function _setLanguage($request)
	{
		$options = Zend_Registry::get('options');
		$auth = Zend_Auth::getInstance();
		$langSession = new Zend_Session_Namespace('lang');

		$lang = $request->getParam('lang', '');
		$browser_lang = $this->_getBrowserLang($options);

		//get locale
		if ($lang != NULL)
		$lang = strstr($lang, '/' ) ? substr($lang, 0, strrpos($lang, '/')) : $lang;

		elseif (isset($_COOKIE['lang']))
		$lang = $_COOKIE['lang'];

		elseif (isset($langSession->lang))
		$lang = $langSession->lang;

		elseif ($auth->hasIdentity())
		{
			$user = new Application_Model_Users();
			$lang = $user->getDbTable()->getUserLanguage($auth->getIdentity());
		}
		else
		$lang = $browser_lang;
			
		//check if the language exist
		if ($this->_langExists($lang, $options['i18n']['translation_source']))
		$lang = $lang;
		else
		$lang = $options['i18n']['default_language'];


		//set current value in cookie and in session
		setcookie("lang",$lang, time() + (60*60*24*14) , '/');
		$langSession->lang = $lang;

		//set locale
		$this->_setLocale($lang);

		//set translate object
		$this->_setTranslation($lang, $options['i18n']['translation_source']);

		return $lang;
	}

	private function _langExists($lang, $source)
	{
		if ($source == 'db')
		{
			$localization = new Application_Model_Localizations();
			return ($localization->getDbTable()->isLocalization($lang));
		}
		elseif ($source == 'csv')
		{
			return file_exists(APPLICATION_PATH . '/configs/lang/'. $lang . '.csv');
		}
	}

	private function _setLocale($lang)
	{
		$array = array('pl' => 'pl_PL', 'cs' => 'cs_CZ', 'sk' => 'sk_SK', 'en' => 'en_UK', 'hu' => 'hu_HU');

		//locale object
		$zl = new Zend_Locale();
		$zl->setLocale($array[$lang]);
		Zend_Registry::set('Zend_Locale', $zl);
	}

	private function _setTranslation($language, $source)
	{
		$view = $this->_getView();
		$langSession = new Zend_Session_Namespace('lang');

		if ($source == 'db')
		{
			$translations = $this->_getTranslationFromDb();
			if (isset($translations[$language]))
			$translate = new Zend_Translate('array',  $translations[$language], $language);
			else
			$translate = new Zend_Translate('array',  $translations['en'], 'en_UK');
			$view->translations = $translations;
			Zend_Registry::set('translations', $translations);
		}
		elseif ($source == 'csv')
		{
			$translate = new Zend_Translate('csv', APPLICATION_PATH . '/configs/lang/'. $language . '.csv' , $language);
		}

		$view->lang = $langSession->lang;

		//set translation object
		Zend_Registry::set('Zend_Translate', $translate);
	}

	private function _getTranslationFromDb()
	{
		$lang = new Application_Model_Localizations();
		$translations = $lang->getTranslationFromDb();
		return $translations;
	}

	protected function _getBrowserLang($options)
	{
		$accepted_lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : NULL;

		if (strstr($accepted_lang, 'pl'))
		{
			return 'pl';
		}
		elseif (strstr($accepted_lang, 'sk'))
		{
			return 'sk';
		}
		elseif (strstr($accepted_lang, 'hu'))
		{
			return 'hu';
		}
		elseif (strstr($accepted_lang, 'cz'))
		{
			return 'cz';
		}
		elseif (strstr($accepted_lang, 'cz'))
		{
			return 'en';
		}
		else
		{
			return $options['i18n']['default_language'];
		}
	}
}