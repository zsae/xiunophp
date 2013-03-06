<?php

/*
 * Copyright (C) xiuno.com
 */

//set_time_limit(0);

ob_implicit_flush(true);  

function xn_file_get_contents($file) {
	$fp = fopen($file, 'r');
	$s = fread($fp, filesize($file));
	fclose($fp);
	return $s;
}

// 扫描所有 .php 后缀的文件
function opendir_recursive($dir, $recall) {
       if (is_dir($dir)) {
          if ($dh = opendir($dir)) {
              while (($file = readdir($dh)) !== false ) {
                       if( $file != "." && $file != ".." )
                       {
                               if( is_dir( $dir . $file ) )
                               {
                                       opendir_recursive( $dir . $file . "/", $recall);
                               }
                               else
                               {
                                        call_user_func_array($recall, array($dir . $file));
                               }
                       }
              }
              closedir($dh);
          }
       }
}

$keywords = explode('|', 'com|system|exec|eval|escapeshell|cmd|passthru|base64_decode|gzuncompress');
function scan_recall($file) {
        global $keywords;
        $ext = substr($file, -4, 4);
        if($ext == '.php' || $ext == '.htm' || substr($file, -3, 3) == '.js') {
              //echo "\r\n$file ...";
                echo '.';
                $filesize = filesize($file);
                if($filesize == 0 || $filesize > 2000000) {
                	return;
                }
                $s = xn_file_get_contents($file);
                foreach($keywords as $keyword) {
                        if(preg_match("#$keyword\s*\(#is", $s)) {
                                echo "\r\n$file ...";
                                // 记录到当前目录下的 log.txt
                                error_log($file." [$keyword]\r\n", 3, './log.txt');
                                echo "[$keyword]\r\n";
                                break;
                        }
                }
        //      echo "\r\n";
        }
}

if(empty($argv[1])) {
        echo 'input the dir.';
        exit;
}

$dir = $argv[1];
opendir_recursive($dir, 'scan_recall');
echo "\r\n Done! log file location: ./log.txt\r\n";
?>