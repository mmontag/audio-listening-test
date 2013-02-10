<?php

// gets a provided token or generates a new one
function getToken() {
  if(isset($_GET['token'])) {
    $token = $_GET['token'];
  } else {
    $token = microtime();
  }
  return $token;
}

// generates a seed based on provided token
function getSeed($token) {
  $salt = 'watermarkz';
  $seed = hexdec(substr(md5($token . $salt), 0, 6));
  return $seed;
}

/**
 * Deterministic key-based shuffle
 * See http://stackoverflow.com/questions/3169805/how-can-i-randomize-an-array-in-php-by-providing-a-seed-and-get-the-same-order
 */
function shuf(&$items, $seed) {
  @mt_srand($seed);
  for ($i = count($items) - 1; $i > 0; $i--) {
    $j = @mt_rand(0, $i);
    $tmp = $items[$i];
    $items[$i] = $items[$j];
    $items[$j] = $tmp;
  }
}

// Scan and match files of the format "name_0.mp3", "name_1.mp3", etc.
function fileScan($dir) {
  $filemap = array();
  @$files = scandir($dir);
  foreach($files as $file) {
    $matches = array();
    if(is_dir($file)) continue;
    if(!preg_match('/^([A-Za-z0-9]+)_([A-Za-z0-9]+)\.(mp3|wav)$/', $file, $matches)) continue;
    $filename = $dir . "/" . $matches[0];
    $name = $matches[1];
    $num = $matches[2];
    $extension = $matches[3];
    $filemap[$name][] = array(
      "filename" => $filename,
      "name" => $name,
      "num" => $num,
      "extension" => $extension
    );
  }
  return $filemap;
}

function shuffleMap($filemap, $token) {
  $indexedMap = array();
  $i = 0;
  foreach($filemap as $_ => $value) {
    shuf($value, $token . $i);
    $indexedMap[$i] = $value;
    $i++;
  }
  $filemap = $indexedMap;
  shuf($filemap, $token);
  return $filemap;
}

function getFilename($obfuscated, $filemap) {
  // the input $obfuscated should be like "0,1", the indexes into the shuffled filemap
  list($idx1, $idx2) = explode(",", $obfuscated);
  $filename = $filemap[$idx1][$idx2]['filename'];
  return $filename;
}

function getFile($filename) {
  if(!file_exists($filename))
    die("Couldn't locate this file.");
  $extension = substr($filename,strrpos($filename,".") + 1);
  if (preg_match('/^(mp3|wav)$/i', $extension) !== 1)
    die("Not an allowed file extension.");
  $downloadname = "document.$extension";
  $finfo = finfo_open(FILEINFO_MIME);
  //echo "$idx1 $idx2<br>";
  //echo "$file<br>";
  //echo finfo_file($finfo,$file)."<br>";
  //echo "<pre>"; print_r($file_array);
  //die();

  header("Content-Type: ".finfo_file($finfo, $filename));
  header("Content-Length: ".filesize($filename));
  header("Content-Disposition: inline; filename=\"$downloadname\"");
  readfile($filename);
  die();
}

?>