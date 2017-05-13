<?PHP
  session_start();
  function __autoload($classname) {
      require_once '../php/class/'.$classname.'.class.php';
  }

  $tablename = trim($_POST['tablename']);
  if (empty($tablename)) {
    if (!empty($_SESSION['TableName'])) {
      $tablename = $_SESSION['TableName'];
    } else {
      echo "<META http-equiv='refresh' content='0; url=./showdata.html'>";
      die();
    }
  }

  $mysql = new PHPMySQL();
  $query = "DESC ".$tablename;
  $result = $mysql->runSQL($query);
  $i = 0;
  if ($result->num_rows != 0) {
    while ($fieldinfo = $result->fetch_assoc()) {
      $fieldinfo['Size'] = substr($fieldinfo['Type'], strpos($fieldinfo['Type'], '(') + 1, strpos($fieldinfo['Type'], ')') - strpos($fieldinfo['Type'], '(') - 1);
      $fields[$i] = $fieldinfo;
      $i++;
    }
  }
  $fieldno = $i;

  $query = "SELECT * FROM $tablename ORDER BY ";
  for ($i = 0; $i < $fieldno; $i++) {
  	if ($fields[$i]['Key'] == "PRI") {
  	  $query .= $fields[$i]['Field'].", ";
  	  $ordflg = true;
  	}
  }
  if ($ordflg) {
    $query = substr($query, 0, strlen($query) - 2);
  } else {
    $query = substr($query, 0, strlen($query) - 10);
  }
  $result = $mysql->runSQL($query);
  $records = array();
  $recordno = $result->num_rows;
  if ($result->num_rows != 0) {
    while ($data = $result->fetch_assoc()) {
      array_push($records, $data);
    }
  }

  $_SESSION['TableName'] = $tablename;
  $_SESSION['Fields'] = $fields;
  $_SESSION['Records'] = $records;
  $sid = session_id();

$header_str = <<< EOHEADER
<HTML>
<HEAD>
  <META http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <TITLE>表 $tablename
  </TITLE>
  <SCRIPT type="text/javascript">
    function jumptocre() {
      sURL = "./CreateData.php?sid=$sid";
      window.open(sURL, "CreateRecords");
    }
    function jumptoedit(recno) {
      sURL = "./UpdateData.php?sid=$sid&recno=" + recno;
      window.open(sURL, "EditRecord");
    }
    function jumptodel(recno) {
      sURL = "./DeleteData.php?sid=$sid&recno=" + recno;
      window.open(sURL, "DeleteRecord");
    }
    function jumptoshwstru() {
      window.open("./ShowStructure.php?sid=$sid", "ShowStructure");
    }
    function jumptoiptfile() {
      window.open("./ImportFile.php?sid=$sid", "ImportFile");
    }
  </SCRIPT>
</HEAD>
<BODY>

EOHEADER;
  print "$header_str";

$theader_str = <<< EOTHEADER
  <TABLE align='left' border=1 cellpadding=5 cellspacing=0 style='table-layout: automatic;'>
    <TR>
      <DIV align='center'>表 $tablename 的数据 (共 $recordno 条记录)
        <BUTTON type='button' style='width:20px; height:20px; cursor:pointer;' onclick='jumptocre();'>
          <IMG src='./add.png' alt='新建纪录' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>
        </BUTTON>
        <BUTTON type='button' style='width:20px; height:20px; cursor:pointer;' onclick='jumptoshwstru();'>
          <IMG src='./list.png' alt='表结构' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>
        </BUTTON>
        <BUTTON type='button' style='width:20px; height:20px; cursor:pointer;' onclick='jumptoiptfile();'>
          <IMG src='./upload.png' alt='上载数据' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>
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
  for ($i = 0; $i < $fieldno; $i++) {
    $fields_str .= "      <TH width=".$fields[$i]['Size']." align='middle'>\n";
    $fields_str .= "        <FONT size=3>".$fields[$i]['Field']."\n";
    $fields_str .= "        </FONT>\n";
    $fields_str .= "      </TH>\n";
  }
  $fields_str .= "    </TR>\n";
  print "$fields_str";

  for ($i = 0; $i < $recordno; $i++) {
    $records_str = "    <TR id='".$i."' valign='middle'>\n";
//    $records_str .= "      <FORM method='post'>\n";
    $records_str .= "      <TD>\n";
    $records_str .= "        <BUTTON type='button' style='width:20px; height:20px; cursor:pointer;' onclick='jumptoedit(this.parentNode.parentNode.id);'>\n";
    $records_str .= "          <IMG src='./pencil.png' alt='更改该纪录' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>\n";
    $records_str .= "        </BUTTON>\n";
    $records_str .= "      </TD>\n";
    $records_str .= "      <TD>\n";
    $records_str .= "        <BUTTON type='button' style='width:20px; height:20px; cursor:pointer;' onclick='jumptodel(this.parentNode.parentNode.id);'>\n";
    $records_str .= "          <IMG src='./multiply.png' alt='删除该纪录' style='width:16px; height:14px; margin:-2px 0 0 -3px;'/>\n";
    $records_str .= "        </BUTTON>\n";
    $records_str .= "      </TD>\n";
    for ($j = 0; $j < $fieldno; $j++) {
      $records_str .= "      <TD align='left' nowrap>\n";
      if (!isset($records[$i][$fields[$j]['Field']]) || $records[$i][$fields[$j]['Field']] == "") {
        $records_str .= "&nbsp\n";
      } else {
        $records_str .= "        <FONT size=3>".$records[$i][$fields[$j]['Field']]."\n";
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