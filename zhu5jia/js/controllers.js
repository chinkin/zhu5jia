angular.module('z5j.controllers', [])

// *******************
// 首页
// *******************
.controller('LandingCtrl', function ($scope, $state, UserService, FileUploader, GeneralService) {
//图片轮播
  $scope.transInterval = 5000;
  $scope.noTransition = false;
  $scope.noWrapSlides = false;
  $scope.active = 0;
  var slides = $scope.slides = [];

  for (var i = 0; i < 3; i++) {
    slides.push({
      image: "../media/bing-" + i + ".jpg",
      id: i
    });
  }

//Popover组件(目的地历史)
  $scope.destinationPopover = {
    content: [],
    display: false,
    templateUrl: 'destinationPopoverTemplate.html'
  };
  function setSearchHistory() {
    if (typeof(window.localStorage['Dest'][0]) == "undefined" || window.localStorage['Dest'][0] == "") {
      $scope.destinationPopover.display = false;
      return;
    }
    $scope.destinationPopover.display = true;
    j = 0;
    for (i = 0; i < window.localStorage['Dest'].length; i++) {
      if (typeof(window.localStorage['Dest'][i]) != "undefined" && window.localStorage['Dest'][i] != "") {
        $scope.destinationPopover.content[j] = window.localStorage['Dest'][i];
        j++;
      }
    }
  }
  $scope.setDestination = function(destination) {
    $scope.destination = destination;
  };

//日期组件
  $scope.fromOptions = {
    customClass: getDayClass,
    showWeeks: true,
    maxDate: new Date(2028, 11, 30),
    minDate: new Date(),
    startingDay: 1
  };
  var formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
  $scope.format = formats[1];
  $scope.fromPopup = {
    opened: false
  };
  $scope.fromOpen = function() {
    $scope.fromPopup.opened = true;
  };
  var nextDay = new Date();
  nextDay.setDate(nextDay.getDate() + 1);
  $scope.toOptions = {
    customClass: getDayClass,
    showWeeks: true,
    maxDate: new Date(2028, 11, 31),
    minDate: new Date(),
    startingDay: 1
  };
  $scope.toOptions.minDate = nextDay;
  $scope.toPopup = {
    opened: false
  };
  $scope.toOpen = function() {
    $scope.toPopup.opened = true;
  };
  function getDayClass(data) {
    var date = data.date,
        mode = data.mode;
    return '';
  }
  $scope.$watch('fromDate', function(newValue, oldValue) {
    if (newValue === oldValue) { return; }
    nextDay = angular.copy(newValue);
    nextDay.setDate(newValue.getDate() + 1);
    $scope.toOptions.minDate = nextDay;
    if (typeof($scope.toDate) == "undefined" || newValue >= $scope.toDate) {
      $scope.toDate = nextDay;
    }
  });

//滚动侦测
  $('#community_scrollspy').scrollspy({
    animation: 'fade',
    delay: 50
  });

//加载数据，定义$scope变量
  var generalinformation = {countries: {all: '*'},
                            currencies: {all: '*'},
                            cities: {all: '*'}
                           };
  GeneralService.getGeneralInformation("Element", generalinformation).then(function (gidata) {
    if (gidata.success) {
      $scope.countries = gidata.countries;
      $scope.information = {users: { gender: 'M',
                                     nationality: $scope.countries[36].id,
                                     type: '0'
                                   }
                           };
      $scope.currentNationality = $scope.countries[36].id;
      $scope.nationality = $scope.countries[36].name;
    } else {
    }
  });

  $scope.me = {};
  $scope.location = "...";
  $scope.destination = "";
  $scope.images = [{address:"../media/bing-1.jpg"},
                   {address:"../media/bing-2.jpg"}
                  ];
  $scope.logonData = {email: "", password: ""};
  $scope.rememberAccount = false;
  $scope.registerData = {
    pushinformation: true
  };
  $scope.loggedOn = true;
  $scope.portraitImage = "../media/user_pic-225x225.png";
  $scope.mobilephone = {users: {insert: false},
                        user_verifications: {insert: false, isemail: false}
                       };
  $scope.verifications = {isemail: false
                         };
  $scope.agents = [{id: 1, name: '是', ename: 'am'},
                   {id: 0, name: '不是', ename: 'am not'}
                  ];
  $scope.currentAgent = $scope.agents[1].id;
  $scope.agent = $scope.agents[1].name;
  $scope.genders = GeneralService.getGeneral("General", "genders");
  if (typeof(window.localStorage['Dest']) == "undefined") {
    window.localStorage['Dest'] = [];
  }
  setSearchHistory();


//刷新处理
  $scope.me = UserService.getMe("");
  if (typeof($scope.me) != "undefined" && $scope.me.users.hasOwnProperty("id")) {
    $scope.loggedOn = true;
  } else {
    if (typeof(window.sessionStorage['User']) != "undefined" && typeof(window.sessionStorage['Token']) != "undefined" && window.sessionStorage['User'] != "" && window.sessionStorage['Token'] != "") {
      UserService.logon(window.sessionStorage['User'], "", window.sessionStorage['Token']).then(function (mydata) {
        if (mydata.success) {
          $scope.loggedOn = true;
//          if (typeof(window.sessionStorage['Location']) != "undefined" && window.sessionStorage['Location'] != "") {
//            $state.go(window.sessionStorage['Location']);
//          }
          $scope.me.users = mydata.users;
        } else {
          $scope.loggedOn = false;
          alert(mydata.message);
        }
      });
    } else {
      $scope.loggedOn = false;
    }
  }

//搜索房源
  $scope.startSearch = function () {
    if ($scope.destination == "") {
      alert("至少给个目的地吧");
    } else {
      for (i = 4; i > 0; i--) {
        if (typeof(window.localStorage['Dest'][i - 1]) != "undefined" && window.localStorage['Dest'][i - 1] != "") {
          window.localStorage['Dest'][i] = window.localStorage['Dest'][i - 1];
        }
      }
      window.localStorage['Dest'][0] = $scope.destination;
      $state.go('search', {cityTo:$scope.destination, timeFrom:$scope.destination.fromDate, timeTo:$scope.destination.toDate, guestNo:$scope.guestNo});
    }
  }

//登录和注册
  getLocalUser = function () {
    if (typeof(window.localStorage['Email']) != "undefined" && window.localStorage['Email'] != "") {
      $scope.logonData.email = window.localStorage['Email'];
      $scope.rememberAccount = true;
    }
  }
  $scope.checkLocalUser = function () {
    getLocalUser();
  }
  $scope.openLogin = function () {
    $('#signup').modal('close');
    $('#login').modal('open');
    getLocalUser();
  }
  $scope.openSignup = function () {
    $('#login').modal('close');
    $('#signup').modal('open');
  }

//用户登录
  var logon = function (logonEmail, logonPassword) {
//    alert(logonEmail + "/" + logonPassword);
    UserService.logon(logonEmail, logonPassword, "").then(function (mydata) {
      if (mydata.success) {
        $('#login').modal('close');
        $('#signup').modal('close');
        if ($scope.rememberAccount) {
          window.localStorage['Email'] = logonEmail;
//          window.localStorage['Password'] = logonPassword;
        } else {
          window.localStorage['Email'] = "";
//          window.localStorage['Password'] = "";
        }
        $state.go('user.dashboard');
      } else {
        alert(mydata.message);
      }
    });
  }
  $scope.doLogon = function (isValid) {
    if (isValid) {
      logon($scope.logonData.email, hex_md5($scope.logonData.password));
//      alert($scope.logonData.email + "|" + $scope.rememberAccount + "|" + hex_md5($scope.logonData.password));
    } else {
      alert("请输入用户名及密码");
    }
  }

//注册用户
  $scope.doRegister = function (isValid) {
    if (isValid && $scope.password == $scope.repeatpassword) {
//      alert($scope.registerData.lastname + "|" + $scope.registerData.pushinformation + "|" + hex_md5($scope.registerData.password));
      $scope.registerData.password = hex_md5($scope.password);
      UserService.register($scope.registerData).then(function (mydata) {
        if (mydata.success) {
          $('#signup').modal('close');
          window.localStorage['Email'] = $scope.registerData.email;

          UserService.logon(window.sessionStorage['User'], "", window.sessionStorage['Token']).then(function (mydata) {
            if (mydata.success) {
              $scope.loggedOn = true;
              $scope.me.users = mydata.users;
            } else {
              $scope.loggedOn = false;
              alert(mydata.message);
            }
          });

          $scope.background = 1;
          $scope.step = 0;
          $scope.subStep = 0;
          $('#newbie').modal({
            closeViaDimmer: false
          });
        } else {
          alert(mydata.message);
        }
      });
    } else {
      alert("重复输入的密码不一致");
    }
  }

//新手指引
//step1: portrait
//angular-file-upload
  var controller = $scope.controller = {
    isImage: function(item) {
      var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
      return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
    }
  };
  var uploader = $scope.uploader = new FileUploader ({
  });
  // FILTERS
  uploader.filters.push({
    name: 'customFilter',
    fn: function(item /*{File|FileLikeObject}*/, options) {
      return this.queue.length < 10;
    }
  });
  // CALLBACKS
  uploader.onWhenAddingFileFailed = function (item /*{File|FileLikeObject}*/, filter, options) {
    alert("上传的是文件吗？");
  };
   uploader.onAfterAddingFile = function (fileItem) {
    $scope.subStep = 10;
  };
  uploader.onSuccessItem = function (fileItem, response, status, headers) {
    if (status == 200 && response.success) {
//      $scope.countries = GeneralService.GetCountries();
      $scope.currentCountry = $scope.countries[36].id;
      $scope.mobilephone.users.phonecode = $scope.countries[36].phonecode;
      $scope.country = $scope.countries[36].name;
      $scope.step++;
      $scope.subStep = 0;
    }
  };
  uploader.onErrorItem = function (fileItem, response, status, headers) {
    alert(status);
  };
  $scope.uploadPortrait = function (fileItem) {
    UserService.uploadFile(fileItem);
  }
  $scope.changePortrait = function () {
    $scope.subStep = 0;
    uploader.queue[0].remove();
  }
//step2: mobile phone
  $scope.changeCountry = function (countryID) {
    phoneCountry = document.getElementById("phone_country"); 
    $scope.mobilephone.users.phonecode = $scope.countries[phoneCountry.selectedIndex].phonecode;
    $scope.country = $scope.countries[phoneCountry.selectedIndex].name;
  }
  $scope.doMobile = function (isValid) {
    $scope.mobilephone.users.id = $scope.me.users.id;
    $scope.mobilephone.user_verifications.user = $scope.me.users.id;
    UserService.updateMyInformation($scope.mobilephone).then(function (mydata) {
      if (mydata.success) {
        $scope.subStep = 1;
      } else {
        alert(mydata.message);
        $scope.currentGender = $scope.genders[0].id;
        $scope.gender = $scope.genders[0].name;
        $scope.step++;
        $scope.subStep = 0;
      }
    });
  }
  $scope.doVerify = function (isValid) {
    $scope.verifications.user = $scope.me.users.id;
    UserService.verifyMyInformation($scope.verifications).then(function (mydata) {
      if (!mydata.success) {
        alert(mydata.message);
      }
      $scope.currentGender = $scope.genders[0].id;
      $scope.gender = $scope.genders[0].name;
      $scope.step++;
      $scope.subStep = 0;
    });
  }
//step3: other information
  $scope.changeGender = function (genderID) {
    gender = document.getElementById("gender"); 
    $scope.gender = $scope.genders[gender.selectedIndex].name;
    $scope.information.users.gender = $scope.genders[gender.selectedIndex].id;
  }
  $scope.changeNationality = function (countryID) {
    nationality = document.getElementById("nationality"); 
    $scope.nationality = $scope.countries[nationality.selectedIndex].name;
    $scope.information.users.nationality = $scope.countries[nationality.selectedIndex].id;
  }
  $scope.changeAgent = function (agentID) {
    agent = document.getElementById("agent"); 
    $scope.agent = $scope.agents[agent.selectedIndex].name;
    $scope.information.users.type = $scope.agents[agent.selectedIndex].id;
  }
  $scope.doInformation = function (isValid) {
    $scope.information.users.id = $scope.me.users.id;
    UserService.updateMyInformation($scope.information).then(function (mydata) {
      if (!mydata.success) {
        alert(mydata.message);
      }
      $scope.step++;
    });
  }
  $scope.gotoNext = function () {
    $scope.background = 0;
    switch ($scope.step) {
      case 1:
        $scope.currentCountry = $scope.countries[36].id;
        $scope.mobilephone.users.phonecode = $scope.countries[36].phonecode;
        $scope.country = $scope.countries[36].name;
        $scope.subStep = 0;
        break;
      case 2:
        $scope.currentGender = $scope.genders[0].id;
        $scope.gender = $scope.genders[0].name;
        $scope.subStep = 0;
        break;
      case 3:
        $scope.subStep = 0;
        $scope.background = 2;
        break;
      case 4:
        $('#newbie').modal('close');
        $state.go('user.dashboard');
        break;
    }
    $scope.step++;
  }

//跳转用户界面
  $scope.gotoUser = function () {
    $state.go('user.dashboard');
  }
})

