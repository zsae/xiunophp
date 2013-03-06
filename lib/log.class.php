<?php

if(!isset($_SERVER['time'])) {
	$_SERVER['time'] = time();
	$_SERVER['time_fmt'] = gmdate('y-n-j H:i', time() + 8 * 3600);
}

/*
 * Copyright (C) xiuno.com
 */

class log {
	public static function write($s, $file = 'phperror.php') {
		if(IN_SAE) {
			sae_set_display_errors(false);
			sae_debug($s);
			return TRUE;
		}
		$logpath = FRAMEWORK_LOG_PATH;
		$s = self::safe_str($s);
		$logfile = $logpath.$file;
		$ip = $_SERVER['ip'];
		$time = $_SERVER['time_fmt'];
		$url = $_SERVER['REQUEST_URI'];
		$url = self::safe_str($url);
		
		$fp = fopen($logfile, 'ab+');
		if(!$fp) {
			throw new Exception('写入日志失败，可能磁盘已满，或者文件'.$logfile.'不可写。');
		}
		fwrite($fp, '<?php exit;?>'."	$time	$ip	$url	$s	\r\n");
		fclose($fp);
		return TRUE;
	}
	
	public static function safe_str($s) {
		$s = str_replace("\r\n", ' ', $s);
		$s = str_replace("\r", ' ', $s);
		$s = str_replace("\n", ' ', $s);
		$s = str_replace("\t", ' ', $s);
		return $s;
	}
	
	// 跟踪变量的值
	public static function trace($s) {
		if(!DEBUG) return;
		$processtime = number_format(microtime(1) - $_SERVER['time'], 3, '.', '');
		empty($_SERVER['trace']) && $_SERVER['trace'] = '';
		$_SERVER['trace'] .= "$s - $processtime\r\n";
	}
}
?>