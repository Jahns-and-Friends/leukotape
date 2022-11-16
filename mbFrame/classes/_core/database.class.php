<?php

/**
 * Database abstraction class
 *
 * 2010 by Marco Biechl
 *
 * v1.1
 * 10.01.2011 - fixed bug in send() where a newline after command (SEND/INSERT/...) broke functionality.
 *
 * v1.11
 * 24.02.2011 - added createTable() and tableExists() methods
 *            - modified send to match for SHOW commands
 * 24.05.2011 - partially fixed SQL strings, so that reserved words get capsulated within ``
 *
 * v1.12
 * 25.08.2011 + added getTableHeader()
  * v1.13
 * 25.04.2012 + added type timestamp and datetime on createTable();
   * v1.14
 * 08.09.2012 + added escape characters to keys
 *
 * v1.15  - 13.06.2013 - added optional collation param to create table
 * v1.151 - 14.06.2013 - added query counter
 * v1.152 - 23.07.2013  - escape table name when creating table (reserved words)
 * v1.154 - 12.09.2013  - added more mysqli_free_result() to single() and send()
 * v1.155 - 03.06.2014 - added UNIQUE to createTable
 * v1.156 - 18.07.2014 - replaced newline replacing with preg_replace() to match \r\n instead of \n only
 * v1.157 - 04.02.2015 - send() added trim to $command. added breaks to switch (not necessary due to returns)

 * potential issue with insertRow/updateRow - updateRow seems to have a problem, I get tons of duplicate entries...
 * v1.578 - 17.11.2015 - added very basic support for ALTER query command
                       - single() - changed return value to false if query failed. did return error text which evald to true.
 * v1.6   - 02.08.2016 - added options to send() - remove newlines(default), remove comment lines


 need to add error log
 */

class Database {

    protected $_sDatabase = '';
    protected $_sUsername = '';
    protected $_sPassword = '';
    protected $_sHost     = '';
    protected $_oResource = null;

    protected static $_s_aReferences = array();

    protected static $_s_nQueryCount = 0;

    public static $s_bEnableQueryLog = false;

    protected static $_s_aQueryLog = array();

    /**
     * @since 1.6
     */

    const DB_OPT_FILTER_NEWLINES = 1;
    const DB_OPT_FILTER_COMMENTS = 2;

    protected function __construct($sHost, $sUsername, $sPassword, $sDatabase)  {
        if ( !strlen($sDatabase) || !strlen($sUsername) || !strlen($sPassword) || !strlen($sHost) )  return false;
        if (!($this->_oResource = mysqli_connect($sHost, $sUsername, $sPassword, $sDatabase))) return false;
        #if (!mysqli_select_db($sDatabase, $this->_oResource)) return false;

      #  mysqli_query("SET NAMES 'utf8'", $this->_oResource);
       # mysqli_query("SET CHARACTER SET 'utf8'", $this->_oResource);
       # mysqli_set_charset('utf8');

        mysqli_query(
            $this->_oResource,
            "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'"
        );

        $this->_sDatabase = $sDatabase;
        $this->_sUsername = $sUsername;
        $this->_sPassword = $sPassword;
        $this->_sHost     = $sHost;
    }

    public function __destruct()  {
        if ($this->_oResource) mysqli_close($this->_oResource);
        $this->_oResource = null;
    }

    public static function getInstance($sHost, $sUsername, $sPassword, $sDatabase)  {
        if (isset(self::$_s_aReferences[$sDatabase])) return self::$_s_aReferences[$sDatabase];
        if (!($ref = new Database($sHost, $sUsername, $sPassword, $sDatabase))) return false;
        self::$_s_aReferences[$sDatabase] = $ref;
        return self::$_s_aReferences[$sDatabase];
    }

    public function getRessource() { return $this->_oResource; }

    public function getInstanceByDbName($sDbName)   {
        return ((isset(self::$_s_aReferences[$sDbName])) ? self::$_s_aReferences[$sDbName] : false);
    }

