<?php

/*
 * Copyright (C) xiuno.com
 */

// 静态类，提供各种全局方法
class core {

	/*
	GET|POST|COOKIE|REQUEST|SERVER
	HTML|SAFE
	*/
	public static function gpc($k, $var = 'G') {
		switch($var) {
			case 'G': $var = &$_GET; break;
			case 'P': $var = &$_POST; break;
			case 'C': $var = &$_COOKIE; break;
			case 'R': $var = isset($_GET[$k]) ? $_GET : (isset($_POST[$k]) ? $_POST : $_COOKIE); break;
			case 'S': $var = &$_SERVER; break;
		}
		return isset($var[$k]) ? $var[$k] : NULL;
	}
		
	public static function addslashes(&$var) {
		if(is_array($var)) {
			foreach($var as $k=>&$v) {
				self::addslashes($v);
			}
		} else {
			$var = addslashes($var);
		}
		return $var;
	}
	
	public static function stripslashes(&$var) {
		if(is_array($var)) {
			foreach($var as $k=>&$v) {
				self::stripslashes($v);
			}
		} else {
			$var = stripslashes($var);
		}
		return $var;
	}
	
	public static function htmlspecialchars(&$var) {
		if(is_array($var)) {
			foreach($var as $k=>&$v) {
				self::htmlspecialchars($v);
			}
		} else {
			$var = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $var);
		}
		return $var;
	}
	
	public static function urlencode($s) {
		$s = urlencode($s);
		return str_replace('-', '%2D', $s);
	}
	
	public static function urldecode($s) {
		return urldecode($s);
	}
	
	public static function json_decode($s) {
		return $s === FALSE ? FALSE : json_decode($s, 1);
	}
	
	// 替代 json_encode
	public static function json_encode($data) {
		if(version_compare(PHP_VERSION, '5.2.0', '>=')) {
			return json_encode($data);
		}
		if(is_array($data) || is_object($data)) {
			$islist = is_array($data) && (empty($data) || array_keys($data) === range(0,count($data)-1));
			if($islist) {
				$json = '['.implode(',', array_map(array('core', 'json_encode'), $data)).']';
			} else {
				$items = Array();
				foreach($data as $key => $value) $items[] = self::json_encode("$key").':'.self::json_encode($value);
				$json = '{'.implode(',', $items).'}';
			}
		} elseif(is_string($data)) {
			$string = '"'.addcslashes($data, "\\\"\n\r\t/".chr(8).chr(12)).'"';
			$json   = '';
			$len    = strlen($string);
			for($i = 0; $i < $len; $i++ ) {
				$char = $string[$i];
				$c1   = ord($char);
				if($c1 <128 ) { 
					$json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1);
					continue;
				}
				$c2 = ord($string[++$i]);
				if (($c1 & 32) === 0) {
					$json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128);
					continue;
				}
				$c3 = ord($string[++$i]);
				if(($c1 & 16) === 0) {
					$json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128));
					continue;
				}
				$c4 = ord($string[++$i]);
				if(($c1 & 8 ) === 0) {
					$u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1;
					$w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3);
					$w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128);
					$json .= sprintf("\\u%04x\\u%04x", $w1, $w2);
				}
			}
		}  else {
			$json = strtolower(var_export( $data, true ));
		}
		return $json;
	}

	// 是否为命令行模式
	public static function is_cmd() {
		return !isset($_SERVER['REMOTE_ADDR']);
	}
	
        public static function ob_handle($s) {
        	if(!empty($_SERVER['ob_gzip']) && !ini_get('zlib.output_compression') && function_exists('gzencode') && strpos(core::gpc('HTTP_ACCEPT_ENCODING', 'S'), 'gzip') !== FALSE) {
			$s = gzencode($s, 5);   		// 0 - 9 级别, 9 最小，最耗费 CPU  
			header("Content-Encoding: gzip");
			//header("Vary: Accept-Encoding");	// 下载的时候，IE 6 会直接输出脚本名，而不是文件名！非常诡异！估计是压缩标志混乱。
			header("Content-Length: ".strlen($s));
        	} else {
        		header("Content-Encoding: none");
               		header("Content-Length: ".strlen($s));
        	}
        	return $s;
        }

        public static function ob_start($gzip = TRUE) {
        	$_SERVER['ob_gzip'] = $gzip;
        	ob_start(array('core', 'ob_handle'));
        	$_SERVER['ob_start'] = 1;
        }
	
	public static function ob_end_clean() {
		if(!empty($_SERVER['ob_start'])) {
			ob_end_clean();
			$_SERVER['ob_start'] = 0;
		}
	}
	
	public static function init_set() {
		//----------------------------------> 全局设置:
		// 错误报告
		if(DEBUG) {
			// E_ALL | E_STRICT
			error_reporting(E_ALL | E_STRICT);
			//error_reporting(E_ALL & ~(E_NOTICE | E_STRICT));
			@ini_set('display_error', 'ON');
			
		} else {
			error_reporting(E_ALL & ~E_NOTICE);
		}
		
		// 关闭运行期间的自动增加反斜线
		@set_magic_quotes_runtime(0);
		
		// 最低版本需求判断
		PHP_VERSION < '5.0' && exit('Required PHP version 5.0.* or later.');
		// 输出 HTTP 头
		// header('Content-Type: text/html; charset=UTF-8');
	}

	public static function init_supevar() {
		// 将更多有用的信息放入 $_SERVER 变量
		$_SERVER['starttime'] = microtime(1);
		$_SERVER['time'] = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		$_SERVER['ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$_SERVER['sqls'] = array();// debug
		
		// 兼容IIS $_SERVER['REQUEST_URI']
		(!isset($_SERVER['REQUEST_URI']) || (isset($_SERVER['HTTP_X_REWRITE_URL']) && $_SERVER['REQUEST_URI'] != $_SERVER['HTTP_X_REWRITE_URL'])) && self::fix_iis_request();
		
		// 重新初始化 $_GET
		$_GET = array();
		self::init_get();
	}
	
	public static function init_handle() {
		// 自动 include
		spl_autoload_register(array('core', 'autoload_handle'));
		
		// 异常处理类
		set_exception_handler(array('core', 'exception_handle'));
		
		// 自定义错误处理函数，设置后 error_reporting 将失效。因为要保证 ajax 输出格式，所以必须触发 error_handle
		if(DEBUG || core::gpc('ajax', 'R')) {
			set_error_handler(array('core', 'error_handle'));
		}
		
	}
	
	// new class 不存在，触发
	public static function autoload_handle($classname) {
		$libclasses = ' check, log, form, utf8, image, template, ';
		if(substr($classname, 0, 3) == 'db_') {
			include FRAMEWORK_PATH.'db/'.$classname.'.class.php';
			return class_exists($classname, false);
		} elseif(substr($classname, 0, 6) == 'cache_') {
			include FRAMEWORK_PATH.'cache/'.$classname.'.class.php';
			return class_exists($classname, false);
		} elseif(strpos($libclasses, ' '.$classname.', ') !== FALSE || substr($classname, 0, 3) == 'xn_') {
			include FRAMEWORK_PATH.'lib/'.$classname.'.class.php';
			return class_exists($classname, false);
		} else {
			// 此处增加了方便，未加载插件，只有一些特殊的场合使用，比如安装程序。建议采用 core::model($conf, 'user'); 的方式，尽量避免 $user = new user($conf);
			global $conf;
			if(!class_exists($classname)) {
				foreach($conf['model_path'] as $path) {
					if(is_file($path."$classname.class.php")) {
						include_once $path."$classname.class.php";
						break;
					}
				}
			}
			if(!class_exists($classname, false)) {
				throw new Exception('class '.$classname.' does not exists.');
			}
		}
		return true;
	}
	
	public static function exception_handle($e) {
		
		// 避免死循环
		DEBUG && $_SERVER['exception'] = 1;
		
		core::ob_end_clean();
		core::ob_start(0);
		
		log::write($e->getMessage().' File: '.$e->getFile().' ['.$e->getLine().']');
		
		$s = '';
		if(DEBUG) {
			try {
				if(self::gpc('ajax', 'R')) {
					$s = xn_exception::to_json($e);
				} else {
					//!core::is_cmd() && header('Content-Type: text/html; charset=UTF-8');
					$s = xn_exception::to_html($e);
				}
			} catch (Exception $e) {
				$s = get_class($e)." thrown within the exception handler. Message: ".$e->getMessage()." on line ".$e->getLine();
			}
		} else {
			if(self::gpc('ajax', 'R')) {
				$s = core::json_encode(array('servererror'=>$e->getMessage()));
			} else {
				$s = $e->getMessage();
			}
		}
		
		echo $s;
		exit;
	}
	
	public static function error_handle($errno, $errstr, $errfile, $errline) {
		
		// 防止死循环
		$errortype = array (
			E_ERROR              => 'Error',
			E_WARNING            => 'Warning',
			E_PARSE              => 'Parsing Error',	# uncatchable
			E_NOTICE             => 'Notice',
			E_CORE_ERROR         => 'Core Error',		# uncatchable
			E_CORE_WARNING       => 'Core Warning',		# uncatchable
			E_COMPILE_ERROR      => 'Compile Error',	# uncatchable
			E_COMPILE_WARNING    => 'Compile Warning',	# uncatchable
			E_USER_ERROR         => 'User Error',
			E_USER_WARNING       => 'User Warning',
			E_USER_NOTICE        => 'User Notice',
			E_STRICT             => 'Runtime Notice',
			//E_RECOVERABLE_ERRROR => 'Catchable Fatal Error'
		);
		
		$errnostr = isset($errortype[$errno]) ? $errortype[$errno] : 'Unknonw';
	
		// 运行时致命错误，直接退出。并且 debug_backtrace()
		$s = "[$errnostr] : $errstr in File $errfile, Line: $errline";
		
		// 抛出异常，记录到日志
		//echo $errstr;
		if(DEBUG && empty($_SERVER['exception'])) {
			throw new Exception($s);
		} else {
			
			log::write($s);
			
			$s = preg_replace('# \S*[/\\\\](.+?\.php)#', ' \\1', $s);
			if(self::gpc('ajax', 'R')) {
				core::ob_end_clean();
				core::ob_start();
				//$s = preg_replace('#[\\x80-\\xff]{2}#', '?', $s);// 替换掉 gbk， 否则 json_encode 会报错！
				// 判断错误级别，决定是否退出。
				
				if($errno != E_NOTICE) {
					echo self::json_encode(array('servererror'=>$s));
					exit;
				} else {
					$_SERVER['notice_error'] .= $s;
					// 继续执行。
				}
			} else {
				echo $s;
				// 继续执行。
			}
		}
		return 0;
	}
	
	/**
	 * 修正 IIS  $_SERVER[REQUEST_URI]
	 *
	 */
	private static function fix_iis_request() {
		if(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$_SERVER['REQUEST_URI'] = &$_SERVER['HTTP_X_REWRITE_URL'];
		} else if(isset($_SERVER['HTTP_REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = &$_SERVER['HTTP_REQUEST_URI'];
		} else {
			if(isset($_SERVER['SCRIPT_NAME'])) {
				$_SERVER['HTTP_REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
			} else {
				$_SERVER['HTTP_REQUEST_URI'] = $_SERVER['PHP_SELF'];
			}
			if(isset($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'] = '?' . $_SERVER['QUERY_STRING'];
			} else {
				$_SERVER['REQUEST_URI'] = '';
			}
		}
	}
	
	/**
	 * URL 隐射，结果保存到 $_GET
	 * 支持格式：
	 * http://www.domain.com/xiuno_framework/demo/index.php?user-login-page-2.htm?xxx=123&yyy=ddd
	 * http://www.domain.com/xiuno_framework/demo/?user-login-page-2.htm?xxx=123&yyy=ddd
	 * http://www.domain.com/xiuno_framework/demo/?user-login-page-2.htm&xxx=123&yyy=ddd
	 * http://www.domain.com/xiuno_framework/demo/index.php?xxx=123&yyy=ddd
	 * http://www.domain.com/xiuno_framework/demo/?xxx=123&yyy=ddd
	 * 
	 * 处理顺序：
	 * /bbs/index.php?abc-ddd.htm&step=2	[1]
	 * abc-ddd.htm&step=2			[2]
	 * abc-ddd.htm?step=2			[3]
	 * abc-ddd?step=2			[4]
	 * abc-add				[5]
	 * step=2				[6]
	 * 
	 * 如下格式：
	 * user-login-page-2?xxx=123&yyy=ddd
	 * 解析后：
	 * $_GET = Array
		(
		    [0] => user
		    [1] => login
		    [2] => page
		    [3] => 2
		    [xxx] => 123
		    [yyy] => ddd
		    [page] => 2
		)
		
	   测试: http://www.domain.com/xn/framework2.0/?a-b.htm?c=d&e=http%3A%2F%2F1111.sss.com%2Ff.htm
	   结果：
	   	Array
		(
		    [0] => a
		    [1] => b
		    [c] => d
		    [e] => http://1111.sss.com/f.htm
		)

	 */
	private static function init_get() {
		$r = $_SERVER['REQUEST_URI'];
		
		// fix rewrite 导致的 .htm 变为 _htm
		if(strrpos($r, '_htm') !== FALSE) {
			$r = str_replace('_htm', '.htm', $r);		
		}
		
		// setcookie 的时候，依赖此设置。在浏览器的头中 HTTP HEADER Cookie: xxx, expiry: xxx
		// 这里初始值，后面可以设置正确的值。
		date_default_timezone_set('Asia/Shanghai');
		
		$get = &$_GET;
		$r = substr($r, strrpos($r, '/') + 1);				//第[1]步
		substr($r, 0, 9) == 'index.php' && $r = substr($r, 9);
		substr($r, 0, 1) == '?' && $r = substr($r, 1);

		//$r = preg_replace('#^/?([^/]+/(index\.php)?\??)*#', '', $r);	//第[1]步
		
		// 第一个分号作为分隔
		$r = str_replace('.htm&', '?', $r);				//第[2]步
		$r = str_replace('.htm?', '?', $r);				//第[3]步
		$sep = strpos($r, '?');						//第[4]步
		
		$s1 = $s2 = '';	// $s1 为 url 前半部分(格式：user-login-page-123), $s2 为后半部分(格式：user=login&page=2)。
		if($sep !== FALSE) {
			$s1 = substr($r, 0, $sep);
			$s2 = substr($r, $sep + 1);
		} else {
			$s1 = $r;
			$s2 = '';
			if(substr($s1, -4) == '.htm') {
				$s1 = substr($s1, 0, -4);			//第[5]步
			} else {
				$s2 = $s1;					//第[6]步
				$s1 = '';
			}
		}
		
		$arr1 = $arr2 = array();
		
		$s1 && $arr1 = explode('-', $s1);
		parse_str($s2, $arr2);
		$get += $arr1;
		$get += $arr2;
		
		$num = count($arr1);
		if($num > 2) {
			for($i=2; $i<$num; $i+=2) {
				isset($arr1[$i+1]) && $get[$arr1[$i]] = $arr1[$i+1];
			}		
		}
		
		$get[0] = isset($get[0]) && preg_match("/^\w+$/", $get[0]) ? $get[0] : 'index';
		$get[1] = isset($get[1]) && preg_match("/^\w+$/", $get[1]) ? $get[1] : 'index';
	}
	
	public static function process_hook(&$conf, $hookfile) {
		$s = '';
		// 遍历插件目录，如果有该 hook
		$plugins = core::get_plugins($conf);
		$pluginnames = array_keys($plugins);
		foreach($pluginnames as $v) {
			$path = $conf['plugin_path'].$v;
			if(!is_file($path.$hookfile)) continue;
			if(empty($plugins[$v]) || empty($plugins[$v]['enable'])) continue;
			
			$s2 = file_get_contents($path.$hookfile);
			
			// 去掉第一行 
			$s2 = preg_replace('#^<\?php\s*exit;\?>\s{0,2}#i', '', $s2);
			if(substr($s2, 0, 5) == '<?php' && substr($s2, -2, 2) == '?>') {
				$s2 = substr($s2, 5, -2);		
			}
			/*$s2 = preg_replace('#^\s*<\?php(.*?)\?>\s*$#ism', '\\1', $s2);*/
			
			$s .= $s2;
		}
		
		core::process_urlrewrite($conf, $s);
		
		return $s;
	}
	
	public static function process_urlrewrite(&$conf, &$s) {
		if($conf['urlrewrite']) {
			$s = preg_replace('#([\'"])\?(.+?\.htm)#i', '\\1'.$conf['app_url'].'\\2', $s);
		} else {
			$s = preg_replace('#([\'"])\?(.+?\.htm)#i', '\\1'.$conf['app_url'].'?\\2', $s);
		}
	}
	
	// 对于包含的目标文件进行处理，生成 bbs_common_control.class.php 
	// 约定 include BBS_PATH.'xxx/xxx.php'; 这样的格式。避免 eval() 解析。
	public static function process_include(&$conf, &$s) {
		preg_match_all('#[\r\n]{1,2}\s*include\s+(\w+)\.[\'"]([^;]+)[\'"];#is', $s, $m);
		if(!empty($m[1])) {
			foreach($m[1] as $k=>$path) {
				$realpath = constant($m[1][$k]).$m[2][$k];
				$file = 'control_'.basename($m[2][$k]); // include 为公共部分，不加 app_id 区分，$conf['app_id'].
				$tmpfile = $conf['tmp_path'].$file;
				$tmptmpfile = FRAMEWORK_TMP_TMP_PATH.$file;
				$s2 = file_get_contents($realpath);
				$s2 = preg_replace('#\t*\/\/\s*hook\s+([^\s]+)#ies', "core::process_hook(\$conf, '\\1')", $s2);
				core::process_urlrewrite($conf, $s2);
				file_put_contents($tmptmpfile, $s2);
				$s = str_replace($m[0][$k], "\r\n\tinclude '$tmpfile';", $s);
			}
		}
		
		// 直接包含内容会加速PHP，但是代码阅读性差，不利于调试。
		//$s = preg_replace('#\r\ninclude\s+\'(\S+)\';\s*\r\n#ies', "file_get_contents('\\2')", $s);
		//$s = preg_replace('#\r\ninclude\s+(\w+)\.\'(\S+)\';\s*\r\n#ies', "substr(file_get_contents(constant('\\1').'\\2'), 5, -2)", $s);	// 直接包含内容，可以加速 include，要求必须 < ? p h p   ? > 的文档格式
		
		return $s;
	}

	public static function get_url_path() {
		$port = self::gpc('SERVER_PORT', 'S');
		$portadd = $port == 80 ? '' : ':80';
		$host = self::gpc('HTTP_HOST', 'S');
		//$schme = self::gpc('SERVER_PROTOCOL', 'S');
		$path = substr(self::gpc('PHP_SELF', 'S'), 0, strrpos(self::gpc('PHP_SELF', 'S'), '/'));
		return  "http://$host$portadd$path/";
	}
	
	// 获取已经开启的 plugin, ，专门用来扫描插件目录
	public static function get_plugins(&$conf, $force = 0) {
		// 缓存结果
		static $plugins = array();
		if(!empty($plugins) && !$force) return $plugins;
		
		if(empty($conf['plugin_path'])) return array();
		$path = $conf['plugin_path'];
		if(!is_dir($path)) return array();
		$settingfile = $conf['upload_path'].'plugin.json';
		$setting = is_file($settingfile) ? (array)core::json_decode(file_get_contents($settingfile)) : array();
		
		$arr = self::get_paths($path);
		foreach($arr as $v) {
			$conffile = $path.$v.'/conf.php';
			$pconf = is_file($conffile) ? include($conffile) : array();
			!isset($pconf['enable']) && $pconf['enable'] = isset($setting[$v]['enable']) ? $setting[$v]['enable'] : 0;
			!isset($pconf['installed']) && $pconf['installed'] = isset($setting[$v]['installed']) ? $setting[$v]['installed'] : 0;
			!isset($pconf['pluginid']) && $pconf['pluginid'] = isset($setting[$v]['pluginid']) ? $setting[$v]['pluginid'] : 0;
			$plugins[$v] = $pconf;
		}
		return $plugins;
	}

	public static function get_paths($path, $fullpath = FALSE) {
		$arr = array();
		$df = opendir($path);
		while($dir = readdir($df)) {
			if($dir == '.' || $dir == '..' || $dir[0] == '.' || !is_dir($path.$dir)) continue;
			$arr[] = $fullpath ? $path.$dir.'/' : $dir;
		}
		sort($arr);// 从低到高排序
		return $arr;
		
	}

	/*
		加载 model：
		$muser = core::model($conf, 'userext');				// 隐式加载 model，从配置文件中加载
		$muser = core::model($conf, 'userext', 'uid', 'uid');		// 显式加载 model，不需要配置文件中申明
	*/
	public static function model(&$conf, $model, $primarykey = array(), $maxcol = '') {
		$modelname = 'model_'.$model.'.class.php';
		if(isset($_SERVER['models'][$modelname])) {
			return $_SERVER['models'][$modelname];
		}
		
		// 隐式加载 model，从配置文件中加载
		if(empty($primarykey)) {
			// 自动配置 model, 不再以来 model/xxx.class.php
			if(isset($conf['model_map'][$model])) {
				$arr = $conf['model_map'][$model];
				$new = new base_model($conf);
				$new->table = $arr[0];
				$new->primarykey = (array)$arr[1];
				$new->maxcol = $arr[2];
				$_SERVER['models'][$modelname] = $new;
				return $new;
			// 搜索 model_path, plugin_path
			} else {
				$modelfile = $conf['tmp_path'].$modelname;
				if((!is_file($modelfile) || DEBUG > 1) && !IN_SAE) {
					// 开始从以下路径查找 model： model, upload/plugin/*/
					$orgfile = '';
					foreach($conf['model_path'] as &$path) {
						if(is_file($path.$model.'.class.php')) {
							$orgfile = $path.$model.'.class.php';
							break;
						}
					}
					if(empty($orgfile) && empty($conf['disable_plugin'])) {
						$plugins = self::get_plugins($conf);
						$pluginnames = array_keys($plugins);
						foreach($pluginnames as &$v) {
							$path = $conf['plugin_path'].$v;
							if(is_file($path."$model.class.php")) {
								$orgfile = $path."$model.class.php";
								break;
							}
						}
					}
					if(empty($orgfile)) {
						return FALSE;
					}
					$s = file_get_contents($orgfile);
					$s = preg_replace('#\t*\/\/\s*hook\s+([^\s]+)#ies', "core::process_hook(\$conf, '\\1')", $s);
					file_put_contents($modelfile, $s);
				}
				
				include $modelfile;
				$new = new $model($conf);
				$_SERVER['models'][$modelname] = $new;
				return $new;
			}
			//throw new Exception("$model 在配置文件中的 model_map 中没有定义过。");
		// 显式加载 model
		} else {
			$new = new base_model($conf);
			$new->table = $model;
			$new->primarykey = (array)$primarykey;
			$new->maxcol = $maxcol;
			$_SERVER['models'][$modelname] = $new;
			return $new;
		}
	}
		
	public static function init() {
		// ---------------------> 初始化
		
		core::init_set();
		core::init_supevar();
		core::init_handle();
		
		// GPC 安全过滤，关闭，数据的正确性可能会受到影响。
		if(get_magic_quotes_gpc()) {
			core::stripslashes($_GET);
			core::stripslashes($_POST);
			core::stripslashes($_COOKIE);
		}
		
		// 如果非命令行，则输出 header 头
		if(!core::is_cmd()) {
			
			header("Expires: 0");
			header("Cache-Control: private, post-check=0, pre-check=0, max-age=0");
			header("Pragma: no-cache");
		}
		
	}
	
	public static function run(&$conf) {
		
		//---------------------------------->  包含相关的 control，并实例化
		
		$control = core::gpc(0);
		$action = core::gpc(1);
		
		$objfile = $conf['tmp_path'].$conf['app_id']."_control_{$control}_control.class.php";
		
		// 如果缓存文件不存在，则搜索目录
		if(!is_file($objfile) || (DEBUG > 0 && !IN_SAE)) {
			$controlfile = '';
			$paths = $conf['control_path'];
			foreach($paths as $path) {
				$controlfile = $path."{$control}_control.class.php";
				if(is_file($controlfile)) {
					break;
				} else {
					$controlfile = '';
				}
			}
			if(empty($controlfile) && empty($conf['disable_plugin'])) {
				$plugins = core::get_plugins($conf);
				$pluginnames = array_keys($plugins);
				foreach($pluginnames as $v) {
					// 如果有相关的 app path, 这只读取该目录
					$path = $conf['plugin_path'].$v;
					if(is_dir($path.$this->conf['app_id'])) {
						$path = $path.$this->conf['app_id'];
					}
					$controlfile = $path."{$control}_control.class.php";
					if(is_file($controlfile)) {
						break;
					} else {
						$controlfile = '';
					}
				}
			}
			if(empty($controlfile)) {
				throw new Exception("您输入的URL 不合法，{$control} control 不存在。");
			}
			
			// 处理 hook  urlrewrite, static_url
			if(!is_file($controlfile)) {
				throw new Exception("您输入的URL 不合法，{$control} control 不存在。");
			}
			$s = file_get_contents($controlfile);
			core::process_include($conf, $s);
			$s = preg_replace('#\t*\/\/\s*hook\s+([^\s]+)#ies', "core::process_hook(\$conf, '\\1')", $s);
			core::process_urlrewrite($conf, $s);
			
			file_put_contents($objfile, $s);
		}
		
		if(include $objfile) {
			$controlclass = "{$control}_control";
			$onaction = "on_$action";
			
			$newcontrol = new $controlclass($conf);
			
			if(method_exists($newcontrol, $onaction)) {
				
				$newcontrol->$onaction();
			} else {
				throw new Exception("您输入的URL 不合法，$action 方法未实现:");
			}
		} else {
			throw new Exception("您输入的URL 不合法，{$control} control 不存在。");
		}
		unset($newcontrol, $control, $action);
	}
}

?>