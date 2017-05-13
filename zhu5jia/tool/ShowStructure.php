<?PHP
  session_id($_GET['sid']);
  session_start();

  $tablename = $_SESSION['TableName'];
  $fields = $_SESSION['Fields'];
  $columnno = 0;
  foreach ($fields[0] as $key => $val) {
    $columns[$columnno] = $key;
    $columnno++;
  }

  if (empty($tablename)) {
    echo "<SCRIPT language='JavaScript'>;alert('表名传递不正确!');window.opener=null;window.open('', '_self');window.close();</SCRIPT>";
    die();
  }

$header_str = <<< EOHEADER
<HTML>
<HEAD>
  <META http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <TITLE>表 $tablename
  </TITLE>
  <SCRIPT type="text/javascript">
    function jumptocre() {
      window.open("./CreateFields.php", "CreateFields");
    }
    function jumptomod(recno) {
      sURL = "./ModifyField.php?recno=" + recno.substr(2);
      window.open(sURL, "ModifyField");
    }
    function jumptodel(recno) {
      sURL = "./DeleteField.php?recno=" + recno.substr(2);
      window.open(sURL, "DeleteField");
    }
    function jumptoshwstru() {
      window.open("./ShowIndex.php", "ShowIndex");
    }
  </SCRIPT>
</HEAD>
<BODY>

EOHEADER;
  print "$header_str";

$theader_str = <<< EOTHEADER
  <TABLE align='left' border=1 cellpadding=5 cellspacing=0 style='table-layout: automatic;'>
    <TR>
      <DIV align='center'>表 $tablename 的结构
        <BUTTON type='button' style='width:20px; height:20px; cursor:pointer;' onclick='jumptocre();'>
          <IMG src='./create.gif' alt='新建列' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>
        </BUTTON>
      </DIV>
    </TR>

EOTHEADER;
  print "$theader_str";

  $fields_str = "    <TR valign='middle'>\n";
  $fields_str .= "      <TH align='middle'>&nbsp\n";
  $fields_str .= "      </TH>\n";
  $fields_str .= "      <TH align='middle'>&nbsp\n";
  $fields_str .= "      </TH>\n";
  for ($i = 0; $i < $columnno - 1; $i++) {
    $fields_str .= "      <TH align='middle'>\n";
    $fields_str .= "        <FONT size=3>".$columns[$i]."\n";
    $fields_str .= "        </FONT>\n";
    $fields_str .= "      </TH>\n";
  }
  $fields_str .= "    </TR>\n";
  print "$fields_str";

  for ($i = 0; $i < count($fields); $i++) {
    $records_str = "    <TR id='".$i."' valign='middle'>\n";
//    $records_str .= "      <FORM method='post'>\n";
    $records_str .= "      <TD>\n";
    $records_str .= "        <BUTTON type='button' id='er".$i."' style='width:20px; height:20px; cursor:pointer;' onclick='jumptomod(this.id);'>\n";
    $records_str .= "          <IMG src='./modify.gif' alt='更改该列' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>\n";
    $records_str .= "        </BUTTON>\n";
    $records_str .= "      </TD>\n";
    $records_str .= "      <TD>\n";
    $records_str .= "        <BUTTON type='button' id='dr".$i."' style='width:20px; height:20px; cursor:pointer;' onclick='jumptodel(this.id);'>\n";
    $records_str .= "          <IMG src='./eraser.gif' alt='删除该列' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>\n";
    $records_str .= "        </BUTTON>\n";
    $records_str .= "      </TD>\n";
    for ($j = 0; $j < $columnno - 1; $j++) {
      $records_str .= "      <TD align='left' nowrap>\n";
      if (!isset($fields[$i][$columns[$j]]) || $fields[$i][$columns[$j]] == "") {
        $records_str .= "&nbsp\n";
      } elseif (is_null($fields[$i][$columns[$j]])) {
        $records_str .= "        <FONT size=3>NULL\n";
        $records_str .= "        </FONT>\n";
      } else {
        $records_str .= "        <FONT size=3>".$fields[$i][$columns[$j]]."\n";
        $records_str .= "        </FONT>\n";
      }
      $records_str .= "      </TD>\n";
    }
//    $records_str .= "      </FORM>\n";
    $records_str .= "    </TR>\n";
    print "$records_str";
  }

$tfooter_str = <<< EOTFOOTER
  </TABLE>

EOTFOOTER;
  print "$tfooter_str";

$footer_str = <<< EOFOOTER
</BODY>
</HTML>
EOFOOTER;
  print "$footer_str";
?>