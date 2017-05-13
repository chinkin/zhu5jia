<?php
/*
* 论坛类
* author chinkin
* revision: 1
*/
class Forum {
  private $headertable = "forumheader";
  private $contenttable = "forumcontent";
  private $usertable = "user";

//获取帖子头信息
//user: 用户ID
//title: 帖子标题
//from, to: 最后编辑的时间范围
  function getHeader($para) {
    if (!is_array($para)) {
      return FALSE;
    }
    $mysql = new PHPMySQL();
    $tablename = $mysql->getTableName($this->headertable);
    $query = "SELECT a.*, b.name AS creater FROM ".$tablename." AS a, ";
    $tablename = $mysql->getTableName($this->usertable);
    $query .= $tablename." AS b WHERE a.user=b.id ";
    if (isset($para['user']) && !empty($para['user'])) {
      if ($para['user'] == "me") {
        if (!isset($_SESSION['User'])) {
          return FALSE;
        }
        $query .= "AND a.user='".$_SESSION['User']['id']."' ";
      } else {
        $query .= "AND a.user='".$para['user']."' ";
      }
    }
    if (isset($para['title']) && !empty($para['title'])) {
      $query .= "AND a.title like '%".$para['title']."%' ";
    }
    if (isset($para['from']) && !empty($para['from'])) {
      $query .= "AND a.edittime>='".$para['from']." 00:00:00' ";
    }
    if (isset($para['to']) && !empty($para['to'])) {
      $query .= "AND a.edittime<='".$para['to']." 23:59:59' ";
    }
    $query .= "ORDER BY a.edittime DESC";
    $_SESSION['Forumheader'] = null;
    $result = $mysql->runSQL($query);
    $mysql = NULL;
    if (!$result) {
      echo "alert('查询论坛信息出错!');";
      return FALSE;
    }
    if (isset($_SESSION['Forumheader'])) {
      unset($_SESSION['Forumheader']);
    }
    if (isset($_SESSION['Forumcontent'])) {
      unset($_SESSION['Forumcontent']);
    }
    if ($result->num_rows == 0) {
      return NULL;
    }
    while ($header = $result->fetch_assoc()) {
      $forumheader[$header['id']] = $header;
    }
    $_SESSION['Forumheader'] = $forumheader;
    return TRUE;
  }

//新建帖子
//title: 帖子标题
//content: 帖子内容
//attachname: 视频文件名
  function updateHeader($para) {
    if (!is_array($para) || !isset($para['title']) || empty($para['title']) || !isset($para['content']) || empty($para['content']) || !isset($para['attachname']) || empty($para['attachname'])) {
      return FALSE;
    }
    $_SESSION['Folder'] = null;
    $_SESSION['Filename'] = null;
    $forumheader['user'] = $_SESSION['User']['id'];
    $forumheader['creater'] = $_SESSION['User']['name'];
    $forumheader['createtime'] = date('Y-m-d H:i:s');
    $forumheader['id'] = md5($forumheader['user'].$forumheader['createtime']);
    $forumheader['title'] = $para['title'];
    $forumheader['edittime'] = $forumheader['createtime'];
    $forumcontent['id'] = $forumheader['id'];
    $forumcontent['forum'] = $forumcontent['id'];
    $forumcontent['user'] = $forumheader['user'];
    $forumcontent['attachname'] = $para['attachname'];
//    $forumcontent['attachname'] = pathinfo($attachname, PATHINFO_BASENAME);
    $forumcontent['attachment'] = $forumcontent['id'];
    $forumcontent['content'] = $para['content'];
    $forumcontent['timestamp'] = $forumheader['edittime'];
    $_SESSION['Folder'] = "Video";
    $fileext = pathinfo($forumcontent['attachname'], PATHINFO_EXTENSION);
    $_SESSION['Filename'] = $forumcontent['attachment'].".".$fileext;
    $mysql = new PHPMySQL();
    $result = $mysql->insertRow($this->headertable, $forumheader);
    if (!$result) {
      echo "alert('新建帖子头信息出错!');";
      return FALSE;
    }
    $result = $mysql->insertRow($this->contenttable, $forumcontent);
    if (!$result) {
      echo "alert('新建帖子内容信息出错!');";
      return FALSE;
    }
    if (!isset($_SESSION['Forumheader']) || is_null($_SESSION['Forumheader'])) {
      $_SESSION['Forumheader'][$forumheader['id']] = $forumheader;
    } else {
      $newforumheader[$forumheader['id']] = $forumheader;
      $_SESSION['Forumheader'] = array_merge($newforumheader, $_SESSION['Forumheader']);
    }
    $_SESSION['Forumcontent'][$forumcontent['id']] = $forumcontent;
    return TRUE;
  }

//删除帖子
//$forum: 帖子ID
  function deleteHeader($header) {
    if (empty($header)) {
      return FALSE;
    }
    $mysql = new PHPMySQL();
    $field[0] = "attachname";
    $field[1] = "attachment";
    $result = $mysql->selectRows($this->contenttable, $field, "forum='".$header."'");
    if (!$result) {
      echo "alert('获取帖子内容信息出错!');";
      return FALSE;
    }
    $i = 0;
    while ($content = $result->fetch_assoc()) {
      if (empty($content['attachment']) || empty($content['attachname'])) {
        continue;
      }
      $attachment[$i] = $content['attachment'].".".pathinfo($content['attachname'], PATHINFO_EXTENSION);
      $i++;
    }
    $result = $mysql->deleteRows($this->contenttable, "forum='".$header."'");
    if (!$result) {
      echo "alert('删除帖子内容信息出错!');";
      return FALSE;
    }
    unset($_SESSION['Forumcontent']);
    $result = $mysql->deleteRows($this->headertable, "id='".$header."'");
    if (!$result) {
      echo "alert('删除帖子头信息出错!');";
      return FALSE;
    }
    unset($_SESSION['Forumheader'][$header]);
    Utility::deleteFile("../Video/", $attachment);
    return TRUE;
  }

//获取帖子内容信息
//$forum: 帖子ID
  function getContent($forum) {
    if (empty($forum)) {
      return FALSE;
    }
    $_SESSION['CurrentForumheader'] = null;
    $_SESSION['Forumcontent'] = null;
    $forumcontent = null;
    $mysql = new PHPMySQL();
    $tablename = $mysql->getTableName($this->contenttable);
    $query = "SELECT a.*, b.name AS username, c.name AS toname FROM ".$tablename." AS a LEFT JOIN ";
    $tablename = $mysql->getTableName($this->usertable);
    $query .= $tablename." AS b ON a.user=b.id LEFT JOIN ".$tablename." AS c ON a.replyto=c.id WHERE a.forum='".$forum."' ORDER BY a.timestamp DESC";
//    $result = $mysql->selectRows($this->contenttable, "*", "forum='".$forum."'", "timestamp");
    $result = $mysql->runSQL($query);
    $mysql = NULL;
    if (!$result) {
      echo "alert('查询帖子信息出错!');";
      return FALSE;
    }
    while ($content = $result->fetch_assoc()) {
      $forumcontent[$content['id']] = $content;
    }
    $_SESSION['Forumcontent'] = $forumcontent;
    $_SESSION['CurrentForumheader'] = $_SESSION['Forumheader'][$forum];
    return TRUE;
  }

//新建回帖
//content: 帖子内容
//attachname: 视频文件名
//replyto: 回复对象
//toname: 回复对象的姓名
//同时更新帖子头和内容信息
  function updateContent($para) {
    if (!is_array($para) || !isset($para['content']) || empty($para['content'])) {
      return FALSE;
    }
    $forumheader['edittime'] = date('Y-m-d H:i:s');
    $forumcontent['timestamp'] = $forumheader['edittime'];
    $forumcontent['user'] = $_SESSION['User']['id'];
    $forumcontent['username'] = $_SESSION['User']['name'];
    $forumcontent['id'] = md5($forumcontent['user'].$forumcontent['timestamp']);
    $forumcontent['forum'] = $_SESSION['CurrentForumheader']['id'];
    $forumcontent['content'] = $para['content'];
    if (isset($para['replyto']) && !empty($para['replyto'])) {
      $forumcontent['replyto'] = $para['replyto'];
    }
    if (isset($para['toname']) && !empty($para['toname'])) {
      $forumcontent['toname'] = $para['toname'];
    }
    if (isset($para['attachname']) && !empty($para['attachname'])) {
      $forumcontent['attachname'] = $para['attachname'];
//      $forumcontent['attachname'] = pathinfo($attachname, PATHINFO_BASENAME);
      $forumcontent['attachment'] = $forumcontent['id'];
      $_SESSION['Folder'] = "Video";
      $fileext = pathinfo($forumcontent['attachname'], PATHINFO_EXTENSION);
      $_SESSION['Filename'] = $forumcontent['attachment'].".".$fileext;
    }
    $mysql = new PHPMySQL();
    $result = $mysql->updateRows($this->headertable, $forumheader, "id='".$_SESSION['CurrentForumheader']['id']."'");
    if (!$result) {
      echo "alert('更新帖子头信息出错!');";
      return FALSE;
    }
    $result = $mysql->insertRow($this->contenttable, $forumcontent);
    if (!$result) {
      echo "alert('更新帖子内容信息出错!');";
      return FALSE;
    }
    $_SESSION['Forumheader'][$forumcontent['forum']]['edittime'] = $forumheader['edittime'];
    $newforumcontent[$forumcontent['id']] = $forumcontent;
    $_SESSION['Forumcontent'] = array_merge($newforumcontent, $_SESSION['Forumcontent']);
    return TRUE;
  }

//删除回复
//$content: 回复ID
  function deleteContent($content) {
    if (empty($content)) {
      return FALSE;
    }
    $mysql = new PHPMySQL();
    $field[0] = "forum";
    $field[1] = "attachname";
    $field[2] = "attachment";
    $result = $mysql->selectRows($this->contenttable, $field, "id='".$content."'");
    if (!$result) {
      echo "alert('获取帖子内容信息出错!');";
      return FALSE;
    }
    $forumcontent = $result->fetch_assoc();
    if (!empty($forumcontent['attachment']) && !empty($forumcontent['attachname'])) {
      $attachment[0] = $forumcontent['attachment'].".".pathinfo($forumcontent['attachname'], PATHINFO_EXTENSION);
    }
    $result = $mysql->deleteRows($this->contenttable, "id='".$content."'");
    if (!$result) {
      echo "alert('删除帖子内容信息出错!');";
      return FALSE;
    }
    unset($_SESSION['Forumcontent'][$content]);
    if (isset($attachment)) {
      Utility::deleteFile("../Video/", $attachment);
    }
    return TRUE;
  }
}
?>