    /* @since 1.151 */
    public function getQueryCount()             { return self::$_s_nQueryCount; }
    public function enableQueryLog($bEnable)    { return self::$s_bEnableQueryLog = $bEnable; }
    public function getQueryLog()               { return self::$_s_aQueryLog; }
    public function logQuery($sql, $duration)   { self::$_s_aQueryLog[] = array($sql, $duration); }

    public function send($sRawSQL, $retIndex=false, $options = self::DB_OPT_FILTER_NEWLINES)  {
        if (!$this->_oResource) return false;

        if (self::$s_bEnableQueryLog) $timer_start = microtime(true);

        if ($options & self::DB_OPT_FILTER_COMMENTS)  {
            $sRawSQL = preg_replace("/\s*#.*/", '', $sRawSQL);
        }
        if ($options & self::DB_OPT_FILTER_NEWLINES)  {
            $sRawSQL = trim(preg_replace("/\r?\n/", "", $sRawSQL)); // added newline replacement in v1.1. mod in v1.156 
        }

        // determine type of SQL command to return proper values
        $command = substr($sRawSQL, 0, strpos($sRawSQL, ' '));
        $command = trim(strtolower($command)); // added trim

        self::$_s_nQueryCount++;

        switch($command)  {

            case 'insert':
                $retVal = mysqli_query($this->_oResource, $sRawSQL);
                $ret = (($retVal) ? mysqli_insert_id($this->_oResource) : false);
                $this->freeResult($retVal);
                if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
                return $ret;
            break;

            case 'update': case 'delete':
                $retVal = mysqli_query($this->_oResource, $sRawSQL);
                if (!$retVal) return false;
                $changed = mysqli_affected_rows($this->_oResource);
               # $this->freeResult($retVal);
                if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
                return (($changed == -1) ? false : $changed); // -1 = error occured
            break;

            case 'select': case 'show':

                $retVal = mysqli_query($this->_oResource, $sRawSQL);
                if ($retVal)  {
                    $data = array();
                    while($row = mysqli_fetch_assoc($retVal))  {
                        if ($retIndex && isset($row[$retIndex]))
                            $data[$row[$retIndex]] = $row;
                        else $data[] = $row;
                    }
                    $this->freeResult($retVal);
                    if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
                    return $data;
                } else return false;
            break;

            case 'alter' :
                $retVal = mysqli_query($this->_oResource, $sRawSQL);
                if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
                return $retVal;
            break;

            case 'truncate' : case 'drop' :
                $retVal = mysqli_query($this->_oResource, $sRawSQL);
                if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
                return $retVal;
            break;

            default:
                 $retVal = mysqli_query($this->_oResource, $sRawSQL);
                 if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
            return true;
        }
    }

    public function single($sRawSQL)  {
        if (!$this->_oResource) return false;
        if (self::$s_bEnableQueryLog) $timer_start = microtime(true);
        self::$_s_nQueryCount++;
        $sRawSQL = trim($sRawSQL);
        $retVal = mysqli_query($this->_oResource, $sRawSQL);
        if ($retVal)  {
            $data = array();
            $row = mysqli_fetch_assoc($retVal);
            $this->freeResult($retVal);
            if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
            return ((sizeof($row)==1 && is_array($row)) ? array_pop($row) : $row);
        } else {
            $this->freeResult($retVal);
            if (self::$s_bEnableQueryLog) $this->logQuery($sRawSQL, round(microtime(true) - $timer_start, 6));
            return false;//$sRawSQL."\n".mysqli_error($this->_oResource);
        }
    }

    public function freeResult(&$retVal)  {
        if (is_resource($retVal))
            mysqli_free_result($retVal);
    }

    public function quick($sRawSQL) { return $this->single($sRawSQL); }

    public function getRow($sTablename, $nId)  {
        if (!$this->_oResource) return false;
        if (!strlen($sTablename) || !$nId) return false;
        self::$_s_nQueryCount++;
        if (!($retVal = mysqli_query($this->_oResource, "SELECT * FROM `".mysqli_real_escape_string($this->_oResource, $sTablename)."` WHERE id = ".((int)$nId))))
            return false;
        $data = mysqli_fetch_assoc($retVal);
        $this->freeResult($retVal);
        return $data;
    }

