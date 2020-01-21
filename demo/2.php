<?php

require_once '../vendor/autoload.php';

use bl\Db;

// $data = Db::table('user')
// 	->field(['id', 'name'])
// 	->whereOr(['id' => '什么'])
// 	->fetchSql(true)
// 	->find();
// var_dump($data);


// $data = [
// 	[
// 		'name' => '张三',
// 		'pass' => 456,
// 	],
// 	[
// 		'name' => '李四',
// 		'pass' => 123,
// 	]
	
// ];
// $result = Db::table('user')
// 	->fetchSql(true)
// 	->insertAll($data);
// var_dump($result);


// 更新数据
// $data = ['name' => '小猪', 'pass' => 123456];
// Db::table('user')
// 	->where('id', 1)
// 	->update($data);


$config = [
	'type'     => 'mysql',
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'username' => 'root',
    'password' => 'root',
    'database' => 'liang',
];
Db::setConfig($config);


// 自增
$data = Db::table('user')
	->field(['name'])
	->where('id', 1)
	->find();

var_dump($data);