<?php

header('Content-Type: text/html; charset=UTF-8');

$location = (isset($_GET['location'])) ? $_GET['location'] : '';

if (!preg_match('/^([0-9]{5}|[a-z]+)(\|([0-9]{5}|[a-z]+))*$/i', $location)) {
   exit;
}

$content = file_get_contents('http://www.drk-blutspende.de/blutspendetermine/ergebnisse.php?rss=1&plz_ort_eingabe=' . $location);

echo $content;
