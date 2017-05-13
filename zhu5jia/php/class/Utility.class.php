<?php
/*
* Utility类
* author chinkin
* revision: 1
*/
class Utility {

/*回车处理
//二维以下
  static public function handleReturn($data) {
    if (empty($data) || is_array($data)) {
      return FALSE;
    }
    foreach ($data as $id => $value) {
      if (is_array($value)) {
        foreach ($value as $vid => $vdata) {
          if (is_string($vdata)) {
            $data[$id][$vid] = str_replace("\n", "\\n", $vdata);
    echo "alert('+".$data[$id][$vid]."+');";
          }
        }
      } else if (is_string($value)) {
        $data[$id] = str_replace("\n", "\\n", $value);
    echo "alert('+".$data[$id]."+');";
      }
    }
    die();
    return $data;
  }
*/

//数组->JSON、压缩、加密数据
  function encodeData($data, $compresslevel=1) {
    if (empty($data) || is_null($data)) {
      return FALSE;
    }
    $result = json_encode($data);
    if (!$result) {
      return FALSE;
    }
    if (!is_int($compresslevel) || $compresslevel > 9) {
      $compresslevel = -1;
    }
    $result = gzdeflate($result, $compresslevel);
//    $result = base64_encode($result);
    return $result;
  }

//解密、解压、JSON->数组数据
  function decodeData ($data, $assoc=TRUE) {
    if (empty($data) || is_null($data)) {
      return FALSE;
    }
//    $result = base64_decode($data);
    $result = gzinflate($result);
    if (!$result) {
      return FALSE;
    }
    if ($assoc !== FALSE) {
      $assoc = TRUE;
    }
    $result = json_decode($result, $assoc);
    return $result;
  }

  //加密函数
  function encrypt($string, $key) {
    for ($i = 0; $i < strlen($string); $i++) {
      for ($j = 0; $j < strlen($key); $j++) {
        $string[$i] = $string[$i]^$key[$j];
      }
    }
    return $string;
  }

