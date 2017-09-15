var chartApp = angular.module('chartApp', [
    //'ngRoute',
    'ui.router',
    'chartControllers',
    'chartServices'
    ]);

chartApp.config(function($stateProvider,$urlRouterProvider){
  $urlRouterProvider.otherwise("/");
  $stateProvider
    .state('home', {
      url: "/",
      templateUrl: "../charts/charthome.php",
      controller: "chartCtrl"
      /*views: {
        "": { 
             templateUrl: "/goonjtv/brand/home",
             controller: "hashtagCtrl"
            },
        "header@home": { 
                  templateUrl: "/goonjtv/brand/header"
                 },
        "wall@home": { 
                  templateUrl: "/goonjtv/brand/wall",
                  controller: "socialWallCtrl"
                 },
        "footer@home": { 
                  templateUrl: "/goonjtv/brand/footer"
                 }
      }*/
    })
    .state('home2', {
      url: "/home2",
      templateUrl: "../charts/charthome.php",
      controller: "chartCtrl"
    })

});