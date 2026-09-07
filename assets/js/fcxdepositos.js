
$(document).ready(function(){
    var BtnOperador = "<button type='button' class='btn btn-tool listaoperador'><span class='badge badge-success' style='font-size: 1.1em;'>COLABORADORES <i class='fas fa-user-circle'></i></span>&nbsp;&nbsp;&nbsp;</button>";
    $('#btn-operador').empty();
    $('#btn-operador').append(BtnOperador);
    $('#nomeoperador, #nomelideraca, #nomefuncao, #nomecolaborador').inputmask({casing:'upper'});
    // $('#nomelideraca').inputmask({casing:'upper'});
    $('#numerobananinha').inputmask({
        mask: "9999999",  
        numericInput: true,  // Digitação da direita para a esquerda
        rightAlign: false,   // Mantém alinhado à esquerda
        placeholder: "0",    // Define "0" como preenchimento padrão
        onBeforeWrite: function(event, buffer, caretPos, opts) {
          // Se o usuário digitar poucos números, preenche automaticamente com zeros à esquerda
          let value = buffer.join("").replace(/_/g, "0"); // Substitui "_" por "0"
          return { value };
        }
    });
    $('#cpfcolaborador').inputmask({
        mask: ['999.999.999-99'],
        keepStatic: true,
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true
    });

    $('#valordeposito, #valoredit').inputmask('currency', Utils.maskCurrencyBR);
    // Footer da pagina
    var copyright = function(){
        $('#copyright').empty();
        var d = new Date();
        var year = d.getFullYear();
        var linha = "Copyright &copy; 2023 - "+year+" <a href='#'> Andre Alves</a>";
        $('#copyright').append(linha);
    }
    copyright();
    // Date picker (inicial e final)
    $('#reservationdateinicial, #reservationdatefinal, #reservationdatesangria,'
        + '#reservationdateinicialedit, #reservationdatefinaledit,'
        + '#reservationretirada, #reservationdateadmissao, #reservationdateatualizarvenda').datetimepicker({
        format: 'L'
    });
        //Timepicker
    $('#timepickerInicial, #timepickerFinal').datetimepicker({
      format: 'LT'
    })

    // Configurações do toastr responsavel pelas notificacões!!!
    toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-left"
    };
    toastr.options.closeButton = true;

    // Relatorio dos depositos
    function RelatorioDeposito(){
        $('#overlay-isv').show();
        $.post(Paths.controller + 'back_fcx_deposito_select_relatorio.php', function(retorno) {
            setTimeout(() => {
                // Limpa a tabela existente
                $('#codtabDeposito').empty();
                // Inicializa um array para armazenar os dados formatados
                let lis = [];
                // console.log(retorno);
                
                // Itera sobre os dados retornados e os formata para a tabela
                $.each(retorno, function(index, val) {           
                    // Adiciona os dados na lista para preencher a tabela
                    lis.push([
                        val.pos, val.id, val.loja, val.dtIni, val.dtFim, val.qtd, val.status, val.created, ''
                    ]);
                });
                // Inicializa a DataTable
                let table = $('#relDepositoDetalhado').DataTable({
                    // Tradução
                    language: {
                        url: Paths.language,
                    },
                    // Centralizar Titulos da tabela
                    initComplete: function() {
                        $('#relDepositoDetalhado thead th').css('text-align', 'center');
                    },
                    "searching": false,
                    "responsive": true,  // Tabela responsiva
                    "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                    "autoWidth": false, // Mantém largura das colunas fixa
                    "pageLength": 13, // Exibe 10 registros por página
                    "columnDefs": [
                        {
                            targets: [0, 1, 3, 4, 5, 7,8 ],
                            className: 'dt-body-center' // Centraliza as colunas selecionadas
                        }
                    ],
                    "destroy": true, // Garante recriação da tabela ao atualizar dados
                    "order": [[1, 'desc']], // Ordena pela data de quebra de forma crescente
                    "data": lis, // Insere os dados na tabela
                    "columns": [
                        { title: '#' },
                        { title: 'ID' },
                        // { title: 'ID',
                        //     render: function (data) {
                        //         return quatroDigitos(data);
                        //     } 
                        //  },
                        { title: 'Origem/Loja' },
                        { title: 'Data Inicial' },
                        { title: 'Data Final' },
                        { title: 'QTD' },
                        { title: 'Status' },
                        { title: 'Criação' },
                        { title: '' }
                    ],
                    createdRow: function (row, data) {
                        const separaDataHora = (dataHora) => {
                            if (!dataHora) return '';

                            const [data, hora] = dataHora.split(' ');
                            const [ano, mes, dia] = data.split('-');

                            return `${dia}/${mes}/${ano} ${hora}`;
                        };
                        const formatarData = (data) => {
                            if (!data) return '';
                            const [ano, mes, dia] = data.split('-');
                            return `${dia}/${mes}/${ano}`;
                        };
                        const ConfereStatus = (data,data2) => {
                            if (!data) return '';

                            if (data === 'Finalizado') {
                                return `<s>${data2}</s>`;
                            }else{
                                return `${data2}`;
                            }
                        };
                        const ConfereStatusBtn = (status, id, htmlPadrao = '') => {

                            if (!status) return htmlPadrao;

                            // 🔒 FINALIZADO → somente visualizar
                            if (status === 'Finalizado') {
                                return `
                                    <a href="#" 
                                    class="text-muted viewRetirada me-4" 
                                    dat="${id}" 
                                    title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    &nbsp;
                                    <a href="#" 
                                    class="text-muted viewRelatorio" 
                                    dat="${id}" 
                                    title="Relatorio">
                                        <i class="fas fa-clipboard-list"></i>
                                    </a>
                                `;
                            }

                            // 🔓 ABERTO → todas ações
                            return `
                                <a href="#" 
                                class="text-muted addSangria me-4" 
                                dat="${id}" 
                                title="Adicionar Sangria">
                                    <i class="fas fa-plus"></i>
                                </a>
                                &nbsp;
                                <a href="#" 
                                class="text-muted editDeposito me-4" 
                                dat="${id}" 
                                title="Editar Depósito">
                                    <i class="fas fa-edit"></i>
                                </a>
                                &nbsp;
                                <a href="#" 
                                class="text-muted editRetirada" 
                                dat="${id}" 
                                title="Adicionar Retirada">
                                    <i class="fas fa-sack-dollar"></i>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            `;
                        };

                        // Formata datas
                        $('td', row).eq(0).html(ConfereStatus(data[6],data[0]));
                        $('td', row).eq(1).html(ConfereStatus(data[6],Utils.quatroDigitos(data[1])));
                        $('td', row).eq(2).html(ConfereStatus(data[6],data[2]));
                        $('td', row).eq(3).html(ConfereStatus(data[6],formatarData(data[3])));
                        $('td', row).eq(4).html(ConfereStatus(data[6],formatarData(data[4])));
                        $('td', row).eq(5).html(ConfereStatus(data[6],Utils.doisDigitos(data[5])));
                        // $('td', row).eq(6).html(ConfereStatus(data[6],data[6]));
                        $('td', row).eq(7).html(ConfereStatus(data[6],separaDataHora(data[7])));
                        $('td', row).eq(8).html(ConfereStatusBtn(data[6],data[1],data[8]));

                    },

                });
                table.buttons().container().appendTo('#relDepositoDetalhado_wrapper .col-md-6:eq(0)');
                $('#overlay-isv').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
        
    }
    RelatorioDeposito();
    // Relatorio das sangrias
    function RelatorioSangria(idRel){
        // toastr.success(idRel);

        let dataInicial = '';
        let dataFinal = '';
        let loja = '';
        let gvt = '';
        let dataRec = '';
        let observ = '';
        $.post(Paths.controller + 'back_fcx_deposito_edit_select_relatorio.php',{id:idRel}, function(retorno) {
            // console.log(retorno);
            $.each(retorno, function(index, val) {
                dataInicial =  Utils.formatarData(val.dtIni);
                dataFinal   =  Utils.formatarData(val.dtFim);
                loja        = val.loja;
                gvt         = val.gvt ?? '';
                dataRec     = val.dtRec ?  Utils.formatarData(val.dtRec) : '';
                observ      = val.observ ?? '';
            });
        });
        $.post(Paths.controller + 'back_fcx_deposito_select_relatorio_itens.php',{id:idRel}, function(retorno) {
            // Limpa a tabela existente
            $('#codtabSangria').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // Concatena os IDs para identificação do item
                var ids = val.id;
                // Cria um link de exclusão e edição para cada linha
                var del = "&nbsp;&nbsp;&nbsp;"+
                            "<td><a href='#' class='text-muted edit-quant' dat=" + ids + "><i class='fas fa-edit'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                            "<td><a href='#' class='text-muted edit-quants' dat=" + ids + "><i class='fas fa-trash'></i></a></td>"+
                        "&nbsp;&nbsp;&nbsp;";
                        
                var hora = val.created.split(" ");
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.pos, val.data, val.pdv, val.operador, hora[1], val.lideranca, val.valor, val.numbanana,  del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relDepositoSangria').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                "searching": false,
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 7, // Exibe 10 registros por página
                "columnDefs": [
                    {
                        targets: [0, 1, 2, 4, 5, 6, 7, 8 ],
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
                        exportOptions: {
                            columns: ':not(:last-child)' // ignora coluna de ações
                        },
                        customize: function (doc) {
                            doc.content.splice(0, 1); // Remove título padrão

                            const colunasCentralizadas = [0, 1, 2, 4, 5, 6, 7, 8 ]; // índices do PDF

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

                            let totalValor = 0;
                                const indiceValor = 6;
                                const indiceQuant = 0;

                                const table = doc.content[0];

                                table.table.body.forEach(function (row, i) {
                                    if (i === 0) return;

                                    const textoq = row[indiceQuant]?.text ?? '';
                                    const texto = row[indiceValor]?.text ?? '';
                                    const numero = Utils.moedaParaNumero(texto);

                                    // console.log('valor:', texto, '→', numero);

                                totalValor += numero;
                                totalBanana = textoq;
                            });
                            // console.log('valor:', totalValor);


                            // Formatação dos valores na exportação
                            doc.content[0].table.body.forEach(function(row, i) {
                            
                                // Lista de colunas a serem formatadas
                                const colunas = [
                                    { index: 6, texto: 'Valor' },
                                ];
                                
                                colunas.forEach(coluna => {
                                    if (row[coluna.index].text !== coluna.texto) {
                                        row[coluna.index].text = Utils.formatarMoeda(row[coluna.index].text);
                                    }
                                });
                                 // Lista de colunas a serem formatadas
                                const colunasdata = [
                                    { index: 1, texto: 'Data' },
                                ];
                                
                                colunasdata.forEach(colunasdata => {
                                    if (row[colunasdata.index].text !== colunasdata.texto) {
                                        row[colunasdata.index].text = Utils.formatarData(row[colunasdata.index].text);
                                    }
                                });
                                
                            });

                            // 🔽 LINHA TOTAL (EXATAMENTE 8 COLUNAS)
                            // LINHA 01
                            table.table.body.push([
                                {text: 'GVT: '+gvt,colSpan: 4, alignment: 'right', bold: true},
                                {}, 
                                {},
                                {},
                                { text: 'TOTAL', colSpan: 2, alignment: 'right', bold: true },
                                {},
                                {
                                    text: 'R$ ' + totalValor.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    bold: true,
                                    alignment: 'center'
                                },
                                { text: '' }
                            ]);
                            // LINHA 02
                             table.table.body.push([
                                {text: 'Quant. de Bananinha: '+totalBanana+' ',colSpan: 4, alignment: 'right', bold: true}, 
                                {},
                                {},
                                
                                {},
                                { text: 'Data do Recolhimento: '+dataRec, colSpan: 3, alignment: 'right', bold: true },
                                {},
                                {},
                                { text: '' }
                            ]);
                            // LINHA 03
                            table.table.body.push([
                                
                                {text: 'Observações: ',colSpan: 2, alignment: 'right', bold: true}, 
                                {},
                                {text: ''+observ,colSpan: 6,},
                                {},
                                {},
                                {},
                                {},
                                { text: '' }
                            ]);

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
                                                                                                                        'PERÍODO DE: '+dataInicial+' ATÉ '+ dataFinal+
                                                                                                                        '\n ORIGEM/LOJA: '+loja, fontSize: 16 },
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
                            
                            doc.content[0].table.widths = [20, 80 , 30, '*',  80, 120, 90, 100]; // Ajusta larguras da tabela
                        }
                    },
                    // {
                    //     extend: 'excelHtml5',
                    //     text: '<i class="fas fa-file-excel"></i> Excel',
                    //     className: 'btn btn-success btn-sm me-2',
                    //     filename: function () {
                    //         let now = new Date();
                    //         return `relatorio_depositos_${now.getFullYear()}-${now.getMonth()+1}-${now.getDate()}`;
                    //     },
                    //     title: 'CONTROLE DE SANGRIA PARA BOCA DE LOBO \n oi', 
                    //     exportOptions: {
                    //         columns: ':not(:last-child)',
                    //         format: {
                    //             body: function (data, row, column) {

                    //                 // 🔢 coluna VALOR (índice 6)
                    //                 if (column === 6) {
                    //                     return data
                    //                         .replace(/[^\d,.-]/g, '')
                    //                         .replace(',', '.');
                    //                 }

                    //                 return data;
                    //             }
                    //         }
                    //     },
                    //     customize: function (xlsx) {

                    //         let sheet = xlsx.xl.worksheets['sheet1.xml'];
                           
                    //         const styles = xlsx.xl['styles.xml'];

                    //         /* ==========================
                    //         1️⃣ CRIAR ESTILO CENTRALIZADO
                    //         ========================== */
                    //         const cellXfs = $('cellXfs', styles);

                    //         const newStyleIndex = $('xf', cellXfs).length;

                    //         cellXfs.append(`
                    //             <xf xfId="0" applyAlignment="1">
                    //                 <alignment horizontal="center" vertical="center"/>
                    //             </xf>
                    //         `);
                    //          /* ==========================
                    //         3️⃣ APLICAR ESTILO NA CÉLULA A1
                    //         ========================== */
                    //         $('c[r="A1"]', sheet).attr('s', newStyleIndex);


                    //         // =========================
                    //         //  CALCULA SUBTOTAL
                    //         // =========================
                    //         let total = 0;

                    //         $('row c[r^="G"]', sheet).each(function () {
                    //             let valor = $(this).text();
                    //             total += parseFloat(valor) || 0;
                    //         });

                    //         let ultimaLinha = $('row', sheet).length + 1;

                    //         // =========================
                    //         //  LINHA SUBTOTAL
                    //         // =========================
                    //         let subtotalRow = `
                    //             <row r="${ultimaLinha}">
                    //                 <c t="inlineStr" r="F${ultimaLinha}">
                    //                     <is><t>TOTAL</t></is>
                    //                 </c>
                    //                 <c r="G${ultimaLinha}">
                    //                     <v>${total}</v>
                    //                 </c>
                    //             </row>
                    //         `;

                    //         sheet.childNodes[0].childNodes[1].innerHTML += subtotalRow;
                    //     }
                    // }
                ],
                "data": lis, // Insere os dados na tabela
                "columns": [
                    { title: '#' },
                    { title: 'Data' },
                    { title: 'PDV',
                        render: function (data) {
                            return Utils.doisDigitos(data);
                        }  
                    },
                    { title: 'Operador' },
                    { title: 'Horário' },
                    { title: 'Liderança' },
                    { title: 'Valor' },
                    { title: 'N.Bananinha' },
                    { title: '' }
                ],
                createdRow: function (row, data) {
                    // Função utilitária para formatar data YYYY-MM-DD → DD/MM/YYYY
                    const formatarData = (data) => {
                        if (!data) return '';
                        const [ano, mes, dia] = data.split('-');
                        return `${dia}/${mes}/${ano}`;
                    };

                    // Formata datas
                    $('td', row).eq(1).html(formatarData(data[1]));
                    // $('td', row).eq(4).html(formatarData(data[4]));

                    // Formata valor monetário
                    const valor = Number(data[6]) || 0;
                    $('td', row).eq(6).html(
                        `R$ ${valor.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })}`
                    );
                },

            });
            table.buttons().container().appendTo('#relDepositoSangria_wrapper .col-md-6:eq(0)');
        });
    }
    function RelatorioSangriaFinalizado(idRel){
        // toastr.success(idRel);

        let dataInicial = '';
        let dataFinal = '';
        let loja = '';
        let gvt = '';
        let dataRec = '';
        let observ = '';
        $.post(Paths.controller + 'back_fcx_deposito_edit_select_relatorio.php',{id:idRel}, function(retorno) {
            // console.log(retorno);
            $.each(retorno, function(index, val) {
                dataInicial =  Utils.formatarData(val.dtIni);
                dataFinal   =  Utils.formatarData(val.dtFim);
                loja        = val.loja;
                gvt         = val.gvt ?? '';
                dataRec     = val.dtRec ?  Utils.formatarData(val.dtRec) : '';
                observ      = val.observ ?? '';
            });
        });
        $.post(Paths.controller + 'back_fcx_deposito_select_relatorio_itens.php',{id:idRel}, function(retorno) {
            // Limpa a tabela existente
            $('#codtabSangriaFinalizado').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                var hora = val.created.split(" ");
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.pos, val.data, val.pdv, val.operador, hora[1], val.lideranca, val.valor, val.numbanana
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relDepositoSangriaFinalizado').DataTable({
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

                            let totalValor = 0;
                                const indiceValor = 6;
                                const indiceQuant = 0;

                                const table = doc.content[0];

                                table.table.body.forEach(function (row, i) {
                                    if (i === 0) return;

                                    const textoq = row[indiceQuant]?.text ?? '';
                                    const texto = row[indiceValor]?.text ?? '';
                                    const numero = Utils.moedaParaNumero(texto);

                                    // console.log('valor:', texto, '→', numero);

                                totalValor += numero;
                                totalBanana = textoq;
                            });
                            // console.log('valor:', totalValor);


                            // Formatação dos valores na exportação
                            doc.content[0].table.body.forEach(function(row, i) {
                            
                                // Lista de colunas a serem formatadas
                                const colunas = [
                                    { index: 6, texto: 'Valor' },
                                ];
                                
                                colunas.forEach(coluna => {
                                    if (row[coluna.index].text !== coluna.texto) {
                                        row[coluna.index].text = Utils.formatarMoeda(row[coluna.index].text);
                                    }
                                });
                                 // Lista de colunas a serem formatadas
                                const colunasdata = [
                                    { index: 1, texto: 'Data' },
                                ];
                                
                                colunasdata.forEach(colunasdata => {
                                    if (row[colunasdata.index].text !== colunasdata.texto) {
                                        row[colunasdata.index].text = Utils.formatarData(row[colunasdata.index].text);
                                    }
                                });
                                
                            });

                            // 🔽 LINHA TOTAL (EXATAMENTE 8 COLUNAS)
                            // LINHA 01
                            table.table.body.push([
                                {text: 'GVT: '+gvt,colSpan: 4, alignment: 'right', bold: true},
                                {}, 
                                {},
                                {},
                                { text: 'TOTAL', colSpan: 2, alignment: 'right', bold: true },
                                {},
                                {
                                    text: 'R$ ' + totalValor.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }),
                                    bold: true,
                                    alignment: 'center'
                                },
                                { text: '' }
                            ]);
                            // LINHA 02
                             table.table.body.push([
                                {text: 'Quant. de Bananinha: '+totalBanana+' ',colSpan: 4, alignment: 'right', bold: true}, 
                                {},
                                {},
                                
                                {},
                                { text: 'Data do Recolhimento: '+dataRec, colSpan: 3, alignment: 'right', bold: true },
                                {},
                                {},
                                { text: '' }
                            ]);
                            // LINHA 03
                            table.table.body.push([
                                
                                {text: 'Observações: ',colSpan: 2, alignment: 'right', bold: true}, 
                                {},
                                {text: ''+observ,colSpan: 6,},
                                {},
                                {},
                                {},
                                {},
                                { text: '' }
                            ]);

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
                                                                                                                        'PERÍODO DE: '+dataInicial+' ATÉ '+ dataFinal+
                                                                                                                        '\n ORIGEM/LOJA: '+loja, fontSize: 16 },
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
                            
                            doc.content[0].table.widths = [20, 80 , 30, '*',  80, 120, 90, 100]; // Ajusta larguras da tabela
                        }
                    },
                ],
                "data": lis, // Insere os dados na tabela
                "columns": [
                    { title: '#' },
                    { title: 'Data' },
                    { title: 'PDV',
                        render: function (data) {
                            return Utils.doisDigitos(data);
                        }  
                    },
                    { title: 'Operador' },
                    { title: 'Horário' },
                    { title: 'Liderança' },
                    { title: 'Valor' },
                    { title: 'N.Bananinha' }
                ],
                createdRow: function (row, data) {
                    // Função utilitária para formatar data YYYY-MM-DD → DD/MM/YYYY
                    const formatarData = (data) => {
                        if (!data) return '';
                        const [ano, mes, dia] = data.split('-');
                        return `${dia}/${mes}/${ano}`;
                    };

                    // Formata datas
                    $('td', row).eq(1).html(formatarData(data[1]));
                    // $('td', row).eq(4).html(formatarData(data[4]));

                    // Formata valor monetário
                    const valor = Number(data[6]) || 0;
                    $('td', row).eq(6).html(
                        `R$ ${valor.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })}`
                    );
                },

            });
            table.buttons().container().appendTo('#relDepositoSangriaFinalizado_wrapper .col-md-6:eq(0)');
        });
    }
    // Relatorio das Funções
    function RelatorioFuncao(){
        $('#overlay-funcao').show();
        $.post(Paths.controller + 'back_fcx_funcao_select.php', function(retorno) {
            setTimeout(() => {
            // Limpa a tabela existente
            $('#codtabFuncao').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // Concatena os IDs para identificação do item
                var ids = val.id;
                // Cria um link de exclusão e edição para cada linha
                var del = "&nbsp;&nbsp;&nbsp;"+
                            "<td><a href='#' class='text-muted editfuncao' dat=" + ids + "><i class='fad fa-pencil-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                            "<td><a href='#' class='text-muted delfuncao' dat=" + ids + "><i class='fad fa-trash-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                        "&nbsp;&nbsp;&nbsp;";
         
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                   val.funcao, del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relFuncao').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relFuncao thead th').css('text-align', 'center');
                },
                "searching": false,
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 9, // Exibe 10 registros por página
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[0, 'asc']], // Ordena pela data de quebra de forma crescente
                "data": lis, // Insere os dados na tabela
                "columnDefs": [
                    { targets: 0, width: '120px' },                             // FUNÇÃO
                    { targets: 1, width: '25px', className: 'text-center' }   // Ações
                ],
                "columns": [
                    { title: 'FUNÇÃO' },
                    { title: '' }
                    
                ]
                
            });
            table.buttons().container().appendTo('#relFuncao_wrapper .col-md-6:eq(0)');
            $('#overlay-funcao').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
        
    }
    // Relatorio dos Horarios
    function RelatorioHorario(){
        $('#overlay-horario').show();
        $.post(Paths.controller + 'back_fcx_horario_select.php', function(retorno) {
            setTimeout(() => {
            // Limpa a tabela existente
            $('#codtabHorario').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // Concatena os IDs para identificação do item
                var ids = val.id;
                // Cria um link de exclusão e edição para cada linha
                var del = "&nbsp;&nbsp;&nbsp;"+
                            "<td><a href='#' class='text-muted edithorario' dat=" + ids + "><i class='fad fa-pencil-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                            "<td><a href='#' class='text-muted delhorario' dat=" + ids + "><i class='fad fa-trash-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                        "&nbsp;&nbsp;&nbsp;";
         
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                   val.hini, val.hfim, val.inter, del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relHorario').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relHorario thead th').css('text-align', 'center');
                },
                "searching": false,
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 9, // Exibe 10 registros por página
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[0, 'asc']], // Ordena pela data de quebra de forma crescente
                "data": lis, // Insere os dados na tabela
                "columnDefs": [
                    { targets: [0,1,2], className: 'text-center' }   // Ações
                ],
                "columns": [
                    { title: 'Hora Inicial' },
                    { title: 'Hora Final' },
                    { title: 'Invervalo' },
                    { title: '' }
                    
                ]
                
            });
            table.buttons().container().appendTo('#relHorario_wrapper .col-md-6:eq(0)');
            $('#overlay-horario').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
        
    }
    // Relatorio dos colaboradores
    function RelatorioColaboradores(){
        $('#overlay-colaboradores').show();
        $.post(Paths.controller + 'back_fcx_colaborador_select.php', function(retorno) {
            setTimeout(() => {
            // Limpa a tabela existente
            $('#codtabOperadores').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // Concatena os IDs para identificação do item
                var ids = val.id;
                var nome = val.nome;
                // Cria um link de exclusão e edição para cada linha
                var del = "&nbsp;&nbsp;&nbsp;"+
                            "<td><a href='#' class='text-muted edithorario' dat=" + ids + "><i class='fad fa-pencil-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                            "<td><a href='#' class='text-muted desativacolab' dat=" + ids +";"+ nome +"><i class='fas fa-ban'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                        "&nbsp;&nbsp;&nbsp;";
         
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                   val.codigo, val.nome, val.admissao, val.cpf, val.idfuncao, val.idhorario, val.status, del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relOperadores').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relOperadores thead th').css('text-align', 'center');
                },
                "searching": true,
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 12, // Exibe 10 registros por página
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[0, 'asc']], // Ordena pela data de quebra de forma crescente
                "data": lis, // Insere os dados na tabela
                "columnDefs": [
                    { targets: [0,2,3,5,6], className: 'text-center' }   // Ações
                ],
                "columns": [
                    { title: 'Codigo' },
                    { title: 'Nome' },
                    { title: 'Admissão' },
                    { title: 'CPF',
                        render: function (data) {
                            return Utils.formatarCPF(data);
                        } 
                    },
                    { title: 'Função' },
                    { title: 'Horário' },
                    { title: 'Status' },
                    { title: '' }
                    
                ],
                createdRow: function (row, data) {

                    const formatarData = (data) => {
                        if (!data) return '';
                        const [ano, mes, dia] = data.split('-');
                        return `${dia}/${mes}/${ano}`;
                    };
                    const ConfereStatus = (data) => {
                        if (!data) return '';

                        if (data == 0) {
                            return `Ativos`;
                        }else{
                            return `<s>Desativado</s>`;
                        }
                    };
                    
                    // 📅 Data
                    $('td', row).eq(2).html(formatarData(data[2]));
                    // Status
                    $('td', row).eq(6).html(ConfereStatus(data[6]));

                },
                
            });
            table.buttons().container().appendTo('#relOperadores_wrapper .col-md-6:eq(0)');
            $('#overlay-colaboradores').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
        
    }
    // Ações para load 
    $(document).on('click','.syncRelat',function() {
        RelatorioDeposito();
    });
    // Abre modal Criar deposito
    $(document).on('click','.createRel',function() {
        nomeloja    = $('#nomeloja').val('');
        dataincial  = $('.datainicial').val('');
        datafinal   = $('.datafinal').val('');
        $('#modal-create-relatorio').modal({backdrop: 'static', keyboard: false});
    });
    // Criar relatorio de deposito
    $(document).on('click','.create-relat-deposito',function() {
        nomeloja    = $('#nomeloja').val();
        datainicial  = $('.datainicial').val();
        datafinal   = $('.datafinal').val();
        if (nomeloja === '' || nomeloja === 0 || nomeloja === null) {
            toastr.error('Selecione a loja!');
        }else if(dataincial === '' || dataincial === null){
            toastr.error('Selecione a data Inicial!');
        }else if(datafinal === '' || datafinal === null){
            toastr.error('Selecione a data Final!');
        }else{
            var dados = {
                nomeloja: nomeloja,
                dataincial:datainicial,
                datafinal:datafinal
            }
            // toastr.success(datainicial);
            $.post(Paths.controller+'back_fcx_deposito_insert_relatorio.php',dados, function(retorno){
                // console.log(retorno);
                if(retorno.error == 101){
                    toastr.success(retorno.debug);
                    $("#modal-create-relatorio").modal('hide');
                    RelatorioDeposito();
                    
                }else{
                    toastr.error(retorno.message);
                }
            });
        }  
        // $('#modal-create-relatorio').modal('hide');
    });
    // Abre modal adicionar deposito
    $(document).on('click','.addSangria',function() {
        // nomeloja    = $('#nomeloja').val('');
        // dataincial  = $('.datainicial').val('');
        // datafinal   = $('.datafinal').val('');
        var pega = $(this).attr("dat");
        $('#IDRel').empty();
        $('#IDRel').append("<b >"+pega+"</b>");
        // Chama a função para preencher os selects
        Utils.selectOperadores('selectoperador');
        RelatorioSangria(pega);
        $('#modal-edit-deposito').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.viewRelatorio',function() {
        var pega = $(this).attr("dat");
        $('#IDRel').empty();
        $('#IDRel').append("<b >"+pega+"</b>");
        RelatorioSangriaFinalizado(pega);
        $('#modal-view-deposito').modal({backdrop: 'static', keyboard: false});
    });
    // Abre modal para edição do relatorio
    $(document).on('click','.editDeposito',function() {
        var pega = $(this).attr("dat");
        $('#IDReleditDep').empty();
        $('#IDReleditDep').append("<b >"+pega+"</b>");
        // RelatorioSangria(pega);
        // console.log(pega);
        $.post(Paths.controller + 'back_fcx_deposito_select_relatorio_filtro.php',{id:pega}, function(retorno) {
            // console.log(retorno.loja);
            var valor = retorno.total ? retorno.total.toString().replace('.', ',') : '';
            var dataIni = Utils.formatarData(retorno.dtIni);
            var dataFim = Utils.formatarData(retorno.dtFim)
            $('#nomelojaedit').val('1').trigger('change');
            $('.datainicialedit').val(dataIni);
            $('.datafinaledit').val(dataFim);
            $('#valoredit').val(valor);
            $('#statusedit').val(retorno.status);
            $('#criacaoedit').val(retorno.created);
            $('#modal-edit-relatorio-deposito').modal({backdrop: 'static', keyboard: false});
        });
    });
    // edição do relatorio
    $(document).on('click','.editRetirada',function() {
        // nomeloja    = $('#nomeloja').val('');
        // dataincial  = $('.datainicial').val('');
        // datafinal   = $('.datafinal').val('');
        var pega = $(this).attr("dat");
        $('#IDRelRetFinal').empty();
        $('#IDRelRetFinal').append("<b >"+pega+"</b>");
        // RelatorioSangria(pega);
        
        $.post(Paths.controller + 'back_fcx_deposito_edit_select_relatorio.php',{id:pega}, function(retorno) {
            // console.log(retorno);
            $.each(retorno, function(index, val) {
                // console.log(val.loja);
                // $('#nomelojaedit').val('1').trigger('change');
                $('#valorretirada').val( Utils.formatarMoeda(val.valor));
                $('#qtdetirada').val( Utils.doisDigitos(val.qtd));
                $('#dataretirada').val(val.dtRec ? Utils.formatarData(val.dtRec) : '');
                $('#empresaretirada').val( Utils.doisDigitos(val.empresa ? val.empresa : '  '));
                $('#gvtretirada').val(val.gvt ? val.gvt : '  ');
                $('#observretirada').val(val.observ ? val.observ : '  ');
                $('#modal-edit-retirada').modal({backdrop: 'static', keyboard: false});
            });
        });
        
    });
    $(document).on('click','.viewRetirada',function() {
        var pega = $(this).attr("dat");
        $.post(Paths.controller + 'back_fcx_deposito_edit_select_relatorio.php',{id:pega}, function(retorno) {
            // console.log(retorno);
            $.each(retorno, function(index, val) {
                // console.log(val.loja);
                // $('#nomelojaedit').val('1').trigger('change');
                $('#valorretiradaview').val( Utils.formatarMoeda(val.valor));
                $('#qtdetiradaview').val( Utils.doisDigitos(val.qtd));
                $('#dataretiradaview').val(val.dtRec ?  Utils.formatarData(val.dtRec) : '');
                $('#empresaretiradaview').val( Utils.doisDigitos(val.empresa ? val.empresa : '  '));
                $('#gvtretiradaview').val(val.gvt ? val.gvt : '  ');
                $('#statusdoretiradaview').val(val.status ? val.status : '');
                $('#finalizadoretiradaview').val(val.finished ?  Utils.separaDataHora(val.finished) : '');
                $('#observretiradaview').val(val.observ ? val.observ : '  ');
                $('#modal-view-retirada').modal({backdrop: 'static', keyboard: false});
            });
        });
        
    });
    $(document).on('click','.alterar-relat-deposito',function() {
        var id = $('#IDReleditDep').text().trim();
        var loja = $('#nomelojaedit option:selected').text().trim();
        var datainicial = $('.datainicialedit').val();
        var datafinal  = $('.datafinaledit').val();
        if (loja === '' || loja === '0' || loja === null) {
            toastr.error('Adicione o nome da empresa!');
        }else if(datainicial === '' || datainicial === null){
            toastr.error('Adicione a data de Inicial!');
        }else if(datafinal === '' || datafinal === null){
            toastr.error('Adicione a data de Final!');
        }else{
            var dados = {
                id: id,
                loja: loja,
                datainicial: datainicial,
                datafinal : datafinal
            }
            // console.log(dados);
            $.post(Paths.controller+'back_fcx_deposito_update_relatorio.php',dados, function(retorno){
                // console.log(retorno);
                if(retorno.error == 101){
                    toastr.success(retorno.message);
                    $("#modal-edit-relatorio-deposito").modal('hide');
                    RelatorioDeposito();
                    
                }else{
                    toastr.error(retorno.message);
                }
        }, 'json');
        }

    });
    $(document).on('click', '.finalizar-relat-deposito', function () {

        var id = $('#IDRelRetFinal').text().trim(); // ou .val() se for input
        var empresa = $('#empresaretirada').val().trim();
        var dataretirada = $('#dataretirada').val();
        var gvtretirada = $('#gvtretirada').val().trim();
        var observretirada = $('#observretirada').val();

        if (!empresa) {
            toastr.error('Adicione o numero do GVT!');
            return;
        }
        if (!dataretirada) {
            toastr.error('Adicione data de retirada!');
            return;
        }

        if (!gvtretirada) {
            toastr.error('Adicione o numero do GVT!');
            return;
        }

        var dados = {
            id: id,
            empresa: empresa,
            dataret: dataretirada,
            gvt: gvtretirada,
            observ: observretirada
        };

        // console.log(dados);
        $.post(Paths.controller+'back_fcx_deposito_insert_retirada.php',dados, function(retorno){
            // console.log(retorno);
            if(retorno.error == 101){
                toastr.success(retorno.message);
                $("#modal-edit-retirada").modal('hide');
                RelatorioDeposito();
                
            }else{
                toastr.error(retorno.message);
            }
        }, 'json');
        
    });
    
    // Abre Modal Desativar
    $(document).on('click','.desativacolab',function() {
        var pega = $(this).attr("dat");
        // console.log(pega);
        sep = pega.split(';')
        // toastr.success(sep[0]+";"+sep[1]);
        $('#iddesativar').empty();
        $('#iddesativar').append(sep[1]);
        $('#addbtnDesativar').empty();
        $('#addbtnDesativar').append("<a><button id='btn-desativa' type='button' class='btn btn-warning' dataid="+sep[0]+">Desativar</button></a>");
        $('#modal-desativar-colab').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#btn-desativa',function() {
        var pega = $(this).attr("dataid");
        // toastr.success(pega);
        $.post(Paths.controller + 'back_fcx_update_geral.php', {id: pega, tabela: 'vest_relatorio_colaborador'}, function(dados){
            // toastr.success(dados);
            if(dados.error === false){
                toastr.success("Desativado com sucesso!!!");
                $('#modal-desativar-colab').modal('hide');
                RelatorioColaboradores();
            }else{
                toastr.error("Erro ao Deletar!!!");
            }

        }, "json");
    });
    // Navegação com ENTER unificada
    $(document).on('keydown', '.modal-content input, .modal-content select, .modal-content textarea', function (e) {

        if (e.key === 'Enter' && this.tagName !== 'TEXTAREA') {

            e.preventDefault();

            let container = $(this).closest('.modal-content');

            let campos = container
                .find('input:not([type=hidden]), select, textarea')
                .filter(function () {
                    return !$(this).closest('.dataTables_filter').length;
                })
                .filter(':visible:not([readonly]):not([disabled])');
                
            let index = campos.index(this);

            // 👉 Se NÃO for o último campo → apenas foca próximo
            if (index > -1 && index < campos.length - 1) {
                campos.eq(index + 1).focus().select();
                return;
            }

            // 👉 Se for o ÚLTIMO campo → executa o POST
            var idRelat = $('#IDRel').text();

            const dados = {
                idrel:              idRelat,
                datadeposito:       $('.datasangria').val(),
                pdv:                $('#numpdv').val(),
                nomeoperador:       $('#selectoperador').val(),
                nomelideranca:      $('#nomelideraca').val(),
                valodeposito:       $('#valordeposito').inputmask('unmaskedvalue'),
                numerobananinha:    $('#numerobananinha').val()
            };

            console.log(dados);

            $.post(Paths.controller + 'back_fcx_deposito_insert.php', { dados }, function (retorno) {

                if (retorno.error == 101) {

                    toastr.success(retorno.message);

                    $("#modal-create-relatorio").modal('hide');

                    $('#numpdv').val('0').trigger('change');
                    $('#selectoperador').val('0').trigger('change');
                    $('#valordeposito').val('');
                    $('#numerobananinha').val('');
                    $('#numpdv').focus();

                    RelatorioDeposito();
                    RelatorioSangria(idRelat);

                } else if (retorno.error == 102) {

                    toastr.warning(retorno.message);

                } else {

                    toastr.error(retorno.message);
                }
            });
        }
    });
    // Operadores
    $(document).on('click','.listaoperador',function() {
        var BtnOperadorCad = "<button type='button' class='btn btn-tool cadcolaborador'>"
                                +"<span class='badge badge-success' style='font-size: 1.1em;'>CADASTRAR <i class='fas fa-user-plus'></i></span>&nbsp;&nbsp;&nbsp;"
                            +"</button>"
                             +"<button type='button' class='btn btn-tool btncadfuncao'>"
                                +"<span class='badge badge-success' style='font-size: 1.1em;'>FUNÇÕES <i class='fas fa-user-cog'></i></span>&nbsp;&nbsp;&nbsp;"
                            +"</button>"
                             +"<button type='button' class='btn btn-tool btncadhorario'>"
                                +"<span class='badge badge-success' style='font-size: 1.1em;'>HORÁRIO <i class='fas fa-user-clock'></i></span>&nbsp;&nbsp;&nbsp;"
                            +"</button>";
        
        $('#btn-operador-cad').empty();
        $('#btn-operador-cad').append(BtnOperadorCad);
        RelatorioColaboradores()
        $('#modal-operadores').modal({backdrop: 'static', keyboard: false});
    });
    // Acoes da funcoes
    $(document).on('click','.btncadfuncao',function() {
        RelatorioFuncao()
        $('#modal-funcao').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#btnfuncao',function() {
        nomefuncao    = $('#nomefuncao').val();
        if (nomefuncao === '' || nomefuncao === 0 || nomefuncao === null) {
            toastr.info('Digite nome da função!');
        }else{
            var dados = {
                nomefuncao: nomefuncao
            }
            // toastr.success(dataincial);
            $.post(Paths.controller+'back_fcx_funcao_insert.php',dados, function(retorno){
                console.log(retorno);
                if(retorno.error == 101){
                    toastr.success(retorno.message);
                    // $("#modal-create-relatorio").modal('hide');
                    RelatorioFuncao()
                    $('#nomefuncao').val('');
                    
                }else{
                    toastr.error(retorno.message);
                }
            });
        }  
        // $('#modal-funcao').modal('hide');
    });
    // Acoes de horarios
    $(document).on('click','.btncadhorario',function() {
        $('#horainicial').val('');
        $('#horafinal').val('');
        $('#selectintervalo').val('0').trigger('change');
        RelatorioHorario()
        $('#modal-horario').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#btnhorario',function() {
        hini = $('#horainicial').val();
        hfim = $('#horafinal').val();
        // inter = parseInt($('#selectintervalo').val(), 10);
        let inter = $('#selectintervalo option:selected').text();

        console.log(inter);
        if (hini === '' || hini === 0 || hini === null) {
            toastr.info('Digite nome da horário inicial!');
        }else if (hfim === '' || hfim === 0 || hfim === null) {
            toastr.info('Digite nome da horário final!');
        }else if (inter === 'Intervalo') {
            toastr.info('Selecione o intervalo!');
        }else{
            var dados = {
                hini: hini,
                hfim: hfim,
                inter: inter
            }
            // toastr.success(dataincial);
            $.post(Paths.controller+'back_fcx_horario_insert.php',dados, function(retorno){
                console.log(retorno);
                if(retorno.error == 101){
                    toastr.success(retorno.message);
                    // $("#modal-create-relatorio").modal('hide');
                    RelatorioHorario()
                    $('#horainicial').val('');
                    $('#horafinal').val('');
                    $('#selectintervalo').val('0').trigger('change');
                    
                }else{
                    toastr.error(retorno.message);
                }
            });
        }  
        // $('#modal-funcao').modal('hide');
    });
    // Acoes de cadastro colaborador
    $(document).on('click','.cadcolaborador',function() {
        // Chama a função para preencher os selects
        Utils.selectFuncao();
        Utils.selectHorario();
        $('#modal-cad-colaborador').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click', '#addcolaborador', function (e) {
        e.preventDefault();
        let codigo  = $('#codigocolaborador').val();
        let nome    = $('#nomecolaborador').val().trim();
        let dtadmis = $('#dataadmissao').val();
        let cpf     = $('#cpfcolaborador').inputmask('unmaskedvalue');
        let funcao  = parseInt($('#selectfuncao').val(), 10);
        let horario = parseInt($('#selecthorario').val(), 10);
        // console.log(cpf);
        // 🔹 Nome
        if (nome.length < 3) {
            toastr.warning('Informe o nome do colaborador');
            $('#nomecolaborador').focus();
            return false;
        }
        // 🔹 Data de admissão
        if (!dtadmis) {
            toastr.warning('Informe a data de admissão');
            $('#dataadmissao').focus();
            return false;
        }
        // 🔹 CPF
        if (!cpf) {
            toastr.warning('Informe o CPF');
            $('#cpfcolaborador').focus();
            return false;
        }
        if (!validarCPF(cpf)) {
            toastr.error('CPF inválido');
            $('#cpfcolaborador').focus();
            return false;
        }
        // 🔹 Função
        if (isNaN(funcao) || funcao <= 0) {
            toastr.warning('Selecione a função');
            $('#selectfuncao').focus();
            return false;
        }
        // 🔹 Horário
        if (isNaN(horario) || horario <= 0) {
            toastr.warning('Selecione o horário');
            $('#selecthorario').focus();
            return false;
        }
        var dados = {
            codigo: codigo,
            nome: nome,
            dtadmis: dtadmis,
            cpf: cpf,
            funcao: funcao,
            horario: horario
        }
        // console.log(dados);
        $.post(Paths.controller+'back_fcx_colaborador_insert.php',{dados}, function(retorno){
            // console.log(retorno);
            if(retorno.error == 101){
                toastr.success(retorno.message);
                // $("#modal-create-relatorio").modal('hide');
                RelatorioColaboradores();
                $('#codigocolaborador').val('');
                $('#nomecolaborador').val('');
                $('#dataadmissao').val('');
                $('#cpfcolaborador').val('');
                $('#selectfuncao').val('0').trigger('change');
                $('#selecthorario').val('0').trigger('change');
                $('#codigocolaborador').focus();
                
            }else{
                toastr.error(retorno.message);
            }
        });
        // ✅ Se chegou aqui, está tudo validado
        // salvarColaborador();
    });

    
}); 