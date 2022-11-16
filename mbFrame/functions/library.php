<?php

function random_string($length) {
    $key = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $key;
}


function isEmpty(&$var) {
    return empty($var) || $var === "0";
}
// ---------------------------------------------------------------------------------------------- //
function validate_Mail($mail)  {
    return preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/', $mail);
}
// ---------------------------------------------------------------------------------------------- //
function getMailtext($type, $replaces=null, $blockEnables=null)  {
	if (!$replaces) $replaces = array();

	if (file_exists(dirname(__FILE__)."/../content/mails/mail_$type.ml"))
	{
		$tpl = new template();
		$tpl->extension = 'ml';
		$tpl->baseDirTPL   = dirname(__FILE__)."/../content/mails/";
        $tpl->load('mail_'.$type);

	    $mailraw = $tpl->quickParse('mail_'.$type, $replaces,$blockEnables);
	    $maildata = explode(";#;", $tpl->parsedData);
	    return $maildata;
	}
	return false;
}
// ---------------------------------------------------------------------------------------------- //
// V2.0: used pw as index instead of value. damn lot faster. 14.10.2010
function createPassword($length, $amount=1, $genChars=false)  {
    $passwords = array();
    if (!$genChars) $genChars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    if ($amount > pow(strlen($genChars), $length) || $amount < 1) return false;
    $fe = function_exists('mt_rand');
    for ($y=0; $y < $amount; $y++)  {
        do {
            $newPW = "";
            for ($i = 1; $i <= $length; $i++)  {
                if ($fe) $rand = mt_rand(1,strlen($genChars));
                else     $rand = rand(1,strlen($genChars));
                $newPW .= substr($genChars,$rand -1,1);
            }
        } while (isset($passwords[$newPW]));//  in_array($newPW, $passwords));
        $passwords[$newPW] = true;
    }
    $passwords = array_keys($passwords);
    return (($amount==1)?$passwords[0]:$passwords);
}
// ---------------------------------------------------------------------------------------------- //
function echoln($string)  {
    echo "$string\n";
}
// -------------------------------------------------------------------------- //
function mtfts($unixTime=false, $withSeconds=true)  {
    if (!$unixTime) $unixTime = time();
    return (strftime("%Y%m%d".(($withSeconds)?"%H%M%S":"000000"), $unixTime));
}
// -------------------------------------------------------------------------- //
// Get time from timestamp
function gtfts($ts,$long=false)
{
  if (!$ts) return(0); if ($long) $long=1; else $long=0;
  $monat=substr($ts,4+$long,2); if (!$monat) $monat=1;
  $tag=substr($ts,6+$long*2,2); if (!$tag) $tag=1;
  return(frame()->mktime(substr($ts,8+$long*3,2),substr($ts,10+$long*4,2),substr($ts,12+$long*5,2),$monat,$tag,substr($ts,0,4)));
}
// -------------------------------------------------------------------------- //
function cleanPost(&$data, $stripslashes=true, $striptags=true, $trim=true)  {
    if (is_array($data)) foreach($data as $key => $val) cleanPost($data[$key], $stripslashes, $striptags, $trim);
    else {
        $data = (($stripslashes)                            ? stripslashes($data)   : $data);
        $data = (($striptags)                               ? strip_tags($data)     : $data);
        $data = (($trim)                                    ? trim($data)           : $data);
    }
    return $data;
}
// -------------------------------------------------------------------------- //
function cleanString($some_string)  {
    //reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
    $some_string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
    '|[\x00-\x7F][\x80-\xBF]+'.
    '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
    '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
    '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
    '?', $some_string );

    //reject overly long 3 byte sequences and UTF-16 surrogates and replace with ?
    $some_string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
    '|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $some_string );
    return $some_string;
}
function string_escape($string)  {
   # return htmlentities($string, ENT_COMPAT, 'UTF-8');
    #$string = utf8_decode($str);
    #$string = htmlentities($string, ENT_COMPAT, 'UTF-8');
    
   # return mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');
    
    $string = cleanString($string);
     return htmlentities($string, ENT_COMPAT, 'UTF-8');

    
    $string = mb_convert_encoding($string, 'UTF-8','HTML-ENTITIES');
    $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
    $string = utf8_decode($string);
    return $string;

}
function sesc($string) { return string_escape($string); }
// -------------------------------------------------------------------------- //
function convertNumber($value, $from, $country='de')  {
    if (!strlen("$value") || !$from || !$country) return false;
    if ($from == 'db')  {
        switch($country)  {
            case 'ch': break; // same format as db format
            default:
                $value = str_replace(",","X", $value);
                $value = str_replace(".",",", $value);
                $value = str_replace("X",".", $value);
            break;
        }
        return $value;
    } else {
        switch($country)  {
            case 'ch':
                $value = str_replace(",","", $value);
            break;
            default:
                $value = str_replace(".","", $value);
                $value = str_replace(",",".", $value);
            break;
        }
        return $value;
    }
}
// -------------------------------------------------------------------------- //
function forceFilename($str, $spaceChar = '_')
{

  $str=trim($str);

  $_str = '';
  $i_max = strlen($str);
  for ($i=0; $i<strlen($str); $i++)
  {
   $ch = $str[$i];
   switch ($ch)
   {
     case 'Ä': case 'Æ':
     $_str .= 'AE'; break;

     case 'ä': case 'æ':
     $_str .= 'ae'; break;

     case 'à': case 'á':  case 'â': case 'ã':  case 'å':
     $_str .= 'a'; break;
     case 'À': case 'Á':  case 'Â': case 'Ã':  case 'Å':
     $_str .= 'a'; break;

     case 'Ç': case 'ç':
     $_str .= 'c'; break;

     case 'è': case 'é':  case 'ê': case 'ë':
     $_str .= 'e'; break;

     case 'È': case 'É':  case 'Ê': case 'Ë':
     $_str .= 'E'; break;

     case 'Ì': case 'Í':  case 'Î': case 'Ï':
     $_str .= 'I'; break;
     case 'ì': case 'í':  case 'î': case 'ï':
     $_str .= 'i'; break;

     case 'Ñ': case 'ñ':
     $_str .= 'n'; break;

     case 'Ö':
     $_str .= 'OE'; break;

     case 'ö':
     $_str .= 'oe'; break;

     case 'Ò': case 'Ó':  case 'Ô': case 'Õ':
     $_str .= 'O'; break;
     case 'ò': case 'ó':  case 'ô': case 'õ':
     $_str .= 'i'; break;

     case 'ß':
     $_str .= 'ss'; break;

     case 'Ù': case 'Ú':  case 'Û':
     $_str .= 'U'; break;
     case 'ù': case 'ú':  case 'û':
     $_str .= 'u'; break;

     case 'Ü':
       $_str .= 'UE'; break;

     case 'ü':
     $_str .= 'ue'; break;

     case 'Ý':
       $_str .= 'Y'; break;

     case 'ý': case 'ÿ':
     $_str .= 'y'; break;

     case 'Ð':
     $_str .= 'D'; break;

     case ' ': $_str .= $spaceChar; break;

     case '/': case '\\': case '\'': case '"':
     $_str .= ''; break;

     case '-': case ':':
     $_str .= '-'; break;

     default : if (ereg('[A-Za-z0-9._\(\)]', $ch)) { $_str .= $ch;  } break;
   }
  }

  $_str = str_replace("{$spaceChar}{$spaceChar}", "{$spaceChar}", $_str);
  $_str = str_replace("{$spaceChar}-", '-', $_str);
  $_str = str_replace("-{$spaceChar}", '-', $_str);

  return $_str;
}
// -------------------------------------------------------------------------- //
function humanFileSize($size, $roundTo=2)  {
    if ($size > 1048576) return number_format($size / 1048576, 2, ",",".")." Mb";
    else return number_format($size / 1024, $roundTo, ",",".")." Kb";
}
// -------------------------------------------------------------------------- //
// ---------------------------------------------------------------------------------------------- //
function shortenString($string, $maxChars, $addDots=true)  {
    if (!mb_strlen($string) || !$maxChars ) return $string;
    
    if (mb_strlen($string) > $maxChars)  {
        if ($addDots)
             $string = mb_substr($string, 0, $maxChars - 3) . "...";
        else $string = mb_substr($string, 0, $maxChars);
    }
    return $string;
}
// ---------------------------------------------------------------------------------------------- //
function mres($data)  {
    if (is_array($data)) foreach($data as $k => $v) $data[$k] = mres($v);
   # else $data = mysqli_real_escape_string($data);
    else {
        if (!($oDB = db()->getRessource())) return false;
        if (is_object($data)) throw new mbException("Trying to escape object data instead of string");
        $data = mysqli_real_escape_string($oDB, $data);
    }
    return $data;
}
// ---------------------------------------------------------------------------------------------- //
/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if(!function_exists('get_called_class')) {
    function get_called_class($bt = false,$l = 1) {
        if (!$bt) $bt = debug_backtrace();
        if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
        if (!isset($bt[$l]['type'])) {
            throw new Exception ('type not set');
        }
        else switch ($bt[$l]['type']) {
            case '::':
                $lines = file($bt[$l]['file']);
                $i = 0;
                $callerLine = '';
                do {
                    $i++;
                    $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
                } while (stripos($callerLine,$bt[$l]['function']) === false);
                preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                            $callerLine,
                            $matches);
                if (!isset($matches[1])) {
                    // must be an edge case.
                    throw new Exception ("Could not find caller class: originating method call is obscured.");
                }
                switch ($matches[1]) {
                    case 'self':
                    case 'parent':
                        return get_called_class($bt,$l+1);
                    default:
                        return $matches[1];
                }
                // won't get here.
            case '->': switch ($bt[$l]['function']) {
                    case '__get':
                        // edge case -> get class of calling object
                        if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
                        return get_class($bt[$l]['object']);
                    default: return $bt[$l]['class'];
                }

            default: throw new Exception ("Unknown backtrace method type");
        }
    }
}
// -------------------------------------------------------------------------- //
function convert($text,$from,$to,$fromhtml=false,$tohtml=false)
{
  global $g_entities;
  // Arrays rekursiv durchgehen
  if (is_array($text)) { foreach($text as $key => $value) $text[$key]=convert($value,$from,$to,$fromhtml,$tohtml); return($text); }
  // Generell erstmal nach UTF-8 wandeln
  if (!eregi('UTF-?8',$from)) $text=mb_convert_encoding($text,'UTF-8',$from);
  if ($fromhtml)
   {
     $text=preg_replace('~&[^#][a-z0-9]+;~Uie','$g_entities[\'\0\']',$text);
     $text=preg_replace('~&#x0*([0-9a-f]+);~Uie','UnicodeToUTF8(hexdec(\'\1\'))',$text);
     $text=preg_replace('~&#([0-9]+);~Ue','UnicodeToUTF8(\1)',$text);
   }
  // Ins Zielcharset umwandeln
  if ($tohtml) $text=mb_convert_encoding($text,'HTML-ENTITIES','UTF-8');
  if (!eregi('UTF-?8',$to)) $text=mb_convert_encoding($text,$to,'UTF-8');
  return($text);
}
// -------------------------------------------------------------------------- //
// Einzelnes Unicode-Zeichen in UTF-8 umrechnen
/*
function UnicodeToUTF8($a)
{
  if ($a<128) return(chr($a));
  if ($a<2048) return(chr(($a >> 6)+192).chr(($a & 63)+128));
  if ($a<65536) return(chr(($a >> 12)+224).chr((($a >> 6) & 63)+128).chr(($a & 63)+128));
  return(chr(($a >> 18)+240).chr((($a >> 12) & 63)+128).chr((($a >> 6) & 63)+128).chr(($a & 63)+128));
}
 
 */
