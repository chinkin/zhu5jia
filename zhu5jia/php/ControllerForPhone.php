<?PHP
  header('Content-Type:text/html; charset=utf-8');
  $postarray = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], TRUE);
  $contclass = ucfirst(trim($postarray['contclass']))."ForPhone";
  $actpost = trim($postarray['act']);
//echo "alert('CLASS: ".$contclass."/ ACT: ".$actpost."');";

//自动加载 class 文件
  function __autoload($classname) {
      require_once './Class/'.$classname.'.class.php';
  }

  $result = "";
  if (!empty($contclass) || !empty($actpost)) {
    if (isset($postarray['para'])) {
      $result = call_user_func_array(array($contclass, $actpost), $postarray['para']);
    } else {
      $result = call_user_func(array($contclass, $actpost));
    }
  } else {
    $result['success'] = false;
    $result['message'] = "类或操作未指定";
  }
//  $result['success'] = true;
//  $result['data']['name'] = $actpost;
//  error_log(json_encode($result)."\r\n", 3, '..\Log\php-error.log');
  echo json_encode($result);
?>