angular.module('z5j', ['z5j.directives', 'z5j.filters', 'z5j.services', 'z5j.controllers', 'ui.router', 'ngAnimate', 'ui.bootstrap', 'angularFileUpload'])

.config(function ($stateProvider, $urlRouterProvider, $locationProvider) {
  $stateProvider
    .state('landing', {
      url: '/landing',
      templateUrl: 'template/landing.html',
      controller: 'LandingCtrl'
    })
/*
    .state('logon', {
      url: '/logon',
      templateUrl: 'template/logon.html',
      controller: 'LogonCtrl'
    })
*/
    .state('search', {
      url: '/search/:cityTo/:timeFrom/:timeTo/:guestNo',
      templateUrl: 'template/searching.html',
      controller: 'SearchCtrl'
    })
    .state('room', {
      url: '/room/:roomID/:timeFrom/:timeTo/:guestNo',
      templateUrl: 'template/room.html',
      controller: 'RoomCtrl'
    })
    .state('reload', {
      url: '/reload',
      templateUrl: "template/reload.html",
      controller: 'ReloadCtrl'
    })
    .state('user', {
      url: '/user',
      abstract: true,
      templateUrl: 'template/user.html',
      controller: 'UserCtrl'
    })
    .state('user.dashboard', {
      url: '/dashboard',
      views: {
        'userContent' :{
          templateUrl: "template/user/dashboard.html",
          controller: 'UserDashboardCtrl'
        }
      }
    })
    .state('user.inbox', {
      url: '/inbox/:typeID/:itemID',
      views: {
        'userContent' :{
          templateUrl: "template/user/inbox.html",
          controller: 'UserInboxCtrl'
        }
      }
    })
    .state('user.room', {
      url: '/room/:naviID/:itemID',
      views: {
        'userContent' :{
          templateUrl: "template/user/room.html",
          controller: 'UserRoomCtrl'
        }
      }
    })
    .state('user.trip', {
      url: '/trip/:naviID/:itemID',
      views: {
        'userContent' :{
          templateUrl: "template/user/trip.html",
          controller: 'UserTripCtrl'
        }
      }
    })
    .state('user.me', {
      url: '/me/:naviID/:itemID',
      views: {
        'userContent' :{
          templateUrl: "template/user/me.html",
          controller: 'UserMeCtrl'
        }
      }
    })
    .state('user.account', {
      url: '/account/:naviID/:itemID',
      views: {
        'userContent' :{
          templateUrl: "template/user/account.html",
          controller: 'UserAccountCtrl'
        }
      }
    });

    $urlRouterProvider.otherwise('/landing');

//    $locationProvider.html5Mode(true);
});