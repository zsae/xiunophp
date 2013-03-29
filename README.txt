XiunoPHP
========

XiunoPHP 是一款面向高负载应用的 PHP 开发框架，PHPer 通过它可以快速的简单的开发出高负载项目。

它诞生于 NoSQL 刚刚兴起的时代，从开始就良好的支持 NoSQL DB，比如 MongoDB，当让也可以通过添加驱动文件来支持其他类型的DB。

它是 Xiuno BBS 产品开发过程中的衍生品，只有340K，34个文件，它良好的封装了各种DB（MySQL、MongoDB...), CACHE(Memcached、TTServer、Redis...），对上层只提供了12个方法，只需要掌握这12个方法，开发者就可以任意操作各种DB,CACHE。

它以行为最小存储单位，这样大大的简化和统一了 DB,CACHE 的接口，并且它引入了单点分发自增ID，让应用不再依赖于DB的 count(), max()，函数，便于分布式程序的设计。

在线文档地址: http://www.xiuno.com/doc/xiunophp/