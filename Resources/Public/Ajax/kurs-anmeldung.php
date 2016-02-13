<?php

header('Content-Type: text/html; charset=ISO-8859-1');

$query = parse_url($_SERVER[REQUEST_URI], PHP_URL_QUERY);

$content = file_get_contents('https://www.kurs-anmeldung.de/go.dll?'.$query);

$content = preg_replace_callback(
   '#<url_anmeldung>(.+)</url_anmeldung>#',
   function ($matches) {
      $url = $matches[1];
      $url = htmlspecialchars_decode($url); //prevents double encoding
      $url = htmlspecialchars($url);
      
      return '<url_anmeldung>'.$url.'</url_anmeldung>';
   },
   $content);

echo $content;
