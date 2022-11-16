<?php
/**
 * GLOBAL PHP SETTINGS
 */
error_reporting(E_ALL);

/**
 * LOCALE & TIMEZONE
 */
setlocale(LC_TIME, 'de_DE.utf8');
date_default_timezone_set('Europe/Berlin');

/**
 * SESSION
 */
$config['session']['name']  = 'mbf';

/**
 * PROJECT DIRECTORY DEFAULTS
 * Usually these do not need to be changed
 */
$config['paths']['app']      = dirname(__FILE__).'/../app/';
$config['paths']['files']    = realpath(dirname(__FILE__).'/../../files/').'/';
$config['paths']['modules']  = dirname(__FILE__).'/../modules/';
$config['paths']['template']['tpl'] = realpath(dirname(__FILE__).'/../../content/templates/').'/';
$config['paths']['template']['php'] = realpath(dirname(__FILE__).'/../../content/code/').'/';
$config['paths']['pdf']     = realpath(dirname(__FILE__).'/../../assets/pdf/').'/';
$config['paths']['fonts']   = realpath(dirname(__FILE__).'/../../assets/fonts/').'/';
$config['paths']['data']    = realpath(dirname(__FILE__).'/../../data/').'/';

/**
 * PROJECT HOSTING CONFIGURATION
 */

// auto-determine which setup to use based on HOST and absolute path
$myDir = dirname(__FILE__);

try {
    // if servername is alderaan, we're in dev mode
    if (exec('hostname') == 'alderaan') {
        
        if (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chd") !== false) {
            $type = "dev-chd";
            $config['target_country'] = "chd";
            $config['target_language'] = "de";
            $config['target_land'] = "ch";
        } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chf") !== false) {
            $type = "dev-chf";
            $config['target_country'] = "chf";
            $config['target_language'] = "fr";
            $config['target_land'] = "ch";
        } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chi") !== false) {
            $type = "dev-chi";
            $config['target_country'] = "chi";
            $config['target_language'] = "it";
            $config['target_land'] = "ch";
        } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/at") !== false) {
            $type = "dev-at";
            $config['target_country'] = "at";
            $config['target_language'] = "de";
        } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/de") !== false) {
            $type = "dev-de";
            $config['target_country'] = "de";
            $config['target_language'] = "de";
        } else {
            $type = 'dev';
            $config['target_country'] = "de";
            $config['target_language'] = "de";
        }
    } else {
        // if "preview" in path, we are in preview mode
        // else assume we're live
        if (strrpos($myDir, 'preview') !== false) {
            if (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chd") !== false) {
                $type = "preview-chd";
                $config['target_country'] = "chd";
                $config['target_language'] = "de";
                $config['target_land'] = "ch";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chf") !== false) {
                $type = "preview-chf";
                $config['target_country'] = "chf";
                $config['target_language'] = "fr";
                $config['target_land'] = "ch";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chi") !== false) {
                $type = "preview-chi";
                $config['target_country'] = "chi";
                $config['target_language'] = "it";
                $config['target_land'] = "ch";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/at") !== false) {
                $type = "preview-at";
                $config['target_country'] = "at";
                $config['target_language'] = "de";
                $config['target_land'] = "at";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/de") !== false) {
                $type = "preview-de";
                $config['target_country'] = "de";
                $config['target_language'] = "de";
                $config['target_land'] = "de";
            } else {
                $type = 'preview';
                $config['target_country'] = "de";
                $config['target_language'] = "de";
                $config['target_land'] = "de";
                frame()->forward("./de/");
            }
        } else {
            if (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chd") !== false) {
                $type = "live-chd";
                $config['target_country'] = "chd";
                $config['target_language'] = "de";
                $config['target_land'] = "ch";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chf") !== false) {
                $type = "live-chf";
                $config['target_country'] = "chf";
                $config['target_language'] = "fr";
                $config['target_land'] = "ch";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/chi") !== false) {
                $type = "live-chi";
                $config['target_country'] = "chi";
                $config['target_language'] = "it";
                $config['target_land'] = "ch";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/at") !== false) {
                $type = "live-at";
                $config['target_country'] = "at";
                $config['target_language'] = "de";
            } elseif (strrpos(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/de") !== false) {
                $type = "live-de";
                $config['target_country'] = "de";
                $config['target_language'] = "de";
            } else {
                $type = 'live';
                $config['target_country'] = "de";
                $config['target_language'] = "de";
                frame()->forward("./de/");
            }
        }
    }

} catch(Exception $e)  {}

