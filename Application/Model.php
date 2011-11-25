<?php
/**
 * @package Zefir_Application_Model
 */
/**
 * Main class for all models
 * In order to function properly the model needs to have defined publi properties
 * which names correspond to the columns names in the database table.
 * For accessing related data defined in Zefir_Application_Model_DbTable class there need 
 * to be defined protected properties which names match the key in 
 * $_belongsTo and $_hasMany arrays
 * 
 * @author zefiryn
 * @since Jan 2011
 *
 */
class Zefir_Application_Model {
	
	/**
	 * Zefir_Application_Model_DbTable class
	 * 
	 * @var Zend_Db_Table
	 */
	protected $_dbTable;
	
	
	/**
	 * Name of the Zefir_Application_Model_DbTable class related to this model
	 * 
	 * @var string
	 */
	protected $_dbTableModelName;
	
	
	/**
	 * Set this to TRUE if this model has no table in the database
	 * 
	 * @var boolean
	 */
	protected $_externalModel = FALSE;
	
	/**
	 * Setup model to handle image attachments
	 * $_image stores basic data about the attachment and it should look like this 
	 * array(
	 * 		'property' => model property where image name is stored 
	 * 		' dir' => directory in which the file is saved 
	 * );
	 * @var array
	 */
	protected $_image = array();
	
	/**
	 * Setup model to handle image attachments
	 * $_imageData include information about miniatures that are can be created
	 * $_imageData is an array of arrays that look like this:
	 * array(
	 * 		'thumbName' => array(
	 *	 		'width' => new width in pixels,
	 *			'height' => new height in pixels,
	 *			'crop' => TRUE/FALSE - states whether crop the thumbnail or not 
	 *			'ratio' => width/height  - save file ratio according to new width or height
	 * 		) 
	 * );
	 * @var array
	 */
	protected $_imageData = array();
	 
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param array $options
	 * @param int $id
	 * @throws Exception
	 * @return void
	 */
	public function __construct($id = NULL, $options = NULL) 
	{
	    if ( null != $this->_dbTableModelName ) 
	    {
			$this->setDbTable($this->_dbTableModelName);
	    }
	    else 
	    {
	    	if (FALSE == $this->_externalModel)
				throw new Exception('Invalid table data gateway provided');
	    }
	    
		if (is_array($options)) 
	    {
			$this->setOptions($options);
	    }
	    
	    //if there is id given retrieve data from the db and populate the model
	    if ($id != null)
	    {
	    	$row = $this->getDbTable()->find($id)->current();

	    	if ($row)
	    		$this->populate($row);	
	    }
	    
	    return $this;
	}
	
	
	/**
	 * Set Zend_Db_Table object for current model
	 * 
	 * @access public
	 * @param Zend_Db_Table_Abstract $dbTable
	 * @throws Exception
	 * @return Zefir_Application_Model_Mapper $this
	 */
	public function setDbTable($dbTable) 
	{
	    if (is_string($dbTable)) 
	    {
			$dbTable = new $dbTable();
	    }
	    if (!$dbTable instanceof Zend_Db_Table_Abstract) 
	    {
			throw new Exception('Invalid table data gateway provided');
	    }
	    $this->_dbTable = $dbTable;
	    return $this;
	}
	 
	/**
	 * Get current Zend_Db_Table object
	 * 
	 * @access public
	 * @return Zend_Db_Table $_dbTable
	 */
	public function getDbTable() 
	{
	    if (null === $this->_dbTable) 
	    {
			$this->setDbTable($this->_dbTableModelName);
	    }
	    return $this->_dbTable;
	}
 
	/**
	 * Magic function for setting properties of the object
	 * 
	 * @access public
	 * @param string $name
	 * @param mixed $value
	 * @return Zefir_Application_Model
	 */
	public function __set($name, $value) 
	{
		if (property_exists($this, $name))
		{
	    	$this->$name = $value;
		}
		
		return $this;
	}
 
