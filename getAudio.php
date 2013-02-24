<?php

require('audioswitcher.inc.php');


$obfuscated = $_GET['file'];
$extension = $_GET['ext'];
if ($obfuscated) {
  $filename = getFilename($obfuscated, $filemap);
  // If "&ext=ogg" then fetch FileName.ogg instead of FileName.mp3
  // Provided ext must be in whitelisted extensions.
  if (preg_match('/^'.ALLOWED_EXTENSIONS.'$/i', $extension) == 1) {
    $filename = preg_replace('/\..+$/', '.'.$extension, $filename);
  }
  getFile($filename);
}

if ($_SERVER['HTTP_HOST'] == 'localhost') {
  echo "<pre>";
  echo $token . " -> " . getSeed($token) . "<br>";
  print_r ($filemap);
}

?>