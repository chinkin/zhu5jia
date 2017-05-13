<?php
/*
* 用户类/账户类
* author chinkin
* revision: 1
*/
class User {
//用户关联表
  private $usertable = "users";
  private $verificationtable = "user_verifications";
  private $reviewtable = "user_reviews";
  private $referencetable = "user_references";
  private $contacttable = "user_contacts";
//账户关联表
  private $accounttable = "accounts";
  private $paymenttable = "account_payments";
  private $logontable = "account_logon";
//通知短信关联表
  private $notificationtable = "notifications";
  private $mynotificationtable = "notification_users";
  private $messagetable = "messages";
  private $contenttable = "message_contents";

//用户登陆API
//$para: 登陆信息数组
//返回$result['users']、$result[table][n]、$result['token']、$result['location']
  static public function logon($para) {
    if (!is_array($para) || 
        ((!isset($para['id']) || empty($para['id']) || !isset($para['token']) || empty($para['token'])) && 
         (!isset($para['password']) || empty($para['password']) || 
          ((!isset($para['email']) || empty($para['email'])) && 
           (!isset($para['mobile']) || empty($para['mobile'])))))) {
      $result['success'] = FALSE;
      $result['message'] = "账号或密码未输入";
      return $result;
    }

    $myself = new self();
    //查询z5j_users=>$result['users']
    if (isset($para['id']) && !empty($para['id'])) {
      $result = $myself->getMe("*", "id='".trim($para['id'])."'", "", trim($para['token']));
    } elseif (!isset($para['email']) || empty($para['email'])) {
      $result = $myself->getMe("*", "mobile='".trim($para['mobile'])."'", trim($para['password']), "");
    } else {
      $result = $myself->getMe("*", "email='".trim($para['email'])."'", trim($para['password']), "");
    }
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询该用户";
      return $result;
    }
    if ($result['success'] == FALSE) {
      return $result;
    }

    //查询其他用户表=>$result[table][0]
    $tempresult = $myself->getUser($myself->verificationtable, "*", "user='".$result[$myself->usertable]['id']."'");
    if (!is_null($tempresult) && $tempresult['success'] != FALSE) {
      $result[$myself->verificationtable] = $tempresult[$myself->verificationtable];
    }
    $tempresult = $myself->getUser($myself->reviewtable, "*", "user='".$result[$myself->usertable]['id']."'");
    if (!is_null($tempresult) && $tempresult['success'] != FALSE && isset($tempresult[$myself->reviewtable])) {
      $result[$myself->reviewtable] = $tempresult[$myself->reviewtable];
    }
    $tempresult = $myself->getUser($myself->referencetable, "*", "user='".$result[$myself->usertable]['id']."'");
    if (!is_null($tempresult) && $tempresult['success'] != FALSE && isset($tempresult[$myself->referencetable])) {
      $result[$myself->referencetable] = $tempresult[$myself->referencetable];
    }
    $tempresult = $myself->getUser($myself->contacttable, "*", "user='".$result[$myself->usertable]['id']."'");
    if (!is_null($tempresult) && $tempresult['success'] != FALSE) {
      $result[$myself->contacttable] = $tempresult[$myself->contacttable];
    }
    if (isset($para['id']) && !empty($para['id'])) {
      return $result;
    }

    //查询z5j_accounts=>$tempresult['accounts'][0]，并更新z5j_account_logon=>$result['token']
    $fields[0] = "id";
    $tempresult = $myself->getAccount($myself->accounttable, $fields, "user='".$result[$myself->usertable]['id']."'");
    if (!is_null($tempresult) && $tempresult['success'] != FALSE) {
      unset($para);
      $para['account'] = $tempresult[$myself->accounttable][0]['id'];
      $tempresult = $myself->updateAccount($myself->logontable, $para, TRUE);
      if (!is_null($tempresult) && $tempresult['success'] != FALSE) {
        $result['token'] = $tempresult[$myself->logontable]['token'];
        $result['location'] = $tempresult[$myself->logontable]['location'];
      }
    }
    return $result;
  }

//用户注册API
//$para: 注册信息数组
//返回$result['users']、$result['token']
  static public function register($para) {
    if (!is_array($para) || 
        !isset($para['password']) || empty($para['password']) || 
        ((!isset($para['email']) || empty($para['email'])) && 
         (!isset($para['mobile']) || empty($para['mobile'])))) {
      $result['success'] = FALSE;
      $result['message'] = "邮箱、手机号或密码未输入";
      return $result;
    }

    $myself = new self();
    //新建z5j_users=>$result['users']
    $result = $myself->updateUser($myself->usertable, $para, TRUE);
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "无法注册用户";
      return $result;
    }
    if ($result['success'] == FALSE) {
      $result['message'] = "请确认您是否已经用这个邮箱/手机号注册过本站的账号";
      return $result;
    }
    //新建z5j_user_contacts
    unset($para);
    $para['user'] = $result[$myself->usertable]['id'];
    $tempresult = $myself->updateUser($myself->contacttable, $para, TRUE);
    if (isset($result[$myself->usertable]['email']) && !empty($result[$myself->usertable]['email'])) {
      $para['isemail'] = TRUE;
    } else {
      $para['isemail'] = FALSE;
    }
    //新建z5j_user_verifications，并发送验证码/邮件
    $tempresult = $myself->updateUser($myself->verificationtable, $para, TRUE);
    if (!is_null($tempresult) && $tempresult['success'] == TRUE) {
      if ($para['isemail']) {
//        $sendresult = $myself->sendEmail($result[$myself->usertable]['email'], $tempresult['user_verifications']['token']);
//        if (!is_null($sendresult) && $sendresult['success'] == TRUE) {
//          $result['sent'] = TRUE;
//        }
      } else {
//        $sendresult = $myself->sendMessage($result[$myself->usertable]['mobile'], $tempresult['user_verifications']['code']);
//        if (!is_null($sendresult) && $sendresult['success'] == TRUE) {
//          $result['sent'] = TRUE;
//        }
      }
    }

    //新建z5j_accounts，并更新z5j_account_logon=>$result['token']
    $tempresult = $myself->updateAccount($myself->accounttable, $para, TRUE);
    if (is_null($tempresult)) {
      $result['success'] = FALSE;
      $result['message'] = "无法注册账号";
      return $result;
    }
    if ($tempresult['success'] == FALSE) {
      $result['success'] = FALSE;
      $result['message'] = $tempresult['message'];
      return $result;
    }
    unset($para);
    $para['account'] = $tempresult[$myself->accounttable]['id'];
    $tempresult = $myself->updateAccount($myself->logontable, $para, TRUE);
    if (!is_null($tempresult) && $tempresult['success'] != FALSE) {
      $result['token'] = $tempresult[$myself->logontable]['token'];
    }
    return $result;
  }

