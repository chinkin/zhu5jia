<?php
/*
* PHPMySQLDB连接数据库类
* author chinkin
* revision: 1
*/
class PHPMySQLDB {
  protected static $rootpath = "";

//连接数据库，返回连接句柄
  static public function connectDB() {
    self::$rootpath = $_SERVER['DOCUMENT_ROOT'];
    $wpmpath = self::$rootpath."/tool/wpm.inc";
    include($wpmpath);
    $mysqli = new mysqli($hostname, $dbuser, $dbpassword, $database);
    if ($mysqli->connect_error) {
      self::DBError($mysqli->connect_errno, $mysqli->connect_error);
      echo "<SCRIPT language='JavaScript'>;alert('数据库未启动，请联系系统管理员!');history.go(-1);</SCRIPT>";
      die();
    }
//注意中文乱码！
//    if (!$mysqli->set_charset("utf8")) {
//      self::DBError("000", "could not set utf8");
//    }
    return $mysqli;
  }

//释放数据库资源
  static public function disconnectDB(&$mysqli) {
    if (is_object($mysqli)) {
      $mysqli->close();
      $mysqli = NULL;
    }
  }

//错误输出
  static function DBError($mysql_errno, $mysql_error, $mysql_state=NULL, $query=NULL) {
    $logpath = self::$rootpath."/log/mysqllog.err";
    $error_time = date('Y-m-d H:i:s');
    $log_time = $error_time." Error occured! \r\n";
    $log_msg =  "SQLErr-".$mysql_errno.": ".$mysql_error.".\r\n";
    error_log($log_time, 3, $logpath);
    error_log($log_msg, 3, $logpath);
    if (!is_null($mysql_state)) {
      $log_msg = "SQLState-".$mysql_state." \r\n";
      error_log($log_msg, 3, $logpath);
    }
    if (!is_null($query)) {
      $log_msg = "Query: ".$query." \r\n";
      error_log($log_msg, 3, $logpath);
    }
  }
}
?>