// OVERRIDE (dev/demo/live)

switch ($type)  {

    case 'dev':
        $config['domain']	        = 'leukotape.av.alderaan/';
        $config['asset_base']           = 'leukotape.av.alderaan/';
        $config['root']['web']      = 'http://leukotape.av.alderaan/';
        $config['root']['local']    = '/home/www/_localRepos/alexander/leukotape/';
    	$config['db']['host']         = 'localhost';
    	$config['db']['user']         = 'root';
    	$config['db']['pass']         = 'FX001d';
    	$config['db']['db']           = 'leukotape';
        #$config['mailer']['override'] = 'mbiechl@jahnsandfriends.de';
        #$config['mailer']['override'] = 'tjanssen@jahnsandfriends.de';

        // need mail blind copies?
        #$config['mailer']['bcc']    = 'mbiechl@jahnsandfriends.de';

        // these are used to toggle certain settings
        $config['debug'] = true;
        $config['live']  = false;
    break;

    case 'dev-chd':
        $config['domain']           = 'leukotape.av.alderaan/chd/';
        $config['asset_base']           = 'http://leukotape.av.alderaan/';
        $config['root']['web']      = 'http://leukotape.av.alderaan/chd/';
        $config['root']['local']    = '/home/www/_localRepos/alexander/leukotape/';
        $config['db']['host']         = 'localhost';
        $config['db']['user']         = 'root';
        $config['db']['pass']         = 'FX001d';
        $config['db']['db']           = 'leukotape';
        $config['debug'] = true;
        $config['live']  = false;
    break;

    case 'dev-chf':
        $config['domain']           = 'leukotape.av.alderaan/chf/';
        $config['asset_base']           = 'http://leukotape.av.alderaan/';
        $config['root']['web']      = 'http://leukotape.av.alderaan/chf/';
        $config['root']['local']    = '/home/www/_localRepos/alexander/leukotape/';
        $config['db']['host']         = 'localhost';
        $config['db']['user']         = 'root';
        $config['db']['pass']         = 'FX001d';
        $config['db']['db']           = 'leukotape';
        $config['debug'] = true;
        $config['live']  = false;
    break;

    case 'dev-chi':
        $config['domain']           = 'leukotape.av.alderaan/chi/';
        $config['asset_base']           = 'http://leukotape.av.alderaan/';
        $config['root']['web']      = 'http://leukotape.av.alderaan/chi/';
        $config['root']['local']    = '/home/www/_localRepos/alexander/leukotape/';
        $config['db']['host']         = 'localhost';
        $config['db']['user']         = 'root';
        $config['db']['pass']         = 'FX001d';
        $config['db']['db']           = 'leukotape';
        $config['debug'] = true;
        $config['live']  = false;
    break;

    case 'dev-at':
        $config['domain']           = 'leukotape.av.alderaan/at/';
        $config['asset_base']           = 'http://leukotape.av.alderaan/';
        $config['root']['web']      = 'http://leukotape.av.alderaan/at/';
        $config['root']['local']    = '/home/www/_localRepos/alexander/leukotape/';
        $config['db']['host']         = 'localhost';
        $config['db']['user']         = 'root';
        $config['db']['pass']         = 'FX001d';
        $config['db']['db']           = 'leukotape';
        $config['debug'] = true;
        $config['live']  = false;
    break;

    case 'dev-de':
        $config['domain']           = 'leukotape.av.alderaan/de/';
        $config['asset_base']           = 'http://leukotape.av.alderaan/';
        $config['root']['web']      = 'http://leukotape.av.alderaan/de/';
        $config['root']['local']    = '/home/www/_localRepos/alexander/leukotape/';
        $config['db']['host']         = 'localhost';
        $config['db']['user']         = 'root';
        $config['db']['pass']         = 'FX001d';
        $config['db']['db']           = 'leukotape';
        $config['debug'] = true;
        $config['live']  = false;
    break;

    case 'preview':
        $config['domain']           = 'previews.jaf-systems.de/leukotape/';
        $config['asset_base']       = 'https://previews.jaf-systems.de/leukotape/';
        $config['root']['web']      = 'https://previews.jaf-systems.de/leukotape/';
        $config['root']['local']    = '/var/www/vhosts/previews/leukotape/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape_preview';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        error_reporting(0);
    break;

    case 'preview-chd':
        $config['domain']           = 'previews.jaf-systems.de/leukotape/chd/';
        $config['asset_base']       = 'https://previews.jaf-systems.de/leukotape/';
        $config['root']['web']      = 'https://previews.jaf-systems.de/leukotape/chd/';
        $config['root']['local']    = '/var/www/vhosts/previews/leukotape/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape_preview';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        error_reporting(0);
    break;

    case 'preview-chf':
        $config['domain']           = 'previews.jaf-systems.de/leukotape/chf/';
        $config['asset_base']       = 'https://previews.jaf-systems.de/leukotape/';
        $config['root']['web']      = 'https://previews.jaf-systems.de/leukotape/chf/';
        $config['root']['local']    = '/var/www/vhosts/previews/leukotape/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape_preview';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        error_reporting(0);
    break;

    case 'preview-chi':
        $config['domain']           = 'previews.jaf-systems.de/leukotape/chi/';
        $config['asset_base']       = 'https://previews.jaf-systems.de/leukotape/';
        $config['root']['web']      = 'https://previews.jaf-systems.de/leukotape/chi/';
        $config['root']['local']    = '/var/www/vhosts/previews/leukotape/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape_preview';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        error_reporting(0);
    break;

    case 'preview-at':
        $config['domain']           = 'previews.jaf-systems.de/leukotape/at/';
        $config['asset_base']       = 'https://previews.jaf-systems.de/leukotape/';
        $config['root']['web']      = 'https://previews.jaf-systems.de/leukotape/at/';
        $config['root']['local']    = '/var/www/vhosts/previews/leukotape/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape_preview';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        error_reporting(0);
    break;

    case 'preview-de':
        $config['domain']           = 'previews.jaf-systems.de/leukotape/de/';
        $config['asset_base']       = 'https://previews.jaf-systems.de/leukotape/';
        $config['root']['web']      = 'previews.jaf-systems.de/leukotape/de/';
        $config['root']['local']    = '/var/www/vhosts/previews/leukotape/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape_preview';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        $config['paths']['template']['tpl'] = realpath($config['root']['local'] . '/content/templates/') . '/';
        $config['paths']['template']['php'] = realpath($config['root']['local'] . '/content/code/') . '/';

        error_reporting(0);
    break;

    case 'live' :
        $config['domain']           = 'leukotape.com';
        $config['asset_base']       = 'https://leukotape.com/';
        $config['root']['web']      = 'https://leukotape.com';
        $config['root']['local']    = '/var/www/vhosts/leukotape/docs/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        $config['paths']['template']['tpl'] = realpath($config['root']['local'] . '/content/templates/') . '/';
        $config['paths']['template']['php'] = realpath($config['root']['local'] . '/content/code/') . '/';

        error_reporting(0);
    break;

    case 'live-at' :
        $config['domain']           = 'leukotape.com/at/';
        $config['asset_base']       = 'https://leukotape.com/';
        $config['root']['web']      = 'https://leukotape.com/at/';
        $config['root']['local']    = '/var/www/vhosts/leukotape/docs/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        $config['paths']['template']['tpl'] = realpath($config['root']['local'] . '/content/templates/') . '/';
        $config['paths']['template']['php'] = realpath($config['root']['local'] . '/content/code/') . '/';

        $config['gtag_id'] = "UA-138366634-2";

        error_reporting(0);
    break;

    case 'live-chd' :
        $config['domain']           = 'leukotape.com/chd/';
        $config['asset_base']       = 'https://leukotape.com/';
        $config['root']['web']      = 'https://leukotape.com/chd/';
        $config['root']['local']    = '/var/www/vhosts/leukotape/docs/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        $config['paths']['template']['tpl'] = realpath($config['root']['local'] . '/content/templates/') . '/';
        $config['paths']['template']['php'] = realpath($config['root']['local'] . '/content/code/') . '/';

        $config['gtag_id'] = "UA-138366634-3";

        error_reporting(0);
    break;

    case 'live-chf' :
        $config['domain']           = 'leukotape.com/chf/';
        $config['asset_base']       = 'https://leukotape.com/';
        $config['root']['web']      = 'https://leukotape.com/chf/';
        $config['root']['local']    = '/var/www/vhosts/leukotape/docs/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        $config['paths']['template']['tpl'] = realpath($config['root']['local'] . '/content/templates/') . '/';
        $config['paths']['template']['php'] = realpath($config['root']['local'] . '/content/code/') . '/';

        $config['gtag_id'] = "UA-138366634-3";

        error_reporting(0);
    break;

    case 'live-chi' :
        $config['domain']           = 'leukotape.com/chi/';
        $config['asset_base']       = 'https://leukotape.com/';
        $config['root']['web']      = 'https://leukotape.com/chi/';
        $config['root']['local']    = '/var/www/vhosts/leukotape/docs/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        $config['paths']['template']['tpl'] = realpath($config['root']['local'] . '/content/templates/') . '/';
        $config['paths']['template']['php'] = realpath($config['root']['local'] . '/content/code/') . '/';

        $config['gtag_id'] = "UA-138366634-3";

        error_reporting(0);
    break;

    case 'live-de' :
        $config['domain']           = 'leukotape.com/de/';
        $config['asset_base']       = 'https://leukotape.com/';
        $config['root']['web']      = 'https://leukotape.com/de/';
        $config['root']['local']    = '/var/www/vhosts/leukotape/docs/';

        $config['db']['host']       = 'localhost';
        $config['db']['user']       = 'leukotape';
        $config['db']['pass']       = 'S98uNBIWRN7Mx22e';
        $config['db']['db']         = 'leukotape';

        $config['mailer']['override'] = 'avonehr@jahnsandfriends.de';

        $config['debug'] = false;

        $config['paths']['template']['tpl'] = realpath($config['root']['local'] . '/content/templates/') . '/';
        $config['paths']['template']['php'] = realpath($config['root']['local'] . '/content/code/') . '/';

        error_reporting(0);
    break;

    default: die("error in framework configuration: no type specified");
}
$config['type'] = $type;
$config['languages'] = [
    "de",
    "fr",
    "it"
];
if (strpos($type, 'dev') === 0) {
    $config['promotions'] = [
        "de"  => false,
        "at"  => true,
        "chd" => true,
        "chf" => true,
        "chi" => true,
    ];
} elseif (strpos($type, 'preview') === 0) {
    $config['promotions'] = [
        "de"  => false,
        "at"  => false,
        "chd" => false,
        "chf" => false,
        "chi" => false,
    ];
} else {
    $config['promotions'] = [
        "de"  => false,
        "at"  => false,
        "chd" => false,
        "chf" => false,
        "chi" => false,
    ];
}



?>
