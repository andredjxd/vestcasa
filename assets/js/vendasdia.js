
$(document).ready(function(){

    
    //Data on-line
    var update = function () {
        date = moment(new Date())
        datetime.html(date.format('dddd, D MMMM YYYY, HH:mm:ss '));
    };
    datetime = $('#datagora')
    update();
    setInterval(update, 1000);

    
    const VendaDia = function () {
        const d = new Date();
        const year = d.getFullYear();
        const lastYear = d.getFullYear() - 1;
        const afterYear = d.getFullYear() + 1;
    
        // Exibe o modal de carregamento
        //$('#modal-loading').modal('show');
       

        // Limpa os elementos antes de preencher
        $('#venda-dia').empty().append(`
            <div class="card-header p-0 pt-1">
              <ul class='nav nav-tabs' id='custom-tabs-two-tab-razao' role='tablist'>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content" id="custom-tabs-two-tabContent-razao">
              </div>
            </div>`);


        $('#custom-tabs-two-tab-razao').empty().append(`<li class='pt-2 px-3'><h3 class='card-title'>${year}</h3></li>`);
        $('#custom-tabs-two-tabContent-razao').empty();
        

        const months = ["DEZ", "JAN", "FEV", "MAR", "ABR", "MAI", "JUN", "JUL", "AGO", "SET", "OUT", "NOV", "DEZ"];
        const mesesnum        = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
        const mesesnumpassado = ["02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "01"];

        let totalRequests = months.length * 1; // 2 requisições por mês
        let completedRequests = 0;
        let abaAtivada = false;
        let primeiraAba = true;
        let melhorIndice = -1;
        let melhorDistancia = 999;

        months.forEach((month, i) => {

            let fila = Promise.resolve();

            let anoBase = year;

            // 👉 Se for o PRIMEIRO DEZ (posição 0), usar ano anterior
            if (i === 0) {
                anoBase = year - 1;
            }

            // 👉 Se for o ÚLTIMO DEZ (posição final), continua ano atual
            if (i === months.length - 1) {
                anoBase = year;
            }

            const baseDate = new Date(anoBase, (month === "DEZ" && i === 0) ? 11 : i - 1, 1);

            const dataini = baseDate.toISOString().slice(0,10).replace(/-/g,'');

            const nextMonth = new Date(baseDate.getFullYear(), baseDate.getMonth() + 1, 1);
            const datafim = nextMonth.toISOString().slice(0,10).replace(/-/g,'');

            // console.log(month, dataini, datafim);

            var bottonEdit = `<div class='card-tools'><button type='button' class='btn btn-tool edit-meta-caf-${months[i]}' dat='${dataini}'><i class='fas fa-calendar-alt'></i></button> </div>`;
            const tabcompleta = `
                <div class="overlay-wrapper" style="min-height: 300px; position: relative;">

                    <div class="overlay purple" id="overlay-${month}-${i}">
                        <div class="overlay-content text-center">
                            <i class="fas fa-4x fa-sync-alt fa-spin"></i>
                            <div class="text-bold mt-3">
                                Carregando dados...
                            </div>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-md-9'>
                            <div class='card'>
                                <div class='card-body p-0'>
                                    <table class='table table-bordered table-striped table-sm' style='font-size: 0.9rem;'>
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th class='text-center'>Dia</th>
                                                <th class='text-center'>Juros</th>
                                                <th class='text-center'>Devol. QTD</th>
                                                <th class='text-center'>Devol. Total</th>
                                                <th class='text-center'>Fechamento</th>
                                                <th class='text-center'>Sangria</th>
                                                <th class='text-center'>Valor</th>
                                                <th class='text-center'>Meta</th>
                                                <th class='text-center'>Deficit/Superávit</th>
                                                <th class='text-center'>OPE</th>
                                                <th class='text-center'>TM</th>
                                                <th class='text-center'></th>
                                            </tr>
                                        </thead>
                                        <tbody id='rols-${month}-${i}'></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class='col-md-3'>                
                            <div class="sticky-top mb-3">
                                <div class='card'>
                                    <div class='card-header bg-blue'>
                                        <h3 class='card-title '>Resultado do Mês</h3>${bottonEdit}
                                    </div>
                                    <table class='table table-striped table-sm'>
                                        <tbody id='resul-${month}-${i}'></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            `;
            // const activeClass = i-1 === d.getMonth() ? "active" : "";
            const isActive = primeiraAba ? 'active' : '';
            const isShow = primeiraAba ? 'show active' : '';

            $('#custom-tabs-two-tab-razao').append(`
                <li class='nav-item'>
                    <a class='nav-link ${isActive}' data-toggle='pill' href='#custom-tabs-two-home-razao-${month}-${i}'>${month}</a>
                </li>
            `);

            $('#custom-tabs-two-tabContent-razao').append(`
                <div class='tab-pane fade ${isShow}' id='custom-tabs-two-home-razao-${month}-${i}'>
                    ${tabcompleta}
                </div>
            `);
            if (primeiraAba) {
                $('#overlay-' + month + '-' + i).show();
                primeiraAba = false;
            }
            // $('#overlay-' + month + '-' + i).show();

            fila = fila.then(() => {

                return new Promise((resolve) => {

                    $.post(`${Paths.controller}back_venda_dia.php`, { ini: dataini, fim: datafim }, function (dados) {

                        if (Array.isArray(dados) && dados.length > 0) {

                            const mesAtual = new Date().getMonth(); // 0-11
                            const indiceMes = (i === 0) ? 11 : i - 1;

                            // distância do mês atual (prioriza meses anteriores)
                            let distancia = mesAtual - indiceMes;
                            if (distancia < 0) distancia += 12;

                            if (Array.isArray(dados) && dados.length > 0) {
                                if (distancia < melhorDistancia) {
                                    melhorDistancia = distancia;
                                    melhorIndice = i;
                                }
                            }
                        }

                        const parsedData = dados;
                        let tableRows = "";

                        let totaldias = 0;
                        let totalvendas = 0;
                        let mediaDiaria = 0;
                        let projecao = 0;
                        // console.log(dados);
                        parsedData.forEach((val) => {
                            let det = `<a href='#' class='text-muted detalhevenda' dat="${dataini+'|'+datafim+'|'+val.datadia}"><i class='fas fa-search'></i></a>`;
                            let nfliq = (val.totalnf || 0) - (val.devolval || 0);
                            let verif = Math.round((nfliq - val.totaldia) * 100) / 100;

                            totaldias = val.dia;
                            totalvendas = val.totalvendi;
                            mediaDiaria = totalvendas / totaldias;
                            diasRestantes = val.dias - totaldias;
                            projecao = (diasRestantes * mediaDiaria) + totalvendas;

                            let cor = verif !== 0 ? '#ff0000' : '#3a7bdb';
                            let corRest = val.restante < 0 ? 'red' : 'green';

                            tableRows += `
                                <tr>
                                    <td>${Utils.formatarData(val.datadia) || ''}</td>
                                    <td class='text-center'>${val.semanadia || ''}</td>
                                    <td class='text-center'>${val.juros ? Utils.formatarMoeda(val.juros) : ''}</td>
                                    <td class='text-center'>${val.devolqtd || ''}</td>
                                    <td class='text-center'>${val.devolval ? Utils.formatarMoeda(val.devolval) : ''}</td>
                                    <td class='text-center'>${val.totalfcx ? Utils.formatarMoeda(val.totalfcx) : ''}</td>
                                    <td class='text-center'>${val.totalsangr ? Utils.formatarMoeda(val.totalsangr) : ''}</td>
                                    <td class='text-center' style='color:${cor}'>${val.totaldia ? Utils.formatarMoeda(val.totaldia) : ''}</td>
                                    <td class='text-center'>${val.metadiaria ? Utils.formatarMoeda(val.metadiaria) : ''}</td>
                                    <td class='text-center' style='color:${corRest}'>${val.restante ? Utils.formatarMoeda(val.restante) : ''}</td>
                                    <td class='text-center'>${val.operacoes || ''}</td>
                                    <td class='text-center'>${val.cmedio ? Utils.formatarMoeda(val.cmedio) : ''}</td>
                                    <td class='text-center'>${det}</td>
                                </tr>
                            `;
                        });

                        $('#rols-' + month + '-' + i).html(tableRows);

                        completedRequests++;
                        if (completedRequests >= totalRequests) {

                            if (melhorIndice !== -1) {
                                const month = months[melhorIndice];

                                $('#custom-tabs-two-tab-razao .nav-link').removeClass('active');
                                $('#custom-tabs-two-tabContent-razao .tab-pane').removeClass('show active');

                                $(`#custom-tabs-two-tab-razao a[href='#custom-tabs-two-home-razao-${month}-${melhorIndice}']`)
                                    .addClass('active');

                                $(`#custom-tabs-two-home-razao-${month}-${melhorIndice}`)
                                    .addClass('show active');
                            }

                            $('#modal-loading').modal('hide');
                        }

                        $.post(`${Paths.controller}back_venda_meta.php`, { ini: dataini }, function (metaDados) {

                            let valorMeta = 0;
                            if (Array.isArray(metaDados) && metaDados.length > 0) {
                                valorMeta = metaDados[0].metavenda ?? 0;
                            }

                            const resultPorc = valorMeta > 0 ? (totalvendas / valorMeta) * 100 : 0;

                            $('#resul-' + month + '-' + i).html(`
                                <tr>
                                    <td>META DE VENDA</td>
                                    <td>${Utils.formatarMoeda(valorMeta)}</td>
                                </tr>
                                <tr>
                                    <td>TOTAL VENDIDO</td>
                                    <td>${Utils.formatarMoeda(totalvendas)}</td>
                                </tr>
                                <tr>
                                    <td colspan='2'>
                                        <div class='progress'>
                                            <div class='progress-bar bg-orange progress-bar-striped'
                                                style='width:${parseInt(resultPorc)}%'>
                                                ${parseInt(resultPorc)}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MÉDIA DIÁRIA</td>
                                    <td>${Utils.formatarMoeda(mediaDiaria)}</td>
                                </tr>
                                <tr>
                                    <td>PROJEÇÃO</td>
                                    <td>${Utils.formatarMoeda(projecao)}</td>
                                </tr>
                            `);

                            $('#overlay-' + month + '-' + i).hide();

                            resolve(); // 🔥 libera próxima execução
                        }, 'json');

                    });

                });

            });
        });
        
    //    <div class='card'><div class='card-header'><h3 class='card-title'>Diarização</h3></div>
    //             <table class='table table-striped table-sm'><thead><tr><th>Ano</th><th>Dias</th><th>Média</th></tr></thead><tbody id='mediadiaria-${month}'></tbody></table></div>
    }  
    VendaDia();
    function DetalheVendaDia(data){
         $.post(Paths.controller + 'back_pdv_log.php',{data:data}, function(retorno) {
            // Limpa a tabela existente
            $('#codtabVendaDetalhadaDia').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // var hora = val.created.split(" ");
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.identificador, val.nf, val.serie, val.data, val.pdv, val.pagamentos, val.total, ''
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relVendaDetalhadaDia').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 12, // Exibe 10 registros por página
                "columnDefs": [
                    {
                        targets: [0, 1, 2, 4, 5, 6, 7],
                        className: 'dt-body-center dt-head-center'// Centraliza as colunas selecionadas
                    }
                ],
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[1, 'desc']], // Ordena pela data de quebra de forma crescente
                "dom": "<'row mb-2'<'col-md-6 d-flex align-items-center'B><'col-md-6'f>>" +
                        "<'row'<'col-md-12'tr>>" +
                        "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",

                "buttons": [
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> Gerar PDF',
                        className: 'btn btn-danger btn-sm',
                        "filename": function() {
                            let now = new Date();
                            let dateStr = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()}`;
                            let timeStr = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
                            return `relatorio_depositos_${dateStr}_${timeStr}`;
                        },
                        orientation: 'landscape',
                        pageSize: 'A4',
                        // title: 'Relatório de Sangria',
                        // exportOptions: {
                        //     columns: ':not(:last-child)' // ignora coluna de ações
                        // }
                        exportOptions: {
                            columns: [0,1,2,3,4,5,6,7] 
                        }
                        ,
                        customize: function (doc) {
                            doc.content.splice(0, 1); // Remove título padrão

                            const colunasCentralizadas = [0, 1, 2, 4, 5, 6, 7 ]; // índices do PDF

                            doc.content[0].table.body.forEach(function (row, i) {
                                if (i === 0) return;
                                row.forEach(function (cell, index) {
                                if (colunasCentralizadas.includes(index)) {
                                    cell.alignment = 'center';
                                }
                                });
                            });

                            // 🔥 ATIVA BORDA COMPLETA
                            doc.content[0].layout = {
                                hLineWidth: function () { return 1; },
                                vLineWidth: function () { return 1; },
                                hLineColor: function () { return '#000'; },
                                vLineColor: function () { return '#000'; },
                                paddingLeft: function () { return 1; },
                                paddingRight: function () { return 1; },
                                paddingTop: function () { return 1; },
                                paddingBottom: function () { return 1; }
                            };

                            // let totalValor = 0;
                            //     const indiceValor = 6;
                            //     const indiceQuant = 0;

                            //     const table = doc.content[0];

                            //     table.table.body.forEach(function (row, i) {
                            //         if (i === 0) return;

                            //         const textoq = row[indiceQuant]?.text ?? '';
                            //         const texto = row[indiceValor]?.text ?? '';
                            //         const numero = Utils.moedaParaNumero(texto);

                            //         // console.log('valor:', texto, '→', numero);

                            //     totalValor += numero;
                            //     totalBanana = textoq;
                            // });
                            // console.log('valor:', totalValor);


                            // Formatação dos valores na exportação
                            // doc.content[0].table.body.forEach(function(row, i) {
                            
                            //     // Lista de colunas a serem formatadas
                            //     const colunas = [
                            //         { index: 6, texto: 'Valor' },
                            //     ];
                                
                            //     colunas.forEach(coluna => {
                            //         if (row[coluna.index].text !== coluna.texto) {
                            //             row[coluna.index].text = Utils.formatarMoeda(row[coluna.index].text);
                            //         }
                            //     });
                            //      // Lista de colunas a serem formatadas
                            //     const colunasdata = [
                            //         { index: 1, texto: 'Data' },
                            //     ];
                                
                            //     colunasdata.forEach(colunasdata => {
                            //         if (row[colunasdata.index].text !== colunasdata.texto) {
                            //             row[colunasdata.index].text = Utils.formatarData(row[colunasdata.index].text);
                            //         }
                            //     });
                                
                            // });

                            // 🔽 LINHA TOTAL (EXATAMENTE 8 COLUNAS)
                            // LINHA 01
                            // table.table.body.push([
                            //     {text: 'GVT: '+gvt,colSpan: 4, alignment: 'right', bold: true},
                            //     {}, 
                            //     {},
                            //     {},
                            //     { text: 'TOTAL', colSpan: 2, alignment: 'right', bold: true },
                            //     {},
                            //     {
                            //         text: 'R$ ' + totalValor.toLocaleString('pt-BR', {
                            //             minimumFractionDigits: 2,
                            //             maximumFractionDigits: 2
                            //         }),
                            //         bold: true,
                            //         alignment: 'center'
                            //     },
                            //     { text: '' }
                            // ]);
                            // // LINHA 02
                            //  table.table.body.push([
                            //     {text: 'Quant. de Bananinha: '+totalBanana+' ',colSpan: 4, alignment: 'right', bold: true}, 
                            //     {},
                            //     {},
                                
                            //     {},
                            //     { text: 'Data do Recolhimento: '+dataRec, colSpan: 3, alignment: 'right', bold: true },
                            //     {},
                            //     {},
                            //     { text: '' }
                            // ]);
                            // // LINHA 03
                            // table.table.body.push([
                                
                            //     {text: 'Observações: ',colSpan: 2, alignment: 'right', bold: true}, 
                            //     {},
                            //     {text: ''+observ,colSpan: 6,},
                            //     {},
                            //     {},
                            //     {},
                            //     {},
                            //     { text: '' }
                            // ]);

                            let now = new Date();
                            let jsDate = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()} às ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
                            
                            // Configura margens e estilos do documento
                            doc.pageMargins = [25, 90, 25, 30];
                            doc.defaultStyle.fontSize = 10;
                            doc.styles.tableHeader.fontSize = 15;

                            // Cabeçalho do PDF
                            doc['header'] = function() {
                                return {
                                    columns: [
                                        { alignment: 'right', image: vestcasa, width: 135, height: 60 },
                                        { alignment: 'center', margin: [0, 10, 0, 0], italics: true, bold: true, text: 'CONTROLE DE SANGRIA PARA BOCA DE LOBO\n '+
                                                                                                                        'PERÍODO DE:  ATÉ '+
                                                                                                                        '\n ORIGEM/LOJA: '},
                                        { alignment: 'left', image: vestcasa, width: 135, height: 60 }
                                    ],
                                    margin: 20
                                };
                            };

                            // Rodapé do PDF
                            doc['footer'] = function(page, pages) {
                                return {
                                    columns: [
                                        { alignment: 'left', text: [`Gerado em: ${jsDate}`] },
                                        { alignment: 'center', text: ['By: Andre Alves '] },
                                        { alignment: 'right', text: [`Página ${page} de ${pages}`] }
                                    ],
                                    margin: [20, 10]
                                };
                            };
                            
                            doc.content[0].table.widths = [90, 50, 40, 100, 40,'*',   90, 100]; // Ajusta larguras da tabela
                        }
                    },
                ],
                "data": lis, // Insere os dados na tabela
                "columns": [
                    { title: 'Identificador' },
                    { title: 'NFC-e' },
                    // { title: 'PDV',
                    //     render: function (data) {
                    //         return Utils.doisDigitos(data);
                    //     }  
                    // },
                    { title: 'Serie' },
                    { title: 'Data' },
                    { title: 'PDV' },
                    { title: 'Pagamentos' },
                    { title: 'Total' }
                ],
                createdRow: function (row, data) {
                    // Função utilitária para formatar data YYYY-MM-DD → DD/MM/YYYY
                    // const formatarData = (data) => {
                    //     if (!data) return '';
                    //     const [ano, mes, dia] = data.split('-');
                    //     return `${dia}/${mes}/${ano}`;
                    // };

                    // Formata datas
                    // $('td', row).eq(1).html(formatarData(data[1]));
                    // $('td', row).eq(4).html(formatarData(data[4]));

                    // Formata valor monetário
                    // const valor = Number(data[6]) || 0;
                    // $('td', row).eq(6).html(
                    //     `R$ ${valor.toLocaleString('pt-BR', {
                    //         minimumFractionDigits: 2,
                    //         maximumFractionDigits: 2
                    //     })}`
                    // );
                },

            });
            table.buttons().container().appendTo('#relVendaDetalhadaDia_wrapper .col-md-6:eq(0)');
        });
    }
    $('#btnsyncdbrazao').on('click', function() {
        VendaDia();
        //razaoDespesaDetalhado();
    });
    $(document).on('click','.detalhevenda',function() {
        var rol = $(this).attr("dat");
        separa = rol.split('|')
        toastr.success(separa[2], 'Teste !');
        // razaoDespesaDetalhado(separa[0],separa[1],separa[2]);
        DetalheVendaDia(separa[2]);
        $('#modal-relatorio-venda').modal({backdrop: 'static', keyboard: false});
        
    });
    $(document).on('click','.detalherolalmox',function() {
        var rol = $(this).attr("dat");
        separa = rol.split('|')
        toastr.success(rol, 'Teste !');
        razaoAlmoxDetalhado(separa[0],separa[1],separa[2]);
        $('#modal-relatorio-rol-detalhado-almox').modal({backdrop: 'static', keyboard: false});
        
    });
}); 