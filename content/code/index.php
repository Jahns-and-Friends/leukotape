<?php

$replaceData['page_title'] = ((isset($g_page_title) && strlen($g_page_title)) ? $g_page_title : '');

// Videos nur auf der Formular/Startseite anzeigen
if (strpos($g_file, "formular") !== false) {
	$blockEnable['formOnly'] = true;
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

$blockEnable["language_".frame()->aConfig['target_land']] = true;
if (frame()->aConfig['target_country'] == "de") {
    $blockEnable['formOnly'] = false;
}
$blockEnable['showContent'] = true;

// Show/Hide flag select based on country set in config
if (frame()->aConfig['type'] == "preview-de" || frame()->aConfig['type'] == "live-de") {
    $blockEnable['countryselect'] = true;
    $blockEnable['showContent'] = false;
}

$replaceData['HTML_LANGUAGE_PARAMETER'] = frame()->aConfig['target_language']."_".strtoupper(frame()->aConfig['target_land']);
$replaceData['asset_base'] = frame()->aConfig['asset_base'];
$replaceData['baseurl'] = frame()->aConfig['root']['web'] ;
// always handy on copyright notice in footer
$replaceData['currentYear'] = strftime("%Y", time());
$replaceData['gtag_id'] = frame()->aConfig['gtag_id'];
?>