<?php
/*
* 房屋类
* author chinkin
* revision: 1
*/
class House {
//房屋关联表
  private $housetable = "houses";
  private $mediatable = "house_media";
  private $roomtable = "house_rooms";
  private $reviewtable = "house_reviews";
  private $pricetable = "prices";
  private $scheduletable = "schedules";
//用户关联表
  private $usertable = "users";

//检索房屋信息API
//$para: 表名数组
//返回$result['house'][n]，$result['photo'][n][m]
  static public function searchHouseInformation($para) {
    if (!is_array($para) || !isset($para['country']) || empty($para['country']) ||
        !isset($para['city']) || empty($para['city']) ||
        !isset($para['fromdate']) || empty($para['fromdate']) ||
        !isset($para['todate']) || empty($para['todate']) ||
        !isset($para['person']) || empty($para['person'])) {
      $result['success'] = FALSE;
      $result['message'] = "未指定必要检索条件";
      return $result;
    }
    $myself = new self();
    $result = $myself->searchHouse($para);
    if (!$result) {
      $result['success'] = FALSE;
      $result['message'] = "无法检索房屋信息";
    }
    return $result;
  }

//获取房屋信息API
//$para: 表名数组
//返回$result[table][n]
  static public function getHouseInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定房屋表";
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
        case $myself->housetable:
          if ((!isset($fields['id']) || empty($fields['id'])) && 
              (!isset($fields['owner']) || empty($fields['owner'])) && 
              (!isset($fields['agent']) || empty($fields['agent']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋ID";
          } else {
            if (isset($fields['id']) && !empty($fields['id'])) {
              $condition = "id='".trim($fields['id'])."'";
            } else {
              if (isset($fields['agent']) && !empty($fields['agent'])) {
                $condition = "agent='".trim($fields['agent'])."'";
                if (isset($fields['owner']) && !empty($fields['owner'])) {
                  $condition = "(".$condition." OR owner='".trim($fields['owner'])."')";
                }
              } else {
                $condition = "owner='".trim($fields['owner'])."'";
              }
            }
            if (isset($fields['status'])) {
              $condition .= " AND status='".trim($fields['status'])."'";
            }
          }
          break;
        case $myself->roomtable:
        case $myself->reviewtable:
          if ((!isset($fields['id']) || empty($fields['id'])) && (!isset($fields['house']) || empty($fields['house']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房间";
            return $result;
          } else {
            if (isset($fields['id']) && !empty($fields['id'])) {
              $condition = "id='".trim($fields['id'])."'";
            } else {
              $condition = "house='".trim($fields['house'])."'";
            }
            if (isset($fields['status'])) {
              $condition .= " AND status='".trim($fields['status'])."'";
            }
          }
          break;
        //$myself->mediatable、$myself->pricetable、$myself->scheduletable
        default:
          if (!isset($fields['house']) || empty($fields['house'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋";
            return $result;
          } else {
            $condition = "house='".trim($fields['house'])."'";
          }
      }
      $tempresult = $myself->getHouse($table, $selectfields, $condition);
      if (is_null($tempresult)) {
        switch ($table) {
          case $myself->housetable:
            $result['message'] = "无法查询该房屋信息";
            break;
          case $myself->roomtable:
            $result['message'] = "无法查询该房屋的房间信息";
            break;
          case $myself->reviewtable:
            $result['message'] = "无法查询该房屋的评价信息";
            break;
          case $myself->mediatable:
            $result['message'] = "无法查询该房屋的媒体信息";
            break;
          case $myself->pricetable:
            $result['message'] = "无法查询该房屋的价格信息";
            break;
          case $myself->scheduletable:
            $result['message'] = "无法查询该房屋的日程信息";
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

//更新房屋信息API
//$para: 表名数组
//返回$result[table]
  static public function updateHouseInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定房屋表";
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
        case $myself->housetable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋ID";
            return $result;
          }
          break;
        case $myself->roomtable:
        case $myself->reviewtable:
          if ($fields['insert']) {
            if (!isset($fields['house']) || empty($fields['house'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定房屋";
              return $result;
            }
          } else {
            if (!isset($fields['id']) || empty($fields['id'])) {
              $result['success'] = FALSE;
              $result['message'] = "未指定房间或评价ID";
              return $result;
            }
          }
          break;
        case $myself->mediatable:
          if (!isset($fields['house']) || empty($fields['house'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋";
            return $result;
          }
          break;
        case $myself->pricetable:
        case $myself->scheduletable:
          if (!isset($fields['house']) || empty($fields['house']) || 
              !isset($fields['room']) || empty($fields['room']) || 
              !isset($fields['fromdate']) || empty($fields['fromdate']) || 
              !isset($fields['todate']) || empty($fields['todate'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋房间或日期";
            return $result;
          }
          break;
      }
      //$myself->pricetable和scheduletable需要调整冲突的记录
      if ($table == $myself->pricetable || $table == $myself->scheduletable) {
        $tempresult = $myself->updatePriceSchedule($table, $fields);
      } else {
        if (isset($fields['insert']) && $fields['insert']) {
          unset($fields['insert']);
          $tempresult = $myself->updateHouse($table, $fields, TRUE);
        } else {
          if (isset($fields['insert'])) {
            unset($fields['insert']);
          }
          $tempresult = $myself->updateHouse($table, $fields);
        }
      }
      if (is_null($tempresult)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->housetable:
            $result['message'] = "无法更新该房屋信息";
            break;
          case $myself->roomtable:
            $result['message'] = "无法更新该房屋的房间信息";
            break;
          case $myself->reviewtable:
            $result['message'] = "无法更新该房屋的评价信息";
            break;
          case $myself->mediatable:
            $result['message'] = "无法更新该房屋的媒体信息";
            break;
          case $myself->pricetable:
            $result['message'] = "无法更新该房屋的价格信息";
            break;
          case $myself->scheduletable:
            $result['message'] = "无法更新该房屋的日程信息";
            break;
        }
        return $result;
      }
      $result[$table] = $tempresult[$table];
    }
    return $result;
  }

//删除房屋信息API
//$para: 表名数组
//返回$result['success']
  static public function deleteHouseInformation($para) {
    if (!is_array($para)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定房屋表";
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
        case $myself->housetable:
          if (!isset($fields['id']) || empty($fields['id'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋ID";
            return $result;
          }
          break;
        case $myself->roomtable:
        case $myself->reviewtable:
          if ((!isset($fields['house']) || empty($fields['house'])) && (!isset($fields['id']) || empty($fields['id']))) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋或房间及评价ID";
            return $result;
          }
          break;
        case $myself->mediatable:
          if (!isset($fields['house']) || empty($fields['house'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋";
            return $result;
          }
          break;
        case $myself->pricetable:
        case $myself->scheduletable:
          if (!isset($fields['house']) || empty($fields['house']) || 
              !isset($fields['room']) || empty($fields['room']) || 
              !isset($fields['fromdate']) || empty($fields['fromdate']) || 
              !isset($fields['todate']) || empty($fields['todate'])) {
            $result['success'] = FALSE;
            $result['message'] = "未指定房屋房间或日期";
            return $result;
          }
          break;
      }
      //$myself->pricetable和scheduletable需要调整冲突的记录
      if ($table == $myself->pricetable || $table == $myself->scheduletable) {
        $tempresult = $myself->updatePriceSchedule($table, $fields, TRUE);
      } else {
        $result = $myself->deleteHouse($table, $fields);
      }
      if (is_null($result)) {
        $result['success'] = FALSE;
        switch ($table) {
          case $myself->housetable:
            $result['message'] = "无法删除该房屋信息";
            break;
          case $myself->roomtable:
            $result['message'] = "无法删除该房屋的房间信息";
            break;
          case $myself->reviewtable:
            $result['message'] = "无法删除该房屋的评价信息";
            break;
          case $myself->mediatable:
            $result['message'] = "无法删除该房屋的媒体信息";
            break;
          case $myself->pricetable:
            $result['message'] = "无法删除该房屋的价格信息";
            break;
          case $myself->scheduletable:
            $result['message'] = "无法删除该房屋的日程信息";
            break;
        }
        return $result;
      }
    }
    return $result;
  }

//检索房屋信息
//$fields: 项目数组
//返回$result['house'][n]，$result['photo'][n][m]
  function searchHouse($fields) {
    if (!is_array($fields)) {
      $result['success'] = FALSE;
      $result['message'] = "查询项目不正确";
      return $result;
    }

    $mysql = new PHPMySQL();
//    $sql = "SELECT * FROM (SELECT T1.id AS hid, T2.id AS rid FROM z5j_houses AS T1 LEFT JOIN z5j_house_rooms AS T2 ON T1.id = T2.house WHERE T1.country = 'abcdefgh' AND T1.city = 'hgfedcbaabcdefgh'
//            AND CONCAT(T1.id, T2.id) NOT IN (SELECT CONCAT(house, room) FROM z5j_schedules WHERE (fromdate < '2016-05-07' and todate >= '2016-05-07') OR (fromdate <= '2016-06-04' and todate > '2016-06-04') OR (fromdate >= '2016-05-07' and todate <= '2016-06-04'))) AS V1
//            LEFT JOIN (SELECT * FROM z5j_prices WHERE (fromdate < '2016-05-07' and todate >= '2016-05-07') OR (fromdate <= '2016-06-04' and todate > '2016-06-04') OR (fromdate >= '2016-05-07' and todate <= '2016-06-04')) AS T3 ON hid = T3.house AND rid = T3.room;";

    $sql = "SELECT V1.*, T3.pprice, T3.pfromdate, T3.ptodate, T4.*, T5.portrait AS oportrait, T6.portrait AS aportrait
            FROM (SELECT T1.id AS hid, T1.name, T1.owner, T1.agent, T1.country, T1.city, T1.district, T1.address, T1.lng, T1.lat, T1.type AS htype, T2.id AS rid, T2.type AS rtype, T2.price FROM z5j_".$this->housetable." AS T1 
                  LEFT JOIN z5j_".$this->roomtable." AS T2 ON T1.id=T2.house WHERE T1.country='".$fields['country']."' AND T1.city='".$fields['city']."' AND T1.person>=".$fields['person']." AND T1.status=0 AND T2.status=0";
    foreach ($fields as $field => $value) {
      switch ($field) {
        case "neighborhood":
          $sql .= " AND T1.neighborhood='".$value."'";
          break;
        case "htype":
          $sql .= " AND T1.type='".$value."'";
          break;
        case "child":
        case "infant":
        case "parking":
          $sql .= " AND T2.".$field.">='".$value."'";
          break;
        case "rtype":
          $sql .= " AND T2.type='".$value."'";
          break;
        case "bathroom":
        case "kitchen":
        case "breakfast":
        case "lunch":
        case "dinner":
        case "internet":
        case "TV":
        case "washer":
        case "dryer":
        case "aircon":
        case "heating":
        case "smoking":
        case "pet":
        case "pool":
          $sql .= " AND T2.".$field."='".$value."'";
          break;
      }
    }
    $sql .= " AND CONCAT(T1.id, T2.id) NOT IN (SELECT CONCAT(house, room) FROM z5j_".$this->scheduletable."
                                               WHERE (fromdate<'".$field['fromdate']."' and todate>='".$field['fromdate']."') OR (fromdate<='".$field['todate']."' and todate>'".$field['todate']."')
                                                  OR (fromdate>='".$field['fromdate']."' and todate<='".$field['todate']."'))) AS V1
             LEFT JOIN (SELECT house, room, price AS pprice, fromdate AS pfromdate, todate AS ptodate FROM z5j_".$this->pricetable."
                        WHERE (fromdate<'".$field['fromdate']."' and todate>='".$field['fromdate']."') OR (fromdate<='".$field['todate']."' and todate>'".$field['todate']."')
                           OR (fromdate>='".$field['fromdate']."' and todate<='".$field['todate']."')) AS T3 ON T3.house=hid AND T3.room=rid
             LEFT JOIN z5j_".$this->mediatable." AS T4 ON T4.house=hid
             LEFT JOIN z5j_".$this->usertable." AS T5 ON T5.id=owner
             LEFT JOIN z5j_".$this->usertable." AS T6 ON T6.id=agent ORDER BY hid, rid, pfromdate;";
    $sqlresult = $mysql->runSQL($sql);
    $mysql = NULL;
    if (!$sqlresult) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询房屋";
      return $result;
    }
    $result['success'] = TRUE;
    unset($result['house']);
    if ($sqlresult->num_rows == 0) {
      return $result;
    }
    $result['house'] = array();
    $k = 0;
    while ($houseinfo = $sqlresult->fetch_assoc()) {
      unset($houseinfo['house']);
      $j = 0;
      for ($i = 0; $i < 10; $i++) {
        if (!is_null($houseinfo['hidden'.$i]) && $houseinfo['hidden'.$i] != 1) {
          $result['photo'][$k][$j++]['photo'] = $houseinfo['photo'.$i];
        }
        unset($houseinfo['hidden'.$i]);
        unset($houseinfo['photo'.$i]);
      }
      if (isset($houseinfo['agent']) && !empty($houseinfo['agent']) && isset($houseinfo['aportrait']) && !empty($houseinfo['aportrait'])) {
        $houseinfo['portrait'] = $houseinfo['aportrait'];
      } else {
        $houseinfo['portrait'] = $houseinfo['oportrait'];
      }
      unset($houseinfo['oportrait']);
      unset($houseinfo['aportrait']);
      array_push($result['house'], $houseinfo);
      $k++;
    }
    return $result;
  }

//获取房屋信息
//$fields: 项目数组
//返回$result[table][n]
  function getHouse($table, $fields="*", $condition="") {
    if (empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "未指定房屋表";
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
      $result['message'] = "无法查询房屋表 ".$table;
      return $result;
    }
    $result['success'] = TRUE;
    unset($result[$table]);
    if ($sqlresult->num_rows == 0) {
      return $result;
    }
    $result[$table] = array();
    while ($houseinfo = $sqlresult->fetch_assoc()) {
      array_push($result[$table], $houseinfo);
    }
    return $result;
  }

//新建/更新房屋信息
//$fields: 项目数组
//返回$result[table]
  function updateHouse($table, $fields, $isnew=FALSE) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无更新房屋内容";
      return $result;
    }
    if ($isnew) {
      switch ($table) {
        case $this->housetable:
        case $this->roomtable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['edittime'] = $fields['createtime'];
          $fields['id'] = md5($fields['user'].$fields['createtime']);
          break;
        case $this->reviewtable:
          $fields['createtime'] = date('Y-m-d H:i:s');
          $fields['id'] = md5($fields['user'].$fields['createtime']);
          break;
      }
    } else {
      if ($table == $this->housetable || $table == $this->roomtable || $table == $this->reviewtable) {
        if (!isset($fields['id']) || $fields['id'] == "") {
          $result['success'] = FALSE;
          $result['message'] = "房屋、房间或评价未指定";
          return $result;
        }
        $condition = "id='".$fields['id']."'";
      }
      if ($table == $this->reviewtable && isset($fields['reply']) && !empty($fields['reply'])) {
        $fields['replytime'] = date('Y-m-d H:i:s');
      }
      if ($table == $this->mediatable) {
        if (!isset($fields['house']) || $fields['house'] == "") {
          $result['success'] = FALSE;
          $result['message'] = "房屋未指定";
          return $result;
        }
        $condition = "house='".$fields['house']."'";
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
        $result['message'] = "无法在表 ".$table." 中新建房屋信息";
      } else {
        $result['message'] = "无法更新房屋表 ".$table;
      }
      return $result;
    }
    $result['success'] = TRUE;
    $result[$table] = $fields;
    return $result;
  }

//删除房屋信息
//$fields: 项目数组
//返回$result['success']
  function deleteHouse($table, $fields) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无删除房屋内容";
      return $result;
    }
    $result['success'] = TRUE;
    $condition = "";
    if ($table == $this->roomtable || $table == $this->reviewtable) {
      if ((!isset($fields['id']) || empty($fields['id'])) && (isset($fields['house']) && !empty($fields['house']))) {
        $condition .= "house='".$fields['house']."'";
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
      $result['message'] = "无法从表 ".$table." 中删除房屋信息";
    }
    return $result;
  }

//更新价格日程信息
//$fields: 项目数组
//返回$result[table]
  function updatePriceSchedule($table, $fields, $remove=FALSE) {
    if (!is_array($fields) || empty($table)) {
      $result['success'] = FALSE;
      $result['message'] = "无更新价格或日程内容";
      return $result;
    }
    //查询日期重叠的记录
    $condition = "house='".$fields['house']."' AND room='".$fields['room']."' AND ".
                 "((fromdate<='".$fields['fromdate']."' AND todate>='".$fields['fromdate']."') OR ".
                 "(fromdate<='".$fields['todate']."' AND todate>='".$fields['todate']."') OR ".
                 "(fromdate>='".$fields['fromdate']."' AND todate<='".$fields['todate']."'))";
    $result = $this->getHouse($table, "*", $condition);
    if (is_null($result) || !$result['success']) {
      $result['success'] = FALSE;
      $result['message'] = "无法查询价格或日程内容";
      return $result;
    }
//    if ($table == $this->scheduletable && isset($result[$this->scheduletable][0]) && !$remove) {
//      $result['success'] = FALSE;
//      $result['message'] = "日程冲突";
//      return $result;
//    }

    $mysql = new PHPMySQL();
    if (isset($result[$table][0])) {
      foreach ($result[$table] as $recordno => $record) {
        if ($record['fromdate'] < $field['fromdate']) {
          if ($record['todate'] >= $field['fromdate'] && $record['todate'] <= $field['todate']) {
            $updatefields = $record;
            $updatefields['todate'] = date("Y-m-d", strtotime("-1 day", $field['fromdate']));
            if (isset($updatefields['edittime'])) {
              $updatefields['edittime'] = "NULL";
            }
            $condition = "house='".$record['house']."' AND room='".$record['room']."' AND fromdate='".$record['fromdate']."' AND todate='".$record['todate']."'";
            $sqlresult = $mysql->updateRows($table, $updatefields, $condition);
            if ($sqlresult === FALSE) {
              $result['success'] = FALSE;
              $result['message'] = "无法调整有冲突的价格信息";
              return $result;
            }
          }
          if ($record['todate'] > $field['todate']) {
            $updatefields = $record;
            $updatefields['todate'] = date("Y-m-d", strtotime("-1 day", $field['fromdate']));
            if (isset($updatefields['edittime'])) {
              $updatefields['edittime'] = "NULL";
            }
            $condition = "house='".$record['house']."' AND room='".$record['room']."' AND fromdate='".$record['fromdate']."' AND todate='".$record['todate']."'";
            $sqlresult = $mysql->updateRows($table, $updatefields, $condition);
            if ($sqlresult === FALSE) {
              $result['success'] = FALSE;
              $result['message'] = "无法更新有冲突的价格信息";
              return $result;
            }
            $updatefields['fromdate'] = date("Y-m-d", strtotime("+1 day", $field['todate']));
            $updatefields['todate'] = $record['todate'];
            $sqlresult = $mysql->insertRow($table, $updatefields);
            if ($sqlresult === FALSE) {
              $result['success'] = FALSE;
              $result['message'] = "无法新建调整后的价格信息";
              return $result;
            }
          }
        } else {
          if ($record['todate'] <= $field['todate']) {
            $condition = "house='".$record['house']."' AND room='".$record['room']."' AND fromdate='".$record['fromdate']."' AND todate='".$record['todate']."'";
            $sqlresult = $mysql->deleteRows($table, $condition);
            if ($sqlresult === FALSE) {
              $result['success'] = FALSE;
              $result['message'] = "无法删除有冲突的价格信息";
              return $result;
            }
          }
          if ($record['todate'] > $field['todate']) {
            $updatefields = $record;
            $updatefields['fromdate'] = date("Y-m-d", strtotime("+1 day", $field['todate']));
            if (isset($updatefields['edittime'])) {
              $updatefields['edittime'] = "NULL";
            }
            $condition = "house='".$record['house']."' AND room='".$record['room']."' AND fromdate='".$record['fromdate']."' AND todate='".$record['todate']."'";
            $sqlresult = $mysql->updateRows($table, $updatefields, $condition);
            if ($sqlresult === FALSE) {
              $result['success'] = FALSE;
              $result['message'] = "无法更改有冲突的价格信息";
              return $result;
            }
          }
        }
      }
    }
    if (!$remove) {
      $fields['createtime'] = date('Y-m-d H:i:s');
      $sqlresult = $mysql->insertRow($table, $fields);
      if ($sqlresult === FALSE) {
        $result['success'] = FALSE;
        $result['message'] = "无法更新价格/日程表 ".$table;
        return $result;
      }
    }
    $result['success'] = TRUE;
    $result[$table] = $fields;
    return $result;
  }

//上传照片和视频
//$para: 照片和视频信息数组
//返回$result['house_media']
  static public function uploadHouseMedia($para) {
    if (!is_array($para) || !isset($para['id']) || empty($para['id'])) {
      $result['success'] = FALSE;
      $result['message'] = "未指定房屋";
      return $result;
    }
    if ($para['isphoto'] == 1) {
      $path = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR ."media". DIRECTORY_SEPARATOR ."photos". DIRECTORY_SEPARATOR;
    } else {
      $path = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR ."media". DIRECTORY_SEPARATOR ."videos". DIRECTORY_SEPARATOR;
    }
    $utility = new Utility();
    $uploadresult = $utility->uploadFile($path, $para['filename']);
    if (is_null($uploadresult)) {
      $result['success'] = FALSE;
      $result['message'] = "无法上传照片或视频";
      return $result;
    } elseif ($uploadresult['success'] == FALSE) {
      return $result;
    }
    $filename = $uploadresult['uploadfile'];
    $myself = new self();
    if ($para['isphoto'] == 1 && $para['photono'] == 0) {
      $updatepara['id'] = $para['id'];
      $updatepara['photo'] = $filename;
      $result = $myself->updateHouse($myself->housetable, $updatepara);
      unset($updatepara['id']);
      unset($updatepara['photo']);
    }
    if ($para['isphoto'] == 1) {
      $updatepara['photo'.$para['photono']] = $filename;
    } else {
      $updatepara['video'] = $filename;
    }
    $updatepara['house'] = $para['id'];
    $result = $myself->updateHouse($myself->mediatable, $updatepara);
    return $result;
  }

//删除照片和视频
//$para: 照片和视频信息数组
  static public function removeHouseMedia($para) {
    if (!is_array($para) || !isset($para['isphoto']) || !is_bool($para['isphoto']) || !isset($para['filename']) || empty($para['filename'])) {
      $result['success'] = FALSE;
      $result['message'] = "未指定删除文件";
      return $result;
    }
    if ($para['isphoto']) {
      $path = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR ."media". DIRECTORY_SEPARATOR ."photos". DIRECTORY_SEPARATOR;
    } else {
      $path = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR ."media". DIRECTORY_SEPARATOR ."videos". DIRECTORY_SEPARATOR;
    }
    $file[0] = $para['filename'];
    $utility = new Utility();
    $result = $utility->deleteFile($path, $file);
    if (is_null($result)) {
      $result['success'] = FALSE;
      $result['message'] = "无法删除照片或视频";
    }
    return $result;
  }
}
?>