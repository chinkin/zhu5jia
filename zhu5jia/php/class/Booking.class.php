<?php
/*
* 订单类
* author chinkin
* revision: 1
*/
class Booking {
//订单关联表
  private $bookingtable = "bookings";
  private $detailstable = "booking_details";
//房屋关联表
  private $housetable = "houses";
  private $mediatable = "house_media";
  private $roomtable = "house_rooms";
//用户关联表
  private $usertable = "users";

//检索订单信息API
//$para: 项目数组
//返回$result['orders'][n]
  static public function searchBookingInformation($para) {
    if (!is_array($para) || isset($para['user']) || empty($para['user'])) {
      $result['success'] = FALSE;
      $result['message'] = "未指定订单表和用户";
      return $result;
    }
    $myself = new self();
    $result = $myself->searchBooking($para);
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询该用户的订单信息";
    }
    return $result;
  }

//获取订单信息API
//$para: 表名数组
//返回$result[table][n]
  static public function getBookingInformation($para) {
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
      //检查检索条件
      switch ($table) {
        case $myself->bookingtable:
          if ((!isset($fields['id']) || empty($fields['id'])) && (!isset($fields['number']) || empty($fields['number']) || (!isset($fields['user']) || empty($fields['user'])))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定订单ID和订单号";
          } else {
            if (isset($fields['id']) && !empty($fields['id'])) {
              $condition = "id='".trim($fields['id'])."'";
            } else {
              $condition = "number='".trim($fields['number'])."' AND user='".trim($fields['user'])."'";
            }
            if (isset($fields['status'])) {
              $condition .= " AND status='".trim($fields['status'])."'";
            }
          }
          break;
        case $myself->detailstable:
          if (!isset($fields['booking']) || empty($fields['booking'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定订单";
            return $result;
          } else {
            $condition = "booking='".trim($fields['booking'])."'";
          }
          break;
        default:
      }
      $tempresult = $myself->getBooking($table, $selectfields, $condition);
      if (is_null($tempresult)) {
        switch ($table) {
          case $myself->bookingtable:
            $result['message'] = "无法查询该订单信息";
            break;
          case $myself->detailstable:
            $result['message'] = "无法查询该订单的详细信息";
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

//更新订单信息API
//$para: 表名数组
//返回$result[table]
  static public function updateBookingInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定订单表";
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
        case $myself->bookingtable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定订单ID";
            return $result;
          }
          break;
        case $myself->detailstable:
          if (!isset($fields['booking']) || empty($fields['booking'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定订单";
            return $result;
          }
          break;
      }
      if (isset($fields['insert']) && $fields['insert']) {
        unset($fields['insert']);
        $tempresult = $myself->updateBooking($table, $fields, TRUE);
      } else {
        if (isset($fields['insert'])) {
          unset($fields['insert']);
        }
        $tempresult = $myself->updateBooking($table, $fields);
      }
      if (is_null($tempresult)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->bookingtable:
            $result['message'] = "无法更新该订单信息";
            break;
          case $myself->detailstable:
            $result['message'] = "无法更新该订单的详细信息";
            break;
        }
        return $result;
      }
      $result[$table] = $tempresult[$table];
    }
    return $result;
  }

//删除订单信息API
//$para: 表名数组
//返回$result['success']
  static public function deleteBookingInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定订单表";
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
        case $myself->bookingtable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定订单ID";
            return $result;
          }
          break;
        case $myself->detailstable:
          if (!isset($fields['booking']) || empty($fields['booking'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定订单";
            return $result;
          }
          break;
      }
      $result = $myself->deleteBooking($table, $fields);
      if (is_null($result)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->bookingtable:
            $result['message'] = "无法删除该订单信息";
            break;
          case $myself->detailstable:
            $result['message'] = "无法删除该订单的详细信息";
            break;
        }
        return $result;
      }
    }
    return $result;
  }

