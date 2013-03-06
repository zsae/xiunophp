<?php

function xn_assert($comment, $expression) {
	$add = $expression ? '...	[OK]' : '...	[FAILDED]';
	echo $comment.$add."	\r\n";
}

define('DEBUG', 1);
define('TEST_PATH', str_replace('\\', '/', getcwd()).'/');
define('FRAMEWORK_PATH', TEST_PATH.'../');

include TEST_PATH.'conf.php';
include FRAMEWORK_PATH.'core.php';
core::init();

echo "Test base_model.class.php \r\n\r\n";

$b = new base_model();
$b->table = 'blog';
$b->primarykey = array('blogid');
$b->cacheconf['enable'] = FALSE;
$b->db->query(" DROP TABLE IF EXISTS `xn_blog`");                             
$b->db->query(" CREATE TABLE `xn_blog` (                                
           `blogid` int(11) unsigned NOT NULL auto_increment,    
           `uid` int(11) unsigned NOT NULL default '0',       
           `dateline` int(11) unsigned NOT NULL default '0',     
           `subject` char(80) NOT NULL default '',               
           `message` longtext NOT NULL,                          
           `username` char(16) NOT NULL default '',              
           `isprivate` tinyint(4) NOT NULL default '0',          
           PRIMARY KEY  (`blogid`),                              
           KEY `uidblogid` (`uid`,`blogid`)                
         ) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 ");

$n = $b->count(0);
xn_assert('b->count(0)', $n == 0);

$m = $b->maxid(0);
xn_assert('b->maxid(0)', $m == 0);

$m = $b->maxid('+1');
xn_assert('b->maxid(\'+1\')', $m == 1);

$r = $b->set(1, array('subject'=>'this is subject 111 .', 'message'=>'this is message 111'));
xn_assert('b->set(1)', $r);

$m = $b->maxid('+1');
xn_assert('b->maxid(\'+1\')', $m == 2);

$r = $b->set(2, array('subject'=>'this is subject 222 .', 'message'=>'this is message 222'));
xn_assert('b->set(2)', $r);

$r = $b->count('+2');
xn_assert('b->count("+2")', $r == 2);

$arr = $b->get(2);
xn_assert('b->get("+2")', count($arr) == 2);

$list = $b->index_fetch($cond = array(), $orderby = array('uid'=>-1), 0, $pagesize = 30);
print_r($list);

// ---------------------> 测试主键包含多列字段的情况, 实例 primary key(fid, user)

$b = new base_model();
$b->db->query(" DROP TABLE IF EXISTS `xn_mblog`");    
$b->db->query("
	 CREATE TABLE `xn_mblog` (                                
           `fid` int(11) unsigned NOT NULL default '0',       
           `blogid` int(11) unsigned NOT NULL default '0',       
           `uid` int(11) unsigned NOT NULL default '0',       
           `dateline` int(11) unsigned NOT NULL default '0',     
           `subject` char(80) NOT NULL default '',               
           `message` longtext NOT NULL,                          
           `username` char(16) NOT NULL default '',              
           `isprivate` tinyint(4) NOT NULL default '0',          
           PRIMARY KEY  (`fid`, `blogid`),                              
           KEY `uidblogid` (`uid`,`blogid`)                
         ) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8  
");

$b->table = 'mblog';
$b->primarykey = array('fid', 'blogid');
$b->cacheconf['enable'] = FALSE;

$n = $b->count(0);
xn_assert('b->count(0)', $n == 0);

$m = $b->maxid(0);
xn_assert('b->maxid(0)', $m == 0);

$m = $b->maxid('+1');
xn_assert('b->maxid(\'+1\')', $m == 1);

$r = $b->set(1, 1, array('subject'=>'this is subject 111 .', 'message'=>'this is message 111'));
xn_assert('b->set(1)', $r);

$m = $b->maxid('+1');
xn_assert('b->maxid(\'+1\')', $m == 2);

$r = $b->set(1, 2, array('subject'=>'this is subject 222 .', 'message'=>'this is message 222'));
xn_assert('b->set(2)', $r);

$r = $b->count('+2');
$r = $b->count('+2');
xn_assert('b->count("+2")', $r == 4);

$arr = $b->get(1, 2);
xn_assert('b->get("+2")', count($arr) == 2);

$list = $b->index_fetch($cond = array('fid'=>1), $orderby = array('uid'=>-1), 0, $pagesize = 30);
print_r($list);
