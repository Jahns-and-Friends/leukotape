<?php

/**
 * @name    Object Storage Class
 * @author  Marco Biechl
 *
 * @version 1.1 - 06.12.2010 - changes to constructor
 *                           - added copy method
 *                           - added & modified s_db_getTableName()
 * @version 1.2 - 08.02.2011 - added getValues() method
 *                           - added setValue / getValue Alias
 *                           - added getFields()
 * @version 1.21 - 26.02.2011 - added getValues() method
 *                            % fixed major bug in constructor where the loading
 *                              part would never be executed, IF tablesynch is active.
 *                            + added compatiblity for new tabledefinition
 *                            % fixed setStatic so that it accepts arrays as value
 *                            % fix in save method, where updaterow returned false instead of id
 * @version 1.22 - 13.08.2012 - fixed reference NOTICE in dump method
 *
 */

abstract class ObjStorage  {


	protected $_bChanged 	 = false;
	protected $_aData    	 = array();
	protected $_nId      	 = 0;
	//protected $_bLoaded  	 = false;
	protected $_bWriteTrough = false;
	protected $_bDummy	     = false;
    protected $_aTempData    = array();

	/**
	 * Two options:
	 *   true: this will check if the table structure is in synch every time this object is
	 *         created for the first time. lazy mode.
	 *
	 *   false: table structure will not be synched. so if you alter the table definition or
	 *          use the object for the very first time, you need to init the table yourself.
	 * 			fast mode.
	 *
	 * @var boolean synch table structure
	 */
	protected static $_bSynchTable  = false;
	protected static $_bRegistered  = false;

	protected static $_s_bDbExists  = null;
	protected static $_s_bDbSynched = array();

	protected static $_s_db_sTablename;
	protected static $_s_db_aTabledef;

	protected static $_s_aCleanup   = array();
	protected static $_s_bCleanupInitialized = false;

	protected $_bCleanupRegistered = false;


	/**
	 * Constructor
	 *
	 * @version 1.1 - switched id check in case $id is not false. array first.
	 *              - bugfix in array foreach - was iterating over non existing $retVal
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function __construct($id=false)  {
            // one-time check if database is present
            #if (self::$_s_bDbExists == null) self::$_s_bDbExists = function_exists('db');
            #if (!self::$_s_bDbExists) return false;

            // one-time check if database table exists and is in sync
            if ($this->getStatic('_bSynchTable'))  {
            $t = $this->getStatic('_s_db_sTablename');
            $s = $this->getStatic('_s_bDbSynched');
            if (!isset($s[$t])) $this->_syncTable();
            $s = $this->getStatic('_s_bDbSynched');
                if (!isset($s[$t])) return false;			
            }

            if ($id !== false)  {            
                if (is_array($id) && !empty($id))  {
                        if (!isset($id['id'])) return false; // good to have data, but we need to know the id as well
                        // fill data
                        $this->_nId = $id['id'];
                        foreach($id as $key => $value) $this->_aData[$key] = array($value, false);
                        //$this->_bLoaded = true;
                } else if (preg_match("/^[0-9]+$/", $id) !== false)  {
                        // set id and try to load data
                        $this->_nId = $id;
                        if (!($this->_load())) return false;
                        //$this->_bLoaded = true;
                }
            }

        #echo "parent of ".$this->getStatic('_s_db_sTablename')." loaded<br />";
	}

	public function __destruct()  {
	}

   // Statische Variablen aus Child holen
   public function          getStatic($key)   { return(eval('return('.get_class($this).'::$'.$key.');')); }
   public function          setStatic($key, $value)   { eval(get_class($this).'::$'.$key." = \$value;"); }

   /**
    * @uses PHP >= 5.3.0
    * @param string $key
    * @return unknown
    */
   public static function s_getStatic($key) {
       return(eval('return('.get_called_class().'::$'.$key.');'));
   }

    public function get($field)  {
        // first try to locate in cache
        if (isset($this->_aData[$field])) return $this->_aData[$field][0];
        // if not found, but ID is set, try to load from database
        if (!$this->_nId || !($this->_load())) return null; // load all? maybe faster than to fetch single items
        if (isset($this->_aData[$field][0])) return $this->_aData[$field][0];
        else return null;
    }

