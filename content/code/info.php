<?php

$_CFG = frame()->aConfig;
$_REPLACE = $_BLOCK = array();
$_ERROR = false;

$_REPLACE['sz'] = ($_CFG['target_country'] == "chd") ? "ss" : "ß";
$replaceData['asset_base'] = frame()->aConfig['asset_base'];
$replaceData['baseurl'] = frame()->aConfig['root']['web'] ;

// replaceData contains placeholders of root template (index.php)
$replaceData['content'] = template::s_quickParse(basename(__FILE__, '.php'), $_REPLACE, $_BLOCK, $utf8_encode = false, $utf8_preencode_template = false);
?>