// -------------------------------------------------------------------------- //
// HTML-Entities-Liste
$g_entities=array
(
// Offizieller Teil vom W3C
'&quot;'   => '&#34;',
'&apos;'   => '&#39;',
'&amp;'    => '&#38;',
'&lt;'     => '&#60;',
'&gt;'     => '&#62;',
'&nbsp;'   => '&#160;',
'&iexcl;'  => '&#161;',
'&curren;' => '&#164;',
'&cent;'   => '&#162;',
'&pound;'  => '&#163;',
'&yen;'    => '&#165;',
'&brvbar;' => '&#166;',
'&sect;'   => '&#167;',
'&uml;'    => '&#168;',
'&copy;'   => '&#169;',
'&ordf;'   => '&#170;',
'&laquo;'  => '&#171;',
'&not;'    => '&#172;',
'&shy;'    => '&#173;',
'&reg;'    => '&#174;',
'&trade;'  => '&#8482;',
'&macr;'   => '&#175;',
'&deg;'    => '&#176;',
'&plusmn;' => '&#177;',
'&sup2;'   => '&#178;',
'&sup3;'   => '&#179;',
'&acute;'  => '&#180;',
'&micro;'  => '&#181;',
'&para;'   => '&#182;',
'&middot;' => '&#183;',
'&cedil;'  => '&#184;',
'&sup1;'   => '&#185;',
'&ordm;'   => '&#186;',
'&raquo;'  => '&#187;',
'&frac14;' => '&#188;',
'&frac12;' => '&#189;',
'&frac34;' => '&#190;',
'&iquest;' => '&#191;',
'&times;'  => '&#215;',
'&divide;' => '&#247;',
'&Agrave;' => '&#192;',
'&Aacute;' => '&#193;',
'&Acirc;'  => '&#194;',
'&Atilde;' => '&#195;',
'&Auml;'   => '&#196;',
'&Aring;'  => '&#197;',
'&AElig;'  => '&#198;',
'&Ccedil;' => '&#199;',
'&Egrave;' => '&#200;',
'&Eacute;' => '&#201;',
'&Ecirc;'  => '&#202;',
'&Euml;'   => '&#203;',
'&Igrave;' => '&#204;',
'&Iacute;' => '&#205;',
'&Icirc;'  => '&#206;',
'&Iuml;'   => '&#207;',
'&ETH;'    => '&#208;',
'&Ntilde;' => '&#209;',
'&Ograve;' => '&#210;',
'&Oacute;' => '&#211;',
'&Ocirc;'  => '&#212;',
'&Otilde;' => '&#213;',
'&Ouml;'   => '&#214;',
'&Oslash;' => '&#216;',
'&Ugrave;' => '&#217;',
'&Uacute;' => '&#218;',
'&Ucirc;'  => '&#219;',
'&Uuml;'   => '&#220;',
'&Yacute;' => '&#221;',
'&THORN;'  => '&#222;',
'&szlig;'  => '&#223;',
'&agrave;' => '&#224;',
'&aacute;' => '&#225;',
'&acirc;'  => '&#226;',
'&atilde;' => '&#227;',
'&auml;'   => '&#228;',
'&aring;'  => '&#229;',
'&aelig;'  => '&#230;',
'&ccedil;' => '&#231;',
'&egrave;' => '&#232;',
'&eacute;' => '&#233;',
'&ecirc;'  => '&#234;',
'&euml;'   => '&#235;',
'&igrave;' => '&#236;',
'&iacute;' => '&#237;',
'&icirc;'  => '&#238;',
'&iuml;'   => '&#239;',
'&eth;'    => '&#240;',
'&ntilde;' => '&#241;',
'&ograve;' => '&#242;',
'&oacute;' => '&#243;',
'&ocirc;'  => '&#244;',
'&otilde;' => '&#245;',
'&ouml;'   => '&#246;',
'&oslash;' => '&#248;',
'&ugrave;' => '&#249;',
'&uacute;' => '&#250;',
'&ucirc;'  => '&#251;',
'&uuml;'   => '&#252;',
'&yacute;' => '&#253;',
'&thorn;'  => '&#254;',
'&yuml;'   => '&#255;',
'&OElig;'  => '&#338;',
'&oelig;'  => '&#339;',
'&Scaron;' => '&#352;',
'&scaron;' => '&#353;',
'&Yuml;'   => '&#376;',
'&circ;'   => '&#710;',
'&tilde;'  => '&#732;',
'&ensp;'   => '&#8194;',
'&emsp;'   => '&#8195;',
'&thinsp;' => '&#8201;',
'&zwnj;'   => '&#8204;',
'&zwj;'    => '&#8205;',
'&lrm;'    => '&#8206;',
'&rlm;'    => '&#8207;',
'&ndash;'  => '&#8211;',
'&mdash;'  => '&#8212;',
'&lsquo;'  => '&#8216;',
'&rsquo;'  => '&#8217;',
'&sbquo;'  => '&#8218;',
'&ldquo;'  => '&#8220;',
'&rdquo;'  => '&#8221;',
'&bdquo;'  => '&#8222;',
'&dagger;' => '&#8224;',
'&Dagger;' => '&#8225;',
'&hellip;' => '&#8230;',
'&permil;' => '&#8240;',
'&lsaquo;' => '&#8249;',
'&rsaquo;' => '&#8250;',
'&euro;'   => '&#8364;',

// Sonstige Entities
'&fnof;'     => '&#402;',
'&Alpha;'    => '&#913;',
'&Beta;'     => '&#914;',
'&Gamma;'    => '&#915;',
'&Delta;'    => '&#916;',
'&Epsilon;'  => '&#917;',
'&Zeta;'     => '&#918;',
'&Eta;'      => '&#919;',
'&Theta;'    => '&#920;',
'&Iota;'     => '&#921;',
'&Kappa;'    => '&#922;',
'&Lambda;'   => '&#923;',
'&Mu;'       => '&#924;',
'&Nu;'       => '&#925;',
'&Xi;'       => '&#926;',
'&Omicron;'  => '&#927;',
'&Pi;'       => '&#928;',
'&Rho;'      => '&#929;',
'&Sigma;'    => '&#931;',
'&Tau;'      => '&#932;',
'&Upsilon;'  => '&#933;',
'&Phi;'      => '&#934;',
'&Chi;'      => '&#935;',
'&Psi;'      => '&#936;',
'&Omega;'    => '&#937;',
'&beta;'     => '&#946;',
'&gamma;'    => '&#947;',
'&delta;'    => '&#948;',
'&epsilon;'  => '&#949;',
'&zeta;'     => '&#950;',
'&eta;'      => '&#951;',
'&theta;'    => '&#952;',
'&iota;'     => '&#953;',
'&kappa;'    => '&#954;',
'&lambda;'   => '&#955;',
'&mu;'       => '&#956;',
'&nu;'       => '&#957;',
'&xi;'       => '&#958;',
'&omicron;'  => '&#959;',
'&pi;'       => '&#960;',
'&rho;'      => '&#961;',
'&sigmaf;'   => '&#962;',
'&sigma;'    => '&#963;',
'&tau;'      => '&#964;',
'&upsilon;'  => '&#965;',
'&phi;'      => '&#966;',
'&chi;'      => '&#967;',
'&psi;'      => '&#968;',
'&omega;'    => '&#969;',
'&thetasym;' => '&#977;',
'&upsih;'    => '&#978;',
'&piv;'      => '&#982;',
'&bull;'     => '&#8226;',
'&prime;'    => '&#8242;',
'&Prime;'    => '&#8243;',
'&oline;'    => '&#8254;',
'&frasl;'    => '&#8260;',
'&weierp;'   => '&#8472;',
'&image;'    => '&#8465;',
'&real;'     => '&#8476;',
'&alefsym;'  => '&#8501;',
'&larr;'     => '&#8592;',
'&uarr;'     => '&#8593;',
'&rarr;'     => '&#8594;',
'&darr;'     => '&#8595;',
'&harr;'     => '&#8596;',
'&crarr;'    => '&#8629;',
'&lArr;'     => '&#8656;',
'&uArr;'     => '&#8657;',
'&rArr;'     => '&#8658;',
'&dArr;'     => '&#8659;',
'&hArr;'     => '&#8660;',
'&forall;'   => '&#8704;',
'&part;'     => '&#8706;',
'&exist;'    => '&#8707;',
'&empty;'    => '&#8709;',
'&nabla;'    => '&#8711;',
'&isin;'     => '&#8712;',
'&notin;'    => '&#8713;',
'&ni;'       => '&#8715;',
'&prod;'     => '&#8719;',
'&sum;'      => '&#8721;',
'&minus;'    => '&#8722;',
'&lowast;'   => '&#8727;',
'&radic;'    => '&#8730;',
'&prop;'     => '&#8733;',
'&infin;'    => '&#8734;',
'&ang;'      => '&#8736;',
'&and;'      => '&#8743;',
'&or;'       => '&#8744;',
'&cap;'      => '&#8745;',
'&cup;'      => '&#8746;',
'&int;'      => '&#8747;',
'&there4;'   => '&#8756;',
'&sim;'      => '&#8764;',
'&cong;'     => '&#8773;',
'&asymp;'    => '&#8776;',
'&ne;'       => '&#8800;',
'&equiv;'    => '&#8801;',
'&le;'       => '&#8804;',
'&ge;'       => '&#8805;',
'&sub;'      => '&#8834;',
'&sup;'      => '&#8835;',
'&nsub;'     => '&#8836;',
'&sube;'     => '&#8838;',
'&supe;'     => '&#8839;',
'&oplus;'    => '&#8853;',
'&otimes;'   => '&#8855;',
'&perp;'     => '&#8869;',
'&sdot;'     => '&#8901;',
'&lceil;'    => '&#8968;',
'&rceil;'    => '&#8969;',
'&lfloor;'   => '&#8970;',
'&rfloor;'   => '&#8971;',
'&lang;'     => '&#9001;',
'&rang;'     => '&#9002;',
'&loz;'      => '&#9674;',
'&spades;'   => '&#9824;',
'&clubs;'    => '&#9827;',
'&hearts;'   => '&#9829;',
'&diams;'    => '&#9830;'
);
// -------------------------------------------------------------------------- //
function returnDate($querydate)  {
    $minusdate = time() - $querydate;
    switch ($minusdate) {
        case (0):
            $date_string = 'Jetzt';
        break;
        case ($minusdate < 60):
            $date_string = 'Vor '.($minusdate).' Sekunde'.(($minusdate>1)?'n':'');
        break;
        case ($minusdate < 3600):
            $minusdate = round($minusdate/60,0);
            $date_string = 'Vor '.$minusdate.' Minute'.(($minusdate>1)?'n':'');
        break;
        case ($minusdate < 86400):
            $minusdate = round($minusdate/3600,0);
            $date_string = 'Vor '.$minusdate.' Stunde'.(($minusdate>1)?'n':'');
        break;
        case ($minusdate < 2592000):
            $minusdate = round($minusdate/86400,0);
            $date_string = 'Vor '.$minusdate.' Tag'.(($minusdate>1)?'en':'');
        break;
        case ($minusdate < 756864000):
            $minusdate = round($minusdate/2592000,0);
            $date_string = 'Vor '.$minusdate.' Monat'.(($minusdate>1)?'en':'');
        break;
        default:
            $minusdate = round($minusdate/756864000,0);
            $date_string = 'Vor '.$minusdate.' Jahr'.(($minusdate>1)?'en':'');
        break;
    }
    return $date_string;
}
// -------------------------------------------------------------------------- //





