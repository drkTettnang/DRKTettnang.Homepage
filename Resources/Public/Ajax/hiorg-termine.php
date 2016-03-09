<?php

header('Content-Type: text/html; charset=UTF-8');

$ov = (isset($_GET['ov'])) ? $_GET['ov'] : '';

if (!preg_match('/^[a-z0-9]{1,10}$/i', $ov)) {
   exit;
}

$content = file_get_contents('https://hiorg-server.de/termine.php?onlytable=1&ov=' . $ov);

echo $content;