  //解密函数
  function decrypt($string, $key) {
    for ($i = 0; $i < strlen($string); $i++) {
      for ($j = 0; $j < strlen($key); $j++) {
        $string[$i] = $key[$j]^$string[$i];
      }
    }
    return $string;
  }

//播放器
//$path: 文件路径
//$file: 文件名
//$width, $height: 播放窗口大小
  function playVideo($path, $file, $width, $height) {
    if (empty($path) || empty($file)) {
      return FALSE;
    }
    if (empty($width)) {
      $width = 600;
    }
    if (empty($height)) {
      $height = 500;
    }
    $string = "";
//MediaPlayer播放
    if (preg_match("/^.+\.(mid|avi|asf|asx|wmv|wma)$/i", $file)) {
      $string .= "<OBJECT classid=\'clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95\' type=\'application/x-oleobject\' width=".$width." height=".$height." align=\'middle\' standby=\'Loading Microsoft?Windows?Media Player components...\' id=\'MediaPlayer1\'>";
      $string .= "<PARAM name=\'transparentAtStart\' value=\'true\'>";
      $string .= "<PARAM name=\'transparentAtStop\' value=\'true\'>";
      $string .= "<PARAM name=\'AnimationAtStart\' value=\'true\'>";
      $string .= "<PARAM name=\'AutoStart\' value=\'true\'>";
      $string .= "<PARAM name=\'AutoRewind\' value=\'true\'>";
      $string .= "<PARAM name=\'DisplaySize\' value=\'0\'>";
      $string .= "<PARAM name=\'AutoSize\' value=\'false\'>";
      $string .= "<PARAM name=\'ShowDisplay\' value=\'false\'>";
      $string .= "<PARAM name=\'ShowStatusBar\' value=\'ture\'>";
      $string .= "<PARAM name=\'ShowControls\' value=\'ture\'>";
      $string .= "<PARAM name=\'FileName\' value=\'".$path.$file."\'>";
      $string .= "<PARAM name=\'Volume\' value=\'0\'>";
      $string .= "<EMBED src=\'".$path.$file."\' width=".$width." height=".$height." autostart=\'true\' align=\'middle\' transparentatstart=\'true\' transparentatstop=\'true\' animationatstart=\'ture\' autorewind=\'true\' displaysize=\'0\' autosize=\'false\' showdisplay=\'False\' showstatusbar=\'-1\' showcontrols=\'ture\' filename=\'".$path.$file."\' volume=\'0\'>";
      $string .= "</OBJECT>";
    }

//RealPlayer播放
    if (preg_match("/^.+\.(ra|mp3|rm|ram)$/i", $file)) {
      $string .= "<OBJECT id=\'video\' classid=\'clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\' width=\'".$width."\' height=\'".$height."\' align=\'center\'>";
      $string .= "<PARAM name=\'_ExtentX\' value=\'9260\'>";
      $string .= "<PARAM name=\'_ExtentY\' value=\'6350\'>";
      $string .= "<PARAM name=\'AUTOSTART\' value=\'-1\'>";
      $string .= "<PARAM name=\'SHUFFLE\' value=\'0\'>";
      $string .= "<PARAM name=\'PREFETCH\' value=\'0\'>";
      $string .= "<PARAM name=\'NOLABELS\' value=\'0\'>";
      $string .= "<PARAM name=\'SRC\' value=\'".$path.$file."\'>";
      $string .= "<PARAM name=\'CONTROLS\' value=\'ImageWindow\'>";
      $string .= "<PARAM name=\'CONSOLE\' value=\'Clip1\'>";
      $string .= "<PARAM name=\'LOOP\' value=\'0\'>";
      $string .= "<PARAM name=\'NUMLOOP\' value=\'0\'>";
      $string .= "<PARAM name=\'CENTER\' value=\'0\'>";
      $string .= "<PARAM name=\'MAINTAINASPECT\' value=\'0\'>";
      $string .= "<PARAM name=\'BACKGROUNDCOLOR\' value=\'#000000\'>";
      $string .= "<EMBED src=\'".$path.$file."\' type=\'audio/x-pn-realaudio-plugin\' console=\'Clip1\' controls=\'ImageWindow\' width=\'".$width."\' height=\'".$height."\' autostart=\'false\'>";
      $string .= "</OBJECT>";
    }

//flashplayer播放
    if (preg_match("/^.+\.swf$/i",$file)) {
      $string .= "<OBJECT classid=\'clsid:D27CDB6E-AE6D-11CF-96B8-444553540000\' id=\'obj1\' codebase=\'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0\' border=\'0\' width=\'".$width."\' height=\'".$height."\'>";
      $string .= "<PARAM name=\'movie\' value=\'".$path.$file."\'>";
      $string .= "<PARAM name=\'quality\' value=\'high\'>";
      $string .= "<PARAM name=\'wmode\' value=\'transparent\'>";
      $string .= "<PARAM name=\'menu\' value=\'false\'>";
      $string .= "<EMBED src=\'".$path.$file."\' pluginspage=\'http://www.macromedia.com/go/getflashplayer\' type=\'application/x-shockwave-flash\' name=\'obj1\' width=\'".$width."\' height=\'".$height."\' quality=\'High\' wmode=\'transparent\'>";
      $string .= "</OBJECT>"; 
    }

//HTML5播放
    if (preg_match("/^.+\.mp4$/i",$file)) {
      $string .= "<VIDEO id=\'movie\' preload controls loop poster=\'poster.png\' width=\'".$width."\' height=\'".$height."\'>";
      $string .= "<SOURCE src=\'".$path.$file."\' type=\'video/mp4\' />";
      $string .= "</VIDEO>";
    }
    if (preg_match("/^.+\.ogg$/i",$file)) {
      $string .= "<VIDEO id=\'movie\' preload controls loop poster=\'poster.png\' width=\'".$width."\' height=\'".$height."\'>";
      $string .= "<SOURCE src=\'".$path.$file."\' type=\'video/ogg\' />";
      $string .= "</VIDEO>";
    }
    if (preg_match("/^.+\.webm$/i",$file)) {
      $string .= "<VIDEO id=\'movie\' preload controls loop poster=\'poster.png\' width=\'".$width."\' height=\'".$height."\'>";
      $string .= "<SOURCE src=\'".$path.$file."\' type=\'video/web\' />";
      $string .= "</VIDEO>";
    }
    return $string;
  }

//上传文件
//$path: 文件路径
//$file: 文件名数组
  function uploadFile($path, $filename) {
    $result['success'] = FALSE;
    if (empty($path)) {
      $result['message'] = "上传路径没有指定!";
      return result;
    }
    if(!empty($_FILES['file'])){
      $file_name = $_FILES['file']['name'];
//      $file_size =$_FILES['file']['size'];
      $file_tmp =$_FILES['file']['tmp_name'];
//      $file_type=$_FILES['file']['type'];   
      $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
      $file_name_no_ext = substr($file_name, 0, strripos($file_name, $file_ext) - 1);
      $upload_path = $path;
      $uploadtime = date('Y-m-d H:i:s');
      $upload_file = md5($file_name_no_ext.$uploadtime).".".$file_ext;
      if ($filename != "") {
        unlink($upload_path.$filename);
      }
      if (file_exists($upload_path) && $_FILES['file']['error'] == 0) {
//        if (!move_uploaded_file($_FILES['file']['tmp_name'], iconv("UTF-8", "gb2312", $upload_path.$upload_file))) {
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_path.$upload_file)) {
          $_FILES['file']['error'] = 8;
        }
      }
      switch ($_FILES['file']['error']) {
        case 0:
          $result['success'] = TRUE;
          $result['uploadfile'] = $upload_file;
          break;
        case 1:
          $result['message'] = "文件大小超过限制!";
          break;
        case 2:
          $result['message'] = "文件大小超过FORM限制!";
          break;
        case 3:
          $result['message'] = "文件部分被上传!";
          break;
        case 4:
          $result['message'] = "没有文件被上传!";
          break;
        case 8:
          $result['message'] = "文件无法移动!";
          break;
        default:
          $result['message'] = "上传文件失败!";
      }
    } else {
      $result['message'] = "没有上传文件!";
    }
    return $result;
  }

