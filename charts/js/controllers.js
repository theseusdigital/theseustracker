var chartControllers = angular.module('chartControllers', [])
				.directive('hcChart', function () {
                    return {
                        restrict: 'E',
                        template: '<div></div>',
                        scope: {
                            alldata: '=options',
                        },
                        transclude:true,
                        replace: true,
                        link: function (scope, element) {
                            //console.log(scope.alldata);
                            //Highcharts.chart(element[0], scope.alldata);
                            var Chart;
                            //Update when charts data changes
                            scope.$watch(function() { return scope.alldata; }, function(value) {
                              if(!value) return;
                                var deepCopy = true;
                                var newSettings = {};
                                $.extend(deepCopy, newSettings, scope.alldata);
                                if (scope.alldata && scope.alldata.series.length > 0) {
                                  Chart = new Highcharts.chart(element[0],newSettings);
                                } else {
                                  for (var i = 0; i < Chart.series.length; i++) {
                                    // chart.series[i].setData(scope.alldata.series[i].data);
                                    var new_data = scope.alldata.series[i].data;
                                    for (var j=0;j<new_data.length;j++){
                                      Chart.series[i].data[j].update(new_data[j]);
                                    }                
                                  }
                                }
                            }, true);
                        }
                    };
                })
                .filter('npluralize', function() {
                  return function(number) {
                      if(number<1 || number>1){
                        return 's';
                      }
                  };
              })
                .filter('knumber', function() {
                  return function(numberk) {
                      if(numberk){
                        numberk = numberk.toString();
                        if(numberk.indexOf('k') > -1){
                          number = numberk.replace("k", "");
                          number = parseFloat(number);
                          numberk = formatNumber(number)+'k'
                        }
                        else{
                          numberk = formatNumber(numberk)
                        }
                        return numberk;
                      }
                      else{
                        return 0;
                      }
                  };
              });

