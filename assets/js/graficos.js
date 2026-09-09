// Pasta Raiz do Controller
const controllersgraf = '../controller/';
function isNumeric(str){
    var er = /^[0-9]+$/;
    return (er.test(str));
}


$(document).ready(function() {
    const GraficoVendas = function () {
        $.post(`${Paths.controller}back_com_vendas.php`, function (dados) {
            const data = (dados);
            
            const dates = [];
            const highData = [];
            // const lowData = [];
            const realizedData = [];
    
            data.forEach(item => {
                dates.push(item.data);
                highData.push(parseFloat(item.metames));
                // lowData.push(parseFloat(item.anterior));
                realizedData.push(item.totalvendido !== null ? parseFloat(item.totalvendido) : null);
            });
    
            const isDarkMode = document.documentElement.classList.contains('dark-mode');

            const vendas_chart_options = {  // <-- Renomeei a variável
                series: [
                    // { name: 'Anterior', data: lowData },
                    { name: 'Meta', data: highData },
                    { name: 'Realizado', data: realizedData },
                ],
                theme: { mode: isDarkMode ? 'dark' : 'light' },
                chart: {
                    height: 300,
                    width: '100%',
                    type: 'line',
                    toolbar: { show: true },
                    background: 'transparent',
                },
                colors: ['#0d6efd', '#28a745'],
                stroke: { curve: 'smooth' },
                grid: {
                    borderColor: '#e7e7e7',
                    row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 },
                },
                legend: { show: true },
                markers: { size: 1 },
                xaxis: { categories: dates },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return value !== null ? 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return value !== null ? 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
                        }
                    }
                },
            };
    
            const vendas_chart = new ApexCharts(  // <-- Renomeei a variável
                document.querySelector('#visitors-chart'),
                vendas_chart_options
            );
            vendas_chart.render();
        });
    };
    if (document.querySelector('#visitors-chart')) {
        GraficoVendas();
    }
    
    // const GraficoROR = function () {
    //     $.post(`${Paths.controller}back_budget_ror.php`, function (dados) {
    //         const data = JSON.parse(dados);
            
    //         const dates = [];
    //         const highData = [];
    //         const lowData = [];
    //         const realizedData = [];
    
    //         data.forEach(item => {
    //             dates.push(item.data);
    //             highData.push(parseFloat(item.previsto));
    //             lowData.push(parseFloat(item.anterior));
    //             realizedData.push(item.realizado !== null ? parseFloat(item.realizado) : null);
    //         });
    
    //         const ror_chart_options = {  // <-- Renomeei a variável
    //             series: [
    //                 { name: 'Anterior', data: lowData },
    //                 { name: 'Previsto', data: highData },
    //                 { name: 'Realizado', data: realizedData },
    //             ],
    //             chart: {
    //                 height: 300,
    //                 width: '100%',
    //                 type: 'line',
    //                 toolbar: { show: true },
    //             },
    //             colors: ['#adb5bd', '#0d6efd', '#28a745'],
    //             stroke: { curve: 'smooth' },
    //             grid: {
    //                 borderColor: '#e7e7e7',
    //                 row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 },
    //             },
    //             legend: { show: true },
    //             markers: { size: 1 },
    //             xaxis: { categories: dates },
    //             yaxis: {
    //                 labels: {
    //                     formatter: function(value) {
    //                         return value !== null ? 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
    //                     }
    //                 }
    //             },
    //             tooltip: {
    //                 y: {
    //                     formatter: function(value) {
    //                         return value !== null ? 'R$ ' + Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
    //                     }
    //                 }
    //             },
    //         };
    
    //         const ror_chart = new ApexCharts(  // <-- Renomeei a variável
    //             document.querySelector('#visitors-charts'),
    //             ror_chart_options
    //         );
    //         ror_chart.render();
    //     });
    // };
    // GraficoROR();
    
});
