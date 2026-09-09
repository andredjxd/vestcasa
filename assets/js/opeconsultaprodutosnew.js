
$(document).ready(function(){
    // Configurações do toastr responsavel pelas notificacões!!!
    toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-left"
    };
    toastr.options.closeButton = true;
    function imprimirCodigo(codigo, descricao) {

        let win = window.open('', '', 'width=700,height=400');

        win.document.write(`
            <html>
            <head>
                <script src="${Utils.appBase()}/assets/plugins/jsbarcode/JsBarcode.all.min.js"><\/script>
                <style>
                    @media print {
                        @page { margin: 0; }
                        body { margin: 0; }

                        #etiqueta {
                            width: 220px;
                            margin: 0 auto;
                            text-align: center;
                        }
                    }
                </style>
            </head>
            <body style="text-align:center; font-family: Arial; margin:10px;">
                
                <div id="msg" style="color:red; font-weight:bold; margin-bottom:10px;"></div>

                <!-- 🔥 descrição  -->
                <div id="descricao" style="
                    margin-top:10px;
                    font-size:13px;
                    font-weight:bold;
                    max-width:220px;
                    margin:auto;
                    word-break:break-word;
                "></div>

                <svg id="barcode"></svg>



                <script>

                    function ajustarEAN13(codigo) {
                        codigo = codigo.toString().replace(/\\D/g, '');
                        if (codigo.length > 13) {
                            codigo = codigo.substring(0, 13);
                        }

                        if (codigo.length === 12) {
                            let sum = 0;
                            for (let i = 0; i < 12; i++) {
                                sum += parseInt(codigo[i]) * (i % 2 === 0 ? 1 : 3);
                            }
                            let check = (10 - (sum % 10)) % 10;
                            codigo = codigo + check.toString();
                        }
                        return codigo;
                    }

                    let codigo = ajustarEAN13("${codigo}");
                    let descricao = "${descricao}".replace(/"/g, '&quot;');

                    JsBarcode("#barcode", codigo, {
                        format: "EAN13",
                        displayValue: true,
                        fontSize: 16,
                        height: 60,
                        width: 2,

                        valid: function(valido) {

                            if (!valido) {
                                document.getElementById('barcode').style.display = 'none';
                                document.getElementById('msg').innerHTML = '❌ Código inválido para EAN-13';
                            } else {
                                document.getElementById('descricao').innerHTML = descricao;

                                window.onafterprint = function() {
                                    window.close();
                                };

                                setTimeout(() => {
                                    window.print();
                                }, 200);
                            }
                        }
                    });

                <\/script>

            </body>
            </html>
        `);

        win.document.close();
    }
    function ajustarEAN13(codigo) {

        codigo = codigo.toString().replace(/\D/g, '');

        // 🔥 maior que 13 → corta
        if (codigo.length > 13) {
            codigo = codigo.substring(0, 13);
        }

        // completa digito verificador EAN-13 para codigos de 12 digitos
        if (codigo.length === 12) {
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                sum += parseInt(codigo[i]) * (i % 2 === 0 ? 1 : 3);
            }
            let check = (10 - (sum % 10)) % 10;
            codigo = codigo + check.toString();
        }

        return codigo;
    }

    let carregandoProdutos = false;
    let tabelaConsultaProdutos = null;
    let ultimoFiltro = '';

    function ConsultaProdutosNew(inicial = false) {

        // 🔒 Evita múltiplas chamadas simultâneas
        if (carregandoProdutos) return;
        carregandoProdutos = true;

        if (!inicial) {
            $('#overlay-consulta-produtos').show();
        }

        let filtroCodigoBarras = $('#filtro-codbarras').val();

        if (inicial) {
            filtroCodigoBarras = '';
        } else {

            // 🔥 campo vazio → carrega tudo (SEM recursão)
            if (!filtroCodigoBarras) {
                filtroCodigoBarras = '';
            }

            // 🔥 evita busca curta
            if (filtroCodigoBarras.length < 3 && filtroCodigoBarras !== '') {
                $('#overlay-consulta-produtos').hide();
                carregandoProdutos = false;
                return;
            }
        }
        if (!inicial && filtroCodigoBarras.length >= 3) {
            $('#overlay-consulta-produtos').show();
        }

        $.post(Paths.controller + 'back_ope_consulta_produtos_new.php', {
            codbarras: filtroCodigoBarras
        }, function (retorno) {

            // ✅ ajuste: limpar corretamente
            if (tabelaConsultaProdutos) {
                // tabelaConsultaProdutos.clear();
            }

            let lis = [];

            $.each(retorno, function (index, val) {

                let sku = val.sku;

                let det =
                    "&ensp;<td><a href='#' class='text-muted view-estoque' dat='" + sku + "'><i class='fas fa-eye'></i></a></td>&ensp;" +
                    "<td><a href='#' class='text-muted edit-divergencias' dat='" + sku + "'><i class='fas fa-exclamation-triangle'></i></a></td>&ensp;" +
                    "<td><a href='#' class='text-muted edit-finalizars' dat='" + sku + "'><i class='fas fa-inbox-in'></i></a></td>";

                let lc = val.limclube || 0;
                let ls = lc * 2;
                let lm = val.limmax   || 0;

                let fClube  = val.precoclube  != null ? Utils.formatarMoeda(val.precoclube)  : '-';
                let fMax    = val.precomax    != null ? Utils.formatarMoeda(val.precomax)    : '-';
                let fVarejo = val.precovarejo != null ? Utils.formatarMoeda(val.precovarejo) : '-';

                lis.push([
                    val.sku,
                    val.produto,
                    val.isv,
                    fClube,
                    lc || '',
                    fMax,
                    lm || '',
                    fVarejo,
                    det
                ]);
            });

            if (!tabelaConsultaProdutos) {

                tabelaConsultaProdutos = $('#relConsultaProdutosNew').DataTable({
                    language: {
                        url: Paths.language,
                        emptyTable: "🔍 Digite o código de barras...",
                        zeroRecords: "🔍 Digite o código de barras..."
                    },
                    dom: 'rtip',
                    responsive: true,
                    lengthChange: false,
                    autoWidth: false,
                    pageLength: 12,
                    searching: true,
                    order: [[2, 'desc']],
                    data: lis,
                    columns: [
                        { title: 'SKU',       width: '80px' },
                        { title: 'Descrição', width: '350px' },
                        { title: 'ISV',       width: '25px' },
                        { title: 'Clube',     width: '55px' },
                        { title: 'Lim.C',     width: '25px' },
                        { title: 'Max',       width: '55px' },
                        { title: 'Lim.M',     width: '25px' },
                        { title: 'Varejo',    width: '45px' },
                        { title: '',          width: '80px' }
                    ],
                    columnDefs: [
                        {
                            targets: [0, 2, 3, 4, 5, 6, 7],
                            className: 'dt-body-center'
                        }
                    ],
                    createdRow: function (row, data) {
                        $('td', row).eq(3).html(data[3]).css('color', '#28a745');
                        $('td', row).eq(4).css('color', '#28a745');
                        $('td', row).eq(5).html(data[5]).css('color', '#dc3545');
                        $('td', row).eq(6).css('color', '#dc3545');
                        $('td', row).eq(7).html(data[7]);
                    },
                    initComplete: function () {

                        let api = this.api();

                        $('#relConsultaProdutosNew thead th').css('text-align', 'center');

                        $('#filtrosConsultaProdutosNew').empty();

                        // adiciona espaçamento automático entre colunas
                        let linhaFiltros = $('<div class="row"></div>');

                        linhaFiltros.append(`
                            <div class="col-auto px-2">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    style="width: 152px;"
                                    placeholder="Filtrar Cód. de Barras"
                                    id="filtro-codbarras">
                            </div>
                        `);

                        linhaFiltros.append(`
                            <div class="col-auto px-2">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    style="width: 152px;"
                                    placeholder="Filtrar Código"
                                    data-col="0"
                                    id="filtro-sku">
                            </div>
                        `);

                        linhaFiltros.append(`
                            <div class="col-auto px-2">
                                <input type="text" 
                                    class="form-control form-control-sm"
                                    style="width: 600px;" 
                                    placeholder="Filtrar Descrição"
                                    data-col="1"
                                    id="filtro-descricao">
                            </div>
                        `);

                        linhaFiltros.append(`
                            <div class="col-auto px-2 position-relative">
                                <button type="button" class="btn btn-outline-warning btn-block btn-sm" id="desvinculados">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Desvinculados</span>

                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning desvinculadototal">
                                    0
                                    </span>
                                </button>
                            </div>
                        `);

                        $('#filtrosConsultaProdutosNew').append(linhaFiltros);
                        DesvinculadosTotal();
                        // filtros normais
                        $('#filtrosConsultaProdutosNew input[data-col]')
                            .off('keyup change')
                            .on('keyup change', function () {
                                let coluna = $(this).data('col');
                                api.column(coluna).search(this.value).draw();
                            });

                        // código de barras → backend
                        let timeoutBusca;

                        $('#filtro-codbarras').on('keyup', function () {
                            clearTimeout(timeoutBusca);

                            let valor = $(this).val();

                            // 🔥 evita chamadas repetidas
                            if (valor === ultimoFiltro) return;

                            timeoutBusca = setTimeout(() => {
                                ultimoFiltro = valor;
                                ConsultaProdutosNew();
                            }, 300);
                        });
                    }
                });

            } else {
                tabelaConsultaProdutos.clear();
                tabelaConsultaProdutos.rows.add(lis).draw(false);
            }

            $('#overlay-consulta-produtos').hide();
            carregandoProdutos = false;

        }).fail(function () {
            $('#overlay-consulta-produtos').hide();
            carregandoProdutos = false;
            toastr.error('Erro ao carregar produtos');
        });
    }
    ConsultaProdutosNew(true);

    function DesvinculadosTotal(){
        $.post(Paths.controller + 'back_ope_consulta_produtos_desvinculado_total.php', function (retorno) {
            
            console.log(retorno);

            $('.desvinculadototal').text(retorno.total);

        }, 'json'); // 👈 IMPORTANTE
    }

    function RelatorioDesvinculados(){
        // toastr.success(idRel);

        let dataInicial = '';
        let dataFinal = '';
        let loja = '';
        let gvt = '';
        let dataRec = '';
        let observ = '';
        // $.post(Paths.controller + 'back_fcx_deposito_edit_select_relatorio.php',{id:idRel}, function(retorno) {
        //     // console.log(retorno);
        //     $.each(retorno, function(index, val) {
        //         dataInicial =  Utils.formatarData(val.dtIni);
        //         dataFinal   =  Utils.formatarData(val.dtFim);
        //         loja        = val.loja;
        //         gvt         = val.gvt ?? '';
        //         dataRec     = val.dtRec ?  Utils.formatarData(val.dtRec) : '';
        //         observ      = val.observ ?? '';
        //     });
        // });
        $.post(Paths.controller + 'back_ope_consulta_produtos_desvinculado.php', function(retorno) {
            // Limpa a tabela existente
            $('#codtabDesvinculado').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // var hora = val.created.split(" ");
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.id, val.sku, val.codigo , val.produto
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relDesvinculados').DataTable({
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
                        targets: [0, 1, 2],
                        className: 'dt-body-center dt-head-center'// Centraliza as colunas selecionadas
                    }
                ],
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[1, 'asc']], // Ordena pela data de quebra de forma crescente
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
                            columns: [0,1,2,3] 
                        }
                        ,
                        customize: function (doc) {
                            doc.content.splice(0, 1); // Remove título padrão

                            const colunasCentralizadas = [0, 1, 2]; // índices do PDF

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

                                // FORMATAR CODIGO DE BARRA
                                const colunaCodigo = 2;

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
                                        { alignment: 'center', margin: [0, 10, 0, 0], italics: true, bold: true, text: 'RELATÓRIO DE PRODUTOS SEM VINCULAÇÃO\n '+
                                                                                                                        '\n ORIGEM/LOJA: ',fontSize: 16 },
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
                            
                            doc.content[0].table.widths = [30, 100 , 80, '*']; // Ajusta larguras da tabela
                        }
                    },
                ],
                "data": lis, // Insere os dados na tabela
                "columns": [
                    { title: 'Id' },
                    { title: 'SKU' },
                    { title: 'Codigo Barra'},
                    { title: 'Produto' }
                    
                ],
                createdRow: function (row, data) {
                    // // Função utilitária para formatar data YYYY-MM-DD → DD/MM/YYYY
                    // const formatarData = (data) => {
                    //     if (!data) return '';
                    //     const [ano, mes, dia] = data.split('-');
                    //     return `${dia}/${mes}/${ano}`;
                    // };

                    // // Formata datas
                    // $('td', row).eq(1).html(formatarData(data[1]));
                    // // $('td', row).eq(4).html(formatarData(data[4]));

                    // // Formata valor monetário
                    // const valor = Number(data[6]) || 0;
                    // $('td', row).eq(6).html(
                    //     `R$ ${valor.toLocaleString('pt-BR', {
                    //         minimumFractionDigits: 2,
                    //         maximumFractionDigits: 2
                    //     })}`
                    // );
                    $('td', row).eq(2).html(Utils.formatarCodigosBarra(data[2]));
                },

            });
            table.buttons().container().appendTo('#relDesvinculados_wrapper .col-md-6:eq(0)');
        });
    }

    // Ações para load 
    $(document).on('click','.syncRelat',function() {

        $('#filtro-codbarras').val('');
        $('#filtro-sku').val('');
        $('#filtro-descricao').val('');

        // 🔥 limpa filtros internos do DataTable
        if (tabelaConsultaProdutos) {
            tabelaConsultaProdutos.clear().draw();
            tabelaConsultaProdutos.search('').columns().search('').draw();
        }

        // 🔥 recarrega tudo
        ConsultaProdutosNew(true);
    });
    $(document).on('click', '.fechaatualizar', function () {
        toastr.success('Teste !');
        ConsultaProdutosNew(true);
    });
    $(document).on('click','.view-estoque',function() {
        var pega = $(this).attr("dat");
        // toastr.success(pega);

        $.post(Paths.controller + 'back_ope_consulta_produtos_detalhe.php', { sku: pega }, function(res) {
            $('#produto').empty();
            $('#produto-detalhe').empty();

            let dados = typeof res === "string" ? JSON.parse(res) : res;
            if (dados.error) {
                $('#produto').html(dados.message);
                return;
            }
            console.log(dados);
            // 🔥 agora é objeto, não array
            let html = `
                <strong id='skuproduto'>${dados.sku} - ${dados.produto}</strong><br>
            `;
            $('#produto').html(html);

            const mesesPT = {
                JAN: 'JAN', FEB: 'FEV', MAR: 'MAR',
                APR: 'ABR', MAY: 'MAI', JUN: 'JUN',
                JUL: 'JUL', AUG: 'AGO', SEP: 'SET',
                OCT: 'OUT', NOV: 'NOV', DEC: 'DEZ'
            };

            let vendasHtml = '';
            let linha = '';

            dados.grafico_saida.forEach((item, index) => {

                linha += `
                    <div class="col-6">
                        ${item.mes}: <b>${item.total}</b>
                    </div>
                `;

                // a cada 2 itens, fecha a linha
                if ((index + 1) % 2 === 0) {
                    vendasHtml += `<div class="row">${linha}</div>`;
                    linha = '';
                }
            });

            // caso sobre 1 item (ímpar)
            if (linha !== '') {
                vendasHtml += `<div class="row">${linha}</div>`;
            }



            const cor = dados.quantidade <= 0 ? 'text-danger' : 'text-success';
            const corClube = 'text-success';
            const corMax   = 'text-warning';
            const corVarejo = 'text-danger';
            const corAlteracao = 'text-primary';

            const dias = parseInt(dados.autonomia) || 0;

            // cálculo
            const anos = Math.floor(dias / 365);
            const restoAno = dias % 365;

            const meses = Math.floor(restoAno / 30);
            const diasRestantes = restoAno % 30;

            let partes = [];

            if (anos > 0) {
                partes.push(`${anos} ano${anos > 1 ? 's' : ''}`);
            }

            if (meses > 0) {
                partes.push(`${meses} ${meses > 1 ? 'meses' : 'mês'}`);
            }

            if (diasRestantes > 0) {
                partes.push(`${diasRestantes} dia${diasRestantes > 1 ? 's' : ''}`);
            }

            let texto = partes.join(' e ');

            if (!texto) texto = '0 dias';

            let htmlprecos = '';
            
            dados.listaprecos.forEach(item => {//
                htmlprecos += `
                    <b class="${corClube} text-lg">Clube: ${Utils.formatarMoeda(item.precoclube)}</b><br>
                    <b class="${corClube} text-lg">Limite: ${item.limitacaoclube}</b><br>
                    <b class="${corMax} text-lg">Clube Max: ${Utils.formatarMoeda(item.precomax)}</b><br>
                    <b class="${corMax} text-lg">Limite Max: ${item.limitacaomax}</b><br>
                    <b>Varejo: ${Utils.formatarMoeda(item.precovarejo)}</b><br>
                    <b>Limite: ${item.limitacaovarejo}</b><br>
                    <b>Última Alteração</b><br>
                    <b class="${corAlteracao}">${Utils.separaDataHora(item.consulta)}</b>
                `;
            });

            let html1 = `
                <div class="row">
                    <div class="col-sm-3 col-6">
                        <div class="description-block border-right">
                            SKU: <b>${dados.sku}</b><br>
                            Marca: <b>${dados.marca}</b><br>
                            Estoque: <b class="${cor}">${dados.quantidade}</b> Unidade<br>
                            Ult. Entrada: <b>${Utils.formatarData(dados.dataultimaentrada)}</b><br>
                            Ult. Entrada: <b>${dados.ultimaentrada}</b> UN<br>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="description-block border-right">
                            Venda em 30 dias: <b>${dados.venda30}</b><br>
                            Última Venda <br><b>${Utils.separaDataHora(dados.lastsales)}</b><br>
                            Autonomia de estoque<br>
                            <strong><span class="text-primary">${texto}</span></strong> <br>
                            <small class="text-muted">(${dias} dias)</small>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="description-block border-right">
                            Vendas em (6 meses)<br>
                            ${vendasHtml}
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="description-block border-right">
                            <b>Preços</b><br>
                            ${htmlprecos}
                            
                        </div>
                    </div>
                </div>
            `;
            $('#produto-detalhe').append(html1);

            const baseImg = Utils.appBase() + "/assets/img/produtos/";
            const fallback = baseImg + "sem-imagem.png";

            let htmlIMG = `
                <img src="${baseImg}${dados.sku}.png"
                    onerror="this.onerror=null; this.src='${fallback}';"
                    class="img-fluid">
            `;
            $('#produto-detalhe-img').html(htmlIMG);

            let htmlCodigos = '';

            dados.listacodigobarra.forEach(item => {
                htmlCodigos += `
                    <div class="col-6 col-md-4 col-lg-4 mb-2">
                        <div class="input-group shadow-sm">

                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <input type="checkbox" 
                                        data-codigo="${item.codigo}"
                                        ${item.status == 1 ? 'checked' : ''}>
                                </span>
                            </div>

                            <!-- 🔥 aqui entra o código -->
                            <input type="text" 
                                class="form-control form-control-sm text-center font-weight-bold bg-light" 
                                value="${item.codigo}" 
                                readonly>

                            <div class="input-group-append">
                                <span class="input-group-text imprimir-codigo" 
                                    style="cursor:pointer;" 
                                    data-codigo="${item.codigo}" 
                                    data-descricao="${item.descricao}">
                                    <i class="fal fa-barcode-alt"></i>
                                </span>
                            </div>
                        </div>
                       
                    </div>
                `;
            });

            let html2 = `
                <div class="row">
                    <div class="col-12 mb-2">
                        <hr>
                        <span class="text-muted">Códigos de Barra:</span>
                    </div>
                </div>

                <div class="row">
                    ${htmlCodigos}
                </div>
            `;

            $('#produto-detalhe').append(html2);
        });
        $("#modal-consulta-produto").modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('change', 'input[type=checkbox][data-codigo]', function() {

        let checkbox = $(this);
        let codigo = checkbox.data('codigo');
        let status = checkbox.is(':checked') ? 1 : 0;

        // 🔥 desmarca todos visualmente
        if (status === 1) {
            $('input[type=checkbox][data-codigo]').not(checkbox).prop('checked', false);
        }

        $.post(Paths.controller + 'back_ope_update_produtos_status_codbarra.php', {
            codigo: codigo,
            status: status
        });
    });
    $(document).on('click', '.imprimir-codigo', function () {

        let codigo = $(this).data('codigo');
        let descricao = $(this).data('descricao');

        imprimirCodigo(codigo,descricao);

    });
    $(document).on('click', '.consultaextrato', function () {
        let produto = $('#produto').val() || $('#produto').text();
        sep = produto.split("-");
        let sku = sep[0];
        
        $.post(Paths.controller + 'back_ope_consulta_produtos_extrato.php', { sku: sku }, function(res) {

            $('.timelineextrato').empty();

            // 👉 se vier erro da API
            if (res.error) {
                $('.timelineextrato').append(`<div class="text-danger">${res.message}</div>`);
                return;
            }

            let htmltimeline = '';

            res.forEach(item => {

                let icon = '';

                if (item.produto === 'Venda via integração XML') {
                    icon = "<i class='fas fa-box-usd bg-green'></i>";
                } else if (item.produto === 'Recebimento de carga') {
                    icon = "<i class='fas fa-hand-holding-box bg-blue'></i>";
                } else if (item.produto === 'Devolução') {
                    icon = "<i class='fas fa-undo bg-yellow'></i>";
                } else {
                    icon = "<i class='fas fa-box bg-red'></i>";
                }

                let bg = '';

                if (item.produto === 'Venda via integração XML') {
                    bg = "bg-green";
                } else if (item.produto === 'Recebimento de carga') {
                    bg = "bg-blue";
                } else if (item.produto === 'Devolução') {
                    bg = "bg-yellow";
                } else {
                    bg = "bg-red";
                }

                htmltimeline += `
                    <div class="time-label">
                        <span class="${bg}">${Utils.formatarData(item.data)}</span>
                    </div>

                    <div>
                        ${icon}
                        <div class="timeline-item">
                            <span class="time" style="font-size:18px";>
                                <i class="fas fa-box"></i> Qtde: ${item.total}
                            </span>
                            <h4 class="timeline-header no-border" style="font-size:18px;">
                                ${item.produto}
                            </h4>
                        </div>
                    </div>
                `;
            });

            // 👉 final da timeline
            htmltimeline += `
                <div>
                    <i class="fas fa-clock bg-gray"></i>
                </div>
            `;

            $('.timelineextrato').append(htmltimeline);

        }, 'json');
        $("#modal-consulta-extrato").modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#desvinculados',function() {
        RelatorioDesvinculados();
        $('#modal-view-desvinculados').modal({backdrop: 'static', keyboard: false});
    });
   
    
});
