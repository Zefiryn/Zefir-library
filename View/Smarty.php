<?php

/**
 *
 * @since Dec 7 2007
 * @author Anders Fredriksson
 */
/**
 * Zend View Base Class
 */
require_once 'Zend/View.php';

/**
 * Smarty templating engine
 */
require_once '_smarty/Smarty.class.php';

/**
 * @category
 */
class Zefir_View_Smarty extends Zend_View_Abstract {
	/**
	 * Smarty object
	 * @var Smarty
	 */
	public $_smarty;

	protected $_plugins;

	/**
	 * Constructor
	 *
	 * Pass it a an array with the following configuration options:
	 *
	 * scriptPath: the directory where your templates reside
	 * compileDir: the directory where you want your compiled templates (must be
	 * writable by the webserver)
	 * configDir: the directory where your configuration files reside
	 *
	 * both scriptPath and compileDir are mandatory options, as Smarty needs
	 * them. You can't set a cacheDir, if you want caching use Zend_Cache
	 * instead, adding caching to the view explicitly would alter behaviour
	 * from Zend_View.
	 *
	 * @see Zend_View::__construct
	 * @param array $config ["scriptPath" => /path/to/templates,
	 *			     "compileDir" => /path/to/compileDir,
	 *			     "configDir"  => /path/to/configDir ]
	 * @throws Exception
	 */
	public function __construct($config = array()) {
		$this->_smarty = new Smarty();
		//smarty object

		foreach ($config as $key => $value)
		{
			if (!is_array($value))
			$this->setScriptPath($value);
		}


		$this->_plugins = new Zefir_View_Smarty_Plugin_Broker($this);
		$this->registerPlugin(new Zefir_View_Smarty_Plugin_Standard());

	}

	public function setCompilePath($dir) {
		$this->_smarty->compile_dir = $dir;
		return $this;
	}

	/**
	 * Return the template engine object
	 *
	 * @return Smarty
	 */
	public function getEngine() {
		return $this->_smarty;
	}

	/**
	 * Set the path to the templates
	 *
	 * @param string $path The directory to set as the path.
	 * @return void
	 */
	public function setScriptPath($path)
	{
		if (is_readable($path)) {
			$this->_smarty->template_dir = $path;
			return;
		}

		throw new Exception('Invalid path provided: '.$path);
	}

	/**
	 * Retrieve the current template directory
	 *
	 * @return string
	 */
	public function getScriptPaths()
	{
		return array($this->_smarty->template_dir);
	}

	/**
	 * register a new plugin
	 *
	 * @param EZ_View_Smarty_Plugin_Abstract
	 */
	public function registerPlugin(Zefir_View_Smarty_Plugin_Abstract $plugin,$stackIndex = null) {
		$this->_plugins->registerPlugin ( $plugin, $stackIndex );
		return $this;
	}

	/**
	 * Unregister a plugin
	 *
	 * @param string|EZ_View_Smarty_Plugin_Abstract $plugin Plugin object or class name
	 */
	public function unRegisterPlugin($plugin) {
		$this->_plugins->registerPlugin ( $plugin );
		return $this;
	}

	/**
	 * fetch a template, echos the result,
	 *
	 * @see Zend_View_Abstract::render()
	 * @param string $name the template
	 * @return void
	 */
	protected function _run() {

		$this->strictVars ( true );

		$vars = get_object_vars ( $this );
		foreach ( $vars as $key => $value ) {
			if ('_' != substr ( $key, 0, 1 )) {
				$this->_smarty->assign ( $key, $value );
			}
		}
		//assign variables to the template engine

		$this->_smarty->assign_by_ref ( 'this', $this );
		//why 'this'?
		//to emulate standard zend view functionality
		//doesn't mess up smarty in any way

		$path = $this->getScriptPaths ();

		$file = substr ( func_get_arg ( 0 ), strlen ( $path [0] ) );
		//smarty needs a template_dir, and can only use templates,
		//found in that directory, so we have to strip it from the filename

		$this->_smarty->template_dir = $path [0];
		//set the template diretory as the first directory from the path

		echo $this->_smarty->fetch ( $file );
		//process the template (and filter the output)
	}
}

?>