// -------------------------------------------------------------------------- //
#############################################################
# basic encoding of given string for pageIDs                #
# Parameters: -string- ID (memberid || documentid)          #
# ReturnVal : -string- encoded string                       #
#############################################################
function encode($string)  {
    $string = str_pad($string, 5, "0",  STR_PAD_LEFT);  # fill gaps
    $string = ($string << 2) + 667;                     # bit shift and add prime number
    $string = strrev ($string);                         # reverse resulting string
    return $string;
}
// ---------------------------------------------------------------------------------------------- //

#############################################################
# basic decoding of given string for pageIDs                #
# Parameters: -string- encoded string                       #
# ReturnVal : -string- decoded string                       #
#############################################################
function decode($string)  {
    $string = strrev ($string);
    $string = ($string - 667) >> 2;
    return $string;
}

// -------------------------------------------------------------------------- //
function getPageTitle()  {
    return false;
    global $g_file, $g_country;
    $f = str_replace(".php", ".tpl", $g_file);
    if (file_exists(dirname(__FILE__).'/../content/templates/'.$g_country.'/'.$f))
         $foo = file_get_contents(dirname(__FILE__).'/../content/templates/'.$g_country.'/'.$f);
    else $foo = file_get_contents(dirname(__FILE__).'/../content/templates/'.$f);
    $h1 = $h2 = '';
    preg_match("/<h1>(.*)<\/h1>/isU", $foo, $match);   if (isset($match[1])) $h1 = $match[1];
    preg_match("/<h2>(.*)<\/h2>/isU", $foo, $match);   if (isset($match[1])) $h2 = $match[1];

    $title = (strip_tags("$h1 $h2"));
    $title = str_replace('', '&ndash;', $title); // Gedankenstrich
    $title = str_replace('', '&trade;', $title); // Trademark
    $title = str_replace('', '&bdquo;', $title); //
    $title = str_replace('', '&ldquo;', $title); //

    return utf8_encode($title);
}
// -------------------------------------------------------------------------- //
function parseLanguage($str, $forceLanguage = false)  {
    global $g_language;
    $language = (($forceLanguage !== false) ? $forceLanguage : $g_language);
    return preg_replace("/\[([^]]*)\|([^]]*)(\|([^]]*))?\]/isU", (($language == 'de') ? "$1" : (($language == 'fr') ? "$2" : "$4")), $str);
}
// -------------------------------------------------------------------------- //
function language()  {
    global $g_language;
    return ((isset($g_language) && strlen($g_language)) ? $g_language : 'de');
}
// -------------------------------------------------------------------------- //
// 21.08.12 
// fixed bug where comparison $selected == $_key was matching FALSE as 0. 
// changed to === check.
function renderOptions($aData, $selected = false, $key = false, $value = false, $encode = false)  {
    $out = '';
    if (!is_array($aData) || empty($aData)) return $out;

    foreach ($aData as $k => $v)  {
        $_key = (($key !== false) ? $v[$key] : $k);
        $_value = (($value !== false) ? $v[$value] : $v);
        $out .= '<option value="'.$_key.'"'.(($selected !== false && strlen($selected) && $selected == $_key) ? ' selected="selected"' : '').'>'.(($encode===true) ? utf8_encode($_value) : $_value).'</option>'."\n";
    }

    return $out;
}
// -------------------------------------------------------------------------- //
function renderCheckboxes($aVal, $sName, $nCols=2, $active=false, $totalWidth=false)  {
    $out = '';
    
    $perCol = ceil(sizeof($aVal) / $nCols);
    for ($i=0; $i<$nCols; $i++)  {
        $cnt = 0;
        $out .= '<table style="float:left; '.(($totalWidth !== false) ? 'width: '.floor($totalWidth/$nCols).'px' : '').'">';
        
        foreach($aVal as $k => $v)  {
            if ($cnt < $perCol*$i) { $cnt++; continue; }
            if ($cnt >= $perCol*($i+1)) { $cnt++; continue; }
            

            
            $out .= '<tr>';
            $out .= '<td style="width:16px;">';
            $out .= '<input type="checkbox" class="checkbox" name="'.$sName.'" value="'.$k.'" '.(($active !== false && in_array($k,$active)) ? 'checked="checked"' : '').' />';
            $out .= '</td>';
            $out .= '<td>'.parseLanguage($v).'</td>';
            $out .= '</tr>';
            $cnt++;
        }
        $out .= '</table>';
    }
    return $out;
}

