<?PHP
  session_start();
  function __autoload($classname) {
      require_once '../php/class/'.$classname.'.class.php';
  }

  $tablename = trim($_POST['tablename']);
  $filename = trim($_POST['importfile']);
  $fullpath = trim($_POST['fullpath']);
  if (empty($tablename) || empty($filename) || empty($fullpath)) {
    echo "<SCRIPT language='JavaScript'>;alert('缺少必须项目!');history.go(-1);</SCRIPT>";
    die();
  }
  if (strrchr($fullpath, "\\") != "\\") {
    $fullpath .= "\\";
  }

//echo "tablename:".$tablename." | filename:".$fullpath.$filename;
//die();

  $mysql = new PHPMySQL();
  $query = "DESC ".$tablename;
  $result = $mysql->runSQL($query);
  $i = 0;
  if ($result->num_rows != 0) {
    while ($fieldinfo = $result->fetch_assoc()) {
      $fields[$i] = $fieldinfo['Field'];
      $i++;
    }
  } else {
    echo "<SCRIPT language='JavaScript'>;alert('请确认表的状态!');history.go(-1);</SCRIPT>";
    die();
  }

  if ($file = fopen($fullpath.$filename, "r")) {
    $j = 1;
    while (!feof($file)) {
      $line = str_replace("\r\n", "", fgets($file));
      $values = explode(";", $line);
      if (count($values) != count($fields)) {
        echo "项目个数不匹配".count($values)."|".count($fields);
        die();
      }
      $query = "INSERT IGNORE $tablename SET ";
      $i = 0;
      foreach ($values as $key => $value) {
        $query .= $fields[$i]."='".$value."', ";
        $i++;
      }
      $query = substr($query, 0, strlen($query) - 2);
//      echo $query."<br />";
      $result = $mysql->runSQL($query);
      if (!$result) {
        DBError(mysql_errno(), mysql_error(), $query);
        if (mysql_errno() == 1062) {
          echo "<SCRIPT language='JavaScript'>;alert('第".$j."纪录重复!');history.go(-1);</SCRIPT>";
        } else {
          echo "<SCRIPT language='JavaScript'>;alert('插入第".$j."条纪录失败!');history.go(-1);</SCRIPT>";
        }
        die();
      }
      $j++;
    }
    echo "<SCRIPT language='JavaScript'>;alert('import文件成功!');window.opener.location.reload();history.go(-1);</SCRIPT>";
  } else {
    echo "<SCRIPT language='JavaScript'>;alert('打开文件 ".$fullpath.$filename." 失败!');history.go(-1);</SCRIPT>";
    die();
  }
?>