	/**
	 * Magic function for getting properties of the object
	 * 
	 * @access public
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) 
	{
		//retrieve data from parent model
    	if ($this->_isBelongsTo($name))
    	{	
    		return $this->_getParent($name);
    	}
    	
    	//retrieve data from child model
    	elseif ($this->_isHasMany($name))
    	{
    		return $this->_getChild($name);
    	}
    	
    	//return the property itself
    	elseif (property_exists($this, $name))
    	{
	    	return $this->$name;
    	}
    	
    	//return FALSE if property doesn't exist in this model
    	else
    		return FALSE;

	}
	
	/**
	 * Magic function for calling methods
	 * 
	 * @access public
	 * @param string $name
	 * @param mixed $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (preg_match('/^_has[a-zA-Z]+$/', $name))
		{
			$property = strtolower(substr($name, 4));
			if ($this->_isHasMany($property))
				return $this->_hasChild($property);
		}
	}
 
	/**
	 * Set multiple object properties at once
	 * 
	 * @access public
	 * @param array $options
	 * @return Zefirn_Application_Model $this
	 */
	public function setOptions(array $options) 
	{
	    foreach ($options as $key => $value) 
	    {
	    	$var = '_'.$key;
	    	if (property_exists($this, $var))
				$this->$var = $value;
	    }
	    return $this;
	}
		   
	/**
	 * Convert the object to an array
	 * 
	 * @access public
	 * @return array $array
	 */
	public function toArray() 
	{
	 	   
    	return get_object_vars($this);
	}
	
	public function prepareFormArray()
	{
		return get_object_vars($this);
	}
	
	/**
	 * Populate object with data from the Zend_Db_Table_Row
	 * 
	 * @access public
	 * @param Zend_Db_Table_Row|array $row
	 * @return Zefir_Application_Model $this
	 */
	public function populate(Zend_Db_Table_Row $row)
	{
		foreach ($row as $var => $value)
		{
			$this->$var = $value;
		}

		return $this;
	}
	
	/**
	 * Get an array of all data from the database
	 * 
	 * @access public
	 * @param mixed $args
	 * @return array an array of Zefir_Application_Model
	 */
	public function fetchAll($args = null)
	{
		if (Zend_Registry::isRegistered('set_'.get_class($this)))
		{
			$set = Zend_Registry::get('set_'.get_class($this));
		}
		else
		{
			$rowset = $this->getDbTable()->getAll($args);
			
			$set = array();
			foreach ($rowset as $row)
			{
				$object = new $this;
				$set[] = $object->populate($row);
			}
			
			Zend_Registry::set('set_'.get_class($this), $set);
		}
		
		return ($set);
	}
	
	/**
	 * Compare function used with usort function
	 * 
	 * @access private
	 * @param mixed $a
	 * @param mixed $b
	 * @param array $properties
	 * @return int
	 */
	protected function _compareObjects($a, $b, $properties)
	{
		foreach($properties as $property)
		{
			if (mb_strtolower($a->$property, 'UTF8') <> mb_strtolower($b->$property, 'UTF8'))
			{
				return (mb_strtolower($a->$property, 'UTF8') < mb_strtolower($b->$property, 'UTF8')) ? -1 : 1;
			}
		}
		
		//if none non-zero value was returned, return 0
		return 0;
		
	}
	
	/**
	 * Check if the object has been populated
	 * @access public
	 * @return boolean
	 */
	public function isEmpty()
	{
		$id_var = $this->getDbTable()->getPrimaryKey();

		return (($this->$id_var == NULL) ? TRUE : FALSE);
	}
	
	/**
	 * Get form data and populate object with it; references are not objects but id's of 
	 * rows in associated tables
	 * 
	 * @access public
	 * @param array $data
	 * @return Zefir_Application_Model
	 */
	public function populateFromForm($data)
	{
		foreach ($data as $key => $value)
		{
			if (strstr($key, 'Cache'))
			{
				$key = substr($key, 0, strpos($key, 'Cache'));
			}
			
			$this->$key = $value;
			
			
		}
		
		return $this;
	}
	
	/**
	 * Save object in the database
	 * 
	 * @access public
	 * @return void
	 */
	public function save()
	{
		return $this->getDbTable()->save($this);
	}
	
	/**
	 * Delete object from the database
	 * 
	 * @access public
	 * @return void
	 */
	public function delete()
	{
		$this->getDbTable()->delete($this);
	}
	
	/**
	 * Update new model data in the database
	 * 
	 * @access public
	 * @return Zefir_Application_Model
	 */
	public function update()
	{
		return $this->getDbTable()->save($this->_prepareUpdate());	
	}
	
	public function search($property, $value, $like = FALSE)
	{
		$rowset = $this->getDbTable()->search($property, $value);
		
		$found = array();
		foreach($rowset as $row)
		{
			$found[] = $this->populate($row);
		}
		
		return $found;
	}
	