// *******************
// 查询结果页面
// *******************
.controller('SearchCtrl', function ($scope, $state, $stateParams) {
  $scope.destination = $stateParams.cityTo;
  $scope.country = "澳大利亚";
  $scope.state = "新南威尔士州";
  $scope.city = $stateParams.cityTo;
  $scope.roomNumber = "1000";

  var nowtemp = new Date();
  var now = new Date(nowtemp.getFullYear(), nowtemp.getMonth(), nowtemp.getDate(), 0, 0, 0, 0);
  var checkin = $('#checkin').datepicker({
    onRender: function(date) {
      return date.valueOf() < now.valueOf() ? 'am-disabled' : '';
    }
  }).on('changeDate.datepicker.amui', function(ev) {
    if (ev.date.valueOf() >= checkout.date.valueOf()) {
      var newdate = new Date(ev.date);
      newdate.setDate(newdate.getDate() + 1);
      checkout.setValue(newdate);
    } else {
      var newdate = new Date(checkout.date);
      checkout.setValue(newdate);
    }
    checkin.close();
    $('#checkout')[0].focus();
  }).data('amui.datepicker');
  var checkout = $('#checkout').datepicker({
    onRender: function(date) {
      return date.valueOf() <= checkin.date.valueOf() ? 'am-disabled' : '';
    }
  }).on('changeDate.datepicker.amui', function(ev) {
    checkout.close();
  }).data('amui.datepicker');
  if ($stateParams.timeFrom != $stateParams.timeTo) {
    checkin.setValue($stateParams.timeFrom);
    checkout.setValue($stateParams.timeTo);
  }

  $('#guest_no').find('option[value="' + $stateParams.guestNo + '"]').attr('selected', true);

  $('.am-slider').flexslider({
    controlNav:false,
    slideshow:false
  });

  $scope.goTop = function () {
    $('#search_result').scrollTop(0);
  }
})

// *******************
// 房间详细页面
//  $stateParams.groupId
// *******************
.controller('RoomCtrl', function ($scope, $state, $stateParams, $timeout) {

})

// *******************
// 用户页面
// 
// *******************
.controller('UserCtrl', function ($scope, $state, $timeout, UserService, GeneralService) {
  $scope.me = UserService.getMe("");
  if (!$scope.me.users.hasOwnProperty("id")) {
    $state.go('reload');
  } else {
    if (typeof(window.sessionStorage['Location']) != "undefined" && window.sessionStorage['Location'] != "" && window.sessionStorage['Location'].substr(0, 5) == "user.") {
      $scope.selectednavi = window.sessionStorage['Location'].substr(5);
    } else {
      $scope.selectednavi = "dashboard";
    }

//获取验证信息、通知、短信
    $scope.messages = "";
    $scope.newMessage = 0;
    $scope.notifications = "";
    $scope.newNotification = 0;
    $scope.newInformation = 0;
    $scope.hasHouse = false;
    $scope.hasTrip = false;
    var newmessages = new Array();
    var generalinformation = {messages: {id: '', title: '', fromuser: $scope.me.users.id, status: 1},
                              notification_users: {all: '*', user: $scope.me.users.id, status: 0}
                             };
    GeneralService.getInformation("User", generalinformation).then(function (gidata) {
      if (gidata.success) {
        if (typeof(gidata.messages) != "undefined" && gidata.messages != null) {
          if ($scope.messages == "") {
            $scope.messages = gidata.messages;
            newmessages = gidata.messages;
          } else {
            $scope.messages = newmessages.concat(gidata.messages);
          }
          $scope.newMessage = $scope.messages.length;
        }
        if (typeof(gidata.notification_users) != "undefined" && gidata.notification_users != null) {
          $scope.notifications = gidata.notification_users;
          $scope.newNotification = $scope.notifications.length;
        }
        $scope.newInformation = $scope.newMessage + $scope.newNotification;
      }
    });
    var generalinformation = {messages: {id: '', title: '', touser: $scope.me.users.id, status: 2}
                             };
    GeneralService.getInformation("User", generalinformation).then(function (gidata) {
      if (gidata.success) {
        if (typeof(gidata.messages) != "undefined" && gidata.messages != null) {
          if ($scope.messages == "") {
            $scope.messages = gidata.messages;
            newmessages = gidata.messages;
          } else {
            $scope.messages = newmessages.concat(gidata.messages);
          }
          $scope.newMessage = $scope.messages.length;
        }
        $scope.newInformation = $scope.newMessage + $scope.newNotification;
      }
    });

//获取房屋信息
    generalinformation = {houses: {id: '', owner: $scope.me.users.id, agent: $scope.me.users.id, status: 0}
                         };
    GeneralService.getInformation("House", generalinformation).then(function (gidata) {
      if (gidata.success) {
        if (typeof(gidata.houses) != "undefined" && gidata.houses != null && gidata.houses.length > 0) {
          $scope.hasHouse = true;
        }
      }
    });

//获取预订信息
    generalinformation = {bookings: {id: '', user: $scope.me.users.id}
                         };
    GeneralService.getInformation("Order", generalinformation).then(function (gidata) {
      if (gidata.success) {
        if (typeof(gidata.bookings) != "undefined" && gidata.bookings != null && gidata.bookings.length > 0) {
          $scope.hasTrip = true;
        }
      }
    });

    $scope.$on("Ctr1RemoveNotification", function (event, msg) {
      for (num in $scope.notifications) {
        if ($scope.notifications[num].notification == msg) {
          $scope.notifications.splice(num, 1);
          break;
        }
      }
      $scope.newNotification = $scope.notifications.length;
      $scope.newInformation = $scope.newMessage + $scope.newNotification;
//      $scope.$broadcast("Ctr1NameChangeFromParrent", msg);
    });

    $scope.$on("Ctr1RemoveMessage", function (event, msg) {
      for (num in $scope.messages) {
        if ($scope.messages[num].id == msg) {
          $scope.messages.splice(num, 1);
          break;
        }
      }
      $scope.newMessage = $scope.messages.length;
      $scope.newInformation = $scope.newMessage + $scope.newNotification;
//      $scope.$broadcast("Ctr1NameChangeFromParrent", msg);
    });
  }

  $scope.logoff = function () {
    UserService.logoff();
    $state.go('landing');
  }
})