//删除文件
//$path: 文件路径
//$file: 文件名数组
  function deleteFile($path, $file) {
    $result['success'] = FALSE;
    if (empty($path) || !is_array($file)) {
      $result['message'] = "文件路径没有指定!";
      return result;
    }
    foreach ($file as $id => $filename) {
      $removeresult = unlink($path.$filename);
      if (!$removeresult) {
        $result['message'] = "删除文件".$filename."出错!";
      }
    }
    $result['success'] = TRUE;
    return $result;
  }

//-------------------------------------
//获得访客浏览器类型
  function getBrowser() {
    $browser = $_SERVER['HTTP_USER_AGENT'];
    if (stripos($browser, "Firefox/") > 0) {
      preg_match("/Firefox\/([^;)]+)+/i", $browser, $version);
      $result = "Firefox".'('.$version[1].')';
    } elseif (stripos($browser, "Maxthon") > 0) {
      preg_match("/Maxthon\/([\d\.]+)/", $browser, $version);
      $result = "傲游".'('.$version[1].')';
    } elseif (stripos($browser, "MSIE") > 0) {
      preg_match("/MSIE\s+([^;)]+)+/i", $browser, $version);
      $result = "IE".'('.$version[1].')';
    } elseif (stripos($browser, "OPR") > 0) {
      preg_match("/OPR\/([\d\.]+)/", $browser, $version);
      $result = "Opera".'('.$version[1].')';
    } elseif(stripos($browser, "Edge") > 0) {  //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
      preg_match("/Edge\/([\d\.]+)/", $browser, $version);
      $result = "Edge".'('.$version[1].')';
    } elseif (stripos($browser, "Chrome") > 0) {
      preg_match("/Chrome\/([\d\.]+)/", $browser, $version);
      $result = "Chrome".'('.$version[1].')';
    } elseif(stripos($browser,'rv:')>0 && stripos($browser,'Gecko')>0){
      preg_match("/rv:([\d\.]+)/", $browser, $version);
      $result = "IE".'('.$version[1].')';
    } else {
      $result = "未知浏览器";
    }
    return $result;
  }

//获得访客浏览器语言
  function getLanguage() {
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
      $result = substr($language, 0, 5);
      if (preg_match("/zh-cn/i", $language)) {
        $result = "简体中文";
      } elseif (preg_match("/zh/i", $language)) {
        $result = "繁体中文";
      } else {
        $result = "English";
      }
    } else {
      $result = "未知语言";
    }
    return $result;
  }

