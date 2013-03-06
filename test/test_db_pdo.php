<?php

function bbs_assert($comment, $expression) {
	$add = $expression ? '...	[OK]' : '...	[FAILDED]';
	echo $comment.$add."	\r\n";
}

echo "Test db_pdo.class.php \r\n\r\n";

define('DEBUG', 1);
define('TEST_PATH', str_replace('\\', '/', getcwd()).'/');
define('FRAMEWORK_PATH', TEST_PATH.'../');

$conf = include TEST_PATH.'conf.php';
include FRAMEWORK_PATH.'core.php';
core::init();

$db = new db_pdo($conf['db']['pdo']);

$db->truncate('user');
$db->maxid('user-uid', 0);
$db->count('user', 0);

$db->query("DROP TABLE IF EXISTS `bbs_user`");

$db->query("CREATE TABLE `bbs_user` (
  `uid` int(11) unsigned NOT NULL auto_increment,		# 用户id
  `regip` int(11) NOT NULL default '0',				# 注册ip
  `regdate` int(11) unsigned NOT NULL default '0',		# 注册日期
  `username` char(16) NOT NULL default '',			# 用户名
  `password` char(32) NOT NULL default '',			# 密码 md5()
  `salt` char(8) NOT NULL default '',				# 随机干扰字符，用来混淆密码
  `email` char(40) NOT NULL default '',				# EMAIL
  `groupid` tinyint(3) unsigned NOT NULL default '0',		# 用户组 id
  `threads` mediumint(8) unsigned NOT NULL default '0',		# 主题数
  `posts` mediumint(8) unsigned NOT NULL default '0',		# 回帖数
  `avatar` int(11) unsigned NOT NULL default '0',		# 头像最后更新的时间，0为默认头像
  KEY username(`username`),
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

// 增加一条记录:
$uid = $db->maxid('user-uid', '+1');
bbs_assert("maxid('user', '+1')", $uid == 1);
$r = $db->set("user-uid-$uid", array('username'=>'admin1', 'email'=>'xxx1@xxx.com'));
bbs_assert("set()", $r == TRUE);

// 增加一条记录:
$uid = $db->maxid('user-uid', '+1');
bbs_assert("maxid('user', '+1')", $uid == 2);
$r = $db->set("user-uid-$uid", array('username'=>'admin2', 'email'=>'xxx2@xxx.com'));

// 增加一条记录:
$uid = $db->maxid('user-uid', '+1');
bbs_assert("maxid('user', '+1')", $uid == 3);
$r = $db->set("user-uid-$uid", array('username'=>'admin3', 'email'=>'xxx3@xxx.com'));

$n = $db->count('user', '+3');
bbs_assert("count('user', '+3')", $n == 3);

// 取一条数据
$arr = $db->get('user-uid-1');
bbs_assert("get('user-uid-1')", $arr['username'] == 'admin1');

$n = $db->index_update('user', array('uid'=>array('>='=>1)), array('posts'=>123));
$user = $db->get('user-uid-1');
bbs_assert("index_update()", $n == 3);
bbs_assert("index_update()", $user['posts'] == 123);

// 删除一条记录
$r = $db->delete("user-uid-1");
bbs_assert("delete('user-uid-1')", $r == TRUE);

$n = $db->count('user', '-1');
bbs_assert("count('user', '-1')", $n == 2);

$arr = $db->get("user-uid-1");
bbs_assert("delete('user-uid-1')", $arr == array());

// 翻页取数据
$n = $db->count('user');
bbs_assert("count('user')", $n == 2);

// 翻页取数据
$userlist = $db->index_fetch($table = 'user', $key = 'uid', $cond = array('uid' => array('>'=>0)), $sort = array(), $start = 0, $limit = 10);
print_r($userlist);


// 删除所有数据
$n = $db->index_delete('user', array('uid'=>array('>'=>1)));
$user = $db->get('user-uid-1');
$user2 = $db->get('user-uid-2');
bbs_assert("index_delete()", $user['uid'] == 1);
bbs_assert("index_delete()", empty($user));