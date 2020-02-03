<?php

namespace bl;

class Db
{
    private static $pdo;
    private static $config;
    private static $table;//表名
    private $where;//条件
    private $whereOr;

    
    public  $child;//子查询
    private $join;//连接
    private $between;//between and
    private $group;//分组
    private $having;//分组后操作
    private $order;//排序
    private $field;//字段,默认为*
    private $limit;//限制
    public  $fetchSql;
    private $inc;  
    

    /**
     * 构造方法 获取PDO对象
     */
    private function __construct()
    {
        try {
            if (! self::$pdo instanceof \PDO) {

                // 判断是否设置了数据库配置
                if (is_null(self::$config)) {
                    $config = self::defaultConfig();
                } else {
                    $config = self::$config;
                }

                $dsn = $config['type'] . ':dbname=' . $config['database'] . ';host=' . $config['host'] . ';port=' . $config['port'];

                $pdo = new \PDO($dsn, $config['username'], $config['password']);
                self::$pdo = $pdo;
            }
        } catch (\PDOException $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }

    /**
     * 数据库默认配置参数
     * 
     * @param  array  $config [description]
     * @return [type]         [description]
     */
    private static function defaultConfig()
    {
        return [
            // 数据库类型
            'type'     => 'mysql',
            // 服务器地址
            'host'     => '127.0.0.1',
            // 端口号
            'port'     => 3306,
            // 用户名
            'username' => 'root',
            // 登陆密码
            'password' => '',
            // 数据库名称
            'database' => '',
            // 数据库表前缀
            'prefix'   => '',
        ];
    }

    /**
     * 设定数据库配置参数
     */
    public static function setConfig(array $config = [])
    {
        // 配置数组合并
        self::$config = array_merge(self::defaultConfig(), $config);
    }

    /**
     * 错误信息处理 数组转为字符串
     * 
     * @return [type] [description]
     */
    private function errorInfoChange()
    {
        $errorInfo = self::$pdo->errorInfo();

        exit('ERROR ' . $errorInfo[1] . ' (' . $errorInfo[0] . '): ' . $errorInfo[2]);
    }

    /**
     * 指定查询数据表 完整数据表名
     * 
     * @return [type] [description]
     */
    public static function table(string $table = '')
    {
        self::$table = "`{$table}`";

        return new self;
    }

    /**
     * 指定查询数据表 带前缀
     * 
     * @return [type] [description]
     */
    public static function name(string $table = '')
    {
        self::$table = '`' . self::$config['prefix'] . $table .'`';

        return new self;
    }


    /**
     * where 且
     * 
     * @param  string $field  [description]
     * @param  string $option [description]
     * @param  string $value  [description]
     * @return [type]         [description]
     */
    public function where($field = '', $option = '', $value = '')
    {
        if (is_string($field) && $value != '') {
            $this->where[] = [$field, $option, $value];
        }
        
        if (is_string($field) && $value == '') {
            $value  = $option;
            $option = '=';
            is_numeric($value) || $value = "'{$value}'";
            $this->where[] = [$field, $option, $value];
        }

        if (is_array($field)) {
            foreach ($field as $k => $v) {
                if (is_array($v)) {
                    is_numeric($v[1]) || $v[1] = "'{$v[1]}'";
                    $this->where[] = [$k, $v[0], $v[1]];
                } else {
                    is_numeric($v) || $v = "'{$v}'";
                    $this->where[] = [$k, '=', $v];
                }
            }
        }



        return $this;
    }

    /**
     * where 且
     * 
     * @param  string $field  [description]
     * @param  string $option [description]
     * @param  string $value  [description]
     * @return [type]         [description]
     */
    public function whereOr($field = '', $option = '', $value = '')
    {
        if (is_string($field)) {

            if ($value == '') {
                $value  = $option;
                $option = '=';
            }
            is_numeric($value) || $value = "'{$value}'";
            $this->whereOr[] = [$field, $option, $value];
        }

        if (is_array($field)) {
            foreach ($field as $k => $v) {
                if (is_array($v)) {
                    is_numeric($v[1]) || $v[1] = "'{$v[1]}'";
                    $this->whereOr[] = [$k, $v[0], $v[1]];
                } else {
                    is_numeric($v) || $v = "'{$v}'";
                    $this->whereOr[] = [$k, '=', $v];
                }
            }
        }

        return $this;
    }

    /**
     * where条件
     *
     * @access private
     * 
     * @return [type] [description]
     */
    private function getWhere($field = '', $option = '', $value = '')
    {   
        if (empty($this->where) && empty($this->whereOr)) return '';

        $str = '';

        if (!empty($this->where)) {
            foreach ($this->where as $key => $value) {
                $str .= "`{$value[0]}`" . ' ' . strtoupper($value[1]) . ' ' . $value[2] . ' AND ';
            }
        }

        if (!empty($this->whereOr)) {
            if (!empty($this->where)) {
                $str = rtrim($str, ' AND ') . ' OR ';
            }
            foreach ($this->whereOr as $key => $value) {
                $str .= "`{$value[0]}`" . ' ' . strtoupper($value[1]) . ' ' . $value[2] . ' OR ';
            }
        }
            
        $str = rtrim($str, 'ANDOR ');

        return $str;
    }


    /**
     * 是否显示构建的SQL语句
     * 
     * @param  bool|boolean $bool [description]
     * @return [type]             [description]
     */
    public function fetchSql(bool $bool = false)
    {
        $this->fetchSql = $bool;

        return $this;
    }

    /**
     * 查询的字段
     * 
     * @param  [type] $field [description]
     *
     * field('id')
     * field('id,name')
     * field(['id', 'name'])
     * 
     * @return string `id`, `name`, `age`
     */
    public function field($field = '*')
    {
        if ($field === '*') {
            $this->field2 = '*';
            return $this;
        }

        if (is_string($field)) {
            $arr = explode(',',  $field);
        } else if (is_array($field)){
            $arr = $field;
        }

        $str = '';
        foreach ($arr as $value) {
            $str .= "`{$value}`, ";
        }
        $str = rtrim($str, ', ');

        $this->field = $str;

        return $this;
    }


    /**
     * 查询单条数据
     * 
     * @return [type] [description]
     */
    public function find()
    {
        // 没有调用field方法默认查询所有字段
        $this->field = $this->field ?: '*';

        $sql = 'SELECT ' . $this->field . ' FROM ' . self::$table;

        if (!empty($this->getWhere())) {
            $sql .= ' WHERE ' . $this->getWhere();
        }

        $sql .= ' LIMIT 1';

        // 查看构建的SQL语句
        if ($this->fetchSql === true) return $sql;

        $stmt = self::$pdo->query($sql);

        if ($stmt === false) {
            echo $sql;
            echo '<br>';
            var_dump(self::$pdo->errorInfo());
            exit;
        }

        // 查询到数据返回一维数据 查不到返回false而不是空数组
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        // 没有查询到数据返回false改为返回[]
        return $data ?: [];
    }

    /**
     * 添加单条数据
     *
     * @param  array $data 数据数组
     * 
     * @return [type] [description]
     */
    public function insert(array $data = [])
    {
        // 数组格式转换
        $change = $this->insertDataChange($data);

        $sql = 'INSERT INTO ' . self::$table . '(' . $change['key'] . ') VALUE(' . $change['value'] . ')';

        // 查看构建的SQL语句
        if ($this->fetchSql === true) return $sql;

        $rows = self::$pdo->exec($sql);

        if ($rows === false) $this->errorInfoChange();

        return $rows;
    }

    /**
     * 批量添加数据
     *
     * @param  array $data 二维数组
     * 
     * @return [type] [description]
     */
    public function insertAll(array $data = [])
    {
        $change = $this->insertAllChange($data);

        $sql = 'INSERT INTO ' . self::$table . '(' . $change['key'] . ')' . ' VALUES' . $change['value'];

        // 查看构建的SQL语句
        if ($this->fetchSql === true) return $sql;

        $rows = self::$pdo->exec($sql);

        if ($rows === false) $this->errorInfoChange();

        return $rows;
    }

    /**
     * 添加数据 一维数组格式转换
     * 
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    private function insertDataChange(array $data = [])
    {
        $key = $value = '';
        foreach ($data as $k => $v) {
            is_numeric($v) || $v = '\'' . $v . '\'';
            $key   .= '`' . $k . '`,';
            $value .= $v . ',';
        }
        $key   = rtrim($key, ',');
        $value = rtrim($value, ',');

        return ['key' => $key, 'value' => $value];
    }

    /**
     * 添加数据 二位数组格式转换
     * 
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    private function insertAllChange(array $data = [])
    {
        // 字段名
        $key = '';
        foreach ($data[0] as $k => $v) {
            $key .= "`{$k}`" . ',';
        }
        $key = rtrim($key, ',');


        // 字段值
        $value = '';
        foreach ($data as $v) {
            $value .= '(';
            foreach ($v as $k2 => $v2) {
                is_numeric($v2) || $v2 = "'{$v2}'";
                $value .= $v2 . ',';
            }
            $value = rtrim($value, ',');
            $value .= '),';
        }
        $value = rtrim($value, ',');

        return ['key' => $key, 'value' => $value];
    }


    /**
     * 更新数据 字段自增
     * 
     * @param  string  $field [description]
     * @param  integer $step  [description]
     * @return [type]         [description]
     */
    public function inc(string $field = '', $step = 1)
    {
        $this->inc[$field] = $step;

        return $this;
    }

    /**
     * 执行更新操作
     * 
     * @return [type] [description]
     */
    public function update(array $data = [])
    {
        if (!empty($this->inc)) {
            // 自增操作
            $str = '';
            foreach ($this->inc as $key => $value) {
                $str .= "`{$key}` = `{$key}` + {$value}, ";
            }
            $str = rtrim($str, ' ,');

            $sql = 'UPDATE ' . self::$table . ' SET ' . $str;
        } else {
            // 普通的更新操作
            $up = '';
            foreach ($data as $key => $value) {
                is_numeric($value) || $value = "'{$value}'";
                $up .= "`{$key}`" . '=' . $value . ',';
            }
            $up = rtrim($up, ',');

            $sql = 'UPDATE ' . self::$table . ' SET ' . $up;
        }

        
        // 判断有没有更新条件
        if (empty($this->getWhere())) {
            die('没有更新条件');
        } else {
            $sql .= ' WHERE ' . $this->getWhere();
        }


        // 查看构建的SQL语句
        if ($this->fetchSql === true) return $sql;

        // 执行更新操作
        $rows = self::$pdo->exec($sql);

        // 输出错误信息
        if ($rows === false) $this->errorInfoChange();

        return $rows;
    }

    /**
     * 删除数据
     * 
     * @return [type] [description]
     */
    public function delete(bool $delAll = false)
    {
        if ($delAll === true) {
            $sql = 'DELETE FROM ' . self::$table;
        } else {

            $sql = 'DELETE FROM ' . self::$table;

            // 判断有没有更新条件
            if (empty($this->getWhere())) {
                die('没有更新条件');
            } else {
                $sql .= ' WHERE ' . $this->getWhere();
            }
        }

        // 查看构建的SQL语句
        if ($this->fetchSql === true) return $sql;

        // 执行删除操作
        $rows = self::$pdo->exec($sql);

        // 输出错误信息
        if ($rows === false) $this->errorInfoChange();

        return $rows;
    }

    /**
     * 私有化克隆方法 禁止克隆对象
     *
     * @access private
     */
    private function __clone() {}
}

