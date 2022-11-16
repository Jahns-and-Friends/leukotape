<?php

// Frame einbinden.
require(dirname(__FILE__).'/../mbFrame/mbFrame.php');

// function determineStreetData($in)  {
// 	$in = trim($in);
// 	if (!strlen($in)) return array($in, '');
//
// 	$pos_first_number = false;
// 	for ($i=0; $i<strlen($in); $i++)  {
// 		if (is_numeric($in[$i]))  {
// 			$pos_first_number = $i;
// 			break;
// 		}
// 	}
// 	if (!$pos_first_number)
// 		return array($in, '');
//
// 	return array(
// 		substr($in, 0, $pos_first_number),
// 		substr($in, $pos_first_number)
// 	);
// }

// Brute Force: Alle Artikeldaten laden und dann durchloopen und speichern.
// Bei 500-700 Einträgen kein Ding...
$data = frame()->readCSV(dirname(__FILE__).'/2018-11-07-share-your-story-lady.csv', ';');
$type = "lady"; // lady or lbt

foreach($data as $k => $v)  {
	if (!$k) continue;
	foreach($v as $vk => $vv) $v[$vk] = trim($vv);

	$u = new User();
	$u->set('image', $v[1]);
	$u->set('email', $v[3]);
	$u->set('firstname', $v[4]);
	$u->set('lastname', $v[5]);
	$u->set('story', $v[6]);
	$u->set('created', $v[7]);
	$u->set('type', $type);
	$u->save();
}

?>
