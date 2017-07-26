angular.module('z5j.services', [])

.provider('myData', function () {
  var me = {users:{}, user_verifications:{}, user_reviews:{}, user_referemces:{}, user_contacts:{},
            notification_users:{}, messages:{}, message_contents:{}};
  var user = {users:{}, user_verifications:{}, user_reviews:{}, user_referemces:{}};
  var account = {accounts:{}, account_payments:{}, account_logon:{}};
  var house = {houses:{}, house_rooms:{}, house_media:{}, house_reviews:{}, prices:{}, schedules:{}};
  var booking = {bookings:{}, booking_details:{}};
  var order = {bookings:{}, booking_details:{}};
  var general = {countries:{},
                 currencies:{},
                 cities:{},
                 house_types: [{id: '0', name: '公寓'},
                               {id: '1', name: '别墅'},
                               {id: '2', name: '平房'},
                               {id: '6', name: '整间公寓'},
                               {id: '7', name: '整栋别墅'},
                               {id: '8', name: '小木屋'}
                              ],
                 room_types: [{id: '0', name: '独立房间'},
                              {id: '1', name: '起居室(双人床)'},
                              {id: '2', name: '起居室(单人床)'},
                              {id: '3', name: '起居室(沙发)'},
                              {id: '4', name: '通铺(一个床位)'}
                             ],
                 cancellation_policies: [{id: '0', name: '灵活 (提前1天全额退款)'},
                                         {id: '1', name: '适中 (提前5天全额退款)'},
                                         {id: '2', name: '严格 (提前1周50%退款)'},
                                         {id: '3', name: '极严 (提前30天50%退款)'},
                                         {id: '4', name: '长期住宿'}
                                        ],
                 bed_types: [{id: '1', name: '1张单人床'},
                             {id: '2', name: '1张双人床'},
                             {id: '3', name: '2张单人床'},
                             {id: '4', name: '1张单人床+1张双人床'},
                             {id: '5', name: '2张双人床'},
                             {id: '6', name: '4人通铺'},
                             {id: '7', name: '6人通铺'},
                             {id: '8', name: '8人通铺'},
                             {id: '9', name: '沙发床/床垫'}
                            ],
                 bathroom_types: [{id: '0', name: '无卫生间'},
                                  {id: '1', name: '独立卫生间'},
                                  {id: '2', name: '公用卫生间'},
                                  {id: '3', name: '公用洗手间无浴室'},
                                  {id: '4', name: '独立洗手间公用浴室'},
                                  {id: '5', name: '公用洗手间独立浴室'}
                                 ], 
                 meal_types: [{id: '0', name: '无'},
                              {id: '1', name: '已含'},
                              {id: '2', name: '收费'},
                             ],
                 internet_types: [{id: '0', name: '无网络'},
                                  {id: '1', name: 'WIFI'},
                                  {id: '2', name: '有线网络'},
                                  {id: '3', name: 'WIFI(收费)'},
                                  {id: '4', name: '有线网络(收费)'},
                                  {id: '5', name: 'WIFI+有线网络'}
                                 ],
                 tub_types: [{id: '0', name: '无浴缸'},
                             {id: '1', name: '有浴缸'},
                             {id: '2', name: '按摩浴缸'}
                            ],
                 parking_types: [{id: '0', name: '无停车位'},
                                 {id: '1', name: '有停车位'},
                                 {id: '2', name: '有停车位(收费)'},
                                ],
                 times: [{id: '1', name: '1时'},
                         {id: '2', name: '2时'},
                         {id: '3', name: '3时'},
                         {id: '4', name: '4时'},
                         {id: '5', name: '5时'},
                         {id: '6', name: '6时'},
                         {id: '7', name: '7时'},
                         {id: '8', name: '8时'},
                         {id: '9', name: '9时'},
                         {id: '10', name: '10时'},
                         {id: '11', name: '11时'},
                         {id: '12', name: '12时'},
                         {id: '13', name: '13时'},
                         {id: '14', name: '14时'},
                         {id: '15', name: '15时'},
                         {id: '16', name: '16时'},
                         {id: '17', name: '17时'},
                         {id: '18', name: '18时'},
                         {id: '19', name: '19时'},
                         {id: '20', name: '20时'},
                         {id: '21', name: '21时'},
                         {id: '22', name: '22时'},
                         {id: '23', name: '23时'},
                         {id: '24', name: '24时'}
                        ],
                 genders:[{id: 'M', name: '先生', ename: 'Mr.'},
                          {id: 'F', name: '女士', ename: 'Ms.'}
                         ],
                 default_room:{id: '',
                               house: '',
                               name: '',
                               person: '1',
                               child: '0',
                               infant: '0',
                               type: '0',
                               beds: '1',
                               bathroom: '1',
                               size: '0',
                               breakfast: '0',
                               priceb: '0',
                               lunch: '0',
                               pricel: '0',
                               dinner: '0',
                               priced: '0',
                               kitchen: '0',
                               internet: '1',
                               TV: '1',
                               essentials: '1',
                               shampoo: '1',
                               hairdryer: '1',
                               tub: '0',
                               washer: '0',
                               dryer: '0  ',
                               hangers: '1',
                               iron: '0',
                               parking: '0',
                               aircon: '1',
                               heating: '1',
                               smoking: '0',
                               kids: '1',
                               pet: '0',
                               events: '0',
                               wheelchair: '0',
                               elevator: '1',
                               fireplace: '0',
                               intercom: '1',
                               doorman: '0',
                               pool: '0',
                               gym: '0',
                               workspace: '0',
                               price: '0',
                               extrapeople: '0',
                               extrachild: '0',
                               extrainfant: '0',
                               cleaning: '0',
                               currency: '0',
                               comment: '0',
                               smoke: '0',
                               carbon: '0',
                               aidkit: '0',
                               extinguisher: '0',
                               roomlock: '1',
                               safetycard: '0',
                               safe: '0',
                               availability: '1',
                               specialavailability1: '0',
                               availabilityfrom1: '',
                               availabilityto1: '',
                               specialavailability2: '0',
                               availabilityfrom2: '',
                               availabilityto2: '',
                               specialavailability3: '0',
                               availabilityfrom3: '',
                               availabilityto3: '',
                               longterm: '0',
                               status: ''}
                };
  var z5j_url = 'http://test.zhu5jia.com';
//  var z5j_url = 'http://www.zhu5jia.com';
  this.$get = function($http, $q) {
    return { me: me, account: account, house: house, booking: booking, order: order,
             general: general, user: user,
      getData: function (paras) {
        var deferred = $q.defer();
        $http({method: 'POST',
               url: z5j_url + '/php/Controller.php',
               data: paras
//               headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function successCallback(response) {
//                  alert("success:" + response.status);
                  deferred.resolve(response.data, response.status);
                },
                function errorCallback(response) {
                  var message = "HTTP无法连接服务器, ";
                  if (response.status) {
                    message += "状态: " + response.status;
                  } else {
                    message += "状态未知";
                  }
                  alert(message);
                  deferred.reject(response.data, response.status);
                });
        return deferred.promise;
      },
      setMe: function (table, contents) {
        this.me[table] = contents;
      },
      resetMe: function () {
        this.me.users = "";
        this.me.user_verifications = "";
        this.me.user_reviews = "";
        this.me.user_referemces = "";
        this.me.user_contacts = "";
        this.me.notification_users = "";
        this.me.messages = "";
        this.me.message_contents = "";
      },
      setUser: function (table, contents) {
        this.user[table] = contents;
      },
      setAccount: function (table, contents) {
        this.account[table] = contents;
      },
      setHouse: function (table, contents) {
        this.house[table] = contents;
      },
      setBooking: function (table, contents) {
        this.booking[table] = contents;
      },
      setOrder: function (table, contents) {
        this.order[table] = contents;
      },
      setGeneral: function (table, contents) {
        this.general[table] = contents;
//        for (count in contents) {
//          this.general[table][contents[count]['id']] = contents[count];
//        }
      }
    };
  };
})