    public function getRowCell($sTablename, $nId, $sCellname)  {
        if (!$this->_oResource || !strlen($sTablename) || !$nId || !strlen($sCellname)) return false;
        self::$_s_nQueryCount++;
        $retVal = mysqli_query($this->_oResource, 'SELECT `'.mysqli_real_escape_string($this->_oResource, $sCellname).'` FROM '.mysqli_real_escape_string($this->_oResource, $sTablename).' WHERE id = '.((int)$nId).' LIMIT 1');
        //TODO
        die("todo");
        $this->freeResult($retVal);
    }

    public function insertRow($sTablename, $aData)  {
        if (!$this->_oResource || !strlen($sTablename) || !is_array($aData) || empty($aData)) return false;
        $sql  = 'INSERT INTO `'.mysqli_real_escape_string($this->_oResource, $sTablename).'` ( ';
        $sql2 = ' ) VALUES ( ';
        foreach($aData as $key => $value)  {
            $sql  .= '`'.mysqli_real_escape_string($this->_oResource, $key).'`, ';
            $sql2 .= '\''.mysqli_real_escape_string($this->_oResource, $value).'\', ';
        }
        $sql = substr($sql, 0, -2) . substr($sql2, 0, -2) . ' )';
        self::$_s_nQueryCount++;
        if (!(mysqli_query($this->_oResource, $sql))) {
            echo mysqli_error($this->_oResource)."\n$sTablename\n".print_r($aData, true);
            return false;
        }
        $insert_id = mysqli_insert_id($this->_oResource);
        return $insert_id;
    }

    public function updateRow($sTablename, $nId, $aData)  {
        if (!$this->_oResource || !strlen($sTablename) || !$nId || !is_array($aData) || empty($aData)) return false;
        $sql  = 'UPDATE `'.mysqli_real_escape_string($this->_oResource, $sTablename).'` SET ';
        $sql2 = ' WHERE id = '.((int)$nId).' LIMIT 1';
        foreach($aData as $key => $value) $sql  .= '`'.mysqli_real_escape_string($this->_oResource, $key).'` = \''.mysqli_real_escape_string($this->_oResource, $value).'\', ';
        $sql = substr($sql, 0, -2) . $sql2;
        self::$_s_nQueryCount++;
        if (!(mysqli_query($this->_oResource, $sql))) return false;
        $changed = mysqli_affected_rows($this->_oResource);
        return (($changed == -1) ? false : $changed); // -1 = error occured
    }

    public function deleteRow($sTablename, $nId)  {
        if (!$this->_oResource || !strlen($sTablename) || !$nId) return false;
        self::$_s_nQueryCount++;
        if (!(mysqli_query($this->_oResource, 'DELETE FROM `'.mysqli_real_escape_string($this->_oResource, $sTablename).'` WHERE id = '.((int)$nId).' LIMIT 1'))) return false;
        $changed = mysqli_affected_rows($this->_oResource);
        return (($changed == -1) ? false : $changed); // -1 = error occured
    }
    // ---------------------------------------------------------------------------------------------- //
    public function s_getInsertString($aData, $table)  {
        if (!$aData || !is_array($aData)) return false;
        $sql_1 = "INSERT INTO `$table` ( ";
        $sql_2 = " ) VALUES ( ";
        foreach($aData as $key => $val)  {
            $sql_1 .= " `".$key."`, ";
            $sql_2 .= " '".$val."', ";
        }
        $sql = substr($sql_1,0,-2).substr($sql_2,0,-2).' )';
        return $sql;
    }
    // ---------------------------------------------------------------------------------------------- //
    public function s_getUpdateString($aData, $table, $where=false, $limit=1)  {
        if (!$aData || !is_array($aData)) return false;
        $sql_1 = "UPDATE `$table` SET ";
        foreach($aData as $key => $val)  {
            $sql_1 .= " `".$key."` = '".$val."', ";
        }
        $sql = substr($sql_1,0,-2);
        if ($where) $sql .= ' WHERE '.$where;
        if ($limit) $sql .= ' LIMIT '.$limit;
        return $sql;
    }

