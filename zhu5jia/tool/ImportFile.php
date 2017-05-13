<?PHP
  session_id($_GET['sid']);
  session_start();

  $tablename = $_SESSION['TableName'];

$html_str = <<< EOHEADER
<HTML>
<HEAD>
  <META http-equiv='Content-Type' content='text/html; charset=utf-8'>
  <TITLE>表 $tablename
  </TITLE>
  <SCRIPT type="text/javascript">
    var importfile = document.getElementById("importfile");
    var fullpath = document.getElementById("fullpath");
    importfile.onchange = function() {
      fullpath.value = getFullPath(this);
    }
    function getFullPath(obj){
      if (obj) {
        //ie
        if (window.navigator.userAgent.indexOf("MSIE") >= 1) {
          obj.select();
          return document.selection.createRange().text;
        }
        //firefox
        else if(window.navigator.userAgent.indexOf("Firefox") >=1 ) {
          if(obj.files) {
            return obj.files.item(0).getAsDataURL();
          }
          return obj.value;
        }
        return obj.value;
      }
    }
  </SCRIPT>
</HEAD>
<BODY>
  <FORM action=./ImportData.php method=post>
    <TABLE align=center border=0 cellpadding=5 cellspacing=0 width=300 bgcolor=#ffffcc>
      <TR align=middle>
        <TD width=20% align=right>
          <FONT size=2>表名
          </FONT>
        </TD>
        <TD width=80% align=left>
          <FONT size=2>
            <INPUT type='text' name='tablename' maxlength=100 size=30 value='$tablename' readonly>
          </FONT>
        </TD>
      </TR>
      <TR bgcolor=#ffcc00>
        <TD colspan=2>
          <DIV align=center>请指定import文件
          </DIV>
        </TD>
      </TR>
      <TR align=middle>
        <TD width=20% align=right>
          <FONT size=2>import文件
          </FONT>
        </TD>
        <TD width=80% align=left>
          <FONT size=2>
            <input type="file" name="importfile" id="importfile"/>
          </FONT>
        </TD>
      </TR>
      <TR align=middle>
        <TD width=20% align=right>
          <FONT size=2>路径
          </FONT>
        </TD>
        <TD width=80% align=left>
          <FONT size=2>
            <input type="text" name="fullpath" id="fullpath"/>
          </FONT>
        </TD>
      </TR>
      <TR align=middle>
        <TD colspan=2>
           <DIV align=center>
             <INPUT type='submit' name='submit' value='提交'>
             <INPUT type='reset' name='reset' value='重置'>
           </DIV>
        </TD>
      </TD>
    </TABLE>
  </FORM>
</BODY>
</HTML>

EOHEADER;
  print "$html_str";
?>