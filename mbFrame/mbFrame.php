<?php

/**
 * MB Framework
 * @author Marco Biechl
 * v1.0     
 * v1.1     
 *          % fixed STRICT error in #98
 * v1.11    % fixed session_id provided by GET not replacing current ID
 *          * renamed addSessionId() to addSessionID()
 *          % fixed a lot of session config settings, like ['session_name'] -> ['session']['name']
 *          + added date check methods
 *          % fixed bug in sendFile() - strlen() instead of sizeof() for determining length of data
 * v1.12    24.02.2011
 *          + renames protected vars to private vars
 *          + made mbFrame a singleton and moved frame() function to this file
 *          + added autoloader for frame classes
 *          + modified forward(), so that it will reload current page if no target is specified
 * v1.13    07.09.2011
 *			+ modified paths to make use of new folder structure
 *  11.11.2011 - addGetParam -> added $param to accept an array of key=>value pairs
 * 
 * v1.14    + added functionality to autoloader to search in "classes" subdirs
 */

function scanRecursive($path, &$returnArray)  {
    if (!file_exists($path) || !is_dir($path)) return $returnArray;    
    $files = scandir($path);
    if (sizeof($files) == 2) return $returnArray;
    foreach($files as $k => $v)  {
        if ($v == '.' || $v == '..') continue;
        if (is_dir($path . $v . '/' ))  
            scanRecursive($path . $v . '/', $returnArray);
        else $returnArray[] = $path .  $v;
    }    
}


static $_s_classList = null;
if ($_s_classList == null)  {
    $_s_classList = array();
    scanRecursive(dirname(__FILE__).'/classes/', $_s_classList);
}
 
// -------------------------------------------------------------------------- //
// require classes
// -------------------------------------------------------------------------- //
spl_autoload_register(function ($class_name)   {
    global $_s_classList;
    if (file_exists(dirname(__FILE__)."/classes/".$class_name.".class.php"))  {
        require_once(dirname(__FILE__)."/classes/".$class_name.".class.php");
    } else if (!empty($_s_classList)) {
        foreach($_s_classList as $k => $v)  {
            $classBaseName = basename($v);
            if ($classBaseName == $class_name.".class.php")  {
                require_once($v);
                return;
            }
        }
    }    
});


// require singletons
require_once(dirname(__FILE__).'/functions/singletons.php');
// require helpers & libraries
require_once(dirname(__FILE__).'/functions/dig.php');
require_once(dirname(__FILE__).'/functions/library.php');

// -------------------------------------------------------------------------- //
// frame function
// -------------------------------------------------------------------------- //
function frame() {
    static $_frame = false;
    if (!$_frame) $_frame = mbFrame::s_getInstance();
    return $_frame;
}
// -------------------------------------------------------------------------- //
class mbFrame {
	// vars ------ //
	// TODO: protected machen und zugriffsfunktionen benutzen
	public $nVersion;
	public $oDB;
	public $aConfig;
	public $debug;
    public $bDieOnError; // 1.12
    public $bLogErrors;

	// private vars ----- //
	// TODO: zugriffsfunktionen erstellen
	private $cModLoader;
	private $cUser;
	private $cDatabase;
	private $cFilesystem;
	private $aClasses;
	private $aClassesLoaded;
	private $sFrameDir;
	private $sClassDir;
	private $sModuleDir;

	private $_asDebugData;
    private $_sLogDir;     // 1.12
	private $aRenderData;

    private static $_cInstance = false;

