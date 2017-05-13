<?php
/*
* 页面框架元素类
* author chinkin
* revision: 1
*/
class ElementForPhone {
  protected static $divitable = "division";

//获取Divi信息
//$para: 数组
//id/parent/usertype/hidden: divi的id(可以是数组)/父divi的id/用户类型/是否隐藏type为0的divi
  static public function getDivision($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "查询divi参数不正确";
      return $result;
    }
    $where = "";
    if (isset($para['usertype']) && is_numeric($para['usertype']) && $para['usertype'] >= 0 && $para['usertype'] <= 9) {
      $where = "auth<=".$para['usertype'];
    } else {
      $where = "auth<=1";
    }
    if (isset($para['id']) && !empty($para['id'])) {
      if (!is_array($para['id'])) {
        $where .= " AND id='".$para['id']."'";
      } elseif (count($para['id']) < 2) {
        $where .= " AND id='".$para['id'][0]."'";
      } else {
        $where .= " AND id IN (";
        while ($divi = each($para['id'])) {
          $where .= "'".$divi."', ";
        }
        $where = substr($query, 0, strlen($query) - 2).")";
      }
    }
    if (isset($para['parent']) && $para['parent'] != "") {
      $where .= " AND parent='".$para['parent']."'";
    } else {
      $where .= " AND parent='d0000000'";
    }
    if (!isset($para['hidden']) || $para['hidden'] != FALSE) {
      $where .= " AND type<>0";
    }
    $fields = array("id", "parent", "name", "sequence", "navigation", "content", "navicont", "auth", "type");
    $mysql = new PHPMySQL();
    $sqlresult = $mysql->selectRows(self::$divitable, $fields, $where, "sequence");
    $mysql = NULL;
    if (!$sqlresult) {
      $result['success'] = FALSE;
      $result['message'] = "不能查询divi";
      return $result;
    }
    $result['data'] = array();
    while ($diviinfo = $sqlresult->fetch_assoc()) {
      array_push($result['data'], $diviinfo);
    }
    $result['success'] = TRUE;
    return $result;
  }

//获取Navi信息
//$para: 数组
//division/dim/usertype/hidden: Division数组/返回数组的维度/用户类型/是否隐藏type为0的navi
  static public function getNavigation($para) {
    if (!is_array($para) || !isset($para['division']) || !is_array($para['division']) || !isset($para['dim']) || ($para['dim'] != 1 && $para['dim'] != 2)) {
      $result['success'] = FALSE;
      $result['message'] = "查询navi参数不正确";
      return $result;
    }
    if (isset($para['usertype']) && is_numeric($para['usertype']) && $para['usertype'] >= 0 && $para['usertype'] <= 9) {
      $where = "readauth<=".$para['usertype'];
    } else {
      $where = "readauth<=1";
    }
    if (!isset($para['hidden']) || $para['hidden'] != FALSE) {
      $where .= " AND type<>0";
    }
    if ($para['subflag']) {
      $fields = array("id", "name", "sequence", "detail", "readauth", "writeauth", "type");
    } else {
      $fields = array("id", "name", "sequence", "readauth", "writeauth", "type");
    }
    $mysql = new PHPMySQL();
    if ($para['dim'] == 1) {
      if (!isset($para['division']['navigation'])) {
        $result['success'] = FALSE;
        $result['message'] = "查询navi参数未设置";
        return $result;
      }
      $sqlresult = $mysql->selectRows($para['division']['navigation'], $fields, $where, "sequence");
      $mysql = NULL;
      if (!$sqlresult) {
        $result['success'] = FALSE;
        $result['message'] = "查询navi(".$para['division']['navigation'].")信息出错";
        return $result;
      }
      while ($naviinfo = $sqlresult->fetch_assoc()) {
        array_push($result['data'], $diviinfo);
      }
      $result['success'] = TRUE;
      return $result;
    }
    $result['data'] = array();
    foreach ($para['division'] as $id => $divi) {
      if (!is_array($divi) || !isset($divi['id']) || !isset($divi['navigation'])) {
        $result['success'] = FALSE;
        $result['message'] = "查询navi参数非数组或未设置";
        return $result;
      }
      $sqlresult = $mysql->selectRows($divi['navigation'], $fields, $where, "sequence");
      if (!$sqlresult) {
        $result['message'] = "无法查询navi(".$divi['navigation'].")信息";
        continue;
      }
      $navis = array();
      while ($naviinfo = $sqlresult->fetch_assoc()) {
        array_push($navis, $naviinfo);
      }
      $divinavi = array("did" => $divi['id'], "name" => $divi['name'], "navigation" => $divi['navigation'], "content" => $divi['content'], "navis" => $navis);
      array_push($result['data'], $divinavi);
    }
    $mysql = NULL;
    $result['success'] = TRUE;
    return $result;
  }

//获取Cont信息
  function getContent($division,  $hidden=TRUE) {
  }
}
?>