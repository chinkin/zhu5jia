<?PHP
  session_start();
  function __autoload($classname) {
      require_once '../php/class/'.$classname.'.class.php';
  }

//  $table = $_SESSION['TableName'];
  $keys = $_SESSION['Keys'];
  $table = $_POST['table'];
  unset($_POST['table']);

//SQL DELETE 语句
  $query = "DELETE FROM $table ";
  $where = " WHERE ";
  for ($i = 0; $i < count($keys); $i++) {
    if (isset($keys[$i]['Value'])) {
      $where .= $keys[$i]['Field']."='".$keys[$i]['Value']."' AND ";
    }
  }
  if (substr($where, 7) == "") {
    echo "<SCRIPT language='JavaScript'>;alert('条件子句为空!');history.go(-1);</SCRIPT>";
    die();
  }
  $query = substr_replace($query, $where, -1, 0);
  $query = substr($query, 0, -5);
//  echo "Query: $query";
//  die();

  $mysql = new PHPMySQL();
  $result = $mysql->runSQL($query);
  if (!$result) {
    DBError(mysql_errno(), mysql_error(), $query);
    echo "<SCRIPT language='JavaScript'>;alert('删除纪录失败!');history.go(-1);</SCRIPT>";
    die();
  }
  echo "<SCRIPT language='JavaScript'>;alert('删除纪录成功!');window.opener.location.reload();window.opener=null;window.open('', '_self');window.close();</SCRIPT>";
?>