	// --------------------------------------------------------------------- //
    private function __construct() {
    	$this->nVersion = '1.0';
    	$this->bDieOnError = true;
    	$this->aRenderData = array();
    	$this->init();
    	$this->debug = false;
        $this->bDieOnError = true;
        $this->bLogErrors = false;
    }
    // --------------------------------------------------------------------- //
    public static function s_getInstance()  {
        if (self::$_cInstance == false) self::$_cInstance = new mbFrame();
        return self::$_cInstance;
    }
    // --------------------------------------------------------------------- //
    private function init()  {
    	// read config
    	if (is_file(dirname(__FILE__)."/config/config.php"))  {
    		$config = array();
    		require_once(dirname(__FILE__)."/config/config.php");
    		$this->aConfig = $config;
    	}

    	// presets
    	if (!isset($this->aConfig['db']['host']))
    	     $this->aConfig['db']['host'] = 'localhost';
    	if (!isset($this->aConfig['db']['user']))
    	     $this->aConfig['db']['user'] = 'user';
    	if (!isset($this->aConfig['db']['pass']))
    	     $this->aConfig['db']['pass'] = 'pass';
    	if (!isset($this->aConfig['db']['db']))
    	     $this->aConfig['db']['db'] = 'datenbank';

		if (!isset($this->aConfig['session']['name'])) 		    	$this->aConfig['session']['name']='mbF';
		if (!isset($this->aConfig['session']['autostart'])) 		$this->aConfig['session']['autostart']=false;
		if (!isset($this->aConfig['session']['cookie'])) 			$this->aConfig['session']['cookie']=true;
		if (!isset($this->aConfig['session']['trans_sid'])) 		$this->aConfig['session']['trans_sid']=true;
		if (!isset($this->aConfig['session']['cookie_lifetime'])) 	$this->aConfig['session']['cookie_lifetime']=time()+60*60;
		if (!isset($this->aConfig['session']['domains'])) 			$this->aConfig['session']['domains']=array();
        $this->initializeSession();

    	// set default directories
    	// TODO: include that in config and use these as default
    	$this->sFrameDir  = dirname(__FILE__)."/";
    	$this->sClassDir  = dirname(__FILE__)."/";
    	$this->sLogDir 	  = dirname(__FILE__)."/../../logs/";

    	return true;
    }
    // --------------------------------------------------------------------- //
    public function logError($errmsg, $severity="low")  {
        if ($this->bLogErrors)  {
            if ($fh = fopen(frame()->aConfig['paths']['logs']."error.log", "a+"))  {
                fwrite($fh, "[".strftime("%d.%m.%Y-%H:%M:%S",time())."] ".$errmsg."\r\n");
                fclose($fh);
            } else die("[mbFrame] Critical Error - Cannot write to errorLog ".frame()->aConfig['paths']['logs']."error.log<br>\n");
        }
    	if ($severity != "low" && $this->bDieOnError)  {
    		die("[mbFrame] Critical Error - $errmsg<br>\n");
    	} else echo "[mbFrame] $errmsg<br>\n";
    }
    // ---------------------------------------------------------------------- //
    private function initializeSession()  {
     	ini_set('session.use_cookies',0);
     	$this->aConfig['url_rewriter_tags']=ini_get('url_rewriter.tags'); ini_set('url_rewriter.tags','');
		$sn = $this->aConfig['session']['name'];
		session_name($sn);
		if (isset($_GET[$sn]) || isset($_POST[$sn]) || isset($_COOKIE[$sn]) || ($this->aConfig['session']['autostart'])) {
			$this->startSession();
		}
    }
    // --------------------------------------------------------------------- //
   	// Session starten/stoppen
	public function startSession()  {
    	if (!isset($this->aConfig['session']['started']) || !$this->aConfig['session']['started'])  {
         	ini_set('session.use_cookies',$this->aConfig['session']['cookie']?1:0);
         	ini_set('session.cookie_lifetime',$this->aConfig['session']['cookie_lifetime']);
         	if ($this->aConfig['session']['trans_sid']) ini_set('url_rewriter.tags',$this->aConfig['url_rewriter_tags']);
         	session_name($this->aConfig['session']['name']);
            if (isset($_GET['mbF'])) session_id($_GET['mbF']);
         	session_start();
         	if (!isset($_COOKIE[$this->aConfig['session']['name']]) && $this->aConfig['session']['cookie'])  {
            // 23.12.2010 - no idea what my intention of the following stuff was:
         	#	$retVal = setcookie ( $this->aConfig['session']['name'],
         		#            'test',
         		#            $this->aConfig['session']['cookie_lifetime']);
         	#	if (!$retVal) echo "cookie not set";
         	#	else echo "cookie set";
         	}
         	$this->aConfig['session']['started']=true;
      	}
    }
    // --------------------------------------------------------------------- //
	public function stopSession()  {
		session_unset();
		session_destroy();
		$this->aConfig['session']['started']=false;
	}
    // ---------------------------------------------------------------------- //
   	// Skalare Variablen speichern/auslesen/l�schen
	public function setSessionVar($name,$content) 	{ $this->startSession(); $_SESSION['mbFrame'][$name]=$content; return($content); }
    public function getSessionVar($name) 			{ return($_SESSION['mbFrame'][$name]); }
   	public function issetSessionVar($name) 		    { return(isset($_SESSION['mbFrame'][$name])); }
   	public function unsetSessionVar() 				{ $args=func_get_args(); foreach ($args as $name) unset($_SESSION['mbFrame'][$name]); }
    // --------------------------------------------------------------------- //
	public function getFrameDir()  { return $this->sFrameDir;  }
	public function getClassDir()  { return $this->sClassDir;  }
	public function getModuleDir() { return $this->sModuleDir; }
	public function getLogDir()    { return $this->sLogDir;    }
	// --------------------------------------------------------------------- //
	// Forward mit Session-ID
	public function forward($file='',$addsessionid=false)  {
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) header("Pragma: private");
        if (!strlen($file)) $file = $_SERVER['REQUEST_URI'];
	  	header('Location: '.(($addsessionid) ? $this->addSessionID($file,$addsessionid) : $file ) ); exit;
	}
	// ---------------------------------------------------------------------- //
	// Session-ID an Link anh�ngen falls Trans-SID gebraucht oder Domain gewechselt wird
	public function addSessionID($link='',$addsessionid=false)  {
		$matches = array();
		if (isset($this->aConfig['session']['started']) && $this->aConfig['session']['started'])
	    	if ((!$_COOKIE[$this->aConfig['session']['name']]) && ($this->aConfig['session']['trans_sid'])) $addsessionid=true;
	    	else if ((ereg('^https?://([^/]+)',$link,$matches)) && ($_SERVER['HTTP_HOST']!=$matches[1]))  {
	        	foreach($this->aConfig['session']['domains'] as $domain)  {
                    if ($matches[1]==$domain) {
                        $addsessionid=true;
                        break;
                    }
	        	}
	    	}
	  		if ($addsessionid) if ($link) $link=$this->addGetParam($link,$this->aConfig['session']['name'].'='.session_id()); else $link=$this->aConfig['session']['name'].'='.session_id();
	  return($link);
	}
	// ---------------------------------------------------------------------- //
	public function setCookie($data, $lifetime=0)  {
		if (empty($data)) return false;
		if (!is_array($data)) return false;
		foreach ($data as $key => $value)  {
			// set cookie. if no lifetime given, default lifetime of 30 days
			setcookie($key, $value ,($lifetime)?$lifetime:(time()+(60*60*24*30)),'/');
		}
		return true;
	}
    // ---------------------------------------------------------------------- //
    public function readCSV($file, $seperator = ",")  {
        if (!is_file($file)) return false;
        $readdata = file($file);
        if (!empty($readdata))  {
            foreach($readdata as $key => $value)  {
                $value = trim($value);
                if (strlen($value)==0) continue;
              //  echo "loop $key <br>";
                $val = explode($seperator, $value);
                $data[$key] = explode($seperator, $value);
                foreach($data as $dk => $dv)
                    $data[$dk] = str_replace(array("'", '"'), "", $dv);
                if (!is_array($data[$key])) unset($data[$key]);
            }
        }
        $data = array_values($data);
        return $data;
      //  dig($data);
    }
    // ---------------------------------------------------------------------- //
    public function writeCSV($filename, $data, $dir)  {
        if (!($fh = fopen($dir.$filename, 'wb'))) return false;

        $data = array_values($data);

        foreach($data as $row => $value)  {
            $cnt=0;
            foreach($value as $column => $data)  {
              #  $data = str_replace(";", "-", $data);
              fwrite($fh, '"'.$data.'"');
              if (++$cnt < sizeof($value))
              fwrite($fh, ';');
            }
            fwrite($fh, "\n");
        }
        fclose($fh);
        return true;
    }
    // ---------------------------------------------------------------------- //
    // @ added param filename
    // 27.08.2012
    public function sendFile($file, $mime, $filename=false, $bDelete = false)  {
        if (!is_file($file)) return false;
        $data = file_get_contents($file);
        if ($bDelete) @unlink($file);
        $this->sendData($data, $mime, (($filename !== false) ? $filename : $file));
    }
    public function sendData($data, $mime, $filename)  {
        // stop any output buffering
        while(ob_get_level()) ob_end_clean();
        header("Cache-Control: ");
        header("Pragma: ");
        header('Content-type: application/octet-stream');
        header('Content-type: '.$mime);
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        header("Content-transfer-encoding: binary");
        header('Content-Length: '.strlen($data));
        echo $data;
        exit;
    }
    // ---------------------------------------------------------------------- //
    public function forceFilename($str, $spaceChar = '_')  {
        $str   = trim($str);
        $_str  = '';
        $i_max = strlen($str);
        for ($i=0; $i<strlen($str); $i++)  {
            $ch = $str[$i];
            switch ($ch) {
                case '�': case '�':
                $_str .= 'AE'; break;

                case '�': case '�':
                $_str .= 'ae'; break;

                case '�': case '�':  case '�': case '�':  case '�':
                $_str .= 'a'; break;
                case '�': case '�':  case '�': case '�':  case '�':
                $_str .= 'a'; break;

                case '�': case '�':
                $_str .= 'c'; break;

                case '�': case '�':  case '�': case '�':
                $_str .= 'e'; break;

                case '�': case '�':  case '�': case '�':
                $_str .= 'E'; break;

                case '�': case '�':  case '�': case '�':
                $_str .= 'I'; break;
                case '�': case '�':  case '�': case '�':
                $_str .= 'i'; break;

                case '�': case '�':
                $_str .= 'n'; break;

                case '�':
                $_str .= 'OE'; break;

                case '�':
                $_str .= 'oe'; break;

                case '�': case '�':  case '�': case '�':
                $_str .= 'O'; break;
                case '�': case '�':  case '�': case '�':
                $_str .= 'i'; break;

                case '�':
                $_str .= 'ss'; break;

                case '�': case '�':  case '�':
                $_str .= 'U'; break;
                case '�': case '�':  case '�':
                $_str .= 'u'; break;

                case '�':
                $_str .= 'UE'; break;

                case '�':
                $_str .= 'ue'; break;

                case '�':
                $_str .= 'Y'; break;

                case '�': case '�':
                $_str .= 'y'; break;

                case '�':
                $_str .= 'D'; break;

                case ' ': $_str .= $spaceChar; break;

                case '/': case '\\': case '\'': case '"':
                $_str .= ''; break;

                case '-': case ':': case '?' :
                $_str .= '-'; break;

                default : 
                    if (preg_match('/^([A-Za-z0-9._])$/i', $ch)) { $_str .= $ch;  }
                break;
            }
        }
        $_str = str_replace("{$spaceChar}{$spaceChar}", "{$spaceChar}", $_str);
        $_str = str_replace("{$spaceChar}-", '-', $_str);
        $_str = str_replace("-{$spaceChar}", '-', $_str);
        return $_str;
    }
    // ---------------------------------------------------------------------- //
    public function debugMessage($str)  {
        if (strlen($str)) $this->_asDebugData[] = $str;
    }
    public function outputDebug()  {
        if (empty($this->_asDebugData)) return false;
       # dig($as)
        foreach($this->_asDebugData as $key => $str)
            echo "[DEBUG] $str<br>\n";

    }

    // Wrapper f�r mktime() f�r Timestamps vor 1970
    public function mktime($h,$i,$s,$m,$d,$y,$dst=-1)  {
        $offset = 0;
        if ($y<1970) if ($y<1952) { $offset=-2650838400; $y+=84; if ($y<1942) $dst=0; } else { $offset=-883612800; $y+=28; }

        // take daylight saving into account, which reduces offset by 1 hour!
      #  $is_dls = date(I) == 1 ? true : false; //date(I) returns 1 if in DST, 0 if not
      #  if ($is_dls) $offset -= 3600; //Roll back one hour if you are in DST
        return(mktime($h,$i,$s,$m,$d,$y)+$offset);
    }

    // Get-Parameter hinzuf�gen
    // 11.11.2011 - added $param to accept an array of key=>value pairs
    public function addGetParam($link,$param,$value='',$urlencode=0)  {
        if (is_array($param) && !empty($param))  {
            foreach($param as $k => $v) $link = $this->addGetParam($link, $k, $v, $urlencode);
            return $link;
        }
        if ($param=='') return($link);
        if ($value!='') { if ($urlencode) $value=urlencode($value); $name=$param; $param.='='.$value; } // falls Name und Value getrennt
        if (!isset($name)) { ereg('([^=]+)=',$param,$matches); $name=$matches[1]; }
        $link=preg_replace('/[&?]'.$name.'=[^&]+/','',$link); // alten Parameter l�schen
        if (strpos($link,'?')===false) $link.='?'; else $link.='&';
        return($link.$param);
    }

    // Von Hand eingegebenes Datum auf G�ltigkeit pr�fen
    public function checkDate($date)  {
        list($d,$m,$y)=preg_split('/[\.\/\-]/',str_replace(' ','',trim($date)));
        if (!preg_match('/^([0-9]{1,2})$/',$m) || !preg_match('/^([0-9]+)$/',$d) || !preg_match('/^([0-9]{2,4})$/',$y)) return(false);
        return(checkdate((int)$m,(int)$d,(int)$y));
    }

    // Von Hand eingegebenes Datum in MySQL-Timestamp oder optional Sekunden umrechnen
    public function cleanDate($date, $secs=false)  {
        if (!trim($date)) return;
        list($d, $m, $y)=preg_split('/[\.\/\-]/',str_replace(' ','',trim($date)));
        if (!$d) $d = date('d'); if (!$m) $m=date('m'); if (!$y) $y=date('Y');
        if ($y<50) $y+=2000; else if ($y<1900) $y+=1900;
        $time=$this->mktime(0,0,0,$m,$d,$y);
        if ($secs) return($time);
        return(date('YmdHis',$time));
    }

    public function showErrorPage($errorNum)  {
        switch($errorNum) {
            case '403' : include($this->aConfig['root']['local'].'content/templates/403.tpl'); break;
            case '404' : include($this->aConfig['root']['local'].'content/templates/404.tpl'); break;
        }
    }
}

?>