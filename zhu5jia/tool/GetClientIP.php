<?PHP
  header('Content-Type:text/html; charset=utf-8');

  function GetClientIP(){
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
      $clientip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $clientip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
      $clientip = $_SERVER["REMOTE_ADDR"];
    } else {
      $clientip = "無法取得IP位址！";
    }
    return $clientip;
  }
  echo GetClientIP();
?>