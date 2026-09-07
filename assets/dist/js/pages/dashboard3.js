/* global Chart:false */



$(function () {
  // let controller = '../../../../controller/';
  // console.log(controller);
  // 'use strict'
  // var res = [];
  // var ven = [];
  // $.post(controller+'back_grafico_venda.php', function(retorno){
  //   console.log(retorno);
  //   $.each(JSON.parse(retorno), function(index,val){
  //     res.push(val.gnu);
  //     ven.push(val.venda);
  //   });
  // });
  // var ticksStyle = {
  //   fontColor: '#495057',
  //   fontStyle: 'bold'
  // }
  // console.log(res);
  // var mode = 'index'
  // var intersect = true

  // var $salesChart = $('#sales-chart')
  // // eslint-disable-next-line no-unused-vars
  
  // var salesChart = new Chart($salesChart, {
    
  //   type: 'bar',
  //   data: {
  //     labels: res,
  //     datasets: [
  //       {
  //         backgroundColor: '#007bff',
  //         borderColor: '#007bff',
  //         data: ven
  //       }
  //       // ,
  //       // {
  //       //   backgroundColor: '#ced4da',
  //       //   borderColor: '#ced4da',
  //       //   data: [700, 1700, 2700, 2000, 1800, 1500, 2000]
  //       // }
  //     ]
  //   },
  //   options: {
  //     maintainAspectRatio: false,
  //     tooltips: {
  //       mode: mode,
  //       intersect: intersect
  //     },
  //     hover: {
  //       mode: mode,
  //       intersect: intersect
  //     },
  //     legend: {
  //       display: false
  //     },
  //     scales: {
  //       yAxes: [{
  //         // display: false,
  //         gridLines: {
  //           display: true,
  //           lineWidth: '4px',
  //           color: 'rgba(0, 0, 0, .2)',
  //           zeroLineColor: 'transparent'
  //         },
  //         ticks: $.extend({
  //           beginAtZero: true,

  //           // Include a dollar sign in the ticks
  //           callback: function (value) {
  //             if (value >= 1000) {
  //               value /= 1000
  //               value += 'k'
  //             }

  //             return '$' + value
  //           }
  //         }, ticksStyle)
  //       }],
  //       xAxes: [{
  //         display: true,
  //         gridLines: {
  //           display: false
  //         },
  //         ticks: ticksStyle
  //       }]
  //     }
  //   }
  // })

  var $visitorsChart = $('#visitors-chart')
  // eslint-disable-next-line no-unused-vars
  var visitorsChart = new Chart($visitorsChart, {
    data: {
      labels: ['18th', '20th', '22nd', '24th', '26th', '28th', '30th'],
      datasets: [{
        type: 'line',
        data: [100, 120, 170, 167, 180, 177, 160],
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        pointBorderColor: '#007bff',
        pointBackgroundColor: '#007bff',
        fill: false
        // pointHoverBackgroundColor: '#007bff',
        // pointHoverBorderColor    : '#007bff'
      },
      {
        type: 'line',
        data: [60, 80, 70, 67, 80, 77, 100],
        backgroundColor: 'tansparent',
        borderColor: '#ced4da',
        pointBorderColor: '#ced4da',
        pointBackgroundColor: '#ced4da',
        fill: false
        // pointHoverBackgroundColor: '#ced4da',
        // pointHoverBorderColor    : '#ced4da'
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        display: false
      },
      scales: {
        yAxes: [{
          // display: false,
          gridLines: {
            display: true,
            lineWidth: '4px',
            color: 'rgba(0, 0, 0, .2)',
            zeroLineColor: 'transparent'
          },
          ticks: $.extend({
            beginAtZero: true,
            suggestedMax: 200
          }, ticksStyle)
        }],
        xAxes: [{
          display: true,
          gridLines: {
            display: false
          },
          ticks: ticksStyle
        }]
      }
    }
  })
})

// lgtm [js/unused-local-variable]