chartControllers.controller("chartCtrl",
  function($scope, $rootScope, $http, $interval, $state, inputData, chartData) {

    $rootScope.initData = function(){
      $scope.inputerror = "";
      inputData.ready().then(function(res){
        $scope.inputdata = res['inputdata'];
        console.log($scope.inputdata);
        $scope.since = $scope.inputdata['since'];
        $scope.until = $scope.inputdata['until'];
        $scope.selbrands = $scope.inputdata['selbrands'];
        $scope.selhandles = $scope.inputdata['selhandles'];
        $scope.selplatform = $scope.inputdata['selplatform'];
        $scope.platform_map = $scope.inputdata['platform_map'];
        $scope.selplatformid = $scope.inputdata['selplatformid'];
        $scope.brand_map = $scope.inputdata['brand_map'];
        $scope.brandhandlemap = $scope.inputdata['brandhandlemap'];
        $scope.selmetric = $scope.inputdata['selmetric'];
        $scope.refresh = $scope.inputdata['refresh'];
        $scope.initSelectboxes();
        if($scope.refresh){
          $scope.generate();
        }
        else{
          $scope.initCharts();
        }
        
      });
    };

    $scope.initCharts = function(){
      chartData.ready().then(function(res){
          $scope.alldata = res['chartdata'];
        });
    };

    $scope.generate = function(){
      if($scope.selbrands != ""){
        $scope.selhandles = $scope.updateHandles();
        if($scope.selhandles.length > 0){
          if ($scope.selplatform in $scope.inputdata['metric_map']){
            if ($scope.selmetric in $scope.inputdata['metric_map'][$scope.selplatform]){
              inputs = {
                    since:$scope.since, 
                    until:$scope.until,
                    selbrands:$scope.selbrands,
                    selplatformid:$scope.selplatformid,
                    selplatform:$scope.selplatform,
                    selmetric:$scope.selmetric,
                    dbmetric:$scope.inputdata['metric_map'][$scope.selplatform][$scope.selmetric],
                    selhandles:$scope.selhandles
                };

              $.ajax({
                      type:"POST",
                      url:"./chartdata.php",
                      data:{inputdata:inputs},
                      success:function(data){ 
                          chartData.refresh();
                          $scope.initCharts();
                          //console.log(data);
                      }
                  });
              /*$http({
                      method : "POST",
                      url : "./chartdata.php",
                      data: {branddata:branddata},
                      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                  })
                  .then(function(response) {
                      if(!response.data['expired']){
                        chartData.refresh();
                        $scope.initCharts();
                        console.log(response.data);
                      }
                      else{
                        window.location.href = "../index.php?logout=1";
                      }
                  }, function(response) {
                      
                  });*/
            }
            else{
              $scope.inputerror = "Select Metric";
            }
          }
          else{
            $scope.inputerror = "Select Platform";
          }
        }
        else{
          $scope.inputerror = "No Handles Selected";
        }
      }
      else{
        $scope.inputerror = "Select Atleast 1 Brand";
      }
    };

    $scope.initSelectboxes = function(){
      $("#platform")
        // don't navigate away from the field on tab when selecting an item
        .on( "keydown", function( event ) {
          if ( event.keyCode === $.ui.keyCode.TAB &&
              $( this ).autocomplete( "instance" ).menu.active ) {
            event.preventDefault();
          }
        })
        .autocomplete({
          minLength: 0,
          source: function( request, response ) {
            // delegate back to autocomplete, but extract the last term
            response( $.ui.autocomplete.filter(
              $scope.inputdata['platform_names'], extractLast( request.term ) ) );
          },
          focus: function() {
            // prevent value inserted on focus
            return false;
          },
          change: function( event, ui ) {
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            this.value = ui.item.value;
            $scope.selplatform = this.value;
            $scope.selplatformid = $scope.platform_map[$scope.selplatform];
            $scope.selmetric = $scope.inputdata['metric_map'][$scope.selplatform]['metrics'][0];
            $("#metric").val($scope.selmetric);
            $scope.selhandles = $scope.updateHandles();
            $scope.inputerror = "";
            return false;
          }
        });

    $("#brand")
        // don't navigate away from the field on tab when selecting an item
        .on( "keydown", function( event ) {
          if ( event.keyCode === $.ui.keyCode.TAB &&
              $( this ).autocomplete( "instance" ).menu.active ) {
            event.preventDefault();
          }
        })
        .autocomplete({
          minLength: 0,
          source: function( request, response ) {
            // delegate back to autocomplete, but extract the last term
            response( $.ui.autocomplete.filter(
              $scope.inputdata['brand_names'], extractLast( request.term ) ) );
          },
          focus: function() {
            // prevent value inserted on focus
            return false;
          },
          change: function( event, ui ) {
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            var terms = split(this.value);
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push(ui.item.value);
            // add placeholder to get the comma-and-space at the end
            terms.push("");
            this.value = terms.join(",");
            $scope.selbrands = this.value;
            $scope.selhandles = $scope.updateHandles();
            $scope.inputerror = "";
            console.log($scope.selhandles);
            return false;
          }
        });

        $("#metric")
        // don't navigate away from the field on tab when selecting an item
        .on( "keydown", function( event ) {
          if ( event.keyCode === $.ui.keyCode.TAB &&
              $( this ).autocomplete( "instance" ).menu.active ) {
            event.preventDefault();
          }
        })
        .autocomplete({
          minLength: 0,
          source: function( request, response ) {
            // delegate back to autocomplete, but extract the last term
            response( $.ui.autocomplete.filter(
              $scope.inputdata['metric_map'][$scope.selplatform]['metrics'], extractLast( request.term ) ) );
          },
          focus: function() {
            // prevent value inserted on focus
            return false;
          },
          change: function( event, ui ) {
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            this.value = ui.item.value;
            $scope.selmetric = this.value;
            dbmetric = $scope.inputdata['metric_map'][$scope.selplatform][$scope.selmetric];
            console.log(dbmetric);
            $scope.inputerror = "";
            return false;
          }
        });
    };
    $scope.updateHandles = function(){
      var terms = split($scope.selbrands);
      brand_handles = [];
      brand_list = [];
      for(x in terms){
        if(terms[x] in $scope.brand_map){
          brand_id = $scope.brand_map[terms[x]];
          if(brand_list.indexOf(brand_id) == -1){
            if($scope.selplatformid in $scope.brandhandlemap[brand_id]){
              brand_handles.push($scope.brandhandlemap[brand_id][$scope.selplatformid]);
            }
            brand_list.push(brand_id);
          }
        }
      }
      return brand_handles;
    };
    $rootScope.initData();
});