    public function set($field, $value)  {
        $field = trim($field);
        if ($field == 'id') return false;
        // see if field already exists and has a value. if so, see if value has changed
        if (isset($this->_aData[$field]))  {
            // if value differs, mark as changed
            if ($this->_aData[$field][0] !== $value) $this->_aData[$field][1] = true;
        } else $this->_aData[$field] = array(null, true);
        // set new value

        $this->_aData[$field][0] = $value;
        // if writetrough active and not a dummy object, save to database
        if ($this->_bWriteTrough && $this->_nId && !$this->_bDummy)  {
            return $this->_writeField($field, $value);
        }

        if (!$this->_bCleanupRegistered)  {
            self::_s_registerForCleanup($this);
            $this->_bCleanupRegistered = true;
        }
        return true;
    }

    public function getValue($field)            { return $this->get($field); }
    public function setValue($field, $value)    { return $this->set($field, $value); }

    /**
     * Fetch all values
     *
     * @since v1.2
     */
    public function getValues($parse=true)  {
        $def = $this->getStatic('_s_db_aTabledef');
        if (empty($def)) return array();
        if (isset($def['fields'])) $t = $def['fields'];
        else $t = $def;
        $data = array();
        foreach( $t as $key => $value)  {
            $foo = $this->get($key,$parse);
            if ($foo !== false) $data[$key] = $foo;
        }
        return $data;
    }


    /**
     * Set multiple values
     *
     * @since v1.21
     */
    public function setValues($fields)  {
        $def = $this->getStatic('_s_db_aTabledef');
        if (empty($def)) return false;
        if (isset($def['fields'])) $t = $def['fields'];
        else $t = $def;
        foreach($fields as $key => $value)  {
            if (!isset($t)) continue;
            $this->set($key, $value);
        }
        return true;
    }


    /**
     * Fetch all defined fields
     *
     * @since v1.2
     */
    public function getFields()  {
        $def = $this->getStatic('_s_db_aTabledef');
        if (empty($def)) return array();
        if (isset($def['fields'])) $t = $def['fields'];
        else $t = $def;
        $data = array();
        foreach( $t as $key => $value)  {
            $foo = $this->get($key);
            if ($foo !== false) $data[$key] = $key;
        }
        return $data;
    }
    
    /**
     * Fetch all defined fields (static)
     *
     * @since v1.211
     */
    public function s_getFields()  {
        $def = self::s_getStatic('_s_db_aTabledef');
        if (empty($def)) return array();
        if (isset($def['fields'])) $t = $def['fields'];
        else $t = $def;
        $data = array();
        foreach( $t as $key => $value)  {
             $data[$key] = $key;
        }
        return $data;
    }


    public function save()  {
        if ($this->_bDummy) return false;
        $changed = array();
        foreach($this->_aData as $key => $value) { if ($value[1]) { $changed[$key] = $value[0]; } }

        if (empty($changed))  {
            return (($this->_nId > 0) ? $this->_nId : false); //
        }

        if (!$this->_nId)  {
            if (!($this->_nId = db()->insertRow($this->getStatic('_s_db_sTablename'), $changed)))   {
                return false;
            }
        } else {
            db()->updateRow($this->getStatic('_s_db_sTablename'), $this->_nId, $changed);
        }

        foreach($this->_aData as $key => $value) $this->_aData[$key][1] = false; // mark as not changed
            return $this->_nId;
	}

	public function setDummy($boolean)  { $this->_bDummy = (bool)$boolean; }
	public function getDummy()  		{ return $this->_bDummy; }



	/**
	 *
	 * P R O T E C T E D  M E T H O D S
	 *
	 */

	protected function _load($bOverwrite=false)  {
		if (!$this->_nId) return false;
		$data = db()->getRow($this->getStatic('_s_db_sTablename'), $this->_nId);
		if (!$data || !is_array($data) || empty($data)) return false;
		foreach($data as $key => $value)  {
			if (!isset($this->_aData[$key]) || $bOverwrite) $this->_aData[$key] = array($value, false);
		}
		return true;
	}

