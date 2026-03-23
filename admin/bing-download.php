<?php

// UTF-8 Header (optional aber sauber)
header('Content-Type: text/plain; charset=UTF-8');

// Zielordner
$dir = __DIR__ . '/temp';

// Ordner erstellen, falls nicht vorhanden
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Bing API (Deutschland, 1 Bild)
$apiUrl = "https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=de-DE";

// API abrufen
$response = file_get_contents($apiUrl);
if ($response === FALSE) {
    die("Fehler beim Abrufen der Bing API.");
}

$data = json_decode($response, true);

if (!isset($data['images'][0])) {
    die("Keine Bilddaten gefunden.");
}

$imageData = $data['images'][0];

// 4K Bild URL generieren (UHD Version)
$imageUrl = "https://www.bing.com" . $imageData['urlbase'] . "_UHD.jpg";

// Copyright Text
$copyright = $imageData['copyright'] . " (Bing Deutschland)";

// En-dash / Em-dash ersetzen
$copyright = str_replace(["–", "—"], "-", $copyright);

// ⭐ UTF-8 sicherstellen (WICHTIG)
$encoding = mb_detect_encoding($copyright, ['UTF-8','ISO-8859-1','Windows-1252'], true);
if ($encoding !== 'UTF-8') {
    $copyright = mb_convert_encoding($copyright, 'UTF-8', $encoding ?: 'Windows-1252');
}

// Bild herunterladen
$imageContent = file_get_contents($imageUrl);
if ($imageContent === FALSE) {
    die("Fehler beim Download des Bildes.");
}

// Datum im Format Tag-Monat-Jahr
$dateFormatted = date("d-m-Y");

// Bild speichern
$imagePath = $dir . "/bing_" . $dateFormatted . ".jpg";
file_put_contents($imagePath, $imageContent);

// ⭐ Textdatei UTF-8 speichern
$copyrightFile = $dir . "/temp.txt";
file_put_contents($copyrightFile, $copyright);

?>