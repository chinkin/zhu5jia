<?PHP
  session_id($_GET['sid']);
  session_start();
  $tablename = $_SESSION['TableName'];
  $fields = $_SESSION['Fields'];

  if (empty($tablename)) {
    echo "<SCRIPT language='JavaScript'>;alert('表名传递不正确!');window.opener=null;window.open('', '_self');window.close();</SCRIPT>";
    die();
  }

  $compulsion = "00";
  for ($i = 0; $i < count($fields); $i++) {
    if ($fields[$i]['Null'] == 'NO' && (!isset($fields[$i]['Default']))) {
      $compulsion .= '1';
    } else {
      $compulsion .= '0';
    }
  }

  $header_str = <<< EOHEADER
<HTML>
<HEAD>
  <META http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <TITLE>表 $tablename
  </TITLE>
  <SCRIPT src='./InputCheck.js'>
  </SCRIPT>
</HEAD>
<BODY>

EOHEADER;
  print "$header_str";

  $formheader_str = <<< EOFORMHEADER
  <DIV align=center>请输入 $tablename 的纪录
  </DIV>
  <DIV align=center>(*为必须输入项目)
  </DIV>
  <FORM action='./InsertRec.php' method='post'>

EOFORMHEADER;
  print "$formheader_str";

  $formcontent_str = "    <INPUT type='hidden' name='table' value='".$tablename."'>\n";
  $formcontent_str .= "    <DIV style='width:100; padding:0 0 10px 0; float:left;'>";
  for ($i = 0; $i < count($fields); $i++) {
    if ($compulsion[$i + 2] == '1') {
      $formcontent_str .= $fields[$i]['Field']." *";
    } else {
      $formcontent_str .= $fields[$i]['Field'];
    }
    $formcontent_str .= "    </DIV>\n";
    $formcontent_str .= "    <DIV style='padding:0 0 10px 0; float:left;'>\n";
    if ($fields[$i]['Size'] <= 60) {
      $formcontent_str .= "      <INPUT type='text' name='".$fields[$i]['Field']."' maxlength=".$fields[$i]['Size']." size=".$fields[$i]['Size'].">\n";
    } else {
      $rows = ceil($fields[$i]['Size']/60);
      $formcontent_str .= "      <TEXTAREA name='".$fields[$i]['Field']."' rows=".$rows." cols=60></TEXTAREA>\n";
    }
    if ($i < count($fields) - 1) {
      $formcontent_str .= "    </DIV>\n";
      $formcontent_str .= "    <DIV style='clear:both;'></DIV>\n";
      $formcontent_str .= "    <DIV style='width:100; padding:0 0 10px 0; float:left;'>";
    }
  }
  $formcontent_str .= "    </DIV>\n";
  print "$formcontent_str";

  $compulsion .= '000';
  $formfooter_str = <<< EOFORMFOOTER
    <DIV style='padding:0 0 0 80px; clear:both;'>
      <INPUT type='submit' onClick='return mandInput(this.form, "$compulsion");' value='提交'>
      <INPUT type='reset' value='重置'>
      <INPUT type='button'  value='结束' onClick='window.opener = null; window.open("", "_self"); window.close();'>
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