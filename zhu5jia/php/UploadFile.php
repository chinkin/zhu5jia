<?PHP
  $result['success'] = FALSE;
  if(!empty($_FILES['file'])){
    $action = $_GET['action'];
    $file_name = $_FILES['file']['name'];
    $file_size =$_FILES['file']['size'];
    $file_tmp =$_FILES['file']['tmp_name'];
    $file_type=$_FILES['file']['type'];   
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $file_name_no_ext = substr($file_name, 0, strripos($file_name, $file_ext) - 1);
    $upload_path = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR ."temporary". DIRECTORY_SEPARATOR;
    $upload_file =  $upload_path.$file_name;
/*
    $extensions = array("jpeg","jpg","png");        
    if(in_array($file_ext,$extensions )=== false){
      $errors[]="image extension not allowed, please choose a JPEG or PNG file.";
    }
    if($file_size > 2097152){
      $errors[]='File size cannot exceed 2 MB';
    }               
*/
    if (file_exists($upload_path) && $_FILES['file']['error'] == 0) {
      if (!move_uploaded_file($_FILES['file']['tmp_name'], iconv("UTF-8", "gb2312",  $upload_file))) {
        $_FILES['file']['error'] = 8;
      }
    }
    switch ($_FILES['file']['error']) {
      case 0:
        $result['success'] = TRUE;
        $result['filename'] = $file_name;
        $result['action'] = $action;
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
  echo json_encode($result);
?>