    // ---------------------------------------------------------------------------------------------- //
    public function tableExists($sTableName)  {
        $retVal = $this->send("SHOW TABLES LIKE \"$sTableName\"");
        if ($retVal) return true;
        else return false;
    }
    // ---------------------------------------------------------------------------------------------- //
    public function createTable($sTableName, $aFieldData, $sCollation=false)  {
        if (!strlen(trim($sTableName)) || empty($aFieldData) || !is_array($aFieldData))  return false;

        // TODO:  verify syntax of fieldnames and tablename
        if ($this->tableExists($sTableName)) return false;

        $sql = "CREATE TABLE `".$sTableName."` (";
        $str = '';
        $primary = null;
        $index   = array();
        foreach($aFieldData as $key => $params)  {
            switch($params['type'])  {
                case 'int': case 'bigint' : case 'double' : {
                    if (!empty($params['default']) &&
                        !preg_match("/[0-9]*/is", $params['default']))  {
                        frame()->logError("cannot create table '$sTableName': default value for key '$key' is not numerical.", 'crit');
                        return false;
                    }
                    if (isset($params['primary']) && $params['primary'] == true && isset($params['null']) && $params['null'] == 'null')
                        $params['null'] = 'not null';

                    $str .= "`$key` ".strtoupper($params['type']);
                } break;
                case 'tinyint':  {
                    if (!empty($params['default']) &&
                        !preg_match("/[0-9]*/is", $params['default']))  {
                        frame()->logError("cannot create table '$sTableName': default value for key '$key' is not numerical.", 'crit');
                        return false;
                    }
                    $str .= "`$key` TINYINT";
                } break;
                case 'varchar':  {
                    if (!empty($params['length']) &&
                        !preg_match("/[0-9]*/is", $params['length']))  {
                        frame()->logError("cannot create table '$sTableName': default value for key '$key' is not numerical or not set.", 'crit');
                        return false;
                    }
                    $str .= "`$key` VARCHAR";
                } break;
                case 'text': case 'longtext' : {
                    unset($params['length']);
                    $params['primary'] = false;
                    $str .= "`$key` ".strtoupper($params['type']);
                } break;
                case 'timestamp':  {
                            unset($params['length']);
                            unset($params['default']);
                            $params['primary'] = false;
                            $str .= "`$key` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                } break;
                case 'datetime':  {
                            unset($params['length']);
                            unset($params['default']);
                            $params['primary'] = false;
                            $str .= "`$key` datetime default NULL";
                } break;
                default: continue;
            }
            if (isset($params['length']) && strlen($params['length']) != 0)
                $str .= "( ".$params['length']." ) ";
            else $str .= " ";

            if (!empty($params['default']))
                    $str .= "DEFAULT '".$params['default']."' ";

                if (isset($params['null']))  {
                    if ($params['null'] == 'not null') $str .= "NOT NULL ";
                    else $str .= "NULL ";
                }


                if ($params['type'] != 'timestamp' && $params['type'] != 'datetime')  {
                    if ((isset($params['null']) && $params['null'] == 'not null') || !isset($params['null']))
                    $str .= "NOT NULL ";
                    else $str .= "NULL ";
                }



            if (!empty($params['extra']))
                $str .= strtoupper($params['extra'])." ";


            if (isset($params['primary']) && $params['primary'] == true)
                $str .= "PRIMARY KEY ";

            if (isset($params['unique']) && $params['unique'] == true)
                $str .= "UNIQUE ";

            $str .= ", ";
        }
        $sql .= substr($str, 0, -2) . ")";


        /* @since 1.15 */
        if ($sCollation !== false) $sql .= " COLLATE '".$sCollation."'";

                $sql = str_replace("\n", "", $sql);
        $retVal = $this->send($sql);

        if (!$retVal)  {
            frame()->logError("failed to create table '$sTableName': <br>\n".mysqli_error()."<br>\n", 'crit');
        } else return true;
    }

    /**
     *
     * @param type $table
     * @since 1.12
     */
    public function getTableHeader($table, $full=false)  {
        $retVal = $this->send("SHOW COLUMNS FROM `$table`");
        if (!$retVal) return false;
        $out = array();
        foreach($retVal as $k => $v)  {
            $out[] = (($full) ? $v : $v['Field']);
        }
        return $out;
    }
}

?>