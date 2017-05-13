<?PHP
$filename = $_GET['filename'];
$html_str = <<< EOHEADER
<HTML>
<HEAD>
  <META http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <TITLE>表 $tablename
  </TITLE>
</HEAD>
<BODY>
  <A href='$filename'>下载文件</A>
</BODY>

EOHEADER;
  print "$html_str";
?>