//检索订单信息
//$fields: 项目数组
//返回$result['orders'][n]
  function searchBooking($fields) {
    if (!is_array($fields)) {
      $result['success'] = FALSE;
      $result['message'] = "查询项目不正确";
      return $result;
    }

    $mysql = new PHPMySQL();
    $sql = "SELECT * FROM (SELECT T1.*, T2.*, T3.id AS hid, T3.name, T4.id AS rid FROM z5j_".$this->bookingtable." AS T1
                           LEFT JOIN z5j_".$this->detailstable." AS T2 ON T1.id=T2.booking
                           LEFT JOIN z5j_".$this->housetable." AS T3 ON T1.house=T3.id
                           LEFT JOIN z5j_".$this->roomtable." AS T4 ON T1.house=T4.house AND T2.room=T4.id WHERE T1.user='".$fields['user']."'";
    if (isset($fields['fromdate']) && !empty($fields['fromdate'])) {
      $sql .= "AND (T2.fromdate>='".$fields['fromdate']."' OR (T2.fromdate<'".$fields['fromdate']."' AND T2.todate>='".$fields['fromdate']."'))";
    }
    if (isset($fields['todate']) && !empty($fields['todate'])) {
      $sql .= "AND (T2.todate<='".$fields['todate']."' OR (T2.todate>'".$fields['todate']."' AND T2.fromdate<='".$fields['todate']."'))";
    }
    $sql .= ") AS V1 LEFT JOIN z5j_".$this->mediatable." AS T5 ON T5.house=hid ORDER BY T1.id, T2.fromdate;";
    $sqlresult = $mysql->runSQL($sql);
    $mysql = NULL;
    if (!$sqlresult) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询订单表";
      return $result;
    }
    $result['success'] = TRUE;
    unset($result[$this->bookingtable]);
    if ($sqlresult->num_rows == 0) {
      return $result;
    }
    $result[$this->bookingtable] = array();
    while ($orderinfo = $sqlresult->fetch_assoc()) {
      unset($orderinfo['house']);
      for ($i = 9; $i >= 0; $i--) {
        if (!is_null($orderinfo['hidden'.$i]) && $orderinfo['hidden'.$i] != 1) {
          $orderinfo['photo'] = $orderinfo['photo'.$i];
        }
        unset($orderinfo['hidden'.$i]);
        unset($orderinfo['photo'.$i]);
      }
      array_push($result[$this->bookingtable], $orderinfo);
    }
    return $result;
  }

//获取订单信息
//$fields: 项目数组
//返回$result[table][n]
  function getBooking($table, $fields="*", $condition="") {
    if (empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定订单表";
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
      $result['message'] = "无法查询订单表 ".$table;
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

//新建/更新订单信息
//$fields: 项目数组
//返回$result[table]
  function updateBooking($table, $fields, $isnew=FALSE) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无更新订单内容";
      return $result;
    }
    if ($isnew) {
      switch ($table) {
        case $this->bookingtable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['edittime'] = $fields['createtime'];
          $fields['id'] = md5($fields['user'].$fields['createtime']);
          $fields['number'] = rand(1000000000000000, 9999999999999999);
          break;
        case $this->detailstable:
          break;
      }
    } else {
      if ($table == $this->bookingtable) {
        if (!isset($fields['id']) || $fields['id'] == "") {
          $result['success'] = FALSE;
          $result['message'] = "订单未指定";
          return $result;
        }
        $condition = "id='".$fields['id']."'";
      }
      if ($table == $this->detailstable) {
        if (!isset($fields['booking']) || $fields['booking'] == "") {
          $result['success'] = FALSE;
          $result['message'] = "订单未指定";
          return $result;
        }
        $condition = "booking='".$fields['booking']."'";
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
        $result['message'] = "无法在表 ".$table." 中新建订单信息";
      } else {
        $result['message'] = "无法更新订单表 ".$table;
      }
      return $result;
    }
    $result['success'] = TRUE;
    $result[$table] = $fields;
    return $result;
  }

//删除订单信息
//$fields: 项目数组
//返回$result['success']
  function deleteBooking($table, $fields) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无删除订单内容";
      return $result;
    }
    $result['success'] = TRUE;
    $condition = "";
    if ($table == $this->detailstable) {
      $condition .= "booking='".$fields['booking']."'";
    }
    $mysql = new PHPMySQL();
    if (empty($condition)) {
      $sqlresult = $mysql->deleteRows($table, "", $fields);
    } else {
      $sqlresult = $mysql->deleteRows($table, $condition);
    }
    if ($sqlresult === FALSE) {
      $result['success'] = FALSE;
      $result['message'] = "无法从表 ".$table." 中删除订单信息";
    }
    return $result;
  }
}
?>