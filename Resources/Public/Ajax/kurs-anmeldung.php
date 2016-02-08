<?php
header('Content-Type: text/html; charset=ISO-8859-1');

$query = parse_url($_SERVER[REQUEST_URI], PHP_URL_QUERY);

echo file_get_contents('https://www.kurs-anmeldung.de/go.dll?'.$query);
?>