//获取访客操作系统
  function getOS() {
    $os = $_SERVER['HTTP_USER_AGENT'];
    $result = "";

    if (preg_match("/win/i", $os) && strpos($os, "95")) {
      $result = "Windows 95";
    } elseif (preg_match("/win 9x/i", $os) && strpos($os, "4.90")) {
      $result = "Windows ME";
    } elseif (preg_match("/win/i", $os) && preg_match("/98/i", $os)) {
      $result = "Windows 98";
    } elseif (preg_match("/win/i", $os) && preg_match("/nt 6.0/i", $os)) {
      $result = "Windows Vista";
    } elseif (preg_match("/win/i", $os) && preg_match("/nt 6.1/i", $os)) {
      $result = "Windows 7";
    } elseif (preg_match("/win/i", $os) && preg_match("/nt 6.2/i", $os)) {
      $result = "Windows 8";
    } elseif(preg_match("/win/i", $os) && preg_match("/nt 10.0/i", $os)) {
      $result = "Windows 10";
    } elseif (preg_match("/win/i", $os) && preg_match("/nt 5.1/i", $os)) {
      $result = "Windows XP";
    } elseif (preg_match("/win/i", $os) && preg_match("/nt 5/i", $os)) {
      $result = "Windows 2000";
    } elseif (preg_match("/win/i", $os) && preg_match("/nt/i", $os)) {
      $result = "Windows NT";
    } elseif (preg_match("/win/i", $os) && preg_match("/32/i", $os)) {
      $result = "Windows 32";
    } elseif (preg_match("/linux/i", $os)) {
      $result = "Linux";
    } elseif (preg_match("/unix/i", $os)) {
      $result = "Unix";
    } elseif (preg_match("/sun/i", $os) && preg_match("/os/i", $os)) {
      $result = "SunOS";
    } elseif (preg_match("/ibm/i", $os) && preg_match("/os/i", $os)) {
      $result = "IBM OS/2";
    } elseif (preg_match("/Mac/i", $os) && preg_match("/PC/i", $os)) {
      $result = "Macintosh";
    } elseif (preg_match("/PowerPC/i", $os)) {
      $result = "PowerPC";
    } elseif (preg_match("/AIX/i", $os)) {
      $result = "AIX";
    } elseif (preg_match("/HPUX/i", $os)) {
      $result = "HPUX";
    } elseif (preg_match("/NetBSD/i", $os)) {
      $result = "NetBSD";
    } elseif (preg_match("/BSD/i", $os)) {
      $result = "BSD";
    } elseif (preg_match("/OSF1/i", $os)) {
      $result = "OSF1";
    } elseif (preg_match("/IRIX/i", $os)) {
      $result = "IRIX";
    } elseif (preg_match("/FreeBSD/i", $os)) {
      $result = "FreeBSD";
    } elseif (preg_match("/teleport/i", $os)) {
      $result = "teleport";
    } elseif (preg_match("/flashget/i", $os)) {
      $result = "flashget";
    } elseif (preg_match("/webzip/i", $os)) {
      $result = "webzip";
    } elseif (preg_match("/offline/i", $os)) {
      $result = "offline";
    } else {
      $result = "未知操作系统";
    }
    return $result;
  }
  
//获得访客真实ip
  function getIP() {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {   
      $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {                  //获取代理ip
      $ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    if (isset($ip) && isset($ips)) {
      array_unshift($ips, $ip); 
    }

    if (isset($ips)) {
      for($i = 0; $i < count($ips); $i++){   
        if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {     //排除局域网ip
          $ip = $ips[$i];
          break;
        }
      }
    }

    $result = empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR']; 
    if ($result == "127.0.0.1") {                                       //获得本地真实IP
      return $this->getOnlineIP();   
    } else {
      return $result; 
    }
  }

//获得本地真实IP
  function getOnlineIP() {
    $mip = file_get_contents("http://city.ip138.com/city0.asp");
    if (isset($mip) && !empty($mip)) {
      preg_match("/\[.*\]/", $mip, $sip);
      $p = array("/\[/","/\]/");
      return preg_replace($p, "", $sip[0]);
    } else {
      return "";
    }
  }
  
//根据ip获得访客所在地地名
  function getAddress($ip="") {
    if (empty($ip)) {
      $ip = $this->getIP();
    }
    if (empty($ip)) {
      return "";
    }
    $result = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?ip=".$ip);   //根据新浪api接口获取
    if ($result) {
      $characterset = iconv("gbk", "utf-8", $result);
      preg_match_all("/[\x{4e00}-\x{9fa5}]+/u", $characterset, $result);
      return $result;   //返回一个二维数组
    } else {
      return "";
    }
  }
}
?>