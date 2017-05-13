<?PHP
  header('Content-Type:text/html; charset=utf-8');

  $uploaddir = "../";
  $pathname = str_replace(" ", "", $_POST['pathname']);
  if ($pathname != "") {
    $uploaddir .= $pathname."/";
  } else {
//    $uploaddir .= "tools/";
  }
  $uploadfile = $uploaddir.$_FILES['filename']['name'];

  if (file_exists($uploaddir) && $_FILES['filename']['error'] == 0) {
    if (!move_uploaded_file($_FILES['filename']['tmp_name'], iconv("UTF-8", "gb2312",  $uploadfile))){
      $_FILES['filename']['error'] = 8;
    }
  }

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

  $header_str = <<< EOHEADER
<HTML>
<HEAD>
</HEAD>
<BODY>

EOHEADER;
  print "$header_str";

  $fcontent_str = "  <DIV id='infocont'>";
  $fcontent_str .= $errmsg."\n";
  $fcontent_str .= "  </DIV>\n";
  $fcontent_str .= "  <DIV>\n";
  $fcontent_str .= "    <BUTTON type='submit' class='buttonsubmit' id='submitbutton' onClick='history.go(-1);'>\n";
  $fcontent_str .= "      <IMG class='img' src='../Pics/Icon/ok.gif' alt=''/>确认\n";
  $fcontent_str .= "    </BUTTON>\n";
  $fcontent_str .= "  </DIV>\n";
  print "$fcontent_str";

  $footer_str = <<< EOFOOTER
</BODY>
</HTML>
EOFOOTER;
  print "$footer_str";
?>