<?php
 
/**************************************************************************************************
 *
 *	【注意】：
 *		请不要使用 Windows 的记事本编辑此文件！此文件的编码为UTF-8编码，不带有BOM头！
 *		建议使用UEStudio, Notepad++ 类编辑器编辑此文件！
 *
***************************************************************************************************/

// 全局配置变量

return array (
	
	// 数据库配置， type 为默认的数据库类型，可以支持多种数据库: mysql|pdo|mongodb
	'db' => array (
		'type' => 'mysql',
		'mysql' => array (
			'master' => array (
					'host' => 'localhost',
					'user' => 'root',
					'password' => 'root',
					'name' => 'test',
					'charset' => 'utf8',
					'tablepre' => 'bbs_',
					'engine'=>'MyISAM',
			),
			'slaves' => array (
			)
		),
		'pdo' => array (
			'master' => array (
					'host' => 'localhost',
					'user' => 'root',
					'password' => 'root',
					'name' => 'test',
					'charset' => 'utf8',
					'tablepre' => 'bbs_',
					'engine'=>'MyISAM',
			),
			'slaves' => array (
			)
		),
		'mongodb' => array(
			'master' => array (
					'host' => '10.0.0.253:27017',
					'user' => '',
					'password' => '',
					'name' => 'bbs',
					'tablepre' => '',
			),
			'slaves' => array (
			)
		),
	),	
	// 缓存服务器的配置，支持: memcache|ea|apc|redis
	'cache' => array (
		'enable'=>1,
		'type'=>'memcache',
		'memcache'=>array (
			'multi'=>0,
			'host'=>'10.0.0.253',
			'port'=>'11211',
		)
	),
);

?>