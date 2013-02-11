<?php

define('FILE_RELATIVE_PATH', 'audio');
define('ALLOWED_EXTENSIONS', '(mp3|wav|ogg)');
$token = getToken();
$filemap = shuffleMap(fileScan(FILE_RELATIVE_PATH), getSeed($token));

// gets a token from query string, cookies, or generates a new one
function getToken() {
  if(isset($_GET['token'])) {
    $token = intval($_GET['token']);
  } else if (isset($_COOKIE['token'])) {
    $token = intval($_COOKIE['token']);
  } else {
    $token = mt_rand(1000000,9999999);
  }
  setcookie('token', $token, time() + 30 * 60);
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
    if(!preg_match('/^([A-Za-z0-9]+)_([A-Za-z0-9]+)\.'.ALLOWED_EXTENSIONS.'$/', $file, $matches)) continue;
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
  if (preg_match('/^'.ALLOWED_EXTENSIONS.'$/i', $extension) !== 1)
    die("Not an allowed file extension.");
  $downloadname = "document.$extension";
  // $finfo = finfo_open(FILEINFO_MIME);
  if ($extension == 'mp3')
    $mime_type = 'audio/mpeg';
  else if ($extension == 'wav')
    $mime_type = 'audio/wav';
  else if ($extension == 'ogg')
    $mime_type = 'audio/ogg';
  else
    $mime_type = null;
  // the proxy layer must support byte-range requests for audio seeking.
  serve_file_resumable($filename, $mime_type, $downloadname);
}

function serve_file_resumable ($file, $contenttype = 'application/octet-stream', $downloadname = null) {

  // Avoid sending unexpected errors to the client - we should be serving a file,
  // we don't want to corrupt the data we send
  @error_reporting(0);

  // Make sure the files exists, otherwise we are wasting our time
  if (!file_exists($file)) {
    header("HTTP/1.1 404 Not Found");
    exit;
  }

  // Get the 'Range' header if one was sent
  if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE']; // IIS/Some Apache versions
  } else if ($apache = apache_request_headers()) { // Try Apache again
    $headers = array();
    foreach ($apache as $header => $val) $headers[strtolower($header)] = $val;
    if (isset($headers['range'])) $range = $headers['range'];
    else $range = FALSE; // We can't get the header/there isn't one set
  } else {
    $range = FALSE; // We can't get the header/there isn't one set
  }

  // Get the data range requested (if any)
  $filesize = filesize($file);
  if ($range) {
    $partial = true;
    list($param,$range) = explode('=',$range);
    if (strtolower(trim($param)) != 'bytes') { // Bad request - range unit is not 'bytes'
      header("HTTP/1.1 400 Invalid Request");
      exit;
    }
    $range = explode(',',$range);
    $range = explode('-',$range[0]); // We only deal with the first requested range
    if (count($range) != 2) { // Bad request - 'bytes' parameter is not valid
      header("HTTP/1.1 400 Invalid Request");
      exit;
    }
    if ($range[0] === '') { // First number missing, return last $range[1] bytes
      $end = $filesize - 1;
      $start = $end - intval($range[0]);
    } else if ($range[1] === '') { // Second number missing, return from byte $range[0] to end
      $start = intval($range[0]);
      $end = $filesize - 1;
    } else { // Both numbers present, return specific range
      $start = intval($range[0]);
      $end = intval($range[1]);
      if ($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1)))) $partial = false; // Invalid range/whole file specified, return whole file
    }
    $length = $end - $start + 1;
  } else $partial = false; // No range requested

  // Send standard headers
  header("Content-Type: $contenttype");
  header("Content-Length: $filesize");
  header('Content-Disposition: inline; filename="'.($downloadname || basename($file)).'"');
  header('Accept-Ranges: bytes');

  // if requested, send extra headers and part of file...
  if ($partial) {
    header('HTTP/1.1 206 Partial Content');
    header("Content-Range: bytes $start-$end/$filesize");
    if (!$fp = fopen($file, 'r')) { // Error out if we can't read the file
      header("HTTP/1.1 500 Internal Server Error");
      exit;
    }
    if ($start) fseek($fp,$start);
    while ($length) { // Read in blocks of 8KB so we don't chew up memory on the server
      $read = ($length > 8192) ? 8192 : $length;
      $length -= $read;
      print(fread($fp,$read));
    }
    fclose($fp);
  } else readfile($file); // ...otherwise just send the whole file

  // Exit here to avoid accidentally sending extra content on the end of the file
  exit;

}

if (!function_exists('apache_request_headers')) {
  function apache_request_headers() {
    foreach($_SERVER as $key=>$value) {
      if (substr($key,0,5)=="HTTP_") {
        $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
        $out[$key]=$value;
      }else{
        $out[$key]=$value;
      }
    }
    return $out;
  }
}

?>