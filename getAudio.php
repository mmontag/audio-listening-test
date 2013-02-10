<?php

require('audioswitcher.inc.php');

$obfuscated = $_GET['file'];
$file_relative_path = "audio";

$token = getToken();
$filemap = fileScan($file_relative_path);
$filemap = shuffleMap($filemap, getSeed($token));

if ($obfuscated) {
  $filename = getFilename($obfuscated, $filemap);
  getFile($filename);
}

if ($_SERVER['HTTP_HOST'] == 'localhost') {
  echo "<pre>";
  echo $token . " -> " . getSeed($token) . "<br>";
  print_r ($filemap);
}

?>