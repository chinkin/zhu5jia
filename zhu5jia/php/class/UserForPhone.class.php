<?php
/*
* 用户类
* author chinkin
* revision: 1
*/
class UserForPhone {
  protected $userinfo = NULL;
  protected $userauth = NULL;
  private $usertable = "user";
  private $userauthtable = "userauth";

//用户登录
//$para: 数组
//username/password: 用户名/密码
  static public function logon($para) {
    if (!is_array($para) || !isset($para['username']) || empty($para['username']) || !isset($para['password']) || empty($para['password'])) {
      $result['success'] = FALSE;
      $result['message'] = "用户名或密码未输入";
      return $result;
    }
    $myself = new self();
    $result = $myself->getUser(trim($para['username']), "*", "username='".trim($para['username'])."'");
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "不能查询用户";
      return $result;
    }
    if ($result['success'] != TRUE) {
      return $result;
    }
    if ($para['password'] != $result['data']['password']) {
      $result['success'] = FALSE;
      $result['message'] = "用户名或密码不正确";
      unset($result['data']);
    } else {
      unset($result['data']['password']);
    }
    return $result;

    $updatepara['id'] = $result['data']['id'];
    $updatepara['lastlogon'] = "";
    $myself->updateUser($updatepara);
  }

  static public function getContent($para) {
    if (!is_array($para) || !isset($para['usertype']) || empty($para['usertype'])) {
      $result['success'] = FALSE;
      $result['message'] = "用户类型未输入";
      return $result;
    }
    $fields = "*";
    if ($para['usertype'] == 1) {
      $fields = array("id", "name", "username", "type");
    }
    if ($para['usertype'] == 2) {
      $fields = array("id", "name", "username", "gender", "phone", "mobile", "email", "type");
    }
    $myself = new self();
    $result = $myself->getUser("*", $fields, "");
    return $result;
  }

//获取用户信息
//$username: 用户名，*为所有用户
//$fields: 项目数组
  function getUser($username="*", $fields="*", $condition="") {
    if (empty($username)) {
      $result['success'] = FALSE;
      $result['message'] = "用户名未输入";
      return $result;
    }
    $mysql = new PHPMySQL();
    if ($username == "*") {
      $sqlresult = $mysql->selectRows($this->usertable, $fields, $condition);
    } else {
      if ($condition == "") {
        $condition = "username='".$username."'";
      } else {
        $condition = "username='".$username."' AND ".$condition;
      }
      $sqlresult = $mysql->selectRows($this->usertable, $fields, $condition);
    }
    $mysql = NULL;
    if (!$sqlresult) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询用户";
      return $result;
    }
    if ($sqlresult->num_rows == 0 && $username != "*") {
      $result['success'] = FALSE;
      $result['message'] = "用户信息不存在";
      return $result;
    }
    if ($username == "*") {
      $result['success'] = TRUE;
      $result['data'] = array();
      while ($userinfo = $sqlresult->fetch_assoc()) {
        if ($userinfo['type'] >= 8) {
          continue;
        }
        unset($userinfo['password']);
        array_push($result['data'], $userinfo);
      }
    } else {
      if ($sqlresult->num_rows != 1) {
        $result['success'] = FALSE;
        $result['message'] = "用户信息异常";
        return $result;
      }
      $result['success'] = TRUE;
      $result['data'] = $sqlresult->fetch_assoc();
    }
    return $result;
  }

//新建/更新用户信息
//$para: 数组
  function updateUser($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "无更新内容";
      return $result;
    }
    $isnew = FALSE;
    if ((!isset($para['id']) || $para['id'] == "") && isset($para['username']) && $para['username'] != "") {
      $isnew = TRUE;
    }
    if ($isnew) {
      $user['username'] = $para['username'];
      $user['name'] = $para['name'];
      $user['createtime'] = date('Y-m-d H:i:s');
      $user['edittime'] = $user['createtime'];
      $user['id'] = md5($user['name'].$user['createtime']);
    } else {
      if (!isset($para['id']) || $para['id'] == "") {
        $result['success'] = FALSE;
        $result['message'] = "用户未指定";
        return $result;
      }
      if (isset($para['lastlogon'])) {
        $user['lastlogon'] = date('Y-m-d H:i:s');
      } else {
        $user['edittime'] = date('Y-m-d H:i:s');
      }
    }
    if (isset($para['gender'])) {
      $user['gender'] = $para['gender'];
    }
    if (isset($para['type'])) {
      $user['type'] = $para['type'];
    }
    if (isset($para['password'])) {
      $user['password'] = $para['password'];
    } 
    if (isset($para['mobile'])) {
      $user['mobile'] = $para['mobile'];
    } else {
      $user['mobile'] = "";
    }
    if (isset($para['phone'])) {
      $user['phone'] = $para['phone'];
    } else {
      $user['phone'] = "";
    }
    if (isset($para['email'])) {
      $user['email'] = $para['email'];
    } else {
      $user['email'] = "";
    }
    $mysql = new PHPMySQL();
    if ($isnew) {
      $sqlresult = $mysql->insertRow($this->usertable, $user);
    } else {
      $sqlresult = $mysql->updateRows($this->usertable, $user, "id='".$para['id']."'");
    }
    if (!$sqlresult) {
      $result['success'] = FALSE;
      if ($isnew) {
        $result['message'] = "无法新建用户";
      } else {
        $result['message'] = "无法更新用户";
      }
      return $result;
    }
    $result['success'] = TRUE;
    return $result;
  }