// -------------------------------------------------------------------------- //
function timestamp2datetime($timestamp, $unix=false)  {
    if ($unix === false) $timestamp = gtfts($timestamp);
    return date('Y-m-d H:i:s', $timestamp);
}
// -------------------------------------------------------------------------- //
function datetime2timestamp($datetime, $toUnix = false)  {
    $t = strtotime($datetime);
    if ($toUnix === false) $t = mtfts($t);
    return $t;
}
// -------------------------------------------------------------------------- //
function postHasVal($valname)  {
    if (!isset($_POST[$valname])) return false;
    if (is_array($_POST[$valname]))  {
        return !((empty($_POST[$valname])));
    } else return (bool)strlen($_POST[$valname]);
}
// -------------------------------------------------------------------------- //
function mb_ucasefirst($str){
    $str[0] = mb_strtoupper($str[0]);
    return $str;
} 
// -------------------------------------------------------------------------- //

// -------------------------------------------------------------------------- //
// Conversion helper to convert UTF-8 to UTF-16BE, which is Excel Unicode Style
// -------------------------------------------------------------------------- //
function convertCharSet($data, $baseCharSet = 'UTF-8')  {
    if (is_array($data))  {
        foreach($data as $key => $value)  {
            $data[$key] = convertCharSet($value, $baseCharSet);
        }
    } else {
       /// $step_1 = html_entity_decode($data, ENT_COMPAT, $baseCharSet);
       // $data  = convert($data,$baseCharSet,'UTF-16BE',true,false);
        $data  = mb_convert_encoding($data, 'UTF-16BE', $baseCharSet);
    }
    return $data;
}
// -------------------------------------------------------------------------- //
function isJuryAdmin()  {
    return (user()->get('id') != 3);
}
?>
