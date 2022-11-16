<?php

$_CFG = frame()->aConfig;
$_REPLACE = $_BLOCK = array();
$_ERROR = false;


$required = array(
    //"anrede",
    "vorname",
    "nachname",
    "strasse",
    "hausnummer",
    "plz",
    "ort",
    //"email",
    "datenschutz"
);


if (isset($_POST) && !empty($_POST)) {

    //dig($_POST);
    //dig($_FILES);

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
        $_BLOCK['aktion'] = false;
        $_BLOCK['thankyou'] = true;
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
        //frame()->forward("./thankyou");

    } else {
        $_BLOCK['aktion'] = true;
        $_BLOCK['thankyou'] = false;
        //dig($_ERROR);
        // Errors found
        // Highlight error messages
        $_BLOCK['error'] = true;
        foreach ($_ERROR as $key => $val) {
            $_REPLACE['e_'.$key] = " is-invalid error";
            $_BLOCK['error_'.$key] = true;
        }
        // Refill inputs with submitted data
        foreach ($_POST as $key => $val) {
            $_REPLACE[$key] = $val;

            if ($key == "anrede") {
                $_REPLACE[$key."_".$val] = "selected='selected'";
            }

            if ($key == "datenschutz" && $val == "on") {
                $_REPLACE[$key."_checked"] = "checked='checked'";
            }
        }
    }
} else {
    $_BLOCK['aktion'] = true;
    $_BLOCK['thankyou'] = false;
}


$_REPLACE['sz'] = ($_CFG['target_country'] == "chd") ? "ss" : "ß";
$replaceData['asset_base'] = frame()->aConfig['asset_base'];
$replaceData['baseurl'] = frame()->aConfig['root']['web'] ;
$_REPLACE['asset_base'] = frame()->aConfig['asset_base'];
$_REPLACE['baseurl'] = frame()->aConfig['root']['web'] ;


// replaceData contains placeholders of root template (index.php)
$replaceData['content'] = template::s_quickParse(basename(__FILE__, '.php'), $_REPLACE, $_BLOCK, $utf8_encode = false, $utf8_preencode_template = false);
?>
