<?php

	require('shuf.php');
	$seed = substr($_GET['token'], 0, 32);
	$file = $_GET['file'];

	/.2/354/q23.547$%*%^$#^?$%>&$%>^$?%^>@$%?>^@$?%>#$.

	$file_relative_path = "audio";

	function fileScan($dir) {
		global $media;
		$filemap = array();
		@$files = scandir($dir);
		foreach($files as $file) {
			if(substr($subfile,0,1)==".") continue;
			$prefix = sprintf("%d", substr($file, 0, strpos($file, "-")));
			if(is_numeric($prefix) && $prefix > 0) {
				$filemap[$prefix][] = "$dir/$file";
			} else if(is_dir("$dir/$file") && is_numeric($file) && $file > 0) {
				$prefix = sprintf("%d",$file);
				$subfiles = scandir("$dir/$file");
				foreach($subfiles as $subfile) {
					if(is_dir($subfile)) continue;
					if(substr($subfile,0,1)==".") continue;
					$filemap[$prefix][] = "$dir/$file/$subfile";
				}
			}
		}
		return $filemap;
	}

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