//删除用户
//$id: 用户ID
  function deleteUser($id) {
    if (empty($id)) {
      $result['success'] = FALSE;
      $result['message'] = "用户未指定";
      return $result;
    }
    $mysql = new PHPMySQL();
    $sqlresult = $mysql->deleteRows($this->usertable, "id='".$id."'");
    if ($sqlresult === FALSE) {
      $result['success'] = FALSE;
      $result['message'] = "无法删除用户";
      return FALSE;
    }
    $result['success'] = TRUE;
    return $result;
  }

//获取用户权限
//$username: 用户名
  function getAuthorization($username="") {
    if (empty($username)) {
    	if (is_null($this->userinfo)) {
        return FALSE;
      } elseif (!is_null($this->userauth)) {
        return $this->userauth;
      }
    }
    if (!empty($username) && !is_null($this->userinfo) && $username == $this->userinfo['username'] && !is_null($this->userauth)) {
      return $this->userauth;
    } else {
      $this->userauth = NULL;
    }
    if (is_null($this->userinfo) || (!is_null($this->userinfo) && $username != $this->userinfo['username'])) {
      $this->getUser($username);
      if (!$this->userinfo) {
        return FALSE;
      }
    }
    $mysql =  new PHPMySQL();
    $result = $mysql->selectRows($this->userauthtable, "*", "user='".$this->userinfo['userid']."'", "recno");
    $mysql = NULL;
    if (!$result) {
      echo "alert('用户权限情报出错，请联系系统管理员!');";
      return FALSE;
    }
    if ($result->num_rows < 1) {
      echo "alert('用户权限不存在!');";
      $mysql = NULL;
    }

    $authcount = 0;
    while ($userauthinfo = $result->fetch_row()) {
      for ($i = 2; $i <= 11; $i++ ) {
        if ($userauthinfo[$i] == "a0000000") {
          $this->userauth[0] = $userauthinfo[$i];
          $authcount = 1;
          break;
        }
        if (empty($userauthinfo[$i])) {
          continue;
        } else {
          $this->userauth[$authcount] = $userauthinfo[$i];
          $authcount++;
        }
      }
      if ($this->userauth[0] == "a0000000") {
        break;
      }
    }
    if ($authcount == 0) {
      echo "alert('用户没有任何权限!');";
      return FALSE;
    }
    array_unique($this->userauth);
    $_SESSION['Userauth'] = $this->userauth;
    return $this->userauth;
  }

//获取功能和权限
  function getFunction() {
    if (is_null($this->userinfo)) {
      return FALSE;
    } elseif (is_null($this->userauth)) {
      if (!$this->getAuthorization()) {
        return FALSE;
      }
    }
    $tables = "component, funcgroup";
    $fields = array("componentid", "componentname", "funcgroupid", "funcgroupname", "phpfilename");
    $where = "componentid = component";
    $orderby = "componentid, funcgroupid";
    if ($this->userauth[0] != "a0000000") {
      $tables .=  ", function, authorization";
      $where .= " AND funcgroupid=funcgroup AND functionid=function AND authid IN (";
      while ($auth = each($this->userauth)) {
        $where .= " '".$auth['value']."', ";
      }
      $where = substr($where, 0, strlen($where) - 2)." )";
    }
    $mysql =  new PHPMySQL();
    $result = $mysql->selectRows($tables, $fields, $where, $orderby);
    $mysql = NULL;
    if (!$result) {
      echo "alert('无法打开功能表，请联系系统管理员!');";
      return FALSE;
    }
    if ($result->num_rows < 1) {
      echo "alert('用户没有可执行的功能!');";
      return FALSE;
    }

    while ($funcinfo = $result->fetch_assoc()) {
      $component[$funcinfo['componentid']] = $funcinfo['componentname'];
      $funcgroup[$funcinfo['componentid']][$funcinfo['funcgroupid']]['funcgroupname'] =  $funcinfo['funcgroupname'];
      $funcgroup[$funcinfo['componentid']][$funcinfo['funcgroupid']]['phpname'] =  $funcinfo['phpfilename'];
    }
    array_unique($component);
    array_unique($funcgroup);
    $_SESSION['Component'] = $component;
    $_SESSION['Funcgroup'] = $funcgroup;
    return TRUE;
  }
}
?>