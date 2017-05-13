<?php
/*
* PHPMySQL数据库操作类
* author chinkin
* revision: 1
*/
class PHPMySQL {
  protected $db = NULL;
  protected $controltable = "z5j_tables";

//构造
  function __construct() {
    $this->db = PHPMySQLDB::connectDB();
  }

//获取数据库中表或视图的信息
//$type: tb->table; vw->view
  function getTables($type=NULL) {
    $query = "SHOW FULL TABLES";
    $result = $this->runSQL($query);
    if ($result === FALSE) {
      return FALSE;
    }
    if (mysql_num_rows($result) == 0) {
      echo "数据库里没有任何表或视图!";
      die();
    }
    $i = 0;
    while ($tableinfo = $result->fetch_rows()) {
      switch ($type) {
        case "tb":
          if ($tableinfo[1] == "BASE TABLE") {
            $tables[$i] = $tableinfo[0];
            $i++;
          }
          break;
        case "vw":
          if ($tableinfo[1] == "VIEW") {
            $tables[$i] = $tableinfo[0];
            $i++;
          }
          break;
        default:
          $tables[$i] = $tableinfo[0];
          $i++;
      }
    }
    return $tables;
  }

//从表管理表中获取表名
//$alias: 别名
//$tableinfo[0]: 返回表名
  function getTableName($alias) {
    if (empty($alias)) {
      return NULL;
    }
    if (empty($this->controltable)) {
      return $alias;
    }
    $query = "SELECT tablename FROM ".$this->controltable." WHERE alias='$alias'";
    $result = $this->runSQL($query);
    if ($result === FALSE) {
      return FALSE;
    }
    if ($result->num_rows == 0) {
      echo "<SCRIPT language='JavaScript'>;alert('无法获取表".$alias."的表名!');</SCRIPT>";
      return FALSE;
    }
    $tableinfo = $result->fetch_assoc();
    $result->free();
    return $tableinfo['tablename'];
  }

//获取表项目
//$tablename: 表名
//$fields: 返回项目数组; auto_increment项目在项目名前加"+"
  function getTableFields($tablename) {
    if (empty($tablename)) {
      return NULL;
    }
    $query = "DESC ".$tablename;
    $result = $this->runSQL($query);
    if ($result === FALSE) {
      return FALSE;
    }
    if ($result->num_rows == 0) {
      echo "<SCRIPT language='JavaScript'>;alert('无法获取表".$tablename."的项目信息!');</SCRIPT>";
      return FALSE;
    }
    $i = 0;
    while ($fieldinfo = $result->fetch_assoc()) {
      $fields[$i] = $fieldinfo['Field'];
      if ($fieldinfo['Extra']=='auto_increment') {
        $fields[$i] = "+".$fields[$i];
      }
      $i++;
    }
    return $fields;
  }

//插入数据
//$table: 表名
//$fieldscontent: 数组[项目->值]
  function insertRow($table, $fieldscontent) {
    if (empty($table) || empty($fieldscontent)) {
      return NULL;
    }
    $tablename = $this->getTableName($table);
    if ($tablename === FALSE) {
      return FALSE;
    }
    $fields = $this->getTableFields($tablename);
    if ($fields === FALSE) {
      return FALSE;
    }
    $set = $this->createSet($fields, $fieldscontent);
    if ($set === FALSE) {
      return FALSE;
    }
    $query = "INSERT ".$tablename." SET".$set;
    $result = $this->runSQL($query);
//    if (!$result) {
//      echo "<SCRIPT language='JavaScript'>;alert('表".$tablename."新建记录失败!');</SCRIPT>";
//    }
    return $result;
  }

//更新数据
//$table: 表名
//$fieldscontent: 数组[项目->值]
//$condition: 条件子句(优先于条件数组)
//$keys: 条件数组[项目->值]
  function updateRows($table, $fieldscontent, $condition="", $keys="") {
    if (empty($table) || empty($fieldscontent) || (empty($keys) && empty($condition))) {
      return NULL;
    }
    $tablename = $this->getTableName($table);
    if ($tablename === FALSE) {
      return FALSE;
    }
    $fields = $this->getTableFields($tablename);
    if ($fields === FALSE) {
      return FALSE;
    }
    $set = $this->createSet($fields, $fieldscontent);
    if ($set === FALSE) {
      return FALSE;
    }
    $query = "UPDATE ".$tablename." SET".$set;
    $where = "";
    $condition = trim($condition);
    if (!empty($condition)) {
      $where = " ".$condition;
    } else {
      $where = $this->createWhere($fields, $keys);
      if (!$where) {
        $where = "";
      }
    }
    if (!empty($where)) {
      $query .= " WHERE".$where;
    }
    $result = $this->runSQL($query);
//    if (!$result) {
//      echo "<SCRIPT language='JavaScript'>;alert('表".$tablename."更新记录失败!');</SCRIPT>";
//    }
//echo "alert('更新记录query:".$query."');";
    return $result;
  }

//替换数据
//$table: 表名
//$fieldscontent: 数组[项目->值]
//$condition: 条件子句(优先于条件数组)
//$keys: 条件数组[项目->值]
  function replaceRows($table, $fieldscontent, $condition="", $keys="") {
    if (empty($table) || empty($fieldscontent) || (empty($keys) && empty($condition))) {
      return NULL;
    }
    $tablename = $this->getTableName($table);
    if ($tablename === FALSE) {
      return FALSE;
    }
    $fields = $this->getTableFields($tablename);
    if ($fields === FALSE) {
      return FALSE;
    }
    $set = $this->createSet($fields, $fieldscontent);
    if ($set === FALSE) {
      return FALSE;
    }
    $query = "REPLACE ".$tablename." SET".$set;
    $where = "";
    $condition = trim($condition);
    if (!empty($condition)) {
      $where = " ".$condition;
    } else {
      $where = $this->createWhere($fields, $keys);
      if (!$where) {
        $where = "";
      }
    }
    if (!empty($where)) {
      $query .= " WHERE".$where;
    }
    $result = $this->runSQL($query);
//    if (!$result) {
//      echo "<SCRIPT language='JavaScript'>;alert('表".$tablename."更新记录失败!');</SCRIPT>";
//    }
    return $result;
  }

//删除数据
//$table: 表名
//$condition: 条件子句(优先于条件数组)
//$keys: 条件数组[项目->值]
//$limit: 删除条数
//$order: ORDER BY子句; UTF8汉字排序ORDER BY hex(chinese_field)，$limit存在时有意义
  function deleteRows($table, $condition="", $keys="", $limit=0, $order="") {
    if (empty($table) || (empty($keys) && empty($condition))) {
      return NULL;
    }
    $tablename = $this->getTableName($table);
    if (!$tablename) {
      return FALSE;
    }
    $fields = $this->getTableFields($tablename);
    if (!$fields) {
      return FALSE;
    }
    $query = "DELETE FROM ".$tablename;
    $condition = trim($condition);
    if (!empty($condition)) {
      $where = " ".$condition;
    } else {
      $where = $this->createWhere($fields, $keys);
      if (!$where) {
        $where = "";
      }
    }
    if (!empty($where)) {
      $query .= " WHERE".$where;
    }
    if (is_int($limit) && $limit > 0) {
      $order = trim($order);
      if (!empty($order)) {
        $query .= " ORDER BY ".$order;
      }
      $query .= " LIMIT ".$limit;
    }
    $result = $this->runSQL($query);
//    if (!$result) {
//      echo "<SCRIPT language='JavaScript'>;alert('表".$tablename."删除记录失败!');</SCRIPT>";
//    }
    return $result;
  }

//选择数据
//$table: 表名
//$fields: 项目数组
//$condition: 条件子句
//$order: ORDER BY子句; UTF8汉字排序ORDER BY hex(chinese_field)
//$group: GROUP BY子句
//$having: HAVING子句
//$limit: LIMIT子句，$rowcount存在时为偏移量(从0开始计)，$rowcount不存在时为行数
//$rowcount: 行数
  function selectRows($table, $fields="*", $condition="", $order="", $group="", $having="", $limit=0, $rowcount=0) {
    if (empty($table)) {
      return NULL;
    }
    $tablename = $this->getTableName($table);
    if (!$tablename) {
      return FALSE;
    }
    if (empty($fields) || $fields == "*") {
      $selectfields = " *";
    } elseif (is_array($fields)) {
      $selectfields = $this->createField($fields);
      if (!$selectfields) {
        return FALSE;
      }
    } else {
      return FALSE;
    }
    $query = "SELECT ".$selectfields." FROM ".$tablename;
    $condition = trim($condition);
    if (!empty($condition)) {
      $query .= " WHERE ".$condition;
    }
    $order = trim($order);
    if (!empty($order)) {
      $query .= " ORDER BY ".$order;
    }
    $group = trim($group);
    if (!empty($group)) {
      $query .= " GROUP BY ".$group;
      $having = trim($having);
      if (!empty($having)) {
        $query .= " HAVING ".$having;
      }
    }
    if (is_int($limit) && ($limit !== 0 || $rowcount !== 0)) {
      if ($limit > 0 && (!is_int($rowcount) || $rowcount == 0)) {
        $query .= " LIMIT ".$limit;
      }
      if (is_int($rowcount) && $rowcount > 0) {
        $query .= " LIMIT ".$limit.",".$rowcount;
      }
    }
//echo "<SCRIPT language='JavaScript'>;alert('query:".$query."!');</SCRIPT>";
    $result = $this->runSQL($query);
    return $result;
  }

//生成SET子句
//$fields: 项目数组
//$fieldscontent: 数组[项目->值]
//$query: 返回" A=1, B=2,..., Z=26"
  function createSet($fields, $fieldscontent) {
    $query = "";
    for ($i = 0; $i < count($fields); $i++) {
      if (isset($fieldscontent[$fields[$i]])) {
//        if ($fields[$i] == "password") {
//          $query .= " ".$fields[$i]."=MD5('".$fieldscontent[$fields[$i]]."'),";
//        } else {
        if ($fieldscontent[$fields[$i]] === "NULL" || $fieldscontent[$fields[$i]] === "null") {
          $query .= " ".$fields[$i]."=NULL,";
        } else {
          $query .= " ".$fields[$i]."='".$fieldscontent[$fields[$i]]."',";
        }
//        }
      }
    }
    if (empty($query)) {
      return FALSE;
    }
    $query = substr($query, 0, strlen($query) - 1);
    return $query;
  }

//生成WHERE子句
//$fields: 项目数组
//$fieldscontent: 数组[项目->值]
//$query: 返回" A=1 AND B=2 ... AND Z=26"
  function createWhere($fields, $keys) {
    $query = "";
    for ($i = 0; $i < count($fields); $i++) {
      if ($keys[$fields[$i]]) {
        $query .= " ".$fields[$i]."='".$keys[$fields[$i]]."' AND";
      }
    }
    if (empty($query)) {
      return FALSE;
    }
    $query = substr($query, 0, strlen($query) - 4);
    return $query;
  }

//生成项目子句
//$fields: 项目数组
//$query: 返回" A, B,..., Z"
  function createField($fields) {
    $query = "";
    for ($i = 0; $i < count($fields); $i++) {
      $query .= " ".$fields[$i].",";
    }
    if (empty($query)) {
      return FALSE;
    }
    $query = substr($query, 0, strlen($query) - 1);
    return $query;
  }

//执行SQL
//$query: SQL语句
//$resultmode: 该参数接受两个值，一个是MYSQLI_STORE_RESULT，表示结果作为缓冲集合返回；另一个是 MYSQLI_USE_RESULT，表示结果作为非缓冲集合返回。
//$result: 成功执行SELECT, SHOW, DESCRIBE或 EXPLAIN查询会返回一个mysqli_result对象，失败时返回FALSE；成功执行INSERT、UPDATE、REPLACE或DELETE查询会返回一个TRUE，失败时返回FALSE
  function runSQL($query, $resultmode=MYSQLI_STORE_RESULT) {
    if (empty($query)) {
      return NULL;
    }
    $result = $this->db->query($query, $resultmode);
//    PHPMySQLDB::DBError("000", "debug", NULL, $query);
    if ($result === FALSE) {
      PHPMySQLDB::DBError($this->db->errno, $this->db->error, $this->db->sqlstate, $query);
//      echo "<SCRIPT language='JavaScript'>;alert('SQL执行出错，请查看mysqllog.err文件!');</SCRIPT>";
      return $result;
    }
    if ($result === TRUE) {
      $result = $this->db->affected_rows;
//echo "alert('SQL执行结果:".$result."');";
      if ($result == -1) {
        PHPMySQLDB::DBError($this->db->errno, $this->db->error, $this->db->sqlstate, $query);
        return FALSE;
      }
    }
    return $result;
  }

//析构
  function __destruct() {
    PHPMySQLDB::disconnectDB($this->db);
  }
}
?>