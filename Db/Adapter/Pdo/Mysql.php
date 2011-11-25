<?php
/**
 * @author zefiryn
 * Class that extends Zend_Db_Adapter_Pdo_Mysql for logging queries with phpQuickProfiler
 */

class Zefir_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql {

	/**
	 * State whether or not php Quick Profiler is in use
	 * @var boolean
	 */
	protected static $_logging;
	
	/**
	 * Array with queries
	 * @var array
	 */
	public static $queries = array();
	
	/**
	 * Number of queries
	 * @var int
	 */
	public static $queryCount;
	
	
	public function __construct($config = array()){
		
		$options = Zend_Registry::get('options');
		self::$_logging = $options['pqprofiler']['enabled'];
		
		parent::__construct($config);
	}

	 public function query($sql, $bind = array()) {
	 	if (self::$_logging) {
	 		if ($sql instanceof Zend_Db_Select){ 
	 			$stmt = $sql->__toString();
	 		}
	 		else {
	 			$stmt = $sql;
	 		}
	 		$start = $this->getTime();	 		
	 	}
	 	
	 	$return = parent::query($sql, $bind);
	    
	 	if (self::$_logging) {
	 		$this->logQuery($stmt, $start);
	 	}
	 	
	    return $return;
	 }
	
	 public function logQuery($sql, $start) {
	    $query = array(
	        'sql' => $sql,
	        'time' => ($this->getTime() - $start)*1000
	    );
	    self::$queryCount += 1;
	    array_push(self::$queries, $query);
	}
	 
	public function getTime() {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;
		return $start;
	}
}