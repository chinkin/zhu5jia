<?PHP
  header('Content-Type:text/html; charset=utf-8');
  session_id($_POST['sid']);
  session_start();

//echo "alert('folder:".$_SESSION['Folder']."|filename:".$_SESSION['Filename']."|tempfile:".$_FILES['filename']['tmp_name']."|loaderror:".$_FILES['filename']['error']."');";
  $uploaddir = "../Attachment/";
  if (isset($_SESSION['Folder']) && !empty($_SESSION['Folder']) && $_SESSION['Folder'] != null) {
    $uploaddir .= $_SESSION['Folder']."/";
  }
  if (isset($_SESSION['Filename']) && !empty($_SESSION['Filename']) && $_SESSION['Filename'] != null) {
    $uploadfile = $uploaddir.$_SESSION['Filename'];
  }
//  $uploadfile = $uploaddir.$_FILES['filename']['name'];

  if (file_exists($uploaddir) && $_FILES['filename']['error'] == 0) {
    if (!move_uploaded_file($_FILES['filename']['tmp_name'], iconv("UTF-8", "gb2312",  $uploadfile))){
      $_FILES['filename']['error'] = 8;
    }
  }

  unset($_SESSION['Folder']);
  unset($_SESSION['Filename']);
  switch ($_FILES['filename']['error']) {
    case 0:
      $errmsg = "上传文件成功!";
      break;
    case 1:
      $errmsg = "文件大小超过限制!";
      break;
    case 2:
      $errmsg = "文件大小超过FORM限制!";
      break;
    case 3:
      $errmsg = "文件部分被上传!";
      break;
    case 4:
      $errmsg = "没有文件被上传!";
      break;
    case 8:
      $errmsg = "文件无法移动!";
      break;
    default:
      $errmsg = "上传文件失败!";
  }
  if ($_FILES['filename']['error'] != 0) {
    echo "returncode = false; alert('".$errmsg."');";
  }
?>