//验证用户邮箱手机API
//$para: 验证信息数组
//返回$result['user_verifications'][n]
  static public function verifyUser($para) {
    if (!is_array($para) || !isset($para['isemail']) || !isset($para['user']) || empty($para['user'])) {
      $result['success'] = FALSE;
      $result['message'] = "未指定邮箱手机";
      return $result;
    }
    if ($para['isemail']) {
      if (!isset($para['token']) || empty($para['token'])) {
        $result['success'] = FALSE;
        $result['message'] = "未指定验证信息";
        return $result;
      }
    } else {
      if (!isset($para['code']) || empty($para['code'])) {
        $result['success'] = FALSE;
        $result['message'] = "未指定验证码";
        return $result;
      }
    }

    $myself = new self();
    //查询z5j_user_verifications=>$result['user_verifications'][0]
    $result = $myself->getUser($myself->verificationtable, "*", "user='".trim($para['user'])."'");
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "无法获取验证信息";
      return $result;
    }
    if (!$result['success']) {
      return $result;
    }
    //验证更新z5j_user_verifications=>$result['user_verifications'][0]
    //验证失败则重发验证码/邮件
    $verifytime = date('Y-m-d H:i:s');
    if ($para['isemail']) {
      if ($result[$myself->verificationtable][0]['token'] != $para['token']) {
        $result['success'] = FALSE;
        $result['message'] = "验证信息错误";
      }
      if ($result[$myself->verificationtable][0]['tokenexpiretime'] < $verifytime) {
        $result['success'] = FALSE;
        $result['message'] = "验证信息已经过期";
      }
      if (!$result['success']) {
        $tempresult = $myself->updateUser($myself->verificationtable, $para, FALSE);
        if (!is_null($tempresult) && $tempresult['success'] == TRUE) {
          $token = $tempresult[$myself->verificationtable]['token'];
          $tempresult = $myself->getUser($myself->usertable, "email", "id='".trim($para['user'])."'");
          if (!is_null($tempresult) && $tempresult['success'] == TRUE) {
//            $sendresult = $myself->sendEmail($tempresult[$myself->usertable][0]['email'], $token);
//            if (!is_null($sendresult) && $sendresult['success'] == TRUE) {
//              $result['sent'] = TRUE;
//              $result['message'] .= "，新验证邮件已发送";
//            } else {
//              $result['message'] .= "，".$sendresult['message'];
//            }
          }
        }
        if (!$tempresult['success']) {
          $result['message'] .= "，".$tempresult['message'];
        }
        return $result;
      }
      $unset($para['isemail']);
      $para['token'] = "";
      $para['tokenexpiretime'] = "0000-00-00 00:00:00";
      $para['emailtime'] = $verifytime;
      $result = $myself->updateUser($myself->verificationtable, $para, FALSE);
    } else {
      if ($result[$myself->verificationtable][0]['code'] != $para['code']) {
        $result['success'] = FALSE;
        $result['message'] = "验证码错误";
      }
      if ($result[$myself->verificationtable][0]['codeexpiretime'] < $verifytime) {
        $result['success'] = FALSE;
        $result['message'] = "验证码已经过期";
      }
      if (!$result['success']) {
        $tempresult = $myself->updateUser($myself->verificationtable, $para, FALSE);
        if (!is_null($tempresult) && $tempresult['success'] == TRUE) {
          $code = $tempresult[$myself->verificationtable]['code'];
          $tempresult = $myself->getUser($myself->usertable, "mobile", "id='".trim($para['user'])."'");
          if (!is_null($tempresult) && $tempresult['success'] == TRUE) {
//            $sendresult = $myself->sendEmail($tempresult[$myself->usertable][0]['mobile'], $code);
//            if (!is_null($sendresult) && $sendresult['success'] == TRUE) {
//              $result['sent'] = TRUE;
//              $result['message'] .= "，新验证邮件已发送";
//            } else {
//              $result['message'] .= "，".$sendresult['message'];
//            }
          }
        }
        if (!$tempresult['success']) {
          $result['message'] .= "，".$tempresult['message'];
        }
        return $result;
      }
      unset($para['isemail']);
      $para['code'] = "";
      $para['codeexpiretime'] = "0000-00-00 00:00:00";
      $para['mobiletime'] = $verifytime;
      $result = $myself->updateUser($myself->verificationtable, $para, FALSE);
    }
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "无法更新验证信息";
    }
    return $result;
  }

