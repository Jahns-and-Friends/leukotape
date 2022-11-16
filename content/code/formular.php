<?php

$_CFG = frame()->aConfig;
$_REPLACE = $_BLOCK = array();
$_ERROR = false;

// $ip = md5($_SERVER['REMOTE_ADDR']);
// $_REPLACE['ip_hash'] = $ip;
// $checkIP = db()->quick("SELECT * FROM `users` WHERE `ip_hash` = '".mres($ip)."' AND !deleted");

// Show/Hide promotion based on country set in config
if (array_key_exists($_CFG['target_country'], $_CFG['promotions'])) {
    $_BLOCK['aktion'] = $_CFG['promotions'][$_CFG['target_country']];
    #if ($_CFG['promotions'][$_CFG['target_country']] == false) {
    #    $_BLOCK['countryselect'] = true;
    #}
}

$required = array(
	"workshopOrt",
	"frage_1",
	// "frage_2",
	"frage_3",
	// "frage_4",
	// "frage_5",
    "anrede",
    "vorname",
    "nachname",
    "strasse",
    "hausnummer",
    "plz",
    "ort",
    "email",
    "datenschutz"
);


if (isset($_POST) && !empty($_POST)) {

    //dig($_POST);
    // dig($_FILES);

    if ($_FILES['fileInput']['error'] == 4) {
        $_ERROR['file'] = true;
    }

    $user = new User();
    
    foreach($_FILES as $label => $fVal)  {
        // no file provided or something went wrong...
        if (!$fVal['size'] || $fVal['error']) continue;

        // check extension
        $tmp  = strrpos($fVal['name'], '.');
        $file = substr($fVal['name'],0, $tmp);
        $ext  = substr($fVal['name'],$tmp+1);
        
        if (in_array(strtolower($ext), array('jpg', 'jpeg', 'png', 'bmp', 'gif')))  {

            $filename_new = round(microtime(true)) . '_' . frame()->forceFilename($file) . '.' . strtolower($ext);
            $tmp_dir      = frame()->aConfig['paths']['files'];
            
            // Continue in No Error block
        }
    }

    foreach ($required as $key) {
        if (!array_key_exists($key, $_POST) || empty($_POST[$key])) {
            $_ERROR[$key] = true;
        }
    }

    if (!$_ERROR) {
        // No errors found, submit
        
        // if old image exists, remove
        if (is_file($tmp_dir . $filename_new))
            @unlink($tmp_dir . $filename_new);
        
        if (move_uploaded_file($fVal['tmp_name'], $tmp_dir . $filename_new))  {
            // save
            //dig("YES");
            $user->set('kassenbon', $filename_new);
        }

        foreach ($_POST as $key => $value) {
            if ($value == "on") $value = 1;
            if ($key == "email2") continue;
            
            $user->set($key, mres($value));
        }

        $user->set("date_created", mtfts());
        $user->save();
        //dig($user);
        frame()->forward("https://leukotape.com/".$_CFG['target_country']."/thankyou");

    } else {
        //dig($_ERROR);
        // Errors found
        // Highlight error messages
        $_BLOCK['error'] = true;
        foreach ($_ERROR as $key => $val) {
            $_REPLACE['e_'.$key] = " is-invalid error";
        }
        // Refill inputs with submitted data
        foreach ($_POST as $key => $val) {
            $_REPLACE[$key] = $val;

            if ($key == "anrede" || $key == "frage_4") {
            	$_REPLACE[$key."_".$val] = "selected='selected'";
            }

            if ($key == "workshopOrt" || $key == "frage_1" || $key == "frage_2" || $key == "frage_3") {
                $_REPLACE[$key."_".$val] = "checked='checked'";
            }
            if ($key == "datenschutz" && $val == "on") {
                $_REPLACE[$key."_checked"] = "checked='checked'";
            }
        }
    }
}

$_REPLACE['sz'] = ($_CFG['target_country'] == "chd") ? "ss" : "ß";
$_REPLACE['thecountry'] = $_CFG['target_country'];
$_BLOCK['switzerland'] = ($_CFG['target_country'] == "chd" || $_CFG['target_country'] == "chf" || $_CFG['target_country'] == "chi") ? true : false;
$_BLOCK['austria'] = ($_CFG['target_country'] == "at") ? true : false;
$replaceData['asset_base'] = frame()->aConfig['asset_base'];
$replaceData['baseurl'] = frame()->aConfig['root']['web'] ;

// replaceData contains placeholders of root template (index.php)
$replaceData['content'] = template::s_quickParse(basename(__FILE__, '.php'), $_REPLACE, $_BLOCK, $utf8_encode = false, $utf8_preencode_template = false);
?>