.factory('UserService', function ($q, myData) {
  return {
    logon: function (logonUser, logonPassword, logonToken) {
      var deferred = $q.defer();
      var paras;
      if (logonPassword != "") {
        paras = {contclass: 'User',
                 act: 'logon',
                 para: [{email: logonUser, password: logonPassword}]
                };
      } else {
        paras = {contclass: 'User',
                 act: 'logon',
                 para: [{id: logonUser, token: logonToken}]
                };
      }
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
          for (table in data) {
            if (table == "success") {
              continue;
            }
            if (table == "token") {
              window.sessionStorage['Token'] = data[table];
              continue;
            }
            if (table == "location") {
              if (data[table] == "未知") {
                window.sessionStorage['Country'] = "c0000037";
              } else {
                var myLocation = new Array();
                myLocation = data[table].split("-");
                var countries = myData.general.countries;
                for (num in countries) {
                  if (countries[num]['name'] == myLocation[0]) {
                    window.sessionStorage['Country'] = countries[num]['id'];
                    break;
                  }
                }
              }
              continue;
            }
            if (typeof(data[table]) != "undefined" && data[table] != null) {
              myData.setMe(table, data[table]);
            }
          }
          window.sessionStorage['User'] = data['users'].id;
        }
        deferred.resolve(data, status);
      },
      function (data, status) {
        deferred.reject(data, status);
      });
      return deferred.promise;
    },
    logoff: function () {
      window.sessionStorage['User'] = "";
      window.sessionStorage['Token'] = "";
      myData.resetMe();
    },
    register: function (registerData) {
      var deferred = $q.defer();
      var paras = {contclass: 'User',
                   act: 'register',
                   para: [registerData]
                  };
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
          myData.setMe("users", data.users);
          window.sessionStorage['Token'] = data.token;
          window.sessionStorage['User'] = data.users.id;
        }
        deferred.resolve(data, status);
      },
      function (data, status) {
        deferred.reject(data, status);
      });
      return deferred.promise;
    },
    getMyInformation: function (selectData) {
      var deferred = $q.defer();
      var paras = {contclass: 'User',
                   act: 'getUserInformation',
                   para: [selectData]
                  };
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
          for (table in data) {
            if (table == "success") {
              continue;
            }
            if (typeof(data[table]) != "undefined" && data[table] != null) {
              myData.setMe(table, data[table]);
            }
          }
        }
        deferred.resolve(data, status);
      });
      return deferred.promise;
    },
    updateMyInformation: function (updateData) {
      var deferred = $q.defer();
      var paras = {contclass: 'User',
                   act: 'updateUserInformation',
                   para: [updateData]
                  };
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
          var me = myData.me;
          for (table in data) {
            if (table == "success") {
              continue;
            }
            if (typeof(data[table]) != "undefined" && data[table] != null) {
              for (field in data[table]) {
                me[table][field] = data[table][field];
              }
              myData.setMe(table, me[table]);
            }
          }
        }
        deferred.resolve(data, status);
      },
      function (data, status) {
        deferred.reject(data, status);
      });
      return deferred.promise;
    },
    uploadFile: function (uploaderItem) {
      var me = myData.me;
      uploaderItem.url = myData.z5j_url + '/php/Controller.php?action=uploadPortrait&id=' + me.users.id + '&filename=' + me.users.portrait;
      uploaderItem.upload();
      uploaderItem.onSuccess = function(response, status, headers) {
        if (status == 200 && response.success) {
          me.users.portrait = response.users.portrait;
          myData.setMe("users", me.users);
        }
      }
    },
    verifyMyInformation: function (updateData) {
      var deferred = $q.defer();
      var paras = {contclass: 'User',
                   act: 'verifyUser',
                   para: [updateData]
                  };
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
          var me = myData.me;
          if (typeof(data['user_verifications']) != "undefined" && data['user_verifications'] != null) {
            for (field in data['user_verifications']) {
              me['user_verifications'][field] = data['user_verifications'][field];
            }
          }
          myData.setMe("user_verifications", me['user_verifications']);
        }
        deferred.resolve(data, status);
      },
      function (data, status) {
        deferred.reject(data, status);
      });
      return deferred.promise;
    },
    getMe: function (table) {
      if (table == "") {
        return myData.me;
      } else {
        return myData.me[table];
      }
    },
    resetMe: function () {
      myData.resetMe();
    }
  }
})