//获取用户信息API
//$para: 表名数组
//返回$result[table][n]
  static public function getUserInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定用户表";
      return $result;
    }
    $myself = new self();
    foreach ($para as $table => $fields) {
      if (!isset($fields['all'])) {
        $i = 0;
        $selectfields = array();
        foreach ($fields as $field => $value) {
          $selectfields[$i] = $field;
          $i++;
        }
      } else {
        $selectfields = "*";
        unset($fields['all']);
      }

      $result['success'] = TRUE;
      $condition = "";
      //检查检索条件
      switch ($table) {
        case $myself->usertable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户ID";
            return $result;
          } else {
            $condition = "id='".trim($fields['id'])."'";
            if (isset($fields['status'])) {
              $condition .= " AND status='".trim($fields['status'])."'";
            }
          }
          break;
        case $myself->reviewtable:
          if ((!isset($fields['id']) || empty($fields['id'])) && (!isset($fields['user']) || empty($fields['user']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定评价和用户";
            return $result;
          } else {
            if (isset($fields['id']) && !empty($fields['id'])) {
              $condition = "id='".trim($fields['id'])."'";
            } else {
              $condition = "user='".trim($fields['user'])."'";
            }
            if (isset($fields['status'])) {
              $condition .= " AND T1.status='".trim($fields['status'])."'";
            }
          }
          break;
        case $myself->referencetable:
          if (!isset($fields['user']) || empty($fields['user'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户";
            return $result;
          } else {
            $condition = "user='".trim($fields['user'])."'";
            if (isset($fields['fromuser']) && !empty($fields['fromuser'])) {
              $condition .= " AND fromuser='".trim($fields['fromuser'])."'";
            }
            if (isset($fields['status'])) {
              $condition .= " AND T1.status='".trim($fields['status'])."'";
            }
          }
          break;
        case $myself->messagetable:
          if ((!isset($fields['id']) || empty($fields['id'])) && (!isset($fields['touser']) || empty($fields['touser'])) && (!isset($fields['fromuser']) || empty($fields['fromuser']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定短信和用户";
            return $result;
          } else {
            if (isset($fields['id']) && !empty($fields['id'])) {
              $condition = "T1.id='".trim($fields['id'])."'";
            } else {
              if (isset($fields['touser']) && !empty($fields['touser'])) {
                $condition = "touser='".trim($fields['touser'])."'";
              }
              if (isset($fields['fromuser']) && !empty($fields['fromuser'])) {
                if (isset($condition) && !empty($condition)) {
                  $condition = "(".$condition." OR fromuser='".trim($fields['fromuser'])."')";
                } else {
                  $condition = "fromuser='".trim($fields['fromuser'])."'";
                }
              }
            }
            if (isset($fields['status'])) {
              $condition .= " AND T1.status='".trim($fields['status'])."'";
            }
          }
          break;
        case $myself->contenttable:
          if (!isset($fields['message']) || empty($fields['message'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定短信";
            return $result;
          } else {
            $condition = "message='".trim($fields['message'])."'";
          }
          break;
        case $myself->notificationtable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定通知ID";
            return $result;
          } else {
            $condition = "id='".trim($fields['id'])."'";
          }
          break;
        case $myself->mynotificationtable:
          if ((!isset($fields['notification']) || empty($fields['notification'])) && (!isset($fields['user']) || empty($fields['user']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定通知和用户";
            return $result;
          } else {
            if (isset($fields['notification']) && !empty($fields['notification'])) {
              $condition = "notification='".trim($fields['notification'])."'";
              $condition .= " AND user='".trim($fields['user'])."'";
            } else {
              $condition = "user='".trim($fields['user'])."'";
            }
            if (isset($fields['status'])) {
              $condition .= " AND T2.status='".trim($fields['status'])."'";
            }
          }
          break;
        //$myself->verificationtable、$myself->contacttable
        default:
          if (!isset($fields['user']) || empty($fields['user'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户";
            return $result;
          } else {
            $condition = "user='".trim($fields['user'])."'";
          }
      }
      $tempresult = $myself->getUser($table, $selectfields, $condition);
      if (is_null($tempresult)) {
        switch ($table) {
          case $myself->usertable:
            $result['message'] = "无法查询该用户信息";
            break;
          case $myself->reviewtable:
            $result['message'] = "无法查询该用户的评价信息";
            break;
          case $myself->referencetable:
            $result['message'] = "无法查询该用户的推荐信息";
            break;
          case $myself->verificationtable:
            $result['message'] = "无法查询该用户的验证信息";
            break;
          case $myself->contacttable:
            $result['message'] = "无法查询该用户的联系人信息";
            break;
          case $myself->messagetable:
            $result['message'] = "无法查询该用户的短信";
            break;
          case $myself->contenttable:
            $result['message'] = "无法查询该短信的详细信息";
            break;
          case $myself->notificationtable:
            $result['message'] = "无法查询该通知";
            break;
          case $myself->mynotificationtable:
            $result['message'] = "无法查询该用户的通知";
            break;
        }
        continue;
      }
      if ($tempresult['success'] && isset($tempresult[$table])) {
        $result[$table] = $tempresult[$table];
      }
    }
    return $result;
  }

//更新用户信息API
//$para: 表名数组
//返回$result[table]
  static public function updateUserInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定用户表";
      return $result;
    }
    $myself = new self();
    $result['success'] = TRUE;
    foreach ($para as $table => $fields) {
      if (!is_array($fields)) {
        continue;
      }
      if (!isset($fields['insert'])) {
        $fields['insert'] = FALSE;
      }
      //检查更新条件
      switch ($table) {
        case $myself->usertable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户ID";
            return $result;
          }
          break;
        case $myself->reviewtable:
          if ($fields['insert']) {
            if (!isset($fields['user']) || empty($fields['user'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定用户";
              return $result;
            }
          } else {
            if (!isset($fields['id']) || empty($fields['id'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定用户评价ID";
              return $result;
            }
          }
          break;
        case $myself->messagetable:
          if ($fields['insert']) {
            if (!isset($fields['touser']) || empty($fields['touser'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定用户";
              return $result;
            }
          } else {
            if (!isset($fields['id']) || empty($fields['id'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定短信ID";
              return $result;
            }
          }
          break;
        case $myself->contenttable:
          if (!isset($fields['message']) || empty($fields['message'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定短信";
            return $result;
          }
          break;
        case $myself->notificationtable:
          if (!$fields['insert']) {
            if (!isset($fields['id']) || empty($fields['id'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定通知ID";
              return $result;
            }
          } else {
            if (!isset($fields['title']) || empty($fields['title'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定通知标题";
              return $result;
            }
          }
          break;
        case $myself->mynotificationtable:
          if (!isset($fields['notification']) || empty($fields['notification']) || !isset($fields['user']) || empty($fields['user'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定通知和用户";
            return $result;
          }
          break;
        default:
          if (!isset($fields['user']) || empty($fields['user'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户";
            return $result;
          }
      }

      if (isset($fields['insert']) && $fields['insert']) {
        unset($fields['insert']);
        $tempresult = $myself->updateUser($table, $fields, TRUE);
      } else {
        if (isset($fields['insert'])) {
          unset($fields['insert']);
        }
        $tempresult = $myself->updateUser($table, $fields);
      }
      if (is_null($tempresult)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->usertable:
            $result['message'] = "无法更新该用户信息";
            break;
          case $myself->reviewtable:
            $result['message'] = "无法更新该用户的评价信息";
            break;
          case $myself->referencetable:
            $result['message'] = "无法更新该用户的推荐信息";
            break;
          case $myself->verificationtable:
            $result['message'] = "无法更新该用户的验证信息";
            break;
          case $myself->contacttable:
            $result['message'] = "无法更新该用户的联系人信息";
            break;
          case $myself->messagetable:
            $result['message'] = "无法更新该短信信息";
            break;
          case $myself->contenttable:
            $result['message'] = "无法更新该短信的详细信息";
            break;
          case $myself->notificationtable:
            $result['message'] = "无法更新该通知信息";
            break;
          case $myself->mynotificationtable:
            $result['message'] = "无法更新该用户的通知信息";
            break;
        }
        return $result;
      }
      $result[$table] = $tempresult[$table];
    }
    return $result;
  }

//删除用户信息API
//$para: 表名数组
//返回$result['success']
  static public function deleteUserInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定用户表";
      return $result;
    }
    $myself = new self();
    $result['success'] = TRUE;
    foreach ($para as $table => $fields) {
      if (!is_array($fields)) {
        continue;
      }
      //检查删除条件
      switch ($table) {
        case $myself->usertable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户ID";
            return $result;
          }
          break;
        case $myself->reviewtable:
          if ((!isset($fields['user']) || empty($fields['user'])) && (!isset($fields['id']) || empty($fields['id']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户评价和用户";
            return $result;
          }
          break;
        case $myself->messagetable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定短信ID";
            return $result;
          }
          break;
        case $myself->contenttable:
          if (!isset($fields['message']) || empty($fields['message'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定短信";
            return $result;
          }
          break;
        case $myself->notificationtable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定通知ID";
            return $result;
          }
          break;
        case $myself->mynotificationtable:
          if (!isset($fields['notification']) || empty($fields['notification']) || !isset($fields['user']) || empty($fields['user'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定通知和用户";
            return $result;
          }
          break;
        default:
          if (!isset($fields['user']) || empty($fields['user'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定用户";
            return $result;
          }
      }
      $result = $myself->deleteUser($table, $fields);
      if (is_null($result)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->usertable:
            $result['message'] = "无法删除该用户信息";
            break;
          case $myself->reviewtable:
            $result['message'] = "无法删除该用户的评价信息";
            break;
          case $myself->referencetable:
            $result['message'] = "无法删除该用户的推荐信息";
            break;
          case $myself->verificationtable:
            $result['message'] = "无法删除该用户的验证信息";
            break;
          case $myself->contacttable:
            $result['message'] = "无法删除该用户的联系人信息";
            break;
          case $myself->messagetable:
            $result['message'] = "无法删除该短信信息";
            break;
          case $myself->contenttable:
            $result['message'] = "无法删除该短信的详细信息";
            break;
          case $myself->notificationtable:
            $result['message'] = "无法删除该通知信息";
            break;
          case $myself->mynotificationtable:
            $result['message'] = "无法删除该用户的通知信息";
            break;
        }
        return $result;
      }
    }
    return $result;
  }

//获取登陆用户的信息
//$fields: 项目数组
//返回$result[table]
  function getMe($fields="*", $condition="", $password="", $token="") {
    if ($fields != "*" && !is_array($fields)) {
      $result['success'] = FALSE;
      $result['message'] = "查询项目不正确";
      return $result;
    }

    $mysql = new PHPMySQL();
    if (!empty($token)) {
      $sql = "SELECT T1.* FROM z5j_".$this->usertable." T1 LEFT JOIN z5j_".$this->accounttable." T2 ON T1.id=T2.user LEFT JOIN z5j_".$this->logontable." T3 ON T2.id=T3.account WHERE T1.".$condition." AND T3.token='".$token."'";
      $sqlresult = $mysql->runSQL($sql);
    } else {
      $sqlresult = $mysql->selectRows($this->usertable, $fields, $condition);
    }
    $mysql = NULL;
    if (!$sqlresult) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询用户表 users";
      return $result;
    }
    if (!empty($token)) {
      if ($sqlresult->num_rows != 1) {
        $result['success'] = FALSE;
        $result['message'] = "用户已经退出";
        return $result;
      }
      $result['success'] = TRUE;
      $result[$this->usertable] = $sqlresult->fetch_assoc();
      unset($result[$this->usertable]['password']);
      return $result;
    }
/*  getUser()替代
    if (empty($password)) {
      $result['success'] = TRUE;
      unset($result[$this->usertable]);
      if ($sqlresult->num_rows == 0) {
        return $result;
      }
      $result[$this->usertable] = array();
      while ($userinfo = $sqlresult->fetch_assoc()) {
//      if ($userinfo['type'] >= 8) {
//          continue;
//      }
        unset($userinfo['password']);
        array_push($result[$this->usertable], $userinfo);
      }
    } else {
*/
      if ($sqlresult->num_rows == 0) {
        $result['success'] = FALSE;
        $result['message'] = "用户不存在";
        return $result;
      } elseif ($sqlresult->num_rows > 1) {
        $result['success'] = FALSE;
        $result['message'] = "用户信息异常";
        return $result;
      }
      $result[$this->usertable] = $sqlresult->fetch_assoc();
      if ($password == $result[$this->usertable]['password']) {
        $result['success'] = TRUE;
        //unset敏感信息
        unset($result[$this->usertable]['password']);
      } else {
        $result['success'] = FALSE;
        $result['message'] = "账号或密码不正确";
        unset($result[$this->usertable]);
      }
//    }
    return $result;
  }

//获取用户信息
//$fields: 项目数组
//返回$result[table][n]
  function getUser($table, $fields="*", $condition="") {
    if (empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定用户表";
      return $result;
    }
    if ($fields != "*" && !is_array($fields)) {
      $result['success'] = FALSE;
      $result['message'] = "查询项目不正确";
      return $result;
    }

    $mysql = new PHPMySQL();
    switch ($table) {
      case $this->referencetable:
      case $this->reviewtable:
        if ($fields == "*") {
          $selectfields = "T1.*,";
        } else {
          $selectfields = "";
          foreach ($fields as $i => $field) {
            $selectfields .= "T1.".$field.",";
          }
        }
        $sql = "SELECT ".$selectfields." T2.lastname, T2.firstname FROM z5j_".$table." AS T1
                LEFT JOIN z5j_".$this->usertable." AS T2 ON T1.fromuser=T2.id WHERE T1.".$condition;
        $sqlresult = $mysql->runSQL($sql);
        break;
      case $this->usertable:
      case $this->verificationtable:
      case $this->contacttable:
      case $this->notificationtable:
        $sqlresult = $mysql->selectRows($table, $fields, $condition);
        break;
      case $this->messagetable:
        if ($fields == "*") {
          $selectfields = "T1.*,";
        } else {
          $selectfields = "";
          foreach ($fields as $i => $field) {
            $selectfields .= "T1.".$field.",";
          }
        }
        $sql = "SELECT ".$selectfields." CONCAT(T2.lastname, T2.firstname) AS fname, T2.portrait AS fportrait, CONCAT(T3.lastname, T3.firstname) AS tname, T3.portrait AS tportrait
                FROM z5j_".$table." AS T1
                LEFT JOIN z5j_".$this->usertable." AS T2 ON T1.fromuser=T2.id
                LEFT JOIN z5j_".$this->usertable." AS T3 ON T1.touser=T3.id WHERE ".$condition." ORDER BY T1.REPLYTIME DESC";
        $sqlresult = $mysql->runSQL($sql);
/*
        if (substr($condition, 0, 2) == "id") {
          if ($fields == "*") {
            $selectfields = "T1.*,";
          } else {
            $selectfields = "";
            foreach ($fields as $i => $field) {
              $selectfields .= "T1.".$field.",";
            }
          }
          $sql = "SELECT ".$selectfields." T2.attachmentname, T2.attachment, T2.content FROM z5j_".$table." AS T1
                  LEFT JOIN z5j_".$this->contenttable." AS T2 ON T1.id=T2.message WHERE T1.".$condition;
          $sqlresult = $mysql->runSQL($sql);
        } else {
          $sqlresult = $mysql->selectRows($table, $fields, $condition);
        }
 */
        break;
      case $this->contenttable:
        $sqlresult = $mysql->selectRows($table, $fields, $condition, "createtime DESC");
        break;
      case $this->mynotificationtable:
        if ($fields == "*") {
          $selectfields = ", T2.*";
        } else {
          $selectfields = "";
          foreach ($fields as $i => $field) {
            $selectfields .= ", T2.".$field;
          }
        }
        $sql = "SELECT T1.title, T1.content, T1.url".$selectfields." FROM z5j_".$this->notificationtable." AS T1
                LEFT JOIN z5j_".$table." AS T2 ON T1.id=T2.notification WHERE T2.".$condition;
        $sqlresult = $mysql->runSQL($sql);
        break;
      default:
        $result['success'] = FALSE;
        $result['message'] = "指定用户表不存在";
        return $result;
    }
    $mysql = NULL;
    if (!$sqlresult) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询用户表 ".$table;
      return $result;
    }
    $result['success'] = TRUE;
    unset($result[$table]);
    if ($sqlresult->num_rows == 0) {
      return $result;
    }
    $result[$table] = array();
/*     if ($table == $this->mynotificationtable) {
      while ($userinfo = $sqlresult->fetch_assoc()) {
        if (!empty($userinfo['url'])) {
          if (strpos($userinfo['content'], "##") !== FALSE) {
            $userinfo['content'] = substr_replace($userinfo['content'], $userinfo['parameter1'], strpos($userinfo['content'], "##"), 2);
          }
          if (strpos($userinfo['content'], "##") !== FALSE) {
            $userinfo['content'] = substr_replace($userinfo['content'], $userinfo['parameter2'], strpos($userinfo['content'], "##"), 2);
          }
          if (strpos($userinfo['content'], "##") !== FALSE) {
            $userinfo['content'] = substr_replace($userinfo['content'], $userinfo['parameter3'], strpos($userinfo['content'], "##"), 2);
          }
        } else {
          if (strpos($userinfo['content'], "##") !== FALSE) {
            $content = $userinfo['content'];
            $userinfo['content'] = array();
            $userinfo['url'] = array();
            $i = 0;
            $j = 1;
            do {
              if (strpos($content, "##") == 0) {
                $userinfo['content'][$i] = $userinfo['parameter'.$j];
                $userinfo['url'][$i] = $userinfo['url'.$j];
                $i++;
                $j++;
                $content = substr($content, 2);
              } else {
                $userinfo['content'][$i] = substr($content, 0, strpos($content, "##"));
                $userinfo['url'][$i] = "";
                $i++;
                $userinfo['content'][$i] = $userinfo['parameter'.$j];
                $userinfo['url'][$i] = $userinfo['url'.$j];
                $i++;
                $j++
                $content = substr($content, strpos($content, "##") + 2);
              }
            } while (strpos($content, "##") === FALSE);
            if (!empty($content)) {
              $userinfo['content'][$i] = $content;
              $userinfo['url'][$i] = "";
            }
            unset($userinfo['parameter1']);
            unset($userinfo['url1']);
            unset($userinfo['parameter2']);
            unset($userinfo['url2']);
            unset($userinfo['parameter3']);
            unset($userinfo['url3']);
          }
        }
        array_push($result[$table], $userinfo);
      }
    } else {
    } */
    while ($userinfo = $sqlresult->fetch_assoc()) {
      //unset敏感信息
      if (isset($userinfo['password'])) {
        unset($userinfo['password']);
      }
      array_push($result[$table], $userinfo);
    }
    return $result;
  }

//新建/更新用户信息
//$fields: 项目数组
//返回$result[table]
  function updateUser($table, $fields, $isnew=FALSE) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无更新用户内容";
      return $result;
    }

    if ($isnew) {
      switch ($table) {
        case $this->usertable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['edittime'] = $fields['createtime'];
          $fields['id'] = md5($fields['firstname'].$fields['lastname'].$fields['createtime']);
          break;
        case $this->referencetable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['edittime'] = $fields['createtime'];
          break;
        case $this->reviewtable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['id'] = md5($fields['user'].$fields['createtime']);
          break;
/*        case $this->verificationtable:
          if ($fields['isemail']) {
            $fields['tokenexpiretime'] = date('Y-m-d H:i:s', time()+60*60*24);
            $fields['token'] = md5($fields['user'].$fields['tokenexpiretime']);
          } else {
            $fields['codeexpiretime'] = date('Y-m-d H:i:s', time()+60*10);
            $fields['code'] = rand(100000, 999999);
          }
          unset($fields['isemail']);
          break;
*/
        case $this->messagetable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['replytime'] = $fields['createtime'];
          $fields['id'] = md5($fields['touser'].$fields['createtime']);
          $fields['status'] = 2;
          break;
        case $this->notificationtable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['id'] = md5($fields['title'].$fields['createtime']);
          break;
        case $this->contenttable:
        case $this->mynotificationtable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          break;
      }
    } else {
      switch ($table) {
        case $this->messagetable:
          if (isset($fields['status']) && ($fields['status'] == 1 || $fields['status'] == 2)) {
            $fields['replytime'] = date('Y-m-d H:i:s');
          }
        case $this->usertable:
        case $this->notificationtable:
          if (!isset($fields['id']) || $fields['id'] == "") {
            $result['success'] = FALSE;
            $result['message'] = "用户、短信或通知未指定";
            return $result;
          }
          $condition = "id='".$fields['id']."'";
          break;
        case $this->reviewtable:
          if (!isset($fields['id']) || $fields['id'] == "") {
            $result['success'] = FALSE;
            $result['message'] = "评价未指定";
            return $result;
          }
          $condition = "id='".$fields['id']."'";
          if (isset($fields['reply']) && !empty($fields['reply'])) {
            $fields['replytime'] = date('Y-m-d H:i:s');
          }
          break;
        case $this->contenttable:
          if (!isset($fields['message']) || $fields['message'] == "") {
            $result['success'] = FALSE;
            $result['message'] = "通知未指定";
            return $result;
          }
          $condition = "message='".$fields['message']."'";
          break;
        case $this->mynotificationtable:
          if (!isset($fields['notification']) || $fields['notification'] == "" || !isset($fields['user']) ||$fields['user'] == "") {
            $result['success'] = FALSE;
            $result['message'] = "通知和用户未指定";
            return $result;
          }
          $condition = "notification='".$fields['notification']."' AND user='".$fields['user']."'";
          break;
        default:
          if (!isset($fields['user']) || $fields['user'] == "") {
            $result['success'] = FALSE;
            $result['message'] = "用户没有指定";
            return $result;
          }
          $condition = "user='".$fields['user']."'";
          break;
      }
      if (isset($fields['edittime'])) {
        $fields['edittime'] = "NULL";
      }
    }
    if ($table == $this->verificationtable && isset($fields['isemail'])) {
      if ($fields['isemail']) {
        $fields['tokenexpiretime'] = date('Y-m-d H:i:s', time()+60*60*24);
        $fields['token'] = md5($fields['user'].$fields['tokenexpiretime']);
      } else {
        $fields['codeexpiretime'] = date('Y-m-d H:i:s', time()+60*10);
        $fields['code'] = rand(100000, 999999);
      }
      unset($fields['isemail']);
    }
    $mysql = new PHPMySQL();
    if ($isnew) {
      $sqlresult = $mysql->insertRow($table, $fields);
    } else {
      $sqlresult = $mysql->updateRows($table, $fields, $condition);
    }
    if ($sqlresult === FALSE) {
      $result['success'] = FALSE;
      if ($isnew) {
        $result['message'] = "无法在表 ".$table." 中新建用户信息";
      } else {
        $result['message'] = "无法更新用户表 ".$table;
      }
      return $result;
    }
    //unset敏感信息
    $result['success'] = TRUE;
    if (isset($fields['password'])) {
      unset($fields['password']);
    }
    if (isset($fields['code'])) {
      unset($fields['code']);
    }
    if (isset($fields['codeexpiretime'])) {
      unset($fields['codeexpiretime']);
    }
    if (isset($fields['token'])) {
      unset($fields['token']);
    }
    if (isset($fields['tokenexpiretime'])) {
      unset($fields['tokenexpiretime']);
    }
    $result[$table] = $fields;
    return $result;
  }

//删除用户信息
//$fields: 项目数组
//返回$result['success']
  function deleteUser($table, $fields) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无删除用户内容";
      return $result;
    }
    $result['success'] = TRUE;
    $condition = "";
    if ($table == $this->referencetable) {
      $condition = "user='".$fields['user']."'";
      if (isset($fields['fromuser']) && !empty($fields['fromuser'])) {
        $condition .= "AND fromuser='".$fields['fromuser']."'";
      }
    }
    if ($table == $this->reviewtable) {
      if ((!isset($fields['id']) || empty($fields['id'])) && (isset($fields['user']) && !empty($fields['user']))) {
        $condition .= "user='".$fields['user']."'";
      }
    }
    $mysql = new PHPMySQL();
    if (empty($condition)) {
      $sqlresult = $mysql->deleteRows($table, "", $fields);
    } else {
      $sqlresult = $mysql->deleteRows($table, $condition);
    }
    if ($sqlresult === FALSE) {
      $result['success'] = FALSE;
      $result['message'] = "无法从表 ".$table." 中删除用户信息";
    }
    return $result;
  }

//上传头像
//$para: 头像信息数组
//返回$result['users']
  static public function uploadPortrait($para) {
    if (!is_array($para) || !isset($para['id']) || empty($para['id'])) {
      $result['success'] = FALSE;
      $result['message'] = "未指定用户";
      return $result;
    }
    $path = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR ."media". DIRECTORY_SEPARATOR ."portrait". DIRECTORY_SEPARATOR;
    $utility = new Utility();
    $result = $utility->uploadFile($path, $para['filename']);
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "无法上传头像";
      return $result;
    } elseif ($result['success'] == FALSE) {
      return $result;
    }
    $para['portrait'] = $result['uploadfile'];
    $myself = new self();
    $result = $myself->updateUser($myself->usertable, $para);
    return $result;
  }
  
//获取账户信息API
//$para: 表名数组
//返回$result[table][n]
  static public function getAccountInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定账户表";
      return $result;
    }
    $myself = new self();
    foreach ($para as $table => $fields) {
      if (!isset($fields['all'])) {
        $i = 0;
        $selectfields = array();
        foreach ($fields as $field => $value) {
          $selectfields[$i] = $field;
          $i++;
        }
      } else {
        $selectfields = "*";
        unset($fields['all']);
      }

      $result['success'] = TRUE;
      $condition = "";
      //检查检索条件
      switch ($table) {
        case $myself->accounttable:
          if ((!isset($fields['id']) || empty($fields['id'])) && (!isset($fields['user']) || empty($fields['user']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户和用户";
          } else {
            if (isset($fields['id']) && !empty($fields['id'])) {
              $condition = "id='".trim($fields['id'])."'";
            } else {
              $condition = "user='".trim($fields['user'])."'";
            }
            if (isset($fields['status'])) {
              $condition .= " AND status='".trim($fields['status'])."'";
            }
          }
          break;
        case $myself->paymenttable:
          if ((!isset($para['id']) || empty($para['id'])) && (!isset($para['account']) || empty($para['account']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户和付款方式";
          } else {
            if (isset($fields['id']) && !empty($fields['id'])) {
              $condition = "id='".trim($fields['id'])."'";
            } else {
              $condition = "account='".trim($fields['account'])."'";
            }
            if (isset($fields['status'])) {
              $condition .= " AND status='".trim($fields['status'])."'";
            }
          }
          break;
        default:
          if (!isset($para['account']) || empty($para['account'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户";
            return $result;
          } else {
            $condition = "account='".trim($fields['account'])."'";
            if (isset($fields['status'])) {
              $condition .= " AND status='".trim($fields['status'])."'";
            }
          }
      }
      $tempresult = $myself->getAccount($table, $selectfields, $condition);
      if (is_null($tempresult)) {
        switch ($table) {
          case $myself->accounttable:
            $result['message'] = "无法查询该账户信息";
            break;
          case $myself->paymenttable:
            $result['message'] = "无法查询该账户的收付款方式信息";
            break;
          case $myself->logontable:
            $result['message'] = "无法查询该账户的登录信息";
            break;
        }
        continue;
      }
      if ($tempresult['success'] && isset($tempresult[$table])) {
        $result[$table] = $tempresult[$table];
      }
    }
    return $result;
  }

//更新账户信息API
//$para: 表名数组
//返回$result[table]
  static public function updateAccountInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定账户表";
      return $result;
    }
    $myself = new self();
    $result['success'] = TRUE;
    foreach ($para as $table => $fields) {
      if (!is_array($fields)) {
        continue;
      }
      //检查更新条件
      switch ($table) {
        case $myself->accounttable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户ID";
            return $result;
          }
          break;
        case $myself->paymenttable:
          if ($fields['insert']) {
            if (!isset($fields['account']) || empty($fields['account'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定账户";
              return $result;
            }
          } else {
            if (!isset($fields['id']) || empty($fields['id'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定收付款方式ID";
              return $result;
            }
          }
          break;
        //$myself->logontable
        default:
          if (!isset($fields['account']) || empty($fields['account'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户";
            return $result;
          }
          break;
      }

      if (isset($fields['insert']) && $fields['insert']) {
        unset($fields['insert']);
        $tempresult = $myself->updateAccount($table, $fields, TRUE);
      } else {
        if (isset($fields['insert'])) {
          unset($fields['insert']);
        }
        $tempresult = $myself->updateAccount($table, $fields);
      }
      if (is_null($tempresult)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->accounttable:
            $result['message'] = "无法更新该账户信息";
            break;
          case $myself->paymenttable:
            $result['message'] = "无法更新该账户的收付款方式信息";
            break;
          case $myself->logontable:
            $result['message'] = "无法更新该账户的登录信息";
            break;
        }
        return $result;
      }
      $result[$table] = $tempresult[$table];
    }
    return $result;
  }

//删除账户信息API
//$para: 表名数组
//返回$result['success']
  static public function deleteAccountInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定账户表";
      return $result;
    }
    $myself = new self();
    $result['success'] = TRUE;
    foreach ($para as $table => $fields) {
      if (!is_array($fields)) {
        continue;
      }
      //检查删除条件
      switch ($table) {
        case $myself->accounttable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户ID";
            return $result;
          }
          break;
        case $myself->paymenttable:
          if ((!isset($fields['account']) || empty($fields['account'])) && (!isset($fields['id']) || empty($fields['id']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户和收付款方式";
            return $result;
          }
          break;
        default:
          if (!isset($fields['account']) || empty($fields['account'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定账户";
            return $result;
          }
      }
      $result = $myself->deleteAccount($table, $fields);
      if (is_null($result)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->accounttable:
            $result['message'] = "无法删除该账户信息";
            break;
          case $myself->paymenttable:
            $result['message'] = "无法删除该账户的收付款方式信息";
            break;
          case $myself->logontable:
            $result['message'] = "无法删除该账户的登录信息";
            break;
        }
        return $result;
      }
    }
    return $result;
  }

//获取账户信息
//$fields: 项目数组
//返回$result[table][n]
  function getAccount($table, $fields="*", $condition="") {
    if (empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定账户表";
      return $result;
    }
    if ($fields != "*" && !is_array($fields)) {
      $result['success'] = FALSE;
      $result['message'] = "查询项目不正确";
      return $result;
    }

    $mysql = new PHPMySQL();
    $sqlresult = $mysql->selectRows($table, $fields, $condition);
    $mysql = NULL;
    if (!$sqlresult) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询账户表 ".$table;
      return $result;
    }
    $result['success'] = TRUE;
    unset($result[$table]);
    if ($sqlresult->num_rows == 0) {
      return $result;
    }
    $result[$table] = array();
    while ($accountinfo = $sqlresult->fetch_assoc()) {
      //隐藏账户信息
      if (isset($accountinfo['card']) && !empty($accountinfo['card'])) {
        $accountinfo['card'] = substr($accountinfo['card'], 0, 3)."******".substr($accountinfo['card'], -3);
      }
      //unset敏感信息
      if (isset($accountinfo['valid'])) {
        unset($accountinfo['valid']);
      }
      if (isset($accountinfo['cvv'])) {
        unset($accountinfo['cvv']);
      }
      array_push($result[$table], $accountinfo);
    }
    return $result;
  }

//新建/更新账户信息
//$fields: 项目数组
//返回$result[table]
  function updateAccount($table, $fields, $isnew=FALSE) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无更新账户内容";
      return $result;
    }
    if ($isnew) {
      switch ($table) {
        case $this->accounttable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['edittime'] = $fields['createtime'];
          $fields['id'] = md5($fields['user'].$fields['createtime']);
          break;
        case $this->paymenttable:
          if (isset($fields['card']) && !empty($fields['card'])) {
            $fields['id'] = md5($fields['account'].$fields['bank'].$fields['card'].$fields['bankaccount']);
          } else {
            $fields['id'] = md5($fields['account'].$fields['bank'].$fields['bankaccount']);
          }
          break;
        case $this->logontable:
          $fields['logontime'] = date('Y-m-d H:i:s');
          $fields['token'] = md5($fields['account'].$fields['logontime']);
          $utility = new Utility();
          $fields['device'] = $utility->getOS();
          $fields['browser'] = $utility->getBrowser();
          $location = $utility->getAddress();
          if (empty($location) || !isset($location[0][0])) {
            $fields['location'] = "未知";
          } else {
            $fields['location'] = $location[0][0];
            if (isset($location[0][1]) && !empty($location[0][1])) {
              $fields['location'] .= "-".$location[0][1];
            }
          }
          $fields['ip'] = $utility->getIP();
          break;
      }
    } else {
      if ($table == $this->accounttable || $table == $this->paymenttable) {
        if (!isset($fields['id']) || $fields['id'] == "") {
          $result['success'] = FALSE;
          $result['message'] = "账户或收付款方式未指定";
          return $result;
        }
        $condition = "id='".$fields['id']."'";
      }
      if ($table == $this->logontable) {
        if (!isset($fields['account']) || $fields['account'] == "" || !isset($fields['token']) || $fields['token'] == "") {
          $result['success'] = FALSE;
          $result['message'] = "账户未指定";
          return $result;
        }
        $condition = "account='".$fields['account']."' AND token='".$fields['token']."'";
      }
      if (isset($fields['edittime'])) {
        $fields['edittime'] = "NULL";
      }
    }
    $mysql = new PHPMySQL();
    if ($isnew) {
      $sqlresult = $mysql->insertRow($table, $fields);
    } else {
      $sqlresult = $mysql->updateRows($table, $fields, $condition);
    }
    if ($sqlresult === FALSE) {
      $result['success'] = FALSE;
      if ($isnew) {
        $result['message'] = "无法在表 ".$table." 中新建账户信息";
      } else {
        $result['message'] = "无法更新账户表 ".$table;
      }
      return $result;
    }
    $result['success'] = TRUE;
    //隐藏账户信息
    if (isset($fields['card']) && !empty($fields['card'])) {
      $fields['card'] = substr($fields['card'], 0, 3)."******".substr($fields['card'], -3);
    }
    //unset敏感信息
    if (isset($fields['valid'])) {
      unset($fields['valid']);
    }
    if (isset($fields['cvv'])) {
      unset($fields['cvv']);
    }
    if ($isnew && $table == $this->logontable) {
      unset($fields['device']);
      unset($fields['browser']);
      unset($fields['ip']);
    }
    $result[$table] = $fields;
    return $result;
  }

//删除账户信息
//$fields: 项目数组
//返回$result['success']
  function deleteAccount($table, $fields) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无删除账户内容";
      return $result;
    }
    $result['success'] = TRUE;
    $condition = "";
    if ($table == $this->paymenttable) {
      if ((!isset($fields['id']) || empty($fields['id'])) && (isset($fields['account']) && !empty($fields['account']))) {
        $condition .= "account='".$fields['account']."'";
      }
    }
    if ($table == $this->logontable) {
      $condition = "account='".$fields['account']."'";
      if (isset($fields['token']) && !empty($fields['token'])) {
        $condition .= "AND token='".$fields['token']."'";
      }
    }
    $mysql = new PHPMySQL();
    if (empty($condition)) {
      $sqlresult = $mysql->deleteRows($table, "", $fields);
    } else {
      $sqlresult = $mysql->deleteRows($table, $condition);
    }
    if ($sqlresult === FALSE) {
      $result['success'] = FALSE;
      $result['message'] = "无法从表 ".$table." 中删除账户信息";
    }
    return $result;
  }
}
?>