// *******************
// 用户面板页面
// 
// *******************
.controller('UserDashboardCtrl', function ($scope, $state, $timeout, UserService, GeneralService) {
  window.sessionStorage['Location'] = "user.dashboard";
  $scope.me = UserService.getMe("");
  $scope.hasNotification = false;
  $scope.hasAD = false;
  $scope.newMessage = 0;
  if ($scope.me.users.hasOwnProperty("id")) {
    $scope.me.messages = [];
    var newmessages = new Array();
    var myinformation = {};
    myinformation.user_verifications = {all: '*', user: $scope.me.users.id};
    myinformation.notification_users = {all: '*', user: $scope.me.users.id, status: 0};
    myinformation.messages = {id: '', title: '', fromuser: $scope.me.users.id, status: 1};
    UserService.getMyInformation(myinformation).then(function (mydata) {
      if (mydata.success) {
        if (typeof(mydata.user_verifications) != "undefined" && mydata.user_verifications != null && mydata.user_verifications.length > 0) {
          $scope.me.user_verifications = mydata.user_verifications;
        }
        if (typeof(mydata.notification_users) != "undefined" && mydata.notification_users != null && mydata.notification_users.length > 0) {
          $scope.hasNotification = true;
          for (num in mydata.notification_users) {
            if (mydata.notification_users[num].url != "" || (mydata.notification_users[num].url1 == "" && mydata.notification_users[num].url2 == "" && mydata.notification_users[num].url3 == "")) {
              mydata.notification_users[num].content = mydata.notification_users[num].content.replace(/##/, mydata.notification_users[num].parameter1);
              mydata.notification_users[num].content = mydata.notification_users[num].content.replace(/##/, mydata.notification_users[num].parameter2);
              mydata.notification_users[num].content = mydata.notification_users[num].content.replace(/##/, mydata.notification_users[num].parameter3);
            } else {
              var contentparts = [];
              contentparts = mydata.notification_users[num].content.split("##");
              if (contentparts.length > 1) {
                mydata.notification_users[num].contents = [];
                mydata.notification_users[num].urls = [];
                mydata.notification_users[num].contents[0] = contentparts[0];
                mydata.notification_users[num].urls[0] = "";
                mydata.notification_users[num].contents[1] = mydata.notification_users[num].parameter1;
                mydata.notification_users[num].urls[1] = mydata.notification_users[num].url1;
                mydata.notification_users[num].contents[2] = contentparts[1];
                mydata.notification_users[num].urls[2] = "";
                if (contentparts.length > 2) {
                  mydata.notification_users[num].contents[3] = mydata.notification_users[num].parameter2;
                  mydata.notification_users[num].urls[3] = mydata.notification_users[num].url2;
                  mydata.notification_users[num].contents[4] = contentparts[2];
                  mydata.notification_users[num].urls[4] = "";                  
                }
                if (contentparts.length > 3) {
                  mydata.notification_users[num].contents[5] = mydata.notification_users[num].parameter3;
                  mydata.notification_users[num].urls[5] = mydata.notification_users[num].url3;
                  mydata.notification_users[num].contents[6] = contentparts[3];
                  mydata.notification_users[num].urls[6] = "";                  
                }
                mydata.notification_users[num].content = "";
              }
            }
          }
          $scope.me.notification_users = mydata.notification_users;
        }
        if (typeof(mydata.messages) != "undefined" && mydata.messages != null && mydata.messages.length > 0) {
          if ($scope.me.messages.length == 0 || newmessages.length == 0) {
            $scope.me.messages = mydata.messages;
            newmessages = mydata.messages;
          } else {
            $scope.me.messages = newmessages.concat(mydata.messages);
          }
          $scope.newMessage = $scope.me.messages.length;
        }
      }
    });
    var mymessages = {};
    mymessages.messages = {id: '', title: '', touser: $scope.me.users.id, status: 2};
    UserService.getMyInformation(mymessages).then(function (mydata) {
      if (mydata.success) {
        if (typeof(mydata.messages) != "undefined" && mydata.messages != null && mydata.messages.length > 0) {
          if ($scope.me.messages.length == 0 || newmessages.length == 0) {
            $scope.me.messages = mydata.messages;
            newmessages = mydata.messages;
          } else {
            $scope.me.messages = newmessages.concat(mydata.messages);
          }
          $scope.newMessage = $scope.me.messages.length;
        }
      }
    });
  }

  $scope.removeNotification = function (notificationID) {
    if (notificationID != "") {
      var myinformation = {};
      myinformation.notification_users = {notification: notificationID, user: $scope.me.users.id, status: 9};
      UserService.updateMyInformation(myinformation).then(function (mydata) {
        if (mydata.success) {
          for (num in $scope.me.notification_users) {
            if ($scope.me.notification_users[num].notification == notificationID) {
              $scope.me.notification_users.splice(num, 1);
              break;
            }
          }
          if ($scope.me.notification_users.length > 0) {
            $scope.hasNotification = true;
          } else {
            $scope.hasNotification = false;
          }
          $scope.$emit("Ctr1RemoveNotification", notificationID);
        }
      });
    }
  }
})

// *******************
// 用户收件箱页面
// $stateParams.typeID, $stateParams.messageID
// *******************
.controller('UserInboxCtrl', function ($scope, $state, $stateParams, $timeout, UserService, GeneralService) {
  window.sessionStorage['Location'] = "user.inbox";
  $scope.me = UserService.getMe("");
/* type: 0 -> 普通短信
         1 -> 预订booking
         2 -> 申请order
 status: 0 -> 未读短信
         1 -> 已读短信
         2 -> 加星短信
         9 -> 存档短信(删除)
*/
  $scope.types = [ {id: '0', name: '所有消息', account:0, noMessage: true},
                   {id: '1', name: '已加星标', account:0, noMessage: true},
                   {id: '2', name: '未读消息', account:0, noMessage: true},
                   {id: '3', name: '预订', account:0, noMessage: true},
                   {id: '4', name: '待处理申请', account:0, noMessage: true},
                   {id: '5', name: '已存档', account:0, noMessage: true}
                 ];
  $scope.currentType = "0";
  $scope.currentMessage = "";
  $scope.replyMessage = { replyShow: false,
                          replyContent: ""
                        };
  var oldtype = 0;

  getMessages = function (messageStatus) {
    var myinformation = {};
    if (typeof(messageStatus) == "undefined" || messageStatus == "") {
      myinformation.messages = {all: "*", fromuser: $scope.me.users.id, touser: $scope.me.users.id};
    } else {
      myinformation.messages = {all: "*", fromuser: $scope.me.users.id, touser: $scope.me.users.id, status: messageStatus};
    }
    UserService.getMyInformation(myinformation).then(function (mydata) {
      if (mydata.success) {
        $scope.types[0].account = 0;
        $scope.types[1].account = 0;
        $scope.types[2].account = 0;
        $scope.types[3].account = 0;
        $scope.types[4].account = 0;
        $scope.types[5].account = 0;
        if (typeof(mydata.messages) != "undefined" && mydata.messages != null && mydata.messages.length > 0) {
          $scope.types[0].account = mydata.messages.length;
          for (num in mydata.messages) {
            if (mydata.messages[num].mark == 3 ||
               (mydata.messages[num].mark == 1 && mydata.messages[num].fromuser == $scope.me.users.id) ||
               (mydata.messages[num].mark == 2 && mydata.messages[num].touser == $scope.me.users.id)) {
              $scope.types[1].account++;
              mydata.messages[num].marked = true;
            } else {
              mydata.messages[num].marked = false;
            }
            if ((mydata.messages[num].status == 1 && mydata.messages[num].fromuser == $scope.me.users.id) ||
                (mydata.messages[num].status == 2 && mydata.messages[num].touser == $scope.me.users.id)) {
              $scope.types[2].account++;
              mydata.messages[num].read = false;
            } else {
              mydata.messages[num].read = true;
            }
            if (mydata.messages[num].type == 1) {
              $scope.types[3].account++;
            }
            if (mydata.messages[num].type == 2) {
              $scope.types[4].account++;
            }
            if (mydata.messages[num].status == 9) {
              $scope.types[5].account++;
            }
          }
          $scope.me.messages = mydata.messages;
          $scope.types[0].noMessage = ($scope.types[0].account == 0) ? true : false;
          $scope.types[1].noMessage = ($scope.types[1].account == 0) ? true : false;
          $scope.types[2].noMessage = ($scope.types[2].account == 0) ? true : false;
          $scope.types[3].noMessage = ($scope.types[3].account == 0) ? true : false;
          $scope.types[4].noMessage = ($scope.types[4].account == 0) ? true : false;
          $scope.types[5].noMessage = ($scope.types[5].account == 0) ? true : false;
        }
      }
    });
  }
  getContents = function (messageID) {
    var myinformation = {};
    if (typeof(messageID) != "undefined" && messageID != "") {
      myinformation.message_contents = {all: "*", message: messageID};
      $scope.me.message_contents = {};
      $scope.currentMessage = "";
      UserService.getMyInformation(myinformation).then(function (mydata) {
        if (mydata.success) {
          if (typeof(mydata.message_contents) != "undefined" && mydata.message_contents != null && mydata.message_contents.length > 0) {
            $scope.me.message_contents = mydata.message_contents;
            $scope.currentMessage = mydata.message_contents[0].message;
          }
        }
      });
    }
  }

//初始化
  if ($scope.me.users.hasOwnProperty("id")) {
    if ($stateParams.typeID != null && $stateParams.typeID != "" && $stateParams.typeID >= 0 && $stateParams.typeID < 6) {
      $scope.currentType = $stateParams.typeID;
      oldtype = $scope.currentType;
    }
    var generalinformation = {};
    generalinformation.messages = {all: "*", fromuser: $scope.me.users.id, touser: $scope.me.users.id};
    if ($stateParams.itemID != null && $stateParams.itemID != "") {
      generalinformation.message_contents = {all: "*", message: $stateParams.itemID};
    }
    GeneralService.getInformation("User", generalinformation).then(function (gidata) {
      if (gidata.success) {
        if (typeof(gidata.messages) != "undefined" && gidata.messages != null && gidata.messages.length > 0) {
          var readnew = false;
          if (typeof(gidata.message_contents) != "undefined" && gidata.message_contents != null && gidata.message_contents.length > 0) {
            $scope.me.message_contents = gidata.message_contents;
            $scope.currentMessage = gidata.message_contents[0].message;
          }
          $scope.types[0].account = gidata.messages.length;
          for (num in gidata.messages) {
            if (gidata.messages[num].mark == 3 ||
               (gidata.messages[num].mark == 1 && gidata.messages[num].fromuser == $scope.me.users.id) ||
               (gidata.messages[num].mark == 2 && gidata.messages[num].touser == $scope.me.users.id)) {
              $scope.types[1].account++;
              gidata.messages[num].marked = true;
            } else {
              gidata.messages[num].marked = false;
            }
            if ((gidata.messages[num].status == 1 && gidata.messages[num].fromuser == $scope.me.users.id) ||
                (gidata.messages[num].status == 2 && gidata.messages[num].touser == $scope.me.users.id)) {
              $scope.types[2].account++;
              gidata.messages[num].read = false;
              if (gidata.messages[num].id == $scope.currentMessage) {
                gidata.messages[num].status = 0;
                readnew = true;
              }
            } else {
              gidata.messages[num].read = true;
            }
            if (gidata.messages[num].type == 1) {
              $scope.types[3].account++;
            }
            if (gidata.messages[num].type == 2) {
              $scope.types[4].account++;
            }
            if (gidata.messages[num].status == 9) {
              $scope.types[5].account++;
            }
          }
          $scope.me.messages = gidata.messages;
          $scope.types[0].noMessage = ($scope.types[0].account == 0) ? true : false;
          $scope.types[1].noMessage = ($scope.types[1].account == 0) ? true : false;
          $scope.types[2].noMessage = ($scope.types[2].account == 0) ? true : false;
          $scope.types[3].noMessage = ($scope.types[3].account == 0) ? true : false;
          $scope.types[4].noMessage = ($scope.types[4].account == 0) ? true : false;
          $scope.types[5].noMessage = ($scope.types[5].account == 0) ? true : false;

          if (readnew && $scope.currentMessage != "") {
            $scope.types[2].account--;
            var myinformation = {messages: {id: $scope.currentMessage, status: 0}
                                };
            UserService.updateMyInformation(myinformation).then(function (mydata) {
              if (mydata.success) {
                $scope.$emit("Ctr1RemoveMessage", mydata.messages.id);
              }
            });
          }
        }
      }
    });
  }

  $scope.changeType = function() {
    if ($scope.currentType == "0") {
      getMessages("");
    }
    if ($scope.currentType == 1 || (oldtype == 1 && $scope.currentType != 0)) {
      $scope.types[1].account = 0;
      for (num in $scope.me.messages) {
        if ($scope.me.messages[num].mark == 3 ||
           ($scope.me.messages[num].mark == 1 && $scope.me.messages[num].fromuser == $scope.me.users.id) ||
           ($scope.me.messages[num].mark == 2 && $scope.me.messages[num].touser == $scope.me.users.id)) {
          $scope.types[1].account++;
          $scope.me.messages[num].marked = true;
        } else {
          $scope.me.messages[num].marked = false;
        }
      }
    }
    if ($scope.currentType == 2 || (oldtype == 2 && $scope.currentType != 0)) {
      $scope.types[2].account = 0;
      for (num in $scope.me.messages) {
        if (($scope.me.messages[num].status == 1 && $scope.me.messages[num].fromuser == $scope.me.users.id) ||
            ($scope.me.messages[num].status == 2 && $scope.me.messages[num].touser == $scope.me.users.id)) {
          $scope.types[2].account++;
          $scope.me.messages[num].read = false;
        } else {
          $scope.me.messages[num].read = true;
        }
      }
    }    
    oldtype = $scope.currentType;
    $scope.types[0].noMessage = ($scope.types[0].account == 0) ? true : false;
    $scope.types[1].noMessage = ($scope.types[1].account == 0) ? true : false;
    $scope.types[2].noMessage = ($scope.types[2].account == 0) ? true : false;
    $scope.types[3].noMessage = ($scope.types[3].account == 0) ? true : false;
    $scope.types[4].noMessage = ($scope.types[4].account == 0) ? true : false;
    $scope.types[5].noMessage = ($scope.types[5].account == 0) ? true : false;
    $scope.currentMessage = "";
  }

//管理信息
  $scope.openMessage = function(message) {
    getContents(message.id);
    if ((message.status == 1 && message.fromuser == $scope.me.users.id) || (message.status == 2 && message.touser == $scope.me.users.id)) {
      var myinformation = {};
      myinformation.messages = {id: message.id, status: 0};
      $scope.types[2].account--;
      message.status = 0;
      UserService.updateMyInformation(myinformation).then(function (mydata) {
        if (mydata.success) {
          $scope.$emit("Ctr1RemoveMessage", mydata.messages.id);
        }
      });
    }
    $scope.replyMessage.replyShow = false;
  }
  $scope.closeMessage = function() {
    $scope.currentMessage = "";
  }
  $scope.showReply = function() {
    $scope.replyMessage.replyShow = true;
  }
  $scope.replyMessage = function(fromUser) {
    if ($scope.replyMessage.replyContent != "") {
      var myinformation = {};
      if (fromUser == $scope.me.users.id) {
        myinformation.messages = {id: $scope.currentMessage, status: 2};
      } else {
        myinformation.messages = {id: $scope.currentMessage, status: 1};
      }
      myinformation.message_contents = {message: $scope.currentMessage, user: $scope.me.users.id, content: $scope.replyMessage.replyContent, insert: true};
      UserService.updateMyInformation(myinformation).then(function (mydata) {
        if (mydata.success) {
          var newcontent = new Array();
          var generalinformation = {messages: {all: "*", id: mydata.messages.id}
                                   };
          GeneralService.getInformation("User", generalinformation).then(function (gidata) {
            if (gidata.success) {
              var found = false;
              for (i = $scope.me.messages.length; i > 1; i--) {
                if ($scope.me.messages[i - 1].id ==  gidata.messages[0].id) {
                  found = true;
                }
                if (found) {
                  $scope.me.messages[i - 1] = $scope.me.messages[i - 2];
                }
              }
              $scope.me.messages[0] = gidata.messages[0];
            }
          });
          newcontent[0] = mydata.message_contents;
          $scope.me.message_contents = newcontent.concat($scope.me.message_contents);
        }
      });
      $scope.replyMessage.replyShow = false;
      $scope.replyMessage.replyContent = "";
    }
  }
  $scope.markMessage = function(message) {
    if (typeof(message) == "object") {
      var myinformation = {};
      if ((message.mark == 1 && message.fromuser == $scope.me.users.id) ||
          (message.mark == 2 && message.touser == $scope.me.users.id)) {
        myinformation.messages = {id: message.id, mark: 0};
        $scope.types[1].account--;
        message.mark = 0;
      } else if ((message.mark == 0 && message.fromuser == $scope.me.users.id) ||
                 (message.mark == 3 && message.touser == $scope.me.users.id)) {
        myinformation.messages = {id: message.id, mark: 1};
        if (message.mark == 0) {
          $scope.types[1].account++;
        } else {
          $scope.types[1].account--;
        }
        message.mark = 1;
      } else if ((message.mark == 0 && message.touser == $scope.me.users.id) ||
                 (message.mark == 3 && message.fromuser == $scope.me.users.id)) {
        myinformation.messages = {id: message.id, mark: 2};
        if (message.mark == 0) {
          $scope.types[1].account++;
        } else {
          $scope.types[1].account--;
        }
        message.mark = 2;
      } else if ((message.mark == 2 && message.fromuser == $scope.me.users.id) ||
                 (message.mark == 1 && message.touser == $scope.me.users.id)) {
        myinformation.messages = {id: message.id, mark: 3};
        $scope.types[1].account++;
        message.mark = 3;
      }
      UserService.updateMyInformation(myinformation).then(function (mydata) {
        if (mydata.success) {
        }
      });
    }
  }
})

// *******************
// 用户房源页面
// 
// *******************
.controller('UserRoomCtrl', function ($scope, $state, $stateParams, $timeout, UserService, FileUploader, GeneralService) {
  window.sessionStorage['Location'] = "user.room";
  $scope.me = UserService.getMe("");
  $scope.selectedNavi = "myroom";
  $scope.newHouse = false;
  $scope.myHouses = {hasReleased: false, hasUnreleased: false};
  $scope.currentHouse = {};
  $scope.houseRooms = {};
  $scope.roomNo = 1;
  $scope.currentRoom = "";
  $scope.houseMedia = {};
  $scope.lastMedia = 0;
  $scope.photoNo = '';
  $scope.houseReviews = {};
  $scope.currentStatus = 0;
  $scope.countries = GeneralService.getGeneral("Element", "countries");
  $scope.currencies = GeneralService.getGeneral("Element", "currencies");
  $scope.provinces = {};
  $scope.cities = GeneralService.getGeneral("Element", "cities");
  $scope.districts = {};
  $scope.houseTypes = GeneralService.getGeneral("Element", "house_types");
  $scope.roomTypes = GeneralService.getGeneral("Element", "room_types");
  $scope.cancellationPolicies = GeneralService.getGeneral("Element", "cancellation_policies");
  $scope.generalTimes = GeneralService.getGeneral("Element", "times");
  $scope.checkinToTimes = new Array();
  $scope.checkoutFromTimes = new Array();
  $scope.bedTypes = GeneralService.getGeneral("Element", "bed_types");
  $scope.bathroomTypes = GeneralService.getGeneral("Element", "bathroom_types");
  $scope.mealTypes = GeneralService.getGeneral("Element", "meal_types");
  $scope.internetTypes = GeneralService.getGeneral("Element", "internet_types");
  $scope.tubTypes = GeneralService.getGeneral("Element", "tub_types");
  $scope.parkingTypes = GeneralService.getGeneral("Element", "parking_types");

//photos & video
//angular-file-upload
  var controller = $scope.controller = {
    isImage: function(item) {
      var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
      return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
    },
    isVideo: function(item) {
      var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
      return '|mp4|ogg|webm|'.indexOf(type) !== -1;
    }
  };
  var uploader = $scope.uploader = new FileUploader ({
  });
  // FILTERS
  uploader.filters.push({
    name: 'customFilter',
    fn: function(item /*{File|FileLikeObject}*/, options) {
      return this.queue.length < 10;
    }
  });
  // CALLBACKS
  uploader.onWhenAddingFileFailed = function (item /*{File|FileLikeObject}*/, filter, options) {
    alert("上传的是文件吗？");
  };
  uploader.onAfterAddingFile = function (fileItem) {
    if ($scope.isPhoto) {
      if (controller.isImage(fileItem._file)) {
        GeneralService.uploadFile(fileItem, $scope.currentHouse.id, 1, $scope.photoNo, $scope.houseMedia['photo' + $scope.photoNo]);
      } else {
        alert("上传的是照片吗？");
      }
    } else {
      if (controller.isVideo(fileItem._file)) {
        GeneralService.uploadFile(fileItem, $scope.currentHouse.id, 0, '', $scope.houseMedia.video);
      } else {
        alert("上传的是视频吗？");
      }
    }
  };
  uploader.onSuccessItem = function (fileItem, response, status, headers) {
    if (status == 200 && response.success) {
      if (response.house_media.hasOwnProperty('video')) {
        $scope.houseMedia['video'] = response.house_media['video'];
        $scope.houseMedia['fpvideo'] = "../../media/videos/" + $scope.houseMedia['video'];
      } else {
        if ($scope.houseMedia['photo' + $scope.photoNo] == '') {
          $scope.lastMedia = parseInt($scope.photoNo) + 1;
        }
        $scope.houseMedia['photo' + $scope.photoNo] = response.house_media['photo' + $scope.photoNo];
        if ($scope.photoNo == '0') {
          $scope.currentHouse.photo = $scope.houseMedia.photo0;
        }
      }
    }
    $scope.photoNo = '';
  };
  uploader.onErrorItem = function (fileItem, response, status, headers) {
    alert(status);
    $scope.photoNo = '';
  };
  $scope.uploadPhoto = function (photoNo) {
    $scope.isPhoto = true;
    $scope.photoNo = photoNo;
  }
  $scope.uploadVideo = function () {
    $scope.isPhoto = false;
    $scope.photoNo = "";
  }
  $scope.hidePhoto = function (photoNo) {
    var generalinformation = {};
    var hiddenNo = 'hidden' + photoNo;
    if ($scope.houseMedia[hiddenNo] == 0) {
      $scope.houseMedia[hiddenNo] = 1;
    } else {
      $scope.houseMedia[hiddenNo] = 0;
    }
    generalinformation.house_media = {insert: false, house: $scope.currentHouse.id};
    generalinformation.house_media[hiddenNo] = $scope.houseMedia[hiddenNo];
    GeneralService.updateInformation("House", generalinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
  }
  $scope.hideVideo = function () {
    var generalinformation = {};
    if ($scope.houseMedia['hidden'] == 0) {
      $scope.houseMedia['hidden'] = 1;
    } else {
      $scope.houseMedia['hidden'] = 0;
    }
    generalinformation.house_media = {insert: false, house: $scope.currentHouse.id, hidden: $scope.houseMedia['hidden']};
    GeneralService.updateInformation("House", generalinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
  }
  $scope.removePhoto = function (photoNo) {
    var fileinformation = {};
    var generalinformation = {};
    fileinformation = {isphoto: true, filename: $scope.houseMedia['photo' + photoNo]};
    GeneralService.removeFile("House", fileinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
    generalinformation.house_media = {insert: false, house: $scope.currentHouse.id};
    for (var i = parseInt(photoNo); i < $scope.lastMedia; i++) {
      if (i == 9) {
        generalinformation.house_media.photo9 = "";
        generalinformation.house_media.hidden9 = 0;
      } else {
        generalinformation.house_media['photo' + i] = $scope.houseMedia['photo' + (i + 1)];
        generalinformation.house_media['hidden' + i] = $scope.houseMedia['hidden' + (i + 1)];
        $scope.houseMedia['photo' + i] = $scope.houseMedia['photo' + (i + 1)];
        $scope.houseMedia['hidden' + i] = $scope.houseMedia['hidden' + (i + 1)];
      }
    }
    $scope.lastMedia--;
    GeneralService.updateInformation("House", generalinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
  }
  $scope.removeVideo = function () {
    var fileinformation = {};
    var generalinformation = {};
    fileinformation = {isphoto: false, filename: $scope.houseMedia['video']};
    GeneralService.removeFile("House", fileinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
    $scope.houseMedia['video'] = "";
    $scope.houseMedia['fpvideo'] = "";
    $scope.houseMedia['hidden'] = 0;
    generalinformation.house_media = {insert: false, house: $scope.currentHouse.id, video: '', hidden: 0};
    GeneralService.updateInformation("House", generalinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
  }

  classifyHouse = function (myHouses) {
    for (num in myHouses) {
      if (!$scope.myHouses.hasReleased && myHouses[num].status == 0) {
        $scope.myHouses.hasReleased = true;
      }
      if (!$scope.myHouses.hasUnreleased && myHouses[num].status > 0) {
        $scope.myHouses.hasUnreleased = true;
      }
    }
    if ($scope.myHouses.hasReleased) {
      $scope.currentStatus = 1;
    } else {
      $scope.currentStatus = 2;
    }
  }
  getHouses = function (houseStatus) {
    var generalinformation = {};
    if (typeof(houseStatus) == "undefined" || houseStatus == "") {
      generalinformation.houses = {all: "*", owner: $scope.me.users.id, agent: $scope.me.users.id};
    } else {
      generalinformation.houses = {all: "*", owner: $scope.me.users.id, agent: $scope.me.users.id, status: houseStatus};
    }
    GeneralService.getInformation("House", generalinformation).then(function (gidata) {
      if (gidata.success) {
        if (typeof(gidata.houses) != "undefined" && gidata.houses != null && gidata.houses.length > 0) {
          $scope.house.houses = gidata.houses;
          classifyHouse($scope.house.houses);
        }
      }
    });
  }
  getRooms = function (houseID) {
    $scope.houseRooms = {};
    var generalinformation = {};
    if (typeof(houseID) != "undefined" && houseID != "") {
      generalinformation.house_rooms = {all: "*", house: houseID};
      GeneralService.getInformation("House", generalinformation).then(function (gidata) {
        if (gidata.success) {
          if (typeof(gidata.house_rooms) != "undefined" && gidata.house_rooms != null && gidata.house_rooms.length > 0) {
            $scope.houseRooms = gidata.house_rooms;
            $scope.roomNo = 0;
            for (num in $scope.houseRooms) {
              if ($scope.houseRooms[num].status == 0) {
                $scope.roomNo++;
              }
            }
          }
        }
      });
    }
  }
  getHouseDetails = function (houseID) {
    $scope.houseMedia = {};
    $scope.houseMedia['video'] = "";
    $scope.houseMedia['fpvideo'] = "";
    $scope.houseReviews = {};
    $scope.lastMedia = 0;
    var generalinformation = {};
    if (typeof(houseID) != "undefined" && houseID != "") {
      generalinformation.house_media = {all: "*", house: houseID};
      generalinformation.house_reviews = {all: "*", house: houseID};
      GeneralService.getInformation("House", generalinformation).then(function (gidata) {
        if (gidata.success) {
          if (typeof(gidata.house_media) != "undefined" && gidata.house_media != null) {
            $scope.houseMedia = gidata.house_media[0];
            if (!$scope.houseMedia.hasOwnProperty('photo0') || $scope.houseMedia.photo0 == '') {
              $scope.lastMedia = 0;
            } else if ($scope.houseMedia.photo1 == '') {
              $scope.lastMedia = 1;
            } else if ($scope.houseMedia.photo2 == '') {
              $scope.lastMedia = 2;
            } else if ($scope.houseMedia.photo3 == '') {
              $scope.lastMedia = 3;
            } else if ($scope.houseMedia.photo4 == '') {
              $scope.lastMedia = 4;
            } else if ($scope.houseMedia.photo5 == '') {
              $scope.lastMedia = 5;
            } else if ($scope.houseMedia.photo6 == '') {
              $scope.lastMedia = 6;
            } else if ($scope.houseMedia.photo7 == '') {
              $scope.lastMedia = 7;
            } else if ($scope.houseMedia.photo8 == '') {
              $scope.lastMedia = 8;
            } else if ($scope.houseMedia.photo9 == '') {
              $scope.lastMedia = 9;
            } else {
              $scope.lastMedia = 10;
            }
            $scope.houseMedia['fpvideo'] = "";
            if ($scope.houseMedia['video'] != "") {
              $scope.houseMedia['fpvideo'] = "../../media/videos/" + $scope.houseMedia['video'];
            }
          }
          if (typeof(gidata.house_reviews) != "undefined" && gidata.house_reviews != null && gidata.house_reviews.length > 0) {
            $scope.houseReviews = gidata.house_reviews;
          }
        }
      });
    }
  }
  getBookings = function (houseID) {
  }
  getBookingDetails = function (bookingID) {
  }

  getCity = function (countryID, provinceID, getProvince) {
    var generalinformation = {};
    if (getProvince) {
      generalinformation.provinces = {all: "*", country: countryID};
      GeneralService.getInformation("Element", generalinformation).then(function (gidata) {
        if (gidata.success) {
          if (typeof(gidata.provinces) != "undefined" && gidata.provinces != null && gidata.provinces.length > 0) {
            $scope.provinces = gidata.provinces;
            $scope.currentHouse.province = gidata.provinces[0].id;
          } else {
            $scope.provinces = {};
            $scope.currentHouse.province = "";
          }
          var cityinformation = {};
          if ($scope.currentHouse.province == "") {
            cityinformation.cities = {all: "*", country: $scope.currentHouse.country};
          } else {
            cityinformation.cities = {all: "*", country: $scope.currentHouse.country, province: $scope.currentHouse.province};
          }
          GeneralService.getInformation("Element", cityinformation).then(function (citydata) {
            if (citydata.success) {
              if (typeof(citydata.cities) != "undefined" && citydata.cities != null && citydata.cities.length > 0) {
                $scope.cities = citydata.cities;
                $scope.currentHouse.city = citydata.cities[0].id;
                if (citydata.cities[0].hasdistricts == 1) {
                  var districtinformation = {};
                  districtinformation.districts = {all: "*", city: $scope.currentHouse.city};
                  GeneralService.getInformation("Element", districtinformation).then(function (districtdata) {
                    if (districtdata.success) {
                      $scope.districts = districtdata.districts;
                      $scope.currentHouse.district = districtdata.districts[0].id;
                    }
                  });
                } else {
                  $scope.districts = {};
                  $scope.currentHouse.district = "";
                }
              } else {
                $scope.cities = {};
                $scope.currentHouse.city = "";
              }
            }
          });
        }
      });
    } else {
      if (provinceID == "") {
        generalinformation.cities = {all: "*", country: countryID};
      } else {
        generalinformation.cities = {all: "*", country: countryID, province: provinceID};
      }
      GeneralService.getInformation("Element", generalinformation).then(function (gidata) {
        if (gidata.success) {
          if (typeof(gidata.cities) != "undefined" && gidata.cities != null && gidata.cities.length > 0) {
            $scope.cities = gidata.cities;
            $scope.currentHouse.city = gidata.cities[0].id;
            if (gidata.cities[0].hasdistricts == 1) {
              var districtinformation = {};
              districtinformation.districts = {all: "*", city: $scope.currentHouse.city};
              GeneralService.getInformation("Element", districtinformation).then(function (districtdata) {
                if (districtdata.success) {
                  $scope.districts = districtdata.districts;
                  $scope.currentHouse.district = districtdata.districts[0].id;
                }
              });
            } else {
              $scope.districts = {};
              $scope.currentHouse.district = "";
            }
          } else {
            $scope.cities = {};
            $scope.currentHouse.city = "";
          }
        }
      });
    }
  }
  getDistrict = function (cityID) {
    generalinformation.districts = {all: "*", city: cityID};
    GeneralService.getInformation("Element", generalinformation).then(function (gidata) {
      if (gidata.success) {
        if (typeof(gidata.districts) != "undefined" && gidata.districts != null && gidata.districts.length > 0) {
          $scope.districts = gidata.districts;
          $scope.currentHouse.district = gidata.districts[0].id;
        } else {
          $scope.districts = {};
          $scope.currentHouse.district = "";
        }
      }
    });
  }

//调整checkin-to和checkout-from时间
  adjustTime = function (timeID, isCheckin) {
    var standardTimes = GeneralService.getGeneral("Element", "times");
    var times = angular.copy(standardTimes);
    var timespart1 = times.slice(0, timeID - 1);
    var timespart2 = times.slice(timeID);
    var time0 = [{id: '0', name: ''}];

    if (isCheckin) {
      for (num in timespart1) {
        timespart1[num].name = timespart1[num].name + "(次日)";
      }
    } else {
      for (num in timespart2) {
        timespart2[num].name = timespart2[num].name + "(前日)";
      }
    }
    return time0.concat(timespart2.concat(timespart1));
  }

//初始化
  if ($scope.me.users.hasOwnProperty("id")) {
    if ($stateParams.naviID != null && $stateParams.naviID != "") {
      $scope.selectedNavi = $stateParams.naviID;
    }
    if ($scope.selectedNavi == "myroom") {
      var generalinformation = {};
      if ($stateParams.itemID != null && $stateParams.itemID != "") {
        generalinformation.house_rooms = {house: "", id: $stateParams.itemID};
      }
      $scope.house = GeneralService.getGeneral("House", "");
      if (!($scope.house.houses instanceof Array) || $scope.house.houses.length < 1) {
        generalinformation.houses = {all: "*", owner: $scope.me.users.id, agent: $scope.me.users.id};
      } else {
        classifyHouse($scope.house.houses);
      }
      if (generalinformation.hasOwnProperty("house_rooms") || generalinformation.hasOwnProperty("houses")) {
        GeneralService.getGeneralInformation("House", generalinformation).then(function (gidata) {
          if (gidata.success) {
            if (typeof(gidata.houses) != "undefined" && gidata.houses != null && gidata.houses.length > 0) {
              $scope.house.houses = gidata.houses;
              classifyHouse($scope.house.houses);
            }
            if (typeof(gidata.house_rooms) != "undefined" && gidata.house_rooms != null && gidata.house_rooms.length == 1) {
              for (num in $scope.house.houses) {
                if ($scope.house.houses[num].id == gidata.house_rooms[0].house) {
                  $scope.currentHouse = $scope.house.houses[num];
                  $scope.currentRoom = gidata.house_rooms[0].id;
                  break;
                }
              }
              if ($scope.currentRoom != "") {
                var myinformation = {};
                myinformation.house_rooms = {all: "*", house: $scope.currentHouse.id};
                GeneralService.getGeneralInformation("House", myinformation).then(function (mydata) {
                  if (mydata.success) {
                    if (typeof(mydata.house_rooms) != "undefined" && mydata.house_rooms != null && mydata.house_rooms.length > 0) {
                      $scope.house.house_rooms = mydata.house_rooms;
                    }
                  }
                });
              }
            }
          }
        });
      }
    }
  }

  $scope.changeNavigation = function (changeTo) {
    switch (changeTo) {
      case "mybooking":
        break;
      case "condition":
        break;
      case "myroom":
      default:
        $scope.currentHouse = {};
        $scope.currentRoom = "";
        getHouses("");
    }
    $scope.selectedNavi = changeTo;
  }
  $scope.changeStatus = function (changeTo) {
    if ($scope.currentStatus != changeTo) {
      $scope.currentStatus = changeTo;
    }
  }

//管理房子
  $scope.addHouse = function (selectedHouse) {
    if (typeof(selectedHouse) == "object" && selectedHouse.hasOwnProperty("id") && selectedHouse.id != "") {
      getRooms(selectedHouse.id);
      getHouseDetails(selectedHouse.id);
      $scope.currentHouse = selectedHouse;
      $scope.isowner = 1;
      if (selectedHouse.owner == "") {
        $scope.isowner = 0;
      }
      $scope.step = $scope.currentHouse.status;
    } else {
      $scope.currentHouse = {};
      $scope.currentHouse.id = "";
      $scope.currentHouse.status = 0;
      $scope.currentHouse.type = "0";
      $scope.currentHouse.rooms = 1;
      var hasProvinces = true;
      if (window.sessionStorage['Country'] != "undefined" && window.sessionStorage['Country'] != "") {
        $scope.currentHouse.country = window.sessionStorage['Country'];
        for (num in $scope.countries) {
          if ($scope.currentHouse.country == $scope.countries[num].id) {
            if ($scope.countries[num].hasprovinces == 0) {
              hasProvinces = false;
            }
            break;
          }
        }
      } else {
        $scope.currentHouse.country = "c0000037";
      }
      getCity($scope.currentHouse.country, "", hasProvinces);
      $scope.isowner = 1;
      $scope.step = 0;
    }
    $scope.checkinToTimes = adjustTime(selectedHouse.checkinfrom, true);
    $scope.checkoutFromTimes = adjustTime($scope.currentHouse.checkoutto, false);
    $scope.newHouse = true;
    $scope.substep = 0;
    $scope.currentSubstep = 0;
    $scope.houseRooms = new Array();
    $scope.houseRooms[0] = {name: '', beds: 1, bathroom: 1};
  }
  $scope.goNext = function (isStep) {
    if (isStep && $scope.step < 4) {
      $scope.step++;
      $scope.substep = 0;
    }
    if (!isStep) {
      $scope.substep++;
      $scope.currentSubstep++;
    }
  }
  $scope.goPrevious = function (isStep) {
    if (isStep && $scope.step > 0) {
      $scope.step--;
      $scope.substep = 0;
    }
    if (!isStep) $scope.substep--;
  }
  $scope.initRooms = function () {
    $scope.houseRooms.length = 0;
    var defaultRoom = GeneralService.getGeneral("Element", "default_room");
    if ($scope.currentHouse.type > 5) {
      $scope.houseRooms[0] = angular.copy(defaultRoom);
    } else {
      for (var i = 0; i < $scope.currentHouse.rooms; i++) {
        $scope.houseRooms[i] = angular.copy(defaultRoom);
        $scope.houseRooms[i].id = "room" + i;
      }
      $scope.currentRoom = "room0";
    }
  }
  $scope.changeCountry = function () {
    for (num in $scope.countries) {
      if ($scope.currentHouse.country == $scope.countries[num].id) {
        if ($scope.countries[num].hasprovinces == 1) {
          getCity($scope.currentHouse.country, "", true);
        } else {
          getCity($scope.currentHouse.country, "", false);
        }
        break;
      }
    }
  }
  $scope.changeProvince = function () {
    getCity($scope.currentHouse.country, $scope.currentHouse.province, false);
  }
  $scope.changeCity = function () {
    for (num in $scope.cities) {
      if ($scope.currentHouse.city == $scope.cities[num].id) {
        if ($scope.cities[num].hasdistricts == 1) {
          getDistrict($scope.currentHouse.city);
        }
        break;
      }
    }
  }
  $scope.editHouse = function (selectedHouse) {
    if (typeof(selectedHouse) == "object" && selectedHouse.hasOwnProperty("id") && selectedHouse.id != "") {
      getRooms(selectedHouse.id);
      getHouseDetails(selectedHouse.id);
      $scope.currentHouse = selectedHouse;
      if (selectedHouse.owner == "") {
        $scope.isowner = 2;
      } else {
        $scope.isowner = 1;
      }
      if ($scope.currentHouse.province == "") {
        getCity($scope.currentHouse.country, "", false);
      } else {
        getCity($scope.currentHouse.country, $scope.currentHouse.province, true);
      }
      $scope.checkinToTimes = adjustTime(selectedHouse.checkinfrom, true);
      $scope.checkoutFromTimes = adjustTime($scope.currentHouse.checkoutto, false);
    }
  }
  $scope.switchRoom = function (roomID) {
    $scope.currentRoom = roomID;
  }
  $scope.adjustCheckinTo = function () {
    $scope.checkinToTimes = adjustTime($scope.currentHouse.checkinfrom, true);
  }
  $scope.adjustCheckoutFrom = function () {
    $scope.checkoutFromTimes = adjustTime($scope.currentHouse.checkoutto, false);
  }
  $scope.saveHouse = function (houseForm) {
    var houseinformation = {};
    houseinformation.houses = $scope.currentHouse;
    houseinformation.houses['insert'] = false;
    GeneralService.updateInformation("House", houseinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
    houseForm.$setPristine();
  }
  $scope.hideHouse = function (toggle) {
    var houseinformation = {};
    houseinformation.houses = {id:'', status: '0', insert: false};
    houseinformation.houses['id'] = $scope.currentHouse.id;
    if (toggle == true) {
      houseinformation.houses['status'] = $scope.currentHouse.status = "9";
    } else {
      houseinformation.houses['status'] = $scope.currentHouse.status = "0";
    }
    houseinformation.houses['insert'] = false;
    GeneralService.updateInformation("House", houseinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
  }
  $scope.removeHouse = function () {
    var houseinformation = {};
    houseinformation.house_rooms = {house: $scope.currentHouse.id};
    houseinformation.houses = {id: $scope.currentHouse.id};
    GeneralService.deleteInformation("House", houseinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
    for (num in $scope.house.houses) {
      if ($scope.house.houses[num].id == $scope.currentHouse.id) {
        $scope.house.houses.splice(num, 1);
        break;
      }
    }
    $scope.currentHouse = {};
    $('#confirm').modal('close');
  }
  $scope.addRoom = function () {
    var roominformation = {};
    roominformation.house_rooms = angular.copy($scope.houseRooms[0]);
    roominformation.house_rooms.id = "";
    roominformation.house_rooms.name = "新房间";
    roominformation.house_rooms.status = "9";
    roominformation.house_rooms.insert = true;
    GeneralService.updateInformation("House", roominformation).then(function (gidata) {
      if (gidata.success) {
        $scope.houseRooms.push(gidata.house_rooms);
        $scope.currentRoom = gidata.house_rooms.id;
      }
    });

    var houseinformation = {};
    houseinformation.houses = {};
    houseinformation.houses.person = 0;
    for (num in $scope.houseRooms) {
      houseinformation.houses.person += parseInt($scope.houseRooms[num].person);
    }
    houseinformation.houses.person += parseInt($scope.houseRooms[0].person);
    houseinformation.houses.id = $scope.currentHouse.id;
    houseinformation.houses.insert = false;
    GeneralService.updateInformation("House", houseinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });

    $scope.currentHouse.person = houseinformation.houses.person;
  }
  $scope.saveRoom = function (roomForm, room) {
    if (room.breakfast < 2) {
      room.priceb = 0;
    }
    if (room.lunch < 2) {
      room.pricel = 0;
    }
    if (room.dinner < 2) {
      room.priced = 0;
    }
    var roominformation = {};
    roominformation.house_rooms = room;
    roominformation.house_rooms['insert'] = false;
    GeneralService.updateInformation("House", roominformation).then(function (gidata) {
      if (gidata.success) {
      }
    });

    var houseinformation = {};
    houseinformation.houses = {};
    houseinformation.houses.person = 0;
    for (num in $scope.houseRooms) {
      houseinformation.houses.person += parseInt($scope.houseRooms[num].person);
    }
    houseinformation.houses.id = $scope.currentHouse.id;
    houseinformation.houses.insert = false;
    GeneralService.updateInformation("House", houseinformation).then(function (gidata) {
      if (gidata.success) {
      }
    });

    $scope.currentHouse.person = houseinformation.houses.person;
    roomForm["roomform" + room.id].$setPristine();
  }
  $scope.hideRoom = function (toggle, room) {
    var roominformation = {};
    roominformation.house_rooms = {id:'', status: '0', insert: false};
    roominformation.house_rooms['id'] = room.id;
    if (toggle == true) {
      roominformation.house_rooms['status'] = room.status = "9";
      $scope.roomNo--;
    } else {
      roominformation.house_rooms['status'] = room.status = "0";
      $scope.roomNo++;
    }
    roominformation.house_rooms['insert'] = false;
    GeneralService.updateInformation("House", roominformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
  }
  $scope.removeRoom = function () {
    var roominformation = {};
    roominformation.house_rooms = {id: $scope.currentRoom};
    GeneralService.deleteInformation("House", roominformation).then(function (gidata) {
      if (gidata.success) {
      }
    });
    for (num in $scope.houseRooms) {
      if ($scope.houseRooms[num].id == $scope.currentRoom) {
        $scope.houseRooms.splice(num, 1);
        break;
      }
    }
    $scope.currentRoom = "";
    $('#confirm').modal('close');
  }
  $scope.checkDate = function (roomForm, availabilityDate) {
    if (roomForm.room[availabilityDate] == "" || (new Date(roomForm.room[availabilityDate]).getDate() != roomForm.room[availabilityDate].substring(roomForm.room[availabilityDate].length-2))) {
      roomForm.room[availabilityDate] = "0000-00-00";
    }
  }
  $scope.plusOne = function (inputObject, fileName) {
    inputObject[fileName]++;
  }
  $scope.minusOne = function (inputObject, fileName) {
    if (inputObject[fileName] > 1) {
      inputObject[fileName]--;
    }
  }

  $scope.openPopup = function (confirmAction, content) {
    $scope.confirmAction = confirmAction;
    switch (confirmAction) {
      case "deleteroom":
        $scope.confirmWhat = "删除'" + content + "'";
        $scope.alertContent = "您真的要删除这个房间吗？如果您只是不想让这个房间被找到，可以隐藏这个房间。";
        break;
      case "deletehouse":
        $scope.confirmWhat = "删除'" + content + "'";
        $scope.alertContent = "您真的要删除这个房屋吗？整间房屋和其中的房间都会被删除哦！如果您只是不想让这个房屋被找到，可以隐藏这个房屋。";
        break;
      default:
    }
    $('#confirm').modal('open');
  }
})

// *******************
// 用户行程页面
// 
// *******************
.controller('UserTripCtrl', function ($scope, $state, $stateParams, $timeout, UserService) {
  window.sessionStorage['Location'] = "user.trip";
  $scope.me = UserService.getMe("");
  if (typeof($scope.selectedNavi) == "undefined" || $scope.selectedNavi == "") {
    $scope.selectedNavi = "new";
  }
  $scope.norecord = true;

  var nowtemp = new Date();
  var now = new Date(nowtemp.getFullYear(), nowtemp.getMonth(), nowtemp.getDate(), 0, 0, 0, 0);
  var checkin = $('#checkin').datepicker({
    dateFormat: 'yyyy-mm-dd',
    onRender: function(date) {
      return date.valueOf() < now.valueOf() ? 'am-disabled' : '';
    }
  }).on('changeDate.datepicker.amui', function(ev) {
    if (ev.date.valueOf() >= checkout.date.valueOf()) {
      var newdate = new Date(ev.date);
      newdate.setDate(newdate.getDate() + 1);
      checkout.setValue(newdate);
    } else {
      var newdate = new Date(checkout.date);
      checkout.setValue(newdate);
    }
    checkin.close();
    $('#checkout')[0].focus();
  }).data('amui.datepicker');
  var checkout = $('#checkout').datepicker({
    dateFormat: 'yyyy-mm-dd',
    onRender: function(date) {
      return date.valueOf() <= checkin.date.valueOf() ? 'am-disabled' : '';
    }
  }).on('changeDate.datepicker.amui', function(ev) {
    checkout.close();
  }).data('amui.datepicker');

  $scope.changeNavigation = function (changeTo) {
    switch (changeTo) {
      case "previous":
        break;
      case "new":
      default:
    }
    $scope.selectedNavi = changeTo;
  }
})

// *******************
// 用户个人资料页面
// 
// *******************
.controller('UserMeCtrl', function ($scope, $state, $stateParams, $timeout, UserService) {
  window.sessionStorage['Location'] = "user.me";
  $scope.me = UserService.getMe("");
  if (typeof($scope.selectedNavi) == "undefined" || $scope.selectedNavi == "") {
    $scope.selectedNavi = "mydata";
  }
//  $scope.me.mobile = "";
  $scope.mobilestep = 0;
  $scope.countrycode = "+86";
  $scope.addressstep = 0;
  $scope.editcontact = false;
  $scope.me.contact = "";
/*
  var datebegin = new Date(1899, 11, 31, 0, 0, 0, 0);
  var dateend = new Date(2000, 0, 1, 0, 0, 0, 0);
  var birthday = $('#birthday').datepicker({
    dateFormat: 'yyyy-mm-dd',
    minView: 'year',
    onRender: function(date) {
      if (date.valueOf() < datebegin.valueOf() || date.valueOf() > dateend.valueOf()) {
        return 'am-disabled';
      } else {
        return '';
      }
    }
  }).on('changeDate.datepicker.amui', function(ev) {
    birthday.close();
  }).data('amui.datepicker');
*/
  $scope.changeNavigation = function (changeTo) {
    switch (changeTo) {
      case "photovideo":
        break;
      case "verification":
        break;
      case "review":
        break;
      case "mydata":
      default:
    }
    $scope.selectedNavi = changeTo;
  }

  $scope.editMobile = function () {
    $scope.mobilestep = 1;
  }
  $scope.sendCode = function () {
    $scope.mobilestep = 2;
  }
  $scope.verifyMobile = function () {
    $scope.mobilestep = 2;
  }

  $scope.editAddress = function () {
    $scope.addressstep = 1;
  }

  $scope.editContact = function () {
    $scope.editcontact = true;
  }
})

// *******************
// 用户账号页面
// 
// *******************
.controller('UserAccountCtrl', function ($scope, $state, $stateParams, $timeout, UserService) {
  window.sessionStorage['Location'] = "user.account";
  $scope.me = UserService.getMe("");
  if (typeof($scope.selectedNavi) == "undefined" || $scope.selectedNavi == "") {
    $scope.selectedNavi = "notification";
  }
  $scope.account = {
    pushflag: false,
  };
/*  $scope.mobilestep = 0;
  $scope.countrycode = "+86";
  $scope.addressstep = 0;
  $scope.editcontact = false;
  $scope.me.contact = "";
*/

  $scope.changeNavigation = function (changeTo) {
    switch (changeTo) {
      case "payment":
        break;
      case "transaction":
        break;
      case "security":
        break;
      case "setting":
        break;
      case "notification":
      default:
    }
    $scope.selectedNavi = changeTo;
  }
})

// *******************
// 重新加载页面
// 
// *******************
.controller('ReloadCtrl', function ($scope, $state, $timeout, UserService, GeneralService) {
  var progress = $.AMUI.progress;
  progress.start();
  if (typeof(window.sessionStorage['User']) != "undefined" && typeof(window.sessionStorage['Token']) != "undefined" && window.sessionStorage['User'] != "" && window.sessionStorage['Token'] != "") {
    UserService.logon(window.sessionStorage['User'], "", window.sessionStorage['Token']).then(function (userdata) {
      if (userdata.success) {
        if (typeof(window.sessionStorage['Location']) != "undefined" && window.sessionStorage['Location'] != "") {
          var generalinformation = {countries: {all: '*'},
                                    currencies: {all: '*'}
                                   };
          GeneralService.getGeneralInformation("Element", generalinformation).then(function (gidata) {
            if (gidata.success) {
            }
            $state.go(window.sessionStorage['Location']);
          });
        } else {
          $state.go('landing');
        }
      } else {
        alert(userdata.message);
		    $state.go('landing');
      }
	    progress.done();
    });
  } else {
    progress.done();
	  $state.go('landing');
  }
})