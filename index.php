<?php

// require framework
require_once(dirname(__FILE__).'/mbFrame/mbFrame.php');

// enable error reporting
// @TODO: to config
if (frame()->aConfig['debug'] == true)  {
	error_reporting(E_ALL);
	ini_set('display_errors', FALSE);
	ini_set('display_startup_errors', TRUE);
}

// trigger to reset session
if (isset($_GET['resetSession']))  {
    session_destroy();
    frame()->forward('/');
}

// add username/password check when in preview
/*
if (frame()->aConfig['debug'] != true && frame()->aConfig['live'] != true)  {
    $iusername = ((isset($_SERVER['PHP_AUTH_USER'])) ? $_SERVER['PHP_AUTH_USER'] : '');
    $ipassword = ((isset($_SERVER['PHP_AUTH_PW'])) ? $_SERVER['PHP_AUTH_PW'] : '');
    $success   = (($iusername == 'preview' && $ipassword == 'jaf') ? true : false);
    if (!$success)  {
       header("WWW-Authenticate: Basic realm=\"Preview\"");
       header("HTTP/1.0 401 Unauthorized");
       echo "<h1>Authorization required</h1>";
       exit;
    }
}
*/

// start output buffering
ob_start();

// init global params
$g_page_title    = 'Willkommen!';
$g_language      = 'de';
$g_params        = ((isset($_GET['g_params']))          ? $g_params   : array());
$g_rewrite       = ((isset($_GET['g_rewrite']))         ? $_GET['g_rewrite']  : '');
$g_rewriteparams = ((isset($_GET['g_rewriteparams']))   ? $_GET['g_rewriteparams']  : '');
$g_file          = 'formular';
////////////////////////////////////////////////////////////////////////////////
// Parse rewrite-get-params  ///////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Only important thing here is that you end up with 4 global vars:
// $g_params   - array  - containing all params along with their values (if any)
// $g_file     - string - content file that needs to be included (without ".php" and needs to be validated)
// $g_country  - string - countrytag stripped from URL
////////////////////////////////////////////////////////////////////////////////
if (strlen($g_rewrite))  {
    if (preg_match("/([^\/]+)\/?(.*)$/is", $g_rewrite, $match))  {
        // get file to include from URL
        $g_file = $match[2];
        if (strpos($g_file, '.php') === false) $g_file .= '.php';
        if (!empty($match[2])) $g_params[$match[1]] = $match[2];
        else $g_params[$match[1]] = '';
    }
}
//dig($g_file);
$g_user = null;


if (frame()->aConfig['target_land'] == "ch") {
  if (frame()->issetSessionVar('language')) {
      $g_language = frame()->getSessionVar('language');
  } else {
      $g_language = frame()->aConfig["target_language"];
  }

  if (isset($_GET['cl'])) {
      frame()->setSessionVar('language', $_GET['cl']);
      $g_language = $_GET['cl'];
  } 


  if (!frame()->issetSessionVar('language')) {
    if ($_SERVER['HTTP_HOST'] == 'www.leukotape.com/chd/')  {
      frame()->setSessionVar('language', 'de');
      $g_language = frame()->getSessionVar('language');
    #  exit;
    } 
  }
} else {
  $g_language = "de";
}



if (isset($_GET['uid']))  {

    $g_user = User::s_loadByHash($_GET['uid']);

    if ($g_user)  {
        frame()->setSessionVar('uid', $g_user->get('id'));
        if (!$g_user->get('date_changed'))  {
            $g_user->set('date_changed',mtfts());
            $g_user->save();
          }
    }
} else {
    if (frame()->issetSessionVar('uid')) $g_user = new User(frame()->getSessionVar('uid'));
}

if ($g_file === "index.php" || $g_file === ".php") $g_file = "formular.php";

////////////////////////////////////////////////////////////////////////////////
// quickly validate $g_file
////////////////////////////////////////////////////////////////////////////////
if (!template::s_loadPHP($g_file))  {
    $replaceData['content'] = '<h1>Seite konnte nicht gefunden werden.</h1>';
}
////////////////////////////////////////////////////////////////////////////////
// render main template
////////////////////////////////////////////////////////////////////////////////
$replaceData['title'] = ((isset($g_page_title) && strlen($g_page_title)) ? $g_page_title : '');

$cTemplate = new template(dirname(__FILE__).'/content/templates/', dirname(__FILE__).'/content/code/');
$cTemplate->load('index');
$cTemplate->parse();
echo $cTemplate->getOutput();
$output = ob_get_clean();

// parse language
$output = parseLanguage($output);

// if HTML Tidy does exist, beautify the output
if (class_exists('tidy') && isset(frame()->aConfig['htmltidy']))  {
   $tidy = tidy_parse_string($output, frame()->aConfig['htmltidy'], 'UTF8');
   $tidy->cleanRepair();
   echo $tidy;
} else echo $output;

?>
