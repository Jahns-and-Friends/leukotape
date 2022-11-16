<?php

$_CFG = frame()->aConfig;
$_REPLACE = $_BLOCK = array();
$_ERROR = false;


$_REPLACE['target_language'] = 'language_'.frame()->aConfig['target_land'];
$_BLOCK["language_".frame()->aConfig['target_land']] = true;
//dig($_SERVER['HTTP_REFERER']);
$_BLOCK["language_".$_CFG['target_country']] = true;
$_REPLACE['sz'] = ($_CFG['target_country'] == "chd") ? "ss" : "ß";
$_REPLACE['asset_base'] = frame()->aConfig['asset_base'];
$_REPLACE['baseurl'] = frame()->aConfig['root']['web'] ;

// replaceData contains placeholders of root template (index.php)
$replaceData['content'] = template::s_quickParse(basename(__FILE__, '.php'), $_REPLACE, $_BLOCK, $utf8_encode = false, $utf8_preencode_template = false);
?>
