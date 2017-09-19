var chartServices = angular.module('chartServices', []);

chartServices.service('BrandChart', function() {
  this.sm_line = function(data) {
        imgname = data['title'].split(" ");
        imgname = imgname.join("_");
        options = {
            chart: {
                type: 'spline',
                style: {
                        fontFamily: 'Verdana, sans-serif'
                    }
            },
            exporting: {
              sourceWidth: 800,
              sourceHeight: 400,
              filename: imgname+"_Daywise",
              // scale: 2 (default)
              /*chartOptions: {
                  subtitle: null
              }*/
            },
            credits: {
                enabled: false
            },
            title: {
                text: data['title']
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: data['days']
            },
            yAxis: {
                title: {
                    text: data['title']
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                crosshairs: true,
                shared: true
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                borderWidth: 0
            },
            plotOptions: {
                series: {
                    marker: {
                        symbol:'circle',
                        radius:2.5
                    },
                    lineWidth:1.5,
                    animation:true
                }
            },
            series: data['linedata']
        };

        return options;
    };

    this.sm_column = function(data) {
        imgname = data['title'].split(" ");
        imgname = imgname.join("_");
        options = {
          chart: {
            type: 'column',
            style: {
                    fontFamily: 'Verdana, sans-serif'
                  }
          },
          exporting: {
              sourceWidth: 800,
              sourceHeight: 400,
              filename: imgname+"_Total",
              // scale: 2 (default)
              /*chartOptions: {
                  subtitle: null
              }*/
          },
          credits: {
                enabled: false
            },
          title: {
              text: data['title']
          },
          xAxis: {
              type: 'category',
              labels: {
                useHTML: true,
                formatter:function(){
                    return "<b>"+this.value+"</b>";
                },
              }
          },
          yAxis: {
              title: {
                  text: data['title']
              }
          },
          legend: {
              enabled: false
          },
          plotOptions: {
              series: {
                  borderWidth: 0,
                  dataLabels: {
                      enabled: true,
                      /*format: '{point.y}',*/
                      formatter:function(){
                            return formatNumber(this.y);
                        },
                  }
              }
          },

          tooltip: {
              /*headerFormat: '<span style="font-size:11px">{series.name}</span><br>',*/
              headerFormat: '',
              pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b><br/>'
          },

          series: [{
              name: 'Handles',
              colorByPoint: true,
              data: data['columndata']
          }]
        };
    return options;
    };

});

chartServices.service('chartData',
  function($http, $q, $timeout, BrandChart) {
    var deferred = $q.defer();
    this.init = function(){
      chartdata = {}
      $http({
              method : "GET",
              url : "./chartdata.php",
              /*headers: {'X-CSRFToken': csrf_token}*/
          })
          .then(function(response) {
              result = response.data;
              chartdata['chartdata'] = {'line':BrandChart.sm_line(result),
                                        'column':BrandChart.sm_column(result)};
              deferred.resolve(chartdata);
          }, function(response) {
              deferred.reject('No Data');
          });
    };
    this.ready = function() {
      return deferred.promise;
    };
    this.refresh = function() {
      deferred = $q.defer();
      this.init();
      this.ready();
    };
    this.init();
});

chartServices.service('inputData',
  function($http, $q, $timeout) {
    var deferred = $q.defer();
    this.init = function(){
      inputdata = {}
      $http({
              method : "GET",
              url : "./inputdata.php",
              /*headers: {'X-CSRFToken': csrf_token}*/
          })
          .then(function(response) {
              result = response.data;
              inputdata['inputdata'] = result;
              deferred.resolve(inputdata);
          }, function(response) {
              deferred.reject('No Data');
          });
    };
    this.ready = function() {
      return deferred.promise;
    };
    this.refresh = function() {
      deferred = $q.defer();
      this.init();
      this.ready();
    };
    this.init();
});

formatNumber = function(num) {
  if(num != undefined){
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
  }
  return 0;
};

add_commas = function(num) {
    if (num>1000)
      {
        if (num%1000!=0)
        {
          post_number = formatNumber((num/1000).toFixed(1))+'k'
        }
        else{
          post_number = formatNumber(num/1000)+'k'
        }
      }
      else
      {
          post_number = formatNumber(num)
      }
    return post_number;
};