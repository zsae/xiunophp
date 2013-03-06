<?php

/*
 * Copyright (C) xiuno.com
 */

function opendir_recursive($dir, $recall, $skip_keywords = array()) {
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false ) {
				if( $file != "." && $file != ".." && !in_array($file, $skip_keywords)) {
					if(is_dir( $dir . $file)) {
				    		call_user_func_array($recall, array($dir . $file, true));
					    		opendir_recursive( $dir . $file . "/", $recall);
					    } else {
						    call_user_func_array($recall, array($dir . $file, false));
					    }
				}
			}
			closedir($dh);
		 }
	 }
}

opendir_recursive($argv[1], 'scan_u8_header', array('.svn'));

function scan_u8_header($file, $isdir) {
	if(!$isdir) {
		$fp = fopen($file, 'rb');
		$s = fread($fp, 3);
		if($s[0] == chr(0xEF) && $s[1] == chr(0xBB) && $s[2] == chr(0xBF)) {
			echo "\n$file\n";
			fclose($fp);
			file_put_contents($file, substr(file_get_contents($file), 3));
		} else {
			echo ".";
			fclose($fp);
		}
	}
}

?>