.factory('GeneralService', function ($q, myData) {
  return {
    getGeneralInformation: function (className, selectData) {
      var deferred = $q.defer();
      var paras = {contclass: className,
                   act: 'get' + className + 'Information',
                   para: [selectData]
                  };
      if (className == "Order") {
        paras = {contclass: 'Booking',
                 act: 'getbookingInformation',
                 para: [selectData]
                };
      }
      if (className == "Account") {
        paras = {contclass: 'User',
                 act: 'getAccountInformation',
                 para: [selectData]
                };
      }
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
          for (table in data) {
            if (table == "success") {
              continue;
            }
            if (typeof(data[table]) != "undefined" && data[table] != null) {
              switch (className) {
                case 'User':
                  myData.setUser(table, data[table]);
                  break;
                case 'Account':
                  myData.setAccount(table, data[table]);
                  break;
                case 'House':
                  myData.setHouse(table, data[table]);
                  break;
                case 'Booking':
                  myData.setBooking(table, data[table]);
                  break;
                case 'Order':
                  myData.setOrder(table, data[table]);
                  break;
                default:
                  myData.setGeneral(table, data[table]);
              }
            }
          }
        }
        deferred.resolve(data, status);
      });
      return deferred.promise;
    },
    getInformation: function (className, selectData) {
      var deferred = $q.defer();
      var paras = {contclass: className,
                   act: 'get' + className + 'Information',
                   para: [selectData]
                  };
      if (className == "Order") {
        paras = {contclass: 'Booking',
                 act: 'getbookingInformation',
                 para: [selectData]
                };
      }
      if (className == "Account") {
        paras = {contclass: 'User',
                 act: 'getAccountInformation',
                 para: [selectData]
                };
      }
      myData.getData(paras).then(function (data, status) {
        deferred.resolve(data, status);
      });
      return deferred.promise;
    },
    getGeneral: function (className, table) {
      switch (className) {
        case 'User':
          if (table == "") {
            return myData.user;
          } else {
            return myData.user[table];
          }
          break;
        case 'Account':
          if (table == "") {
            return myData.account;
          } else {
            return myData.account[table];
          }
          break;
        case 'House':
          if (table == "") {
            return myData.house;
          } else {
            return myData.house[table];
          }
          break;
        case 'Booking':
          if (table == "") {
            return myData.booking;
          } else {
            return myData.booking[table];
          }
          break;
        case 'Order':
          if (table == "") {
            return myData.order;
          } else {
            return myData.order[table];
          }
          break;
        default:
          if (table == "") {
            return myData.general;
          } else {
            return myData.general[table];
          }
      }
    },
    updateGeneralInformation: function (className, updateData) {
      var deferred = $q.defer();
      var paras = {contclass: className,
                   act: 'update' + className + 'Information',
                   para: [updateData]
                  };
      if (className == "Order") {
        paras = {contclass: 'Booking',
                 act: 'updateBookingInformation',
                 para: [updateData]
                };
      }
      if (className == "Account") {
        paras = {contclass: 'User',
                 act: 'updateAccountInformation',
                 para: [updateData]
                };
      }
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
          var general;
          switch (className) {
            case 'User':
              general = myData.user;
              break;
            case 'Account':
              general = myData.account;
              break;
            case 'House':
              general = myData.house;
              break;
            case 'Booking':
              general = myData.booking;
              break;
            case 'Order':
              general = myData.order;
              break;
            default:
              general = myData.general;
          }
          for (table in data) {
            if (table == "success") {
              continue;
            }
            if (typeof(data[table]) != "undefined" && data[table] != null) {
              for (field in data[table]) {
                general[table][field] = data[table][field];
              }
              switch (className) {
                case 'User':
                  myData.setUser(table, general[table]);
                  break;
                case 'Account':
                  myData.setAccount(table, general[table]);
                  break;
                case 'House':
                  myData.setHouse(table, general[table]);
                  break;
                case 'Booking':
                  myData.setBooking(table, general[table]);
                  break;
                case 'Order':
                  myData.setOrder(table, general[table]);
                  break;
                default:
                  myData.setGeneral(table, general[table]);
              }
            }
          }
        }
        deferred.resolve(data, status);
      },
      function (data, status) {
        deferred.reject(data, status);
      });
      return deferred.promise;
    },
    updateInformation: function (className, updateData) {
      var deferred = $q.defer();
      var paras = {contclass: className,
                   act: 'update' + className + 'Information',
                   para: [updateData]
                  };
      if (className == "Order") {
        paras = {contclass: 'Booking',
                 act: 'updateBookingInformation',
                 para: [updateData]
                };
      }
      if (className == "Account") {
        paras = {contclass: 'User',
                 act: 'updateAccountInformation',
                 para: [updateData]
                };
      }
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
        }
        deferred.resolve(data, status);
      },
      function (data, status) {
        deferred.reject(data, status);
      });
      return deferred.promise;
    },
    deleteInformation: function (className, deleteData) {
      var deferred = $q.defer();
      var paras = {contclass: className,
                   act: 'delete' + className + 'Information',
                   para: [deleteData]
                  };
      if (className == "Order") {
        paras = {contclass: 'Booking',
                 act: 'deleteBookingInformation',
                 para: [deleteData]
                };
      }
      if (className == "Account") {
        paras = {contclass: 'User',
                 act: 'deleteAccountInformation',
                 para: [deleteData]
                };
      }
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
        }
        deferred.resolve(data, status);
      },
      function (data, status) {
        deferred.reject(data, status);
      });
      return deferred.promise;
    },
    uploadFile: function (uploaderItem, houseID, isPhoto, photoNo, fileName) {
      uploaderItem.url = 'http://test.zhu5jia.com/php/Controller.php?action=uploadHouseMedia&id=' + houseID + '&isphoto=' + isPhoto + '&photono=' + photoNo + '&filename=' + fileName;
//      uploaderItem.url = 'http://www.zhu5jia.com/php/Controller.php?action=uploadHouseMedia&id=' + houseID + '&isphoto=' + isPhoto + '&photono=' + photoNo + '&filename=' + fileName;
      uploaderItem.upload();
      uploaderItem.onSuccess = function(response, status, headers) {
        if (status == 200 && response.success) {
        }
      }
    },
    removeFile: function (className, updateData) {
      var deferred = $q.defer();
      var paras = {};
      if (className == "House") {
        paras = {contclass: 'House',
                 act: 'removeHouseMedia',
                 para: [updateData]
                };
      }
      myData.getData(paras).then(function (data, status) {
        if (data.success) {
        }
        deferred.resolve(data, status);
      });
      return deferred.promise;
    }
  }
})