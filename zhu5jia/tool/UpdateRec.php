<?PHP
  session_start();
  function __autoload($classname) {
      require_once '../php/class/'.$classname.'.class.php';
  }

//  $table = $_SESSION['TableName'];
  $keys = $_SESSION['Keys'];
  $table = $_POST['table'];
  unset($_POST['table']);

//密码处理
//  if (isset($_POST['password'])) {
//    if ($_POST['password'] != $_POST['conpass']) {
//      echo "<SCRIPT language='JavaScript'>;alert('两次输入的密码不一致!');history.go(-1);</SCRIPT>";
//      die();
//    }
//    unset($_POST['conpass']);
//    $valueid = $_POST['password'];
//    include("./wpm.inc");
//    include("../Phps/IncludeFiles/Encrypt.inc");
//    $_POST['password'] = funEncrypt($_POST['password'], $keyword);
//  }

//SQL UPDATE 语句
  $query = "UPDATE IGNORE $table SET ";
  reset($_POST);
  while ($postcell = each($_POST)) {
    if ($postcell['key'] == "PHPSESSID") {
      continue;
    }
    if (isset($postcell['value'])) {
      $query .= $postcell['key']."='".$postcell['value']."', ";
    }
  }
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
  $query = substr_replace($query, $where, -2);
  $query = substr($query, 0, -5);
//  echo "Query: $query";
//  die();

  $mysql = new PHPMySQL();
  $result = $mysql->runSQL($query);
  if (!$result) {
    DBError(mysql_errno(), mysql_error(), $query);
    if (mysql_errno() == 1062) {
      echo "<SCRIPT language='JavaScript'>;alert('纪录重复!');history.go(-1);</SCRIPT>";
    } else {
      echo "<SCRIPT language='JavaScript'>;alert('更新纪录失败!');history.go(-1);</SCRIPT>";
    }
    die();
  }
  echo "<SCRIPT language='JavaScript'>;alert('更新纪录成功!');window.opener.location.reload();window.opener=null;window.open('', '_self');window.close();</SCRIPT>";
?>