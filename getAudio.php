<?php

	require('shuf.php');
	$seed = substr($_GET['token'], 0, 32);
	$file = $_GET['file'];

	$file_relative_path = "audio";

  $token = substr(md5(microtime()), 0, 6);

  echo "<pre>";
  // Scan and match files of the format "name_0.mp3", "name_1.mp3", etc.
	function fileScan($dir) {
		$filemap = array();
		@$files = scandir($dir);
		foreach($files as $file) {
	    $matches = array();
      if(is_dir($file)) continue;
      if(!preg_match('/^([A-Za-z0-9]+)_([A-Za-z0-9]+)\.(mp3|wav)$/', $file, $matches)) continue;
      $filename = $matches[0];
      $audioname = $matches[1];
      $num = $matches[2];
      $ext = $matches[3];
      $filemap[$audioname][] = array($filename, $num, $ext);
		}
  	return $filemap;
	}

  function shuffleMap($filemap, $token) {
    $indexedMap = array();
    $i = 0;
    foreach($filemap as $_ => $value) {
      shuf($value, $token . $value[0]);
      $indexedMap[$i] = $value;
      $i++;
    }
    $filemap = $indexedMap;
    shuf($filemap, $token);
    return $filemap;
  }

  $filemap = fileScan($file_relative_path);
  $filemap = shuffleMap($filemap, $token);

  print_r ($filemap);
  die();

	$f = $_GET["f"];
	list($idx1,$idx2) = explode(",",$f);
	$file_array = fileScan($file_relative_path);
	$file = $file_array[$idx1][$idx2];
	$extension = substr($file,strrpos($file,"."));
	$downloadname = "document-$idx1-$idx2$extension";
	$finfo = finfo_open(FILEINFO_MIME);
//echo "$idx1 $idx2<br>";
//echo "$file<br>";
//echo finfo_file($finfo,$file)."<br>";
//echo "<pre>"; print_r($file_array);
//die();
	if(!file_exists($file)) die("Couldn't locate this file.");
	header("Content-Type: ".finfo_file($finfo, $file));
	header("Content-Length: ".filesize($file));
	header("Content-Disposition: inline; filename=\"$downloadname\"");
	readfile($file);
	die();
?>