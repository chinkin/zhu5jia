<?PHP
  session_id($_GET['sid']);
  session_start();
  $tablename = $_SESSION['TableName'];
  $fields = $_SESSION['Fields'];
  $records = $_SESSION['Records'];
  $oldrecno = $_SESSION['RecNo'];

  if (empty($tablename)) {
    echo "<SCRIPT language='JavaScript'>;alert('表名传递不正确!');window.opener=null;window.open('', '_self');window.close();</SCRIPT>";
    die();
  }

  $recno = trim($_GET['recno']);
  if (!isset($recno)) {
    if (!isset($oldrecno)) {
      echo "<SCRIPT language='JavaScript'>;alert('记录号传递出错!');window.opener=null;window.open('', '_self');window.close();</SCRIPT>";
      die();
    } else {
      $recno = $oldrecno;
    }
  } else {
    $_SESSION['RecNo'] = $recno;
  }

  $j = 0;
  for ($i = 0; $i < count($fields); $i++) {
    if ($fields[$i]['Key'] == "PRI") {
      $keys[$j]['Field'] = $fields[$i]['Field'];
      $keys[$j]['Value'] = $records[$recno][$fields[$i]['Field']];
      $j++;
    }
  }

  $_SESSION['Keys'] = $keys;

  $header_str = <<< EOHEADER
<HTML>
<HEAD>
  <META http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <TITLE>表 $tablename
  </TITLE>
</HEAD>
<BODY>

EOHEADER;
  print "$header_str";

  $formheader_str = <<< EOFORMHEADER
  <DIV align=center>请确认要删除的纪录
  </DIV>
  <FORM action='./DeleteRec.php' method='post'>

EOFORMHEADER;
  print "$formheader_str";

  $formcontent_str = "    <INPUT type='hidden' name='table' value='".$tablename."'>\n";
  $formcontent_str .= "    <DIV style='width:100; padding:0 0 10px 0; float:left;'>";
  for ($i = 0; $i < count($fields); $i++) {
    $formcontent_str .= $fields[$i]['Field'];
    $formcontent_str .= "    </DIV>\n";
    if ($fields[$i]['Size'] <= 60) {
      $formcontent_str .= "    <DIV width=600 style='padding:0 0 10px 0; float:left; background-color:#F0F0F0;'>";
      $formcontent_str .= $records[$recno][$fields[$i]['Field']];
   } else {
      $formcontent_str .= "    <DIV width=600 style='padding:0 0 10px 0; float:left; background-color:#F0F0F0;'>";
      $formcontent_str .= $records[$recno][$fields[$i]['Field']];
    }
    if ($i < count($fields) - 1) {
      $formcontent_str .= "    </DIV>\n";
      $formcontent_str .= "    <DIV style='clear:both;'></DIV>\n";
      $formcontent_str .= "    <DIV style='width:100; padding:0 0 10px 0; float:left;'>";
    }
  }
  $formcontent_str .= "    </DIV>\n";
  print "$formcontent_str";

  $formfooter_str = <<< EOFORMFOOTER
    <DIV style='padding:0 0 0 80px; clear:both;'>
      <INPUT type='submit' value='确认'>
      <INPUT type='button' value='关闭' onClick='window.opener = null; window.open("", "_self"); window.close();'>
    </DIV>
  </FORM>

EOFORMFOOTER;
  print "$formfooter_str";

  $footer_str = <<< EOFOOTER
</BODY>
</HTML>
EOFOOTER;
  print "$footer_str";
?>