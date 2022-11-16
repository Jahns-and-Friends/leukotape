<?php

$replaceData['page_title'] = ((isset($g_page_title) && strlen($g_page_title)) ? $g_page_title : '');
$blockEnable['noaktion'] = true;
$blockEnable['aktion'] = false;
$blockEnable['showContent'] = true;
$blockEnable['showFooter'] = true;
// Videos nur auf der Formular/Startseite anzeigen
if (strpos($g_file, "formular") !== false) {
	$blockEnable['formOnly'] = true;
}

// Aktionsformular
if (strpos($g_file, "aktion") !== false) {
    $blockEnable['aktion'] = true;
    $blockEnable['noaktion'] = false;
}

// Anlageseiten
if (strpos($g_file, "-tapen") !== false) {
    $blockEnable['aktion'] = false;
    $blockEnable['noaktion'] = false;
}

$replaceData['language'] = language();
switch(language())  {
    case 'fr':
        $blockEnable['lng_de'] = false;
        $blockEnable['lng_fr'] = true;
        $blockEnable['lng_it'] = false;
    break;

    case 'it': 
        $blockEnable['lng_de'] = false;
        $blockEnable['lng_fr'] = false;
        $blockEnable['lng_it'] = true;
    break;

    case 'de': default: 
        $blockEnable['lng_de'] = true;
        $blockEnable['lng_fr'] = false;
        $blockEnable['lng_it'] = false;
    break;
}

//dig(frame()->aConfig['target_land']);

$replaceData['target_language'] = 'language_'.frame()->aConfig['target_land'];
$blockEnable["language_".frame()->aConfig['target_land']] = true;
if (frame()->aConfig['target_country'] == "de") {
    $blockEnable['formOnly'] = false;
    $blockEnable['showContent'] = false;
    $blockEnable['showFooter'] = false;
}

$replaceData['asset_base'] = frame()->aConfig['asset_base'];
$replaceData['baseurl'] = frame()->aConfig['root']['web'] ;
// always handy on copyright notice in footer
$replaceData['currentYear'] = strftime("%Y", time());
$replaceData['gtag_id'] = frame()->aConfig['gtag_id'];
?>