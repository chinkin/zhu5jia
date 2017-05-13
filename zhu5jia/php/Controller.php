<?PHP
  header('Content-Type:text/html; charset=utf-8');
  if (!empty($_GET['action'])) {
    $actpost = trim($_GET['action']);
    switch ($actpost) {
      case "uploadPortrait":
        $contclass = "User";
        $postarray['para'] = array('para' => array('id' => trim($_GET['id']), 'filename' => trim($_GET['filename'])));
        break;
      case "uploadHouseMedia":
        $contclass = "House";
        $postarray['para'] = array('para' => array('id' => trim($_GET['id']), 'isphoto' => trim($_GET['isphoto']), 'photono' => trim($_GET['photono']), 'filename' => trim($_GET['filename'])));
        break;
    }
  } else {
    $postarray = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], TRUE);
    $contclass = ucfirst(trim($postarray['contclass']));
    $actpost = trim($postarray['act']);
  }
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
//  $result['data']['class'] = $contclass;
//  $result['data']['act'] = $actpost;
//  error_log(json_encode($result)."\r\n", 3, '..\log\php-error.log');
  echo json_encode($result);
?>