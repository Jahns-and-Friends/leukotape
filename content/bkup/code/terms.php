<?php

$_CFG = frame()->aConfig;
$_REPLACE = $_BLOCK = array();
$_ERROR = false;

//dig($_SERVER['HTTP_REFERER']);
$_BLOCK["language_".$_CFG['target_country']] = true;
$_REPLACE['sz'] = ($_CFG['target_country'] == "chd") ? "ss" : "ß";

// replaceData contains placeholders of root template (index.php)
$replaceData['content'] = template::s_quickParse(basename(__FILE__, '.php'), $_REPLACE, $_BLOCK, $utf8_encode = false, $utf8_preencode_template = false);
?>
