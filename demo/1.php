<?php

/**
 * 示例文件
 */

require_once '../vendor/autoload.php';

use bl\Db;

Db::setConfig([
	'type'     => 'mysql',
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'username' => 'root',
    'password' => 'root',
	'database' => 'liang',
	'prefix'   => '',
]);

// 删除数据
Db::table('user')->insert(['name' => 123]);