	protected function _loadField($sFieldname)  {
		if (!$this->_nId) return false;
		if (!($retVal = db()->getRowCell($this->getStatic('_s_db_sTablename'), $this->_nId, $sFieldname))) return false;
		$this->_aData[$sFieldname] = array($retVal, false);
		return $retVal;
	}

	protected function _writeField($sFieldname, $value)  {
		if (!strlen($sFieldname) && !$this->_nId) return false;
		if (!db()->setRowCell($this->getStatic('_s_db_sTablename'), $this->_nId, $sFieldname, $value)) return false;
		$this->_aData[$sFieldname] = array($value, false);
		return true;
	}

	protected function _syncTable()  {
        $tablename   = $this->getStatic('_s_db_sTablename');
        $tablestruct = $this->getStatic('_s_db_aTabledef');
        if (strlen($tablename) && is_array($tablestruct) && !empty($tablestruct))  {
            db()->createTable($tablename, $tablestruct);
        }
        $s = $this->getStatic('_s_bDbSynched');
        $s[$tablename] = true;
        $this->setStatic('_s_bDbSynched', $s);
	}

	/**
	 * Copy object (w/o id)
	 *
	 * @since 1.1
	 *
	 * @return boolean|object
	 */
	public function copy()  {
	    if (empty(self::$_s_db_aTabledef)) return false;
	    $className = get_class($this);
	    $tabledef  = $this->getStatic('_s_db_aTabledef');
	    if (empty($tabledef)) return false;
	    $copy = new $className();
	    foreach($tabledef as $key => $definition)  {
            $copy->set($key, $this->get($key));
	    }
	    return $copy;
	}

	/**
	 * AUTO SAVE ON CLEANUP
     *
     * so what if database or frame gets destructed first? oO
	 */

	protected static function _s_initCleanup()  {
            if (self::$_s_bCleanupInitialized) return true;
            register_shutdown_function(array('ObjStorage', 's_cleanup'));
            self::$_s_bCleanupInitialized = true;
	}

	protected static function _s_registerForCleanup($oSelfReference)  {
	    return true;
            if (!self::$_s_bCleanupInitialized) self::_s_initCleanup();
            if (array_search($oSelfReference, self::$_s_aCleanup, true) === false)
                    self::$_s_aCleanup[] = $oSelfReference;
	}

	public static function s_cleanup()  {
            if (!empty(self::$_s_aCleanup)) { foreach(self::$_s_aCleanup as $oRef) $oRef->save(); }
	}

	/**
	 * @since 1.1
	 * @uses PHP >= 5.3.0
	 *
	 * @return unknown
	 */
	public static function s_db_getTableName()  {
	    return self::$_s_db_sTablename;
	    die("fix me");
	    #return self::$_s_db_sTablename; // nope. returns objStorage data, not that of the inherited class. think about it, it makes sense
	    return(eval('return('.get_class(self).'::$_s_db_sTablename);'));
	}

    /**
     * Dump class to dig.php for debugging
     *
     * @return <boolean|array>
     */
    public function dump()  {
        // access to this is restricted to dig.php        
        $tmp    = debug_backtrace(); // @since 1.22
        $caller = array_shift($tmp);
        $caller = basename($caller['file']);
        if ($caller !== 'dig.php') return false;
        // fetch internal data of object and remove the "changed"-flags.
        $data = $this->_aData;
        $out  = array();
        if (!$data) return $out;
        foreach($data as $key => $value)  $out[$key] = $value[0];
        return $out;
    }

    public function setTemp($key, $value)  {
        $this->_aTempData[$key] = $value;
    }
    public function getTemp($key)  {
        return ((!isset($this->_aTempData[$key])) ? NULL : $this->_aTempData[$key]);
    }
    public function unsetTemp($key)  {
        if (isset($this->_aTempData[$key])) unset($this->_aTempData[$key]);
    }
    /*
    public static function s_delete($id)  {

        dig(self::$_s_db_aTabledef);
        
        eval('echo('.get_class(self).'::$_s_db_sTablename);');
        
        
        if (!isset(self::$_s_db_aTabledef)) return false;
        if (!isset(self::$_s_db_aTabledef['deleted'])) return false;
        
        $this->set('deleted', 1);
        self->save();
        return;
         
         
    }
    */
}

?>