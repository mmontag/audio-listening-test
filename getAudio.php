<?php

require('audioswitcher.inc.php');


$obfuscated = $_GET['file'];
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