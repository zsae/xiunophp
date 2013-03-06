<?php

/*
 * Copyright (C) xiuno.com
 */

class check {
	static function is_email($s) {
		return preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $s);
	}
	
	static function is_url($s) {
		return preg_match('#^http://#i', $s);  //url已http://开头  i 忽略大小写
	}
	
	static function is_qq($s) {
		return preg_match('#^\d+$#', $s);
	}
	
	static function is_tel($s) {
		return preg_match('#^[\d\-]+$#', $s);
	}
	
	static function is_mobile($s) {
		return preg_match('#^\d{11}$#', $s);
	}
	
	static function is_version($s) {
		return preg_match('#^\d+(\.\d+)+$#', $s);
	}
	
	
}

/** 用法

Check::check_email();

*/

?>