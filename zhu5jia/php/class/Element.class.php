<?php
/*
* 页面框架元素类
* author chinkin
* revision: 1
*/
class Element {
  protected static $divitable = "division";

//获取Divi信息
//$id: divi的id，可以是数组
//$hidden: 是否隐藏divi
  static public function getDivision($id="", $parent="d0000000", $hidden=TRUE) {
    $where = "";
    if (!empty($id)) {
      if (!is_array($id)) {
        $where = "id='".$id."'";
      } elseif (count($id) < 2) {
        $where = "id='".$id[0]."'";
      } else {
        $where = "id IN (";
        while ($divi = each($id)) {
          $where .= "'".$divi."', ";
        }
        $where = substr($query, 0, strlen($query) - 2).")";
      }
    }
    if ($parent != "") {
      if (empty($where)) {
        $where = "parent='".$parent."'";
      } else {
        $where .= " AND parent='".$parent."'";
      }
    }
    if ($hidden) {
      if (empty($where)) {
        $where = "type<>0";
      } else {
        $where .= " AND type<>0";
      }
    }
    $mysql = new PHPMySQL();
    $result = $mysql->selectRows(self::$divitable, "*", $where, "sequence");
    $mysql = NULL;
    if (!$result) {
      echo "alert('查询div信息出错!');";
      return FALSE;
    }

    while ($diviinfo = $result->fetch_assoc()) {
      $divi[$diviinfo['id']] = $diviinfo;
    }
    if ($parent == "d0000000") {
      $_SESSION['Divisions'] = $divi;
    } else {
      $_SESSION['Subdivisions'] = $divi;
      $_SESSION['Divisions'] = array_merge($_SESSION['Divisions'], $divi);
    }
    return TRUE;
  }

//获取Navi信息
//$division: Division数组
//$dim: 数组维度
//$hidden: 是否隐藏navi
  static public function getNavigation($division, $dim=2, $hidden=TRUE, $append=FALSE) {
    if (!is_array($division) || ($dim != 1 && $dim != 2)) {
      return FALSE;
    }
    $mysql = new PHPMySQL();
    $where = "type<9 ";
    if ($hidden) {
      $where .= "AND type<>0";
    }
    if ($dim == 1) {
      if (!isset($division['navigation'])) {
        return FALSE;
      }
      $result = $mysql->selectRows($division['navigation'], "*", $where, "sequence");
      $mysql = NULL;
      if (!$result) {
        echo "alert('查询navi(".$divi['navigation'].")信息出错!');";
        return FALSE;
      }
      while ($naviinfo = $result->fetch_assoc()) {
      	$navi[$naviinfo['id']] = $naviinfo;
      }
      return $navi;
    }
    foreach ($division as $id => $divi) {
      if (!is_array($divi)) {
        return FALSE;
      }
      $result = $mysql->selectRows($divi['navigation'], "*", $where, "sequence");
      if (!$result) {
        echo "alert('查询navi(".$divi['navigation'].")信息出错!');";
        continue;
      }
      while ($naviinfo = $result->fetch_assoc()) {
        $navi[$id][$naviinfo['id']] = $naviinfo;
      }
    }
    $mysql = NULL;
    if (!$append) {
      $_SESSION['Navigations'] = $navi;
    } else {
//      $_SESSION['Subnavigations'] = $navi;
      $_SESSION['Navigations'] = array_merge($_SESSION['Navigations'], $navi);
    }
    return TRUE;
  }

//获取Cont信息
  function getContent($division,  $hidden=TRUE) {
  }

//获取通用信息API
//$para: 表名数组
//返回$result[table][n]
  static public function getElementInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定订单表";
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
      foreach ($fields as $field => $value) {
        if (!empty($value)) {
          $condition .= $field."='".$value."' AND ";
        }
      }
      if ($condition != "") {
        $condition = substr($condition, 0, strlen($condition) - 5);
      }
      $tempresult = $myself->getElement($table, $selectfields, $condition);
      if (is_null($tempresult)) {
        $result['message'] = "无法查询".$table."内的信息";
        continue;
      }
      if ($tempresult['success'] && isset($tempresult[$table])) {
        $result[$table] = $tempresult[$table];
      }
    }
    return $result;
  }

//获取表信息
//$fields: 项目数组
//返回$result[table][n]
  function getElement($table, $fields="*", $condition="") {
    if (empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定表";
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
      $result['message'] = "无法查询通用表 ".$table;
      return $result;
    }
    $result['success'] = TRUE;
    unset($result[$table]);
    if ($sqlresult->num_rows == 0) {
      return $result;
    }
    $result[$table] = array();
    while ($bookinginfo = $sqlresult->fetch_assoc()) {
      array_push($result[$table], $bookinginfo);
    }
    return $result;
  }
}
?>