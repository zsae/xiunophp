<?php

function bbs_assert($comment, $expression) {
	$add = $expression ? '...	[OK]' : '...	[FAILDED]';
	echo $comment.$add."	\r\n";
}

define('DEBUG', 2);
define('TEST_PATH', './');
define('FRAMEWORK_PATH', TEST_PATH.'../');

$conf = include TEST_PATH.'conf.php';

include FRAMEWORK_PATH.'core.php';
core::init();

echo "Test db_mongodb.class.php \r\n\r\n";

$db = new db_mongodb($conf['db']['mongodb']);
$db->truncate('user');
$db->maxid('user', 0);
$db->count('user', 0);

// 增加一条记录:
$uid = $db->maxid('user', '+1');

bbs_assert("maxid('user', '+1')", $uid == 1);
$r = $db->set("user-uid-$uid", array('username'=>'admin1', 'email'=>'xxx1@xxx.com', 'posts'=>1));
bbs_assert("set()", $r == TRUE);

// 增加一条记录:
$uid = $db->maxid('user', '+1');
bbs_assert("maxid('user', '+1')", $uid == 2);
$r = $db->set("user-uid-$uid", array('username'=>'admin2', 'email'=>'xxx2@xxx.com', 'posts'=>2));

// 增加一条记录:
$uid = $db->maxid('user', '+1');
bbs_assert("maxid('user', '+1')", $uid == 3);
$r = $db->set("user-uid-$uid", array('username'=>'admin3', 'email'=>'xxx3@xxx.com', 'posts'=>3));

$n = $db->count('user', 3);
bbs_assert("maxid('user', '+1')", $n == 3);

// 取一条数据
$arr = $db->get('user-uid-1');
bbs_assert("maxid('user', '+1')", $arr['username'] == 'admin1');

$n = $db->index_update('user', array('uid'=>array('>='=>1)), array('posts'=>123));
$user = $db->get('user-uid-1');
bbs_assert("index_update()", $n == 3);
bbs_assert("index_update()", $user['posts'] == 123);

// 删除一条记录
$r = $db->delete("user-uid-1");
bbs_assert("maxid('user', '+1')", $r == TRUE);

$n = $db->count('user', 2);
bbs_assert("maxid('user', '+1')", $n == 2);

// 翻页取数据
$n = $db->count('user');

// 翻页取数据
$userlist = $db->index_fetch('user', array('uid'), array('uid' => array('>'=>0)), array(), 0, 10);
print_r($userlist);

// 删除所有数据
$n = $db->index_delete('user', array('uid'=>array('>'=>2)));
$user = $db->get('user-uid-2');
$user2 = $db->get('user-uid-3');
bbs_assert("index_delete()", $user['uid'] != 2);
bbs_assert("index_delete()", empty($user2));

//print_r($_SERVER['sqls']);

exit;


/*
ini_set('error_reporting', E_ALL | E_STRICT);  //打开错误显示开关
//ini_set('error_reporting', 0);  //关闭错误输出

$dburl = 'localhost';
$port= '27017';
$dbname = 'test';
$username = '';
$password = '';

$connection = new Mongo( "mongodb://127.0.0.1:$port" ); // connect to a remote host (default port)
$db = $connection->selectDB($dbname);
$collection = $db->selectCollection('framework_table');
$doc = array('name'=>'mysql', "maxid" => 123);
$collection->insert($doc);
$collection->update(array("name"=>'mysql'), array('$set'=>array('maxid'=>444)));
$collection->update(array("name"=>'mysql'), array('$set'=>array('maxid'=>444)));
$re = $collection->findOne(array('name'=>'mysql'));
print_r($re);
exit;







//遍历：
$cursor = $collection->find();
//var_dump($cursor); //object(MongoCursor)[5] 5个对象
//返回$collection集合中文档的数量
echo '文档条数：'. $collection->count();
//
echo '<br>';
foreach ($cursor as $val)
{
echo $val['_id']. ': '. $val['name'] .'--'. $val['type'].'--'
   . $val['info']['x'] .'--'. $val['info']['y'] .'--'. $val['versions'][2] .'<br>';
}

//更新：
//
$collection->update(array("a"=>10), array('$set'=>array('a'=>10000)));
//
$options['multiple'] = true; //默认是 false，是否改变匹配的多行
$collection->update(
array("info.x"=>100),
array('$set'=>array('info.y'=>800)),
$options);

//按条件查找：
$query = array("a"=>10000);
$cursor = $collection->find($query); //在$collectio集合中查找满足$query的文档
while($cursor->hasNext())
{
var_dump($cursor->getNext()); //返回了数组
}

//$collection -> findOne();  //返回$collection集合中第一个文档
//$joe = $collection->findOne(array("_id" => $ret['_id']));

//删除一个数据库：
//$connection->dropDB("...");
//$connection->dropDB("...");

//列出所有可用数据库：
$m->listDBs(); //无返回值

//关闭连接：
$connection->close();
exit;*/
?>