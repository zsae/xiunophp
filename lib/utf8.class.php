<?php

/*
 * Copyright (C) xiuno.com
 */

if(!defined('FRAMEWORK_UTF8')) {
	if(extension_loaded('mbstring')) {
		mb_internal_encoding('UTF-8');
		define('FRAMEWORK_UTF8', TRUE);
	} else {
		define('FRAMEWORK_UTF8', FALSE);
	}
}

class utf8 {
	public static function substr($str, $offset, $length = NULL) {
		if(FRAMEWORK_UTF8) {
			return mb_substr($str, $offset, $length, 'UTF-8');
		}
		if (self::is_ascii($str)) {
			return ($length === NULL) ? substr($str, $offset) : substr($str, $offset, $length);
		}
		
		$str    = (string) $str;
		$strlen = self::strlen($str);
		$offset = (int) ($offset < 0) ? max(0, $strlen + $offset) : $offset;
		$length = ($length === NULL) ? NULL : (int) $length;
	
		if ($length === 0 OR $offset >= $strlen OR ($length < 0 AND $length <= $offset - $strlen)) {
			return '';
		}
	
		if ($offset == 0 AND ($length === NULL OR $length >= $strlen)) {
			return $str;
		}
	
		$regex = '^';
	
		if ($offset > 0) {
			$x = (int) ($offset / 65535);
			$y = (int) ($offset % 65535);
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= ($y == 0) ? '' : '.{'.$y.'}';
		}
	
		if ($length === NULL)
		{
			$regex .= '(.*)';
		} elseif ($length > 0) {
			$length = min($strlen - $offset, $length);
	
			$x = (int) ($length / 65535);
			$y = (int) ($length % 65535);
			$regex .= '(';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= '.{'.$y.'})';
		} else {
			$x = (int) (-$length / 65535);
			$y = (int) (-$length % 65535);
			$regex .= '(.*)';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= '.{'.$y.'}';
		}
	
		preg_match('/'.$regex.'/us', $str, $matches);
		return $matches[1];
	}
	
	// 安全截取，防止SQL注射
	public static function safe_substr($str, $offset, $length = NULL) {
		$str = self::substr($str, $offset, $length);
		$len = strlen($str) - 1;
		if($len >=0) {
			if($str[$len] == '\\') $str[$len] = '';
		}
		return $str;
	}
	
	public static function is_ascii($str) {
		return !preg_match('/[^\x00-\x7F]/S', $str);
	}
	
	public static function strlen($str) {
		if(FRAMEWORK_UTF8) {
			return mb_strlen($str);
		}
		if(self::is_ascii($str)) {
			return strlen($str);
		} else {
			return strlen(utf8_decode($str));
		}
	}
}

//echo utf8::substr('abc的abc等等等', 1, -1);