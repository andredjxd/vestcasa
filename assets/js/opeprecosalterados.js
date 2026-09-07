
$(document).ready(function(){
    
    const precosAlterados = function () {
        const d = new Date();
        const year = d.getFullYear();
        const lastYear = d.getFullYear() - 1;
        const afterYear = d.getFullYear() + 1;
    
        // Exibe o modal de carregamento
        //$('#modal-loading').modal('show');
       

        // Limpa os elementos antes de preencher
        $('#precosalterados').empty().append(`
            <div class="card-header p-0 pt-1">
              <ul class='nav nav-tabs' id='custom-tabs-two-tab-precosalt' role='tablist'>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content" id="custom-tabs-two-tabContent-precosalt">
              </div>
            </div>`);


        $('#custom-tabs-two-tab-precosalt').empty().append(`<li class='pt-2 px-3'><h3 class='card-title'>${year}</h3></li>`);
        $('#custom-tabs-two-tabContent-precosalt').empty();

        const months = ["JAN", "FEV", "MAR", "ABR", "MAI", "JUN", "JUL", "AGO", "SET", "OUT", "NOV", "DEZ"];
        const mesesnum        = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
        const mesesnumpassado = ["02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "01"];

        let totalRequests = months.length * 1; // 2 requisições por mês
        let completedRequests = 0;

        months.forEach((month, i) => {
            const dataini = `${year}${mesesnum[i]}01`;
            let datafim; 
            let anoanterior = lastYear;
            let anoatual = year;

            datafim = `${mesesnumpassado[i] === "01" ? afterYear : year}${mesesnumpassado[i]}01`;

            $.post(`${Paths.controller}back_ope_precos_alterados.php`, { ini: dataini, fim: datafim }, function (dados) {
                // console.log(dados);
                const parsedData = (dados);
                let tableRowsAlt = "";

                parsedData.forEach((val) => {
                    let det = `<a href='#' class='text-muted detalheprecosalterados' dat="${dataini+'|'+datafim+'|'+val.data}" title='Visualizar detalhe'><i class='fas fa-eye'></i></a>`;
                    let etq = `<a href='#' class='text-muted gerar-etiqueta-dia ml-2' dat="${val.data}" title='Gerar etiquetas'><i class='fas fa-tags'></i></a>`;
                    tableRowsAlt += `
                            <tr>

                                <td>${Utils.formatarData(val.data)  || ''}</td>
                                <td class='text-center' >${val.qtdprodutos  ? val.qtdprodutos : ''}</td>
                                <td class='text-center' >${val.totalprodutos  ? val.totalprodutos : ''}</td>
                                <td class='text-center' >${val.quantidade_alterados  ? val.quantidade_alterados : ''}</td>
                                <td class='text-center' >${Utils.valorPorcetagem(val.percentual_alterado)  ? Utils.valorPorcetagem(val.percentual_alterado) : ''}</td>

                                <td class='text-center'>${det}${etq}</td>
                            </tr>
                        `;
                });

                // <td class='text-center'>${val.realizado  ? formatarMoeda(val.realizado) : ''}</td>
                // <td class='text-center' style="color: ${val.devio > 0 ? 'red' : val.devio < 0 ? 'green' : 'black'};">${formatarMoeda(val.devio)}</td>
                $('#rolsAlt-' + month).html(tableRowsAlt);

                completedRequests++;
                if (completedRequests >= totalRequests) {
                    $('#modal-loading').modal('hide');
                }
            });

            const tabcompleta = `
            <div class='row'>
                <div class='col-md-12'>
                    <div class='card'>
                            <div class='card-body p-0'>
                                <table class='table table-bordered table-striped table-sm' style='font-size: 0.9rem;'>
                                    <thead>
                                       
                                        <tr>
                                            <th>Data</th>
                                            <th class='text-center'>Total de Produtos Adicionados</th>
                                            <th class='text-center'>Total de Produtos cadastrados</th>
                                            <th class='text-center'>Quantidade de Preços Alterados</th>
                                            <th class='text-center'>Procentagem de Preços Alterados</th>
                                            <th class='text-center'></th>
                                            
                                        </tr>   
                                    </thead>
                                    
                                <tbody id='rolsAlt-${month}'></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const activeClass = i === d.getMonth() ? "active" : "";
        
        $('#custom-tabs-two-tab-precosalt').append(`<li class='nav-item'><a class='nav-link ${activeClass}' data-toggle='pill' href='#custom-tabs-two-home-precosalt-${month}'>${month}</a></li>`);
        $('#custom-tabs-two-tabContent-precosalt').append(`<div class='tab-pane fade ${activeClass ? 'show active' : ''}' id='custom-tabs-two-home-precosalt-${month}'>${tabcompleta}</div>`);
        });
        
       
    }  
    precosAlterados();

    function DetalhePrecosAlterados(data){
         $.post(Paths.controller + 'back_ope_precos_alterados_detalhe.php',{data:data}, function(retorno) {
            // Limpa a tabela existente
            $('#cobtabDetalhaPreco').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.pos, val.data_registro, val.sku, val.codigobarra, val.descricao, val.estoque,
                    val.preco_clube_anterior, val.preco_clube, val.status_clube,
                    val.limitacao_clube_anterior, val.limitacao_clube,
                    val.preco_max_anterior, val.preco_max, val.status_max,
                    val.limitacao_max_anterior, val.limitacao_max,
                    val.preco_varejo_anterior, val.preco_varejo, val.status_varejo
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relVendaDetalhaPreco').DataTable({
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
                        targets: [0, 1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
                        className: 'dt-body-center dt-head-center'
                    },
                    {
                        targets: 3,
                        className: 'dt-body-center dt-head-center codigo-barra'
                    }
                ],
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[0, 'asc']], // Ordena pela data de quebra de forma crescente
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
                            columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18]
                        }
                        ,
                        customize: function (doc) {
                            doc.content.splice(0, 1); // Remove título padrão

                            /* ============================
                            🔥 AQUI ENTRA O HEADER DUPLO
                            ============================ */

                            const tabela = doc.content[0].table;

                            // Remove header automático
                            tabela.body.splice(0, 1);

                            // HEADER NÍVEL 1
                            tabela.body.unshift([
                                { text: '#', rowSpan: 2, style: 'tableHeader', alignment: 'center' },
                                { text: 'Data', rowSpan: 2, style: 'tableHeader', alignment: 'center' },
                                { text: 'SKU', rowSpan: 2, style: 'tableHeader', alignment: 'center' },
                                { text: 'Código Barra', rowSpan: 2, style: 'tableHeader', alignment: 'center' },
                                { text: 'Descrição', rowSpan: 2, style: 'tableHeader', alignment: 'center' },
                                { text: 'EST', rowSpan: 2, style: 'tableHeader', alignment: 'center' },

                                { text: 'Preço no Clube', colSpan: 3, style: 'tableHeader', alignment: 'center' },
                                { text: '' }, { text: '' },

                                { text: 'Limite Clube', colSpan: 2, style: 'tableHeader', alignment: 'center' },
                                { text: '' },

                                { text: 'Preço Clube Max', colSpan: 3, style: 'tableHeader', alignment: 'center' },
                                { text: '' }, { text: '' },

                                { text: 'Limite Max', colSpan: 2, style: 'tableHeader', alignment: 'center' },
                                { text: '' },

                                { text: 'Preço no Varejo', colSpan: 3, style: 'tableHeader', alignment: 'center' },
                                { text: '' }, { text: '' }
                            ]);

                            // HEADER NÍVEL 2
                            tabela.body.splice(1, 0, [
                                { text: '' }, { text: '' }, { text: '' }, { text: '' }, { text: '' }, { text: '' },

                                { text: 'ANT', style: 'tableHeader', alignment: 'center' },
                                { text: 'ATU', style: 'tableHeader', alignment: 'center' },
                                { text: 'Status', style: 'tableHeader', alignment: 'center' },

                                { text: 'ANT', style: 'tableHeader', alignment: 'center' },
                                { text: 'ATU', style: 'tableHeader', alignment: 'center' },

                                { text: 'ANT', style: 'tableHeader', alignment: 'center' },
                                { text: 'ATU', style: 'tableHeader', alignment: 'center' },
                                { text: 'Status', style: 'tableHeader', alignment: 'center' },

                                { text: 'ANT', style: 'tableHeader', alignment: 'center' },
                                { text: 'ATU', style: 'tableHeader', alignment: 'center' },

                                { text: 'ANT', style: 'tableHeader', alignment: 'center' },
                                { text: 'ATU', style: 'tableHeader', alignment: 'center' },
                                { text: 'Status', style: 'tableHeader', alignment: 'center' }
                            ]);

                            // Estilo do header
                            doc.styles.tableHeader = {
                                bold: true,
                                fontSize: 8,
                                fillColor: '#eeeeee'
                            };

                            doc.content[0].table.headerRows = 2;

                            const colunasCentralizadas = [0, 1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];

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
                                // paddingTop: function () { return 1; },
                                // paddingBottom: function () { return 1; }
                                paddingTop: function () { return 4; },
                                paddingBottom: function () { return 4; },
                            };

                            
                            const tabela1 = doc.content[0].table;
                            const headerRows = tabela1.headerRows;

                            // Colunas que são moeda
                            const colunasMoeda = [6,7,11,12,16,17];

                            // Colunas que são data
                            const colunasData = [1];

                            tabela1.body.forEach(function(row, i) {

                                // Ignora header
                                if (i < headerRows) return;

                                colunasMoeda.forEach(function(index) {
                                    if (row[index] && row[index].text !== '') {
                                        row[index].text = Utils.formatarMoeda(row[index].text);
                                    }
                                });

                                colunasData.forEach(function(index) {
                                    if (row[index] && row[index].text !== '') {
                                        let partesData = String(row[index].text).split(' ');
                                        row[index].text = Utils.formatarData(partesData[0]) + (partesData[1] ? ` ${partesData[1]}` : '');
                                    }
                                });

                                // FORMATAR CODIGO DE BARRA
                                const colunaCodigo = 3;

                                if (row[colunaCodigo] && row[colunaCodigo].text) {

                                    let valor = row[colunaCodigo].text;

                                    // 🔥 se vier como string
                                    if (typeof valor === 'string') {
                                        row[colunaCodigo].text = valor.replace(/,/g, '\n');
                                    }

                                    // 🔥 se vier como array
                                    if (Array.isArray(valor)) {
                                        row[colunaCodigo].text = valor.join('\n');
                                    }
                                }

                            });

                            let now = new Date();
                            let jsDate = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()} às ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
                            
                            // Configura margens e estilos do documento
                            doc.pageMargins = [20, 70, 20, 30];
                            doc.defaultStyle.fontSize = 6.5;
                            doc.styles.tableHeader.fontSize = 10;

                            // Cabeçalho do PDF
                            doc['header'] = function() {
                                return {
                                    columns: [
                                        { alignment: 'right', image: vestacasagreen, width: 150, height: 40 },
                                        { alignment: 'center', margin: [0, 0, 0, 0], italics: true, bold: true, text: 'RELATORIO PREÇOS ALTERADOS\n '+
                                                                                                                        'ORIGEM/LOJA: MEGA PALMAS', fontSize: 16 },
                                        { alignment: 'left', image: vestacasagreen, width: 150, height: 40 }
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
                            
                            doc.content[0].table.widths = [
                                                            15,   // #
                                                            40,   // Data
                                                            55,   // SKU
                                                            55,   // Codigo Barra
                                                            '*',  // Descrição
                                                            30,   // Estoque
                                                            35,   // P Clube Ant
                                                            35,   // P Clube Atual
                                                            35,   // Status Clube
                                                            30,   // L Clube Ant
                                                            30,   // L Clube Atual
                                                            35,   // P Max Ant
                                                            35,   // P Max Atual
                                                            35,   // Status Max
                                                            30,   // L Max Ant
                                                            30,   // L Max Atual
                                                            35,   // P Varejo Ant
                                                            35,   // P Varejo Atual
                                                            35    // Status Varejo
                                                        ];
                        }
                    },
                ],
                "data": lis, // Insere os dados na tabela
                "columns": [
                    { data: 0 },
                    { data: 1 },
                    { data: 2 },
                    { data: 3 },
                    { data: 4 },
                    { data: 5 },
                    { data: 6 },
                    { data: 7 },
                    { data: 8 },
                    { data: 9 },
                    { data: 10 },
                    { data: 11 },
                    { data: 12 },
                    { data: 13 },
                    { data: 14 },
                    { data: 15 },
                    { data: 16 },
                    { data: 17 },
                    { data: 18 }
                ],
                createdRow: function (row, data) {

                    let partesData = (data[1] || '').split(' ');
                    let dataHoraFormatada = Utils.formatarData(partesData[0]) + (partesData[1] ? ` ${partesData[1]}` : '');
                    $('td', row).eq(1).html(dataHoraFormatada);
                    $('td', row).eq(3).html(Utils.formatarCodigosBarra(data[3]));
                    $('td', row).eq(6).html(Utils.formatarMoeda(data[6]));
                    $('td', row).eq(7).html(Utils.formatarMoeda(data[7]));
                    $('td', row).eq(11).html(Utils.formatarMoeda(data[11]));
                    $('td', row).eq(12).html(Utils.formatarMoeda(data[12]));
                    $('td', row).eq(16).html(Utils.formatarMoeda(data[16]));
                    $('td', row).eq(17).html(Utils.formatarMoeda(data[17]));

                },

            });
            table.buttons().container().appendTo('#relVendaDetalhaPreco_wrapper .col-md-6:eq(0)');
        });
    }

    function GerarEtiquetasDia(dataYMD) {

        $.post(Paths.controller + 'back_ope_precos_alterados_detalhe.php', { data: dataYMD }, function(retorno) {
            gerarEtiquetasPDF(retorno);
        });
    }

    $('#btnsyncdbpreco').on('click', function() {
        precosAlterados();
        //razaoDespesaDetalhado();
    });
    $(document).on('click','.detalheprecosalterados',function() {
        var rol = $(this).attr("dat");
        separa = rol.split('|')
        // toastr.success(separa[2], 'Teste !');
        // razaoDespesaDetalhado(separa[0],separa[1],separa[2]);
        DetalhePrecosAlterados(separa[2]);
        $('#modal-relatorio-precos-alterados').modal({backdrop: 'static', keyboard: false});

    });
    $(document).on('click','.gerar-etiqueta-dia',function(e) {
        e.preventDefault();
        var dataYMD = $(this).attr("dat");
        GerarEtiquetasDia(dataYMD);
    });
    $(document).on('click','.detalherolalmox',function() {
        var rol = $(this).attr("dat");
        separa = rol.split('|')
        toastr.success(rol, 'Teste !');
        razaoAlmoxDetalhado(separa[0],separa[1],separa[2]);
        $('#modal-relatorio-rol-detalhado-almox').modal({backdrop: 'static', keyboard: false});
        
    });
}); 