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
	 * @var string
	 */
	protected $_fetchingChildren = null;
	
	/**
	 * @var array
	 */
	protected $_with = array();
	
	/**
	 * Setup model to handle image attachments
	 * $_image stores basic data about the attachment and it should look like this 
	 * array(
	 * 		'property' => model property where image name is stored 
	 * 		'dir' => directory in which the file is saved 
	 * );
	 * @var array
	 */
	protected $_image = array();
	
	/**
	 * Setup model to handle image attachments
	 * $_imageData include information about miniatures that can be created
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
	
	protected static $_cache;
	
	protected static $_tables;
	 

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
		
		
		if (!self::$_cache !== null && Zend_Registry::isRegistered('cache')) 
		{
			self::$_cache = Zend_Registry::get('cache');
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
	
	public function getTableName()
	{
		return $this->getDbTable()->getTableName();
	}
	
	public function getPrimaryKey()
	{
		return $this->getDbTable()->getPrimaryKey();
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
		//Zefir_Pqp_Classes_Console::logSpeed('Fetching data for '.$name);
		//retrieve data from parent model
		if ($this->_isBelongsTo($name))
		{	
			$return = $this->_getParent($name);
		}
		
		//retrieve data from child model
		elseif ($this->_isHasMany($name))
		{
			$return = $this->_getChild($name);
		}
		
		//return the property itself
		elseif (property_exists($this, $name))
		{
			$return = $this->$name;
		}
		
		//return FALSE if property doesn't exist in this model
		else
			$return = FALSE;
		
		//Zefir_Pqp_Classes_Console::logSpeed('Data for '.$name. ' fetched');

		return $return;
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
	public function populate($row)
	{
		if ($row != null && (is_array($row) || method_exists($row, 'toArray')))
		{
			$properties = array_keys(get_object_vars($this));
			
			$row = (is_array($row)) ? $row : $row->toArray();
			
			foreach($properties as $property)
			{
				if (isset($row[$property]))
					$this->$property = $row[$property];
				
			}
		}
		return $this;
	}
	
	/**
	 * Add children name that will be fetch when data from the database are received
	 * 
	 * @access public
	 * @param string|array $name name or array of name that will be fetched with the resource
	 * @return Zefir_Application_Model $this
	 */
	public function with($names)
	{
		if (!is_array($names))
		{
			$names = array($names);
		}
		
		$this->_with += array_diff($names, $this->_with);
		
		return $this;
	}
	/**
	 * Get an array of all data from the database or cache
	 * 
	 * @access public
	 * @param mixed $args
	 * @return array an array of Zefir_Application_Model
	 */
	public function fetchAll()
	{
		if (self::$_cache != null)
		{
			$set = self::$_cache->load(get_class($this));
			if ($set == null)
			{
				$fetched = $this->_fetchAll();
				$set['data'] = $fetched;
				self::$_cache->save($set, get_class($this), array('table', get_class($this)));	
			}
			$return = $set['data'];
		}
		else
		{
			$return = $this->_fetchAll();
		}
		
		return $return;
		
	}
	
	/**
	 * Get all data accroding to specified arguments
	 * 
	 * @access public
	 * @param mixed $args
	 * @return array $set
	 */
	public function getAll($args = null)
	{
		$rowset = $this->getDbTable()->getAll($args);
		$set = array();
		foreach ($rowset as $row)
		{
			$object = new $this;
			$set[] = $object->populate($row);
		}
		
		return ($set);
	}
	
	/**
	 * Add child data to the current object
	 * 
	 * @access public
	 * @param string $name the name of the child resource
	 * @param Zefir_Application_Model $object the children/parent object
	 * @return Zefir_Application_Model $this
	 */
	public function addChild($object, $name)
	{
		$id = $object->getPrimaryKey();
		if ($this->_isBelongsTo($name))
		{
			$this->$name = $object;
		}
		else
		{
			$children = $this->$name;
			if (!is_array($children))
			{
				$children = array();
			}
			
			$oid = $object->$id;
			if($oid != null && !isset($children[$oid]))
			{
				$children[$oid] = $object;
				$this->$name = $children;
			}
			else
			{
				$children[] = $object;
				$this->$name = $children;
			}
		}
		
		return $this;
	}
	
	protected function _fetchAll()
	{
		$rowset = $this->getDbTable()->fetchAll();

		$this->_retrieveFetchedData($rowset);
		
		$set = array();
		$primaryKey = $this->getDbTable()->getPrimaryKey();
		foreach ($rowset as $row)
		{
			$object = new $this;
			$set[$row->$primaryKey] = $object->populate($row);
		}
		
		return ($set);
	}
	
	/**
	 * Create array with objects from mysql resource
	 * 
	 * @access private
	 * @param Zend_Db_Rowset $rowset
	 * @return array $set;
	 * @todo add fetching children data if _with is not empty
	 */
	protected function _retrieveFetchedData($rowset)
	{
		$set = array();
		$primaryKey = $this->getDbTable()->getPrimaryKey();
		foreach ($rowset as $row)
		{
			$object = new $this;
			$set[$row->$primaryKey] = $object->populate($row);
		}
	}
	
	public function countAll()
	{
		$table = $this->getDbTable();
		$select = $table->select()->from($table->getTableName(), array('COUNT(*) as count'));
		$count = $table->fetchAll($select)->current();
		
		return $count['count'];
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
	protected function _compareObjects($a, $b, $properties, $direction = 'ASC')
	{
		foreach($properties as $property)
		{
			if (mb_strtolower($a->$property, 'UTF8') <> mb_strtolower($b->$property, 'UTF8'))
			{
				if ($direction == 'ASC')
				{
					return (mb_strtolower($a->$property, 'UTF8') < mb_strtolower($b->$property, 'UTF8')) ? -1 : 1;
				}
				else
				{
					return (mb_strtolower($a->$property, 'UTF8') > mb_strtolower($b->$property, 'UTF8')) ? -1 : 1;
				}
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
		$reflection = new ReflectionObject($this);
		foreach($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop)
		{
			$properties[$prop->getName()] = 1;
		};
		foreach ($data as $key => $value)
		{
			if (strstr($key, 'Cache'))
			{
				$key = substr($key, 0, strpos($key, 'Cache'));
			}
			if (isset($properties[$key])) {
				$this->$key = $value;
			}
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
		if (Zend_Registry::isRegistered('cache'))
		{
			$cache = Zend_Registry::get('cache');
			$cache->remove(get_class($this));
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array(get_class($this)));
		}
		
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
		if (Zend_Registry::isRegistered('cache'))
		{
			$cache = Zend_Registry::get('cache');
			$cache->remove(get_class($this));
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('table'));
		}
		$this->getDbTable()->delete($this);
		if (count($this->_image) > 0 )
		{
			if (file_exists($this->_getFileFolder().$this->path))
			{
				unlink($this->_getFileFolder().$this->path);
			}
				
			foreach($this->_imageData as $key => $data)
			{
				if (file_exists($this->_getFileFolder().$this->getImage($key)))
				{
					unlink($this->_getFileFolder().$this->getImage($key));
				}
			}
		}
	}
	
	protected function _getFileFolder()
	{
		return APPLICATION_PATH.'/../public'.$this->_image['dir'].'/';
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
			$refColumn = $association['column'];
			$column = $association['refColumn'];
			
			$tableData = $this->_getTable($association);
			
			if (isset($tableData[$this->$column]))
			{
					$parentModel = $tableData[$this->$column];
			}
			else 
			{
				$parentModel = new $association['model'];
			}
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
			$association = $this->_isHasMany($name);
			//$column = $association['refColumn'];

			$tableData = $this->_getTable($association, $this->getDbTable()->getTableName(), $this->getDbTable()->getPrimaryKey());
			$set = $tableData;
			
			if (isset($association['order']))
			{
				$this->_fetchingChildren = $name; 	
				//usort($set, array($this, '_sortChildren'));
			}
			
			$this->$name = $set;
		}
		return $set;
	}
	
	/**
	 * Get array of objects for the related table from Zend_Cache
	 * 
	 * @access private
	 * @param string $association association data
	 * @param string $parentName name of the parent resource (the property under which it will be stored in the model)
	 * @param string $parentColumnName name of the parent column name which has the key to find related resources
	 * @return array
	 */
	protected function _getTable($association, $parentName = null, $parentColumnName = null)
	{
		$modelName = $association['model'];
		//if no data in $_tables property, get them from cache of fetch
		if (!isset(self::$_tables[$modelName]))
		{
			
			//array of cached table; each value if Db_Row
			if (self::$_cache) 
			{
				$tableData = self::$_cache->load($modelName);
			}
			
			if (!isset($tableData) || !$tableData)
			{
				//load entire table data from the database
				$tableData = $this->_fetchTableData($modelName, $parentName, $parentColumnName, null, $association);
				
				//cache data
				if (self::$_cache)
				{
					self::$_cache->save($tableData, $modelName, array('table', get_class($this), $modelName));
				}
			}
			self::$_tables[$modelName] = $tableData;
		}
		
		
		if ($parentName && isset(self::$_tables[$modelName][$parentName][$this->$parentColumnName]))
		{//cache has needed data, just retrievie it
			
			return $this->_retrieveParentChildren($modelName, $parentName, $this->$parentColumnName);
		}
		
		elseif ($parentName && !isset(self::$_tables[$modelName][$parentName][$this->$parentColumnName]))
		{//no needed data found
			
			if (isset(self::$_tables[$modelName][$parentName]))
			{//no data in the database
				$return = array();
			}
			else
			{//fetch data from the database
				
				$tableData = $this->_fetchTableData($modelName, $parentName, $parentColumnName, self::$_tables[$modelName]);
				
				//get data if there are any
				$return = isset(self::$_tables[$modelName][$parentName][$this->$parentColumnName]) ? 
					$this->_retrieveParentChildren($modelName, $parentName, $this->$parentColumnName) : array();
			}
			return $return;
		}
		else
		{//basic data				
			return isset(self::$_tables[$modelName]['data']) ? self::$_tables[$modelName]['data'] : array();
		}
	}
		
	/**
	 * Get associations for many-to-many using join table
	 * 
	 * @param array $association an array of association data to retrieve
	 * @return array $schema an array to use with self::$_tables
	 */
	protected function _retrieveJoinSchema($association)
	{
		if ($association['joinModel']) 
		{
			$joinModel = new $association['joinModel']();
			$rowset = $joinModel->getDbTable()->fetchAll();
		}
		else
		{
			$rowset = $this->getDbTable()->fetchAllFrom($association['joinTable']);
		}
		
		$tableName = $this->getDbTable()->getTableName();
		$modelRefColumn = $association['refColumn'];
		$childRefColumn = $association['joinRefColumn'];
		$schema = array();
		foreach($rowset as $row)
		{
			$schema[$tableName][$row->$modelRefColumn][] = $row->childRefColumn;
		}
		
		return $schema;
	}
	
	/**
	 * Fetch related data from the database and store it in cache
	 * 
	 * @access private
	 * @param string $modelName  the name of the model of related resorces
	 * @param string $parentName the name of the parent resource (the property under which it will be stored in the model)
	 * @param string $parentColumnName name of the parent column name which has the key to find related resources
	 * @param array $tableData already created table with data
	 * @param array $association an association data array if the related parent is from many-to-many relation
	 * @return array $tableData;
	 */
	protected function _fetchTableData($modelName, $parentName = null, $parentColumnName = null, $tableData = null, $association = array()) 
	{
		$tableData = $tableData == null ? array() : $tableData;

		$basic = isset($tableData['data']) ? FALSE : TRUE;
		$addParent = $parentName == null ? FALSE : TRUE;
		$join = (isset($association['joinModel']) || isset($association['joinTable']));
		
		$model = new $modelName;
		$primaryKey = $model->getDbTable()->getPrimaryKey();
		
		if ($addParent && !$join) 
		{
			$tableData[$parentName] = array();
		}
		elseif ($addParent && $join)
		{//add many-to-many schema
			$tableData += $this->_retrieveJoinSchema($association);
		}
		
		if (isset($association['order'])) {
			$select = $model->getDbTable()->select()->order($association['order']);
		}
		else {
			$select = null;
		}
		foreach($model->getDbTable()->fetchAll($select) as $row)
		{
			$obj = new $modelName;
			$obj->populate($row);
			//add parent data
			if ($addParent && !$join) 
			{
				$tableData[$parentName][$obj->$parentColumnName][] = $obj->$primaryKey;
			}
			
			//add basic data
			if ($basic) 
			{
				$tableData['data'][$obj->$primaryKey] = $obj;
			}
		}
		
		//save new results
		if (self::$_cache !== null)
		{
			self::$_cache->save($tableData, $modelName, array('table'));
		}
		
		self::$_tables[$modelName] = $tableData;
		
		return $tableData;
	}
	
	/**
	 * Get an array of objects associated with current model
	 * 
	 * @access private
	 * @param string $modelName name of the model of related objects
	 * @param string $parentName name of the model for which data has to be created 
	 * @param int $parentId id of the object
	 * @return array $set an array of related objects
	 */
	protected function _retrieveParentChildren($modelName, $parentName, $parentId)
	{
		$set = array();
		$objects = self::$_tables[$modelName]['data'];
		foreach(self::$_tables[$modelName][$parentName][$parentId] as $id)
		{
			$set[] = $objects[$id];
		}
		
		return $set;
	}
	
	/**
	 * Fetch all data of this class from the database and store it in cache
	 * 
	 * @access private
	 * @return array|false return array of objects or false if cache is disabled
	 */
	protected function _createCachedTable()
	{
		if (self::$_cache != null)
		{
			$table = self::$_cache->load(get_class($this));
			if (!$table)
			{
				$table['data'] = array();
				$key = $this->getDbTable()->getPrimaryKey();
				foreach($this->_fetchAll() as $obj)
				{
					$table['data'][$obj->$key] = $obj; 
				}
			}
			self::$_cache->save($table, get_class($this), array('table', get_class($this)));
			
			return $table;
		}
		
		return false;
	}
	
	/**
	 * Call compare objects with order array
	 * 
	 * @access private
	 * @param  object $a
	 * @param  object $b
	 * @return boolean
	 */
	protected function _sortChildren($a, $b)
	{
		$assoc = $this->_isHasMany($this->_fetchingChildren);
		
		$order = $assoc['order'];
		if (!is_array($order)) 
		{
			$order = array($order);
		}
		
		//default direction
		$direction = 'ASC';
		
		foreach($order as $id => $orderStmt) 
		{
			if (strstr($orderStmt, 'ASC') || strstr($orderStmt, 'DESC'))
			{
				$parts = explode(' ', $orderStmt);
				$order[$id] = $parts[0];
				$direction = $parts[1];
			}
		}
		
		return $this->_compareObjects($a, $b, $order, $direction);
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
			$fileName = str_replace('.'.$ext, '_'.$key.'.'.$ext, $file);
			$check= APPLICATION_PATH.'/../public'.$this->_image['dir'].'/'.$fileName;
			if (!file_exists($check)) {
				try { 
				  $this->resizeImage();
			  } catch (Exception $e) {
			    $fileName = null;
			  }
			}
			return $fileName;
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
	
	public function removeImage() 
	{
		if (isset($this->_image))
		{
			$dir = APPLICATION_PATH.'/../public'.$this->_image['dir'].'/';
			$property = $this->_image['property'];
			
			foreach($this->getThumbnails() as $key)
			{
				unlink($dir.$this->getImage($key));
			}
			unlink($dir.$this->$property);
			return TRUE;
		}
		
		return FALSE;
		
	}
	
	/**
	* Add order to Zend_Db_Select statement
	*
	* @access public
	* @param string $string
	* @return Zend_Db_Select
	*/
	public function order($string) 
	{
		if (strstr($string, 'ASC') || strstr($string, 'DESC')) 
		{
			$array = explode(' ', trim($string));
			$string = array($array[1], $array[2]);			
		} 
		return $this->getDbTable()->select()->order($string);
	}
	
	/**
	 * Add where to Zend_Db_Select statement
	 * 
	 * @access public
	 * @param mixed $condition
	 * @param mixed $value
	 * @return Zend_Db_Select
	 */
	public function where($condition, $value) 
	{
		return $this->getDbTable()->select()->where($condition, $value);
	}
	
	/**
	 * Perform search in the database according to the sepcified column 
	 * 
	 * @access public
	 * @param string $column column name
	 * @param mixed $val column value
	 * @return Zefir_Application_Model|array
	 */
	public function getBy($column, $val)
	{
		$rowset = $this->getDbTable()->getBy($column, $val);
		
		if (count($rowset) == 1)
		{
			return $this->populate($rowset->current());
		}
		else
		{
			$set = array();
			foreach($rowset as $row)
			{
				$obj = new $this;
				$set[] = $obj->populate($row);
			}
			
			return $set;
		}
	}
	
	/**
	 * Perform search in the database and return the object or an array of object matching criteria
	 * 
	 * @access public
	 * @param array $conditions an array of conditions 
	 * @param boolean $and whether all criteria has to be met or at least one of them  
	 * @param boolean $strict use strict comparison
	 * @return Zefir_Application_Model|array
	 */
	public function find($conditions, $and = true, $strict = true)
	{
		$rowset = $this->getDbTable()->findData($conditions, $and, $strict);
		
		if (count($rowset) == 1)
		{
			$this->populate($rowset->current());
			return $this;
		}
		elseif (count($rowset) > 1)
		{
			$set = array();
			foreach($rowset as $row)
			{
				$obj = new $this;
				$set[] = $obj->populate($row);
			}
			return $set;
		}
		else {
			return $this;
		}
	}
	
	/**
	* Save message in a log
	*
	* @access private
	* @param mixed $message
	* @return void
	*/
	protected function _log($message)
	{
		$log = Zend_Registry::get('log');
		if (is_bool($message) || is_null($message)) {
			$message = var_export($message, true);
		} else {
			$message = print_r($message, true);
		}
	
		if ($log)
		{
			$log->log($message, Zend_Log::DEBUG);
		}
	
	}
	
	public function logCache()
	{
		$this->_log(self::$_tables);
	}
}


?>