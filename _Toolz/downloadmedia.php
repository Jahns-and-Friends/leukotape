<?php

// Frame einbinden.
require(dirname(__FILE__).'/../mbFrame/mbFrame.php');

$mediaURLs = db()->send("SELECT `id`, `image` FROM `users`");
//dig($mediaURLs);
foreach ($mediaURLs as $key => $url) {
    $urlID = explode("/", $url["image"])[4];
    echo $urlID."<br />";
    $dir = "../assets/images/stories/".$urlID."/";
    exit;
    if (!mkdir($dir, 0777, true)) {
        echo 'nix Ordner machen<br />';
    }
    file_put_contents($dir.$urlID.".jpg", fopen($url["image"], 'r'));
}


?>
