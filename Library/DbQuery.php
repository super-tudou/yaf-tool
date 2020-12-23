<?php
/**
 * Created by PhpStorm.
 * @file   Query.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/10/30 8:36 下午
 * @desc   Query.php
 */

namespace Generate\Library;

class DbQuery
{
    private $host, $user, $port, $dbName, $password, $con, $charset;
    public static $mysqlDb;

    public static function getInstance()
    {
        if (!empty(self::$mysqlDb)) {
            return self::$mysqlDb;
        }
        return self::$mysqlDb = new self();
    }

    public function setParams($params)
    {
        $this->host = $params['host'];
        $this->user = $params['username'];
        $this->charset = $params['charset'];
        $this->port = 3306;
        $this->password = $params['password'];
        $this->dbName = $params['database'];
        return $this;
    }

    public function openDb()
    {
        $this->con = mysqli_connect($this->host, $this->user, $this->password, $this->dbName, $this->port) or die('打开失败');
        //当然如上面不填写数据库也可通过mysqli_select($conn,$dbConf['dbName'])来选择数据库
        mysqli_set_charset($this->con, $this->charset);//设置编码
        return $this->con;
    }

    public function closeDb()
    {
        mysqli_close($this->con);
    }

    public function querySql($sql)
    {
        $rs = $this->con->query($sql);
        $data = array();//保存数据
        while ($tmp = mysqli_fetch_assoc($rs)) {//每次从结果集中取出一行数据
            $data[] = $tmp;
        }
        return $data;
    }

    public static function query($sql)
    {
        return Query::getInstance()->querySql($sql);
    }

}


////1.打开连接
//$conn = openDb($dbConf);
////2query方法执行增、查、删、改
//$sql = 'SELECT t.`id1` from `t1` as t';
///*************数据查询***************************/
//
///*************数据插入***************************/
//$sql = 'INSERT INTO `t1`(`id1`,`id2`) VALUES(3,4);';
//$rs = $conn->query($sql);
////3.关闭连接
//closeDb($conn);
