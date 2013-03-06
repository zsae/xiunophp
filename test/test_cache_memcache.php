<?php

function xn_assert($comment, $expression) {
	$add = $expression ? '...	[OK]' : '...	[FAILDED]';
	echo $comment.$add."	\r\n";
}

echo "Test cache_memcache.class.php \r\n";

define('DEBUG', 1);
define('TEST_PATH', str_replace('\\', '/', getcwd()).'/');
define('FRAMEWORK_PATH', TEST_PATH.'../');

$conf = include TEST_PATH.'conf.php';
include FRAMEWORK_PATH.'core.php';
core::init();

$cache = new cache_memcache($conf['cache']['memcache']);

// 初始化 maxid, count
$r = $cache->maxid('user', 0);
xn_assert("maxid('user', 0)", $r == 0);

$r = $cache->count('user', 0);
xn_assert("count('user', 0)", $r == 0);



// 增加一条记录:
$uid = $cache->maxid('user', '+1');
xn_assert("maxid('user', '+1')", $uid == 1);
$r = $cache->set("user-uid-$uid", array('username'=>'admin1', 'email'=>'xxx1@xxx.com'));
xn_assert("set('user-uid-$uid', array())", $r == TRUE);

// 增加一条记录:
$uid = $cache->maxid('user', '+1');
$r = $cache->set("user-uid-$uid", array('username'=>'admin1', 'email'=>'xxx1@xxx.com'));

// 增加一条记录:
$uid = $cache->maxid('user', '+1');
$r = $cache->set("user-uid-$uid", array('username'=>'admin1', 'email'=>'xxx1@xxx.com'));

$n = $cache->count('user', '+3');

// 取一条记录
$arr = $cache->get('user-uid-1');
xn_assert("maxid('user', '+1')", !empty($arr) && $arr['username'] == 'admin1');



// 删除
$r = $cache->delete("user-uid-1");
xn_assert("delete('user-uid-1')", $r == TRUE);
$n = $cache->count('user', '-1');

// 删除以后的总数，maxid 删除以后不变
$n = $cache->count('user');
xn_assert("count('user')", $n == 2);



// 通过最大ID进行遍历
$uids = array();
$maxid = $cache->maxid('user');
for($i=0; $i<$maxid; $i++) $uids[] = "user-uid-$i";
$userlist = $cache->get($uids);

print_r($userlist);