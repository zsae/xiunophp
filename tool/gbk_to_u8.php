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

opendir_recursive($argv[1], 'gbk_to_u8', array('.svn'));

function gbk_to_u8($file, $isdir) {
	if(!$isdir) {
		$s = file_get_contents($file);
		$s = iconv('GBK', 'UTF-8', $s);
		file_put_contents($file, $s);
		echo ".";
	}
}

?>
