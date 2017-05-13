<?PHP
  session_start();
  function __autoload($classname) {
      require_once '../php/class/'.$classname.'.class.php';
  }

//  $table = $_SESSION['TableName'];
  $table = $_POST['table'];
  unset($_POST['table']);
//  $fields = $_SESSION['Fields'];

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

//SQL INSERT 语句
  $query = "INSERT IGNORE $table SET ";
  reset($_POST);
  while ($postcell = each($_POST)) {
    if ($postcell['key'] == "PHPSESSID") {
      continue;
    }
    if (isset($postcell['value'])) {
      $query .= $postcell['key']."='".$postcell['value']."', ";
    }
  }
  $query = substr($query, 0, strlen($query) - 2);
//  echo "Query: $query";
//  die();

  $mysql = new PHPMySQL();
  $result = $mysql->runSQL($query);
  if (!$result) {
    DBError(mysql_errno(), mysql_error(), $query);
    if (mysql_errno() == 1062) {
      echo "<SCRIPT language='JavaScript'>;alert('纪录重复!');history.go(-1);</SCRIPT>";
    } else {
      echo "<SCRIPT language='JavaScript'>;alert('新建纪录失败!');history.go(-1);</SCRIPT>";
    }
    die();
  }
  echo "<SCRIPT language='JavaScript'>;alert('新建纪录成功!');window.opener.location.reload();history.go(-1);</SCRIPT>";
?>