	/**
	 * Prepare data for the update
	 * 
	 * @access private
	 * @return Zefir_Application_Model
	 */
	protected function _prepareUpdate()
	{
		$map = $this->getDbTable()->getReferenceMap();
		
		foreach($map as $reference)
		{
			$id = $reference['refColumns'][0];
			$this->$reference['objProperty'] = $this->$reference['objProperty']->$id;
		}
		
		return $this;
	}
	
	/**
	 * Check whether given property has belongs_to relation to other model 
	 * 
	 * @access private
	 * @param string $name
	 * @return array|boolean
	 */
	private function _isBelongsTo($name)
	{
		$belongsToArray = $this->getDbTable()->getBelongsTo();

		if (isset($belongsToArray[$name]))
			return $belongsToArray[$name];
		else
			return FALSE;
	}
	
	/**
	 * Check whether given property has has_many relation to other model 
	 * 
	 * @access private
	 * @param string $name
	 * @return array|boolean
	 */
	private function _isHasMany($name)
	{
		$hasManyArray = $this->getDbTable()->getHasMany();

		if (isset($hasManyArray[$name]))
			return $hasManyArray[$name];
		else
			return FALSE;
	}
	
	/**
	 * Check whether given model has any child according to the given property 
	 * 
	 * @access private
	 * @param string $property
	 * @return integer
	 */
	protected function _hasChild($property)
	{
		$hasManyArray = $this->getDbTable()->getHasMany();
		$primary = $this->getDbTable()->getPrimaryKey();

		$model = new $hasManyArray[$property]['model'];
		$modelTable = $model->getDbTable();
		return $modelTable->countParents($hasManyArray[$property]['refColumn'], $this->$primary);	
	}
	
	/**
	 * Get the model of the parent for the given property
	 * 
	 * @access private
	 * @param string $name
	 * @return Zefir_Model $parentModel
	 */
	protected function _getParent($name)
	{
		if (isset($this->$name))
		{
			return $this->$name;
		}
		else 
		{
			$association = $this->_isBelongsTo($name);
			$referenceColumn = $association['refColumn'];
			$parentModel = new $association['model']($this->$referenceColumn);
			$this->$name = $parentModel;
			
			return $parentModel;
		}
	}
	
	/**
	 * Get the array of children models for the given property
	 * 
	 * @access private
	 * @param string $name
	 * @return array $set
	 */
	protected function _getChild($name)
	{		
		if (isset($this->$name) && $this->$name != null)
		{	
			$set = $this->$name;
		}
		else
		{

			$id = $this->getDbTable()->getPrimaryKey();
			$set = $this->getDbTable()->getChild($this, $name); 
			$this->$name = $set;
		}
		return $set;
	}
	
	/**
	 * Get data about specified thumbnail
	 * 
	 * @access public
	 * @param string $key
	 * @return array|NULL $_imageData[$key] 
	 */
	public function getImageData($key)
	{
		if (isset($this->_imageData) && isset($this->_imageData[$key]))
			return $this->_imageData[$key];
		else 
			return NULL;
	}
	
	/**
	 * Get list of data about thumbnails
	 * 
	 * @access public
	 * @return array|NULL 
	 */
	public function getThumbnails()
	{
		if (isset($this->_imageData))
			return array_keys($this->_imageData);
		else
			return NULL;
	}
	
	/**
	 * Create path to the thumbnail file
	 * 
	 * @access public
	 * @param string $key
	 * @return string|boolean 
	 */
	public function getImage($key)
	{
		
		if (isset($this->_image) && array_key_exists($key, $this->_imageData))
		{
			$property = $this->_image['property'];
			$file = $this->$property;
			$ext = Zefir_Filter::getExtension($file);
			return str_replace('.'.$ext, '_'.$key.'.'.$ext, $file);
		}	
		return FALSE;
	}
	
	/**
	 * Make new thumbnail according to current settings
	 * 
	 * @access public
	 * @return boolean TRUE|FALSE
	 */
	public function resizeImage()
	{
		if (isset($this->_image))
		{
			$dir = APPLICATION_PATH.'/../public'.$this->_image['dir'].'/';
			$property = $this->_image['property'];
			
			$table = $this->getDbTable();
			foreach($this->getThumbnails() as $key)
			{
				$table->rerunResize($this, $property, $dir, $key);
			}
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function order($string) 
	{
		return $this->getDbTable()->select()->order($string);
	}
	
	public function where($condition, $value) 
	{
		return $this->getDbTable()->select()->where($condition, $value);
	}
}


?>