
$(document).ready(function(){
    var BtnOperador = "<button type='button' class='btn btn-tool uploadbase' dat='RECEBIMENTO'><span class='badge badge-success' style='font-size: 1.1em;'>RELATÓRIO DE RECEBIMENTO <i class='fas fa-file-excel'></i></i></i></span>&nbsp;&nbsp;&nbsp;</button>";
    $('#btn-recebimento').empty();
    $('#btn-recebimento').append(BtnOperador);

    // $('#nomeoperador, #nomelideraca, #nomefuncao, #nomecolaborador').inputmask({casing:'upper'});
    // $('#valordeposito, #valoredit').inputmask('currency', Utils.maskCurrencyBR);

    //Date picker
    $('#reservationdatarme').datetimepicker({
        format: 'L'
    });

    //Timepicker
    $('#timepickerInicial, #timepickerFinal').datetimepicker({
      format: 'LT'
    })

    // Configurações do toastr responsavel pelas notificacões!!!
    toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-left",
        "closeButton": true,
        "timeOut": 5000,          // 4 segundos
        "extendedTimeOut": 1000   // 1 segundo ao tirar o mouse
    };
    toastr.options.closeButton = true;

    function infobox(aguardando,conferido,finalizado){
        var infobox = 
            "<div class='info-box mb-3 bg-warning'>"+
                "<span class='info-box-icon'><i class='far fa-hourglass-start'></i></span>"+
                "<div class='info-box-content'>"+
                    "<span class='info-box-text'>Aguar. Conferência</span>"+
                    "<span class='info-box-number'><h4>"+aguardando+"</h4></span>"+
                "</div>"+
            "</div>"+
            "<div class='info-box mb-3 bg-danger'>"+
                "<span class='info-box-icon'><i class='fas fa-check-square'></i></span>"+
                "<div class='info-box-content'>"+
                    "<span class='info-box-text'>Conferido</span>"+
                    "<span class='info-box-number'>"+conferido+"</span>"+
                "</div>"+
            "</div>"+
            "<div class='info-box mb-3 bg-info'>"+
                "<span class='info-box-icon'><i class='fas fa-box-check'></i></span>"+
                "<div class='info-box-content'>"+
                    "<span class='info-box-text'>Finalizado</span>"+
                    "<span class='info-box-number'>"+finalizado+"</span>"+
                "</div>"+
            "</div>"
          ;
        $('#boxrme').empty();
        $('#boxrme').append(infobox);
    }
    
    // Relatorio dos depositos
    function Recebimento(){
        $('#overlay-recebimento').show();
        $.post(Paths.controller + 'back_rme_relatorio_consulta.php', function(retorno) {
            setTimeout(() => {
                // Limpa a tabela existente
                $('#codtabRecebimento').empty();
                // Inicializa um array para armazenar os dados formatados
                let lis = [];
                // console.log(retorno);
                
                // Itera sobre os dados retornados e os formata para a tabela
                $.each(retorno, function(index, val) {       
                    // Concatena os IDs para identificação do item
                    var ids = val.id;
                    var chamado = val.chamado;
                    var datarecebimento = val.datarecebimento;
                    var loja = val.loja;
                    // Cria um link de exclusão e edição para cada linha
                    var del = "&nbsp;&nbsp;&nbsp;"+
                                "<td><a href='#' class='text-muted edit-rme' dat='" + chamado + "|"+ datarecebimento +"|"+ loja +"'><i class='fas fa-file-edit'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                                "<td><a href='#' class='text-muted edit-divergencia' dat=" + ids + "><i class='fas fa-exclamation-triangle'></i></a></td>&nbsp;&nbsp;&nbsp;"+
                                "<td><a href='#' class='text-muted edit-finalizar' dat=" + chamado + "><i class='fas fa-inbox-in'></i></a></td>"+
                            "&nbsp;&nbsp;&nbsp;";    
                    // Adiciona os dados na lista para preencher a tabela
                    lis.push([
                        val.loja, val.cnpj, val.datarecebimento,
                        val.qtd, val.status, val.chamado, del
                    ]);
                });
                // Inicializa a DataTable
                let table = $('#relRecebimento').DataTable({
                    // Tradução
                    language: {
                        url: Paths.language,
                    },
                    // Centralizar Titulos da tabela
                    initComplete: function() {
                         $('#relRecebimentoItem thead th').css('text-align', 'center');
                    },
                    searching: true,   // precisa ficar TRUE
                    dom: 'rtip',       // remove o campo de pesquisa visual
                    "responsive": true,  // Tabela responsiva
                    "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                    "autoWidth": false, // Mantém largura das colunas fixa
                    "pageLength": 12, // Exibe 10 registros por página
                    "columnDefs": [
                        {
                            targets: [0, 1, 2, 3, 4, 5, 6 ],
                            className: 'dt-body-center' // Centraliza as colunas selecionadas
                        }
                    ],
                    "destroy": true, // Garante recriação da tabela ao atualizar dados
                    "order": [[2, 'desc']], // Ordena pela data de quebra de forma crescente
                    "data": lis, // Insere os dados na tabela
                    columns: [
                        { title: 'LOJA'},
                        { title: 'CNPJ'},
                        { title: 'DATA RECEBIMENTO'},
                        { title: 'QUANT. NOTAS'},
                        { title: 'STATUS'},
                        { title: 'CHAMADO'},
                        { title: '', width: '100px' }
                    ],
                    createdRow: function (row, data) {
                        
                        // $('td', row).eq(2).html(Utils.formatarMoeda(data[2]));
                        $('td', row).eq(1).html(Utils.formatarCNPJ(data[1]));
                        $('td', row).eq(2).html(Utils.formatarData(data[2]));
                        
                        
                    },

                });
                table.buttons().container().appendTo('#relRecebimento_wrapper .col-md-6:eq(0)');
                $('#overlay-recebimento').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
        $.post(Paths.controller + 'back_rme_relatorio_status.php', function(retorno) {
            // console.log(retorno);
            var aguardando = retorno.aguardando;
            var conferido = retorno.conferido;
            var finalizado = retorno.finalizado;
            infobox(aguardando,conferido,finalizado);
        });
    }
    if (document.getElementById('codtabRecebimento')) {
        Recebimento();
    }

    function ViewRecebimentoItem(chamado,datarecebimento,loja){
        // toastr.success(data, 'Teste !');
        let chamadotitle = chamado;
        let datarecebi = datarecebimento;
        let nomeloja = loja;

        let divergenciasHTML = '';
        $.post(Paths.controller + 'back_rme_relatorio_divergencia.php', {chamado: chamado}, function(retorno) {
            if(retorno.length > 0){
                
                divergenciasHTML += `<ul style="margin-top:10px;">`;
                $.each(retorno, function(index, val) {
                    let texto = val.divergencia ?? 'Sem divergência';

                    // 🔥 AQUI (única mudança)
                    texto = texto.replace(/\n/g, '<br>');

                    divergenciasHTML += `
                        <li>
                            <b>Nota:</b> ${val.nota} - ${texto}
                        </li>
                    `;
                });
                divergenciasHTML += `</ul>`;
            } else {
                divergenciasHTML = `<span>Nenhuma divergência encontrada</span>`;
            }

        });
        $('#overlay-recebimento-item').show();
        $.post(Paths.controller + 'back_rme_relatorio_item.php',{chamado:chamado}, function(retorno) {
            setTimeout(() => {
            // Limpa a tabela existente
            $('#codtabRecebimentoItem').empty();
               
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // Concatena os IDs para identificação do item
                var ids = val.iditem;
                // Cria um link de exclusão e edição para cada linha
                var del = "&nbsp;&nbsp;&nbsp;"+
                            "<td><a href='#' class='text-muted editfechamento' dat=" + ids + "><i class='fad fa-pencil-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                        "&nbsp;&nbsp;&nbsp;";
         
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                   val.pos, val.cpnj, val.pedido, val.nota, val.datafaturamento, 
                   val.sku, val.produto, val.marca, val.qtdesperada,
                   '<input type="number" class="conferir" data-id="'+val.iditem+'" value="'+(parseInt(val.qtdconferida) || '')+'" style="width:60px">',
                   del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relRecebimentoItem').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relRecebimentoItem thead th').css('text-align', 'center');
                    setTimeout(() => {
                        $('.conferir').each(function () {

                            let valor = parseInt($(this).val()) || 0;
                            let esperado = parseInt($(this).closest('tr').find('td:eq(8)').text()) || 0;

                            if(valor != esperado){
                                $(this).css('background','#ffa2a2');
                            }else{
                                $(this).css('background','#a3ffa3');
                            }

                        });
                    }, 300);
                },
                "searching": false,
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 10, // Exibe 10 registros por página
                "paging": false,
                "scrollY": "55vh",
                "scrollX": true,
                "scrollCollapse": true,
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "ordering": false,
                "dom": 'Bfrtip',
                "buttons":[{
                    "extend": 'print',
                        "text": '<i class="fas fa-print"></i>',
                        "titleAttr": 'Imprimir Relatório',
                        "className": 'btn btn-primary custom-pdf-button',
                        "exportOptions": { 
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                            format: {
                                body: function (data, row, column, node) {

                                    let input = $(node).find('input.conferir');

                                    if (input.length) {
                                        return input.val() || '';
                                    }

                                    return data;
                                }
                            }
                        },

                        // Remove o título padrão
                        title: '',

                        messageTop: function () {
                            return `
                                <div style="text-align:center; margin-bottom:15px;">
                                    <h3>${nomeloja}</h3>
                                    <h4>RELATÓRIO DE CONFERÊNCIA DO CHAMADO <b>#${chamadotitle}</b> - DATA: ${Utils.formatarData(datarecebi)}</h4>
                                </div>
                            `;
                        },
                        messageBottom: function () {
                            return `
                                <div style="margin-top:10px; font-size:12px;">
                                    <hr>
                                    <b>DIVERGÊNCIAS:</b>
                                    ${divergenciasHTML}
                                </div>

                                <div style="margin-top:20px; width:100%; text-align:center;">
                                    <table style="width:50%; margin:auto;">
                                        <tr>
                                            <td style="border:none;">____________________________________</td>
                                        </tr>
                                        <tr>
                                            <td style="border:none;">CONFERENTE</td>
                                        </tr>
                                    </table>
                                </div>
                            `;
                        },

                        customize: function (win) {
                            // 🔥 usa o valor salvo
                            $(win.document.body).find('input.conferir').each(function () {

                                let valor = $(this).attr('data-value') || this.value;

                                $(this).replaceWith('<span>' + (valor ? valor : '') + '</span>');
                            });
                            $(win.document.body)
                                .css('zoom','85%');
                            // Diminui tamanho da fonte geral
                            $(win.document.body).css({
                                'font-size': '10pt',
                                'margin': '0',
                                'height': 'auto'
                            });

                            // REMOVER scroll na impressão
                            $(win.document.body).find('.dataTables_scrollBody').css({
                                'overflow': 'visible',
                                'height': 'auto'
                            });

                            $(win.document.body).find('.dataTables_scroll').css({
                                'overflow': 'visible',
                                'height': 'auto'
                            });

                            // remove altura fixa que o DataTables cria
                            $(win.document.documentElement).css('height','auto');
                            // Diminui fonte da tabela e ajusta largura
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', '8pt')
                                .css('width', '100%');

                            // 👉 Formata a coluna 4 (data) e coluna 12 (valor monetário)
                            $(win.document.body).find('table tbody tr').each(function () {
                                // ======== Coluna 4 (data) ========
                                const cellData = $('td', this).eq(4);
                                let dataTexto = cellData.text().trim();
                                cellData.text(Utils.formatarData(dataTexto));
                                // ======== Coluna 4 (data) ========
                                const cellCNPJ = $('td', this).eq(1);
                                let CNPJTexto = cellCNPJ.text().trim();
                                cellCNPJ.text(Utils.formatarCNPJ(CNPJTexto));
                            });

                            // Força modo paisagem e define estilos
                            const css = `
                                @page { 
                                    size: landscape; 
                                    margin: 8mm; 
                                }

                                html, body {
                                    height: auto !important;
                                    overflow: visible !important;
                                }

                                table {
                                    page-break-inside: auto !important;
                                }

                                tr {
                                    page-break-inside: avoid !important;
                                }

                                td, th {
                                    page-break-inside: avoid !important;
                                }

                                table.dataTable,
                                table.dataTable th,
                                table.dataTable td {
                                    border: 1px solid #000 !important;
                                    border-collapse: collapse !important;
                                }

                                table.dataTable th,
                                table.dataTable td {
                                    padding: 2px !important;
                                }

                                h3 { font-size: 14pt; }
                            `;
                            const head = win.document.head || win.document.getElementsByTagName('head')[0];
                            const style = win.document.createElement('style');
                            style.type = 'text/css';
                            style.media = 'print';
                            if (style.styleSheet) {
                                style.styleSheet.cssText = css;
                            } else {
                                style.appendChild(win.document.createTextNode(css));
                            }
                            head.appendChild(style);
                        }

                }],
                "data": lis, // Insere os dados na tabela
                "columnDefs": [
                    { targets: [0,1,2,3,4,8,9], className: 'text-center' }   // Ações
                ],
                "columns": [
                    { title: '#' },
                    { title: 'CNPJ' },
                    { title: 'Pedido' },
                    { title: 'Nota' },
                    { title: 'Data Fatur.' },
                    { title: 'SKU' },
                    { title: 'Produto' },
                    { title: 'Marca' },
                    { title: 'Esperada' },
                    { title: 'Conferido' },
                    { title: '' }
                ],
                createdRow: function (row, data) {
                    
                    $('td', row).eq(1).html(Utils.formatarCNPJ(data[1]));
                    $('td', row).eq(4).html(Utils.formatarData(data[4]));
                    // $('td', row).eq(4).html(formatarMoeda(data[4]));
                    // $('td', row).eq(5).html(formatarMoeda(data[5]));
                    // $('td', row).eq(6).html(formatarMoeda(data[6]));
                    // $('td', row).eq(7).html(formatarMoeda(data[7]));
                    // $('td', row).eq(8).html(formatarMoeda(data[8]));

                },
                
            });
            table.buttons().container().appendTo('#relRecebimentoItem_wrapper .col-md-6:eq(0)');
            $('#overlay-recebimento-item').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
    }
    $(document).on('input', '.conferir', function () {

        let valor = parseInt($(this).val()) || 0;

        // 🔥 PEGAR QUANTIDADE ESPERADA (coluna 8)
        let esperado = parseInt($(this).closest('tr').find('td:eq(8)').text()) || 0;

        // 🔥 COMPARAÇÃO
        if(valor != esperado){
            $(this).css('background','#ffa2a2'); // vermelho
        }else{
            $(this).css('background','#a3ffa3'); // verde
        }

    });
    $('#btn-salvar-conferencia').click(function () {

        var pega = $('#idrme').text();
        sep = pega.split(" ");
        var chamado = sep[1];
        
        // toastr.success(chamado);

        let table = $('#relRecebimentoItem').DataTable();
        let erro = false;
        let dados = {
            chamado: parseInt(chamado),
            itens: []
        };

        table.$('.conferir').each(function () {

            let id    = $(this).data('id');
            let valor = $(this).val();

            // 🔥 Validação
            if(valor === '' || parseInt(valor) < 0){
                erro = true;
                $(this).css('border','2px solid red');
            }else{
                $(this).css('border','');
            }

            dados.itens.push({
                iditem: id,
                qtd_conferida: parseInt(valor) || 0
            });

        });

        // ❌ Se tiver erro
        if(erro){
            toastr.warning("Preencha todos os campos corretamente!");
            return;
        }

        console.log(dados);

        // 🚀 Enviar via AJAX
        $.ajax({
            url: Paths.controller + 'back_rme_relatorio_salvar.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(dados),
            success: function (res) {
                toastr.success(res.message);
            },
            error: function () {
                toastr.error("Erro ao salvar!");
            }
        });

    });
    // Ações para load 
    $(document).on('click','.syncRelat',function() {
        Recebimento();
    });
    $(document).on('click','.uploadbase',function() {
        var pega = $(this).attr("dat");
        // toastr.success(pega);
        $('#numchamando').val('').focus();
        $('#datarme').val(moment().format('DD/MM/YYYY'));
        $('#addfile').empty();
        $('#load').empty();
       
        $('#addfile').append("<i class='fas fa-file-excel pega' dat="+pega+"></i>&nbsp;&nbsp;<span>Adicionar "+pega);
        $("#modal-update-relatorio").modal({backdrop: 'static', keyboard: false});

        
        // $('#addfile').append("<i class='fas fa-plus pega' dat="+pega+"></i><span> </span><span>Adicionar "+pega+"</span>");
        // $("#modal-update-relatorio").modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.edit-rme',function() {
        var pega = $(this).attr("dat");
        sep = pega.split("|");
        var chamado = sep[0];
        var datarec = sep[1];
        var loja = sep[2];
        // toastr.success(id, 'Teste !');
        $('#idrme').empty();
        $('#idrme').append('<b>CHAMANDO '+chamado+'</b>');
        ViewRecebimentoItem(chamado,datarec,loja);
        // Utils.selectOperadores('selectoperadors');
        // $.post(Paths.controller + 'back_fcx_fechamento_select_detalhado.php',{id:id}, function(retorno) {
        //     // console.log(retorno);
        //     $.each(retorno, function(index, val) {
        //         // console.log(val.data);
        //         $('#datafechamentodia').val(Utils.formatarData(val.data));
        //         $('#selectoperadors').val(val.operador);
        //         $('#numpdvdia').val(val.pdv);
        //         $('#saldoinicialdia').val(Utils.formatarMoeda(val.saldoini));
        //         $('#saldoposdia').val(Utils.formatarMoeda(val.saldocartao));
        //         $('#numposdia').val(val.pos);
        //         $('#saldofinaldia').val(Utils.formatarMoeda(val.saldofinal));
        //         $('#fechadepositodia').val(Utils.formatarMoeda(val.deposito));
        //         $('#fechasangriadia').val(Utils.formatarMoeda(val.sangria));
        //         $('#fechaobvservdia').val(val.observ);
        //     });
        // });
        $("#modal-recebimento-edit").modal({backdrop: 'static', keyboard: false});
    });
    $(document).on("input","#numchamando",function(){
        
        let valor = this.value;
        if(/\D/.test(valor)){
            toastr.warning("</i>DIGITE SOMENTE NÚMEROS!!!");
            this.value = valor.replace(/\D/g,'');
        }
    });
    $(document).on('click','.edit-divergencia',function() {
        var pega = $(this).attr("dat");
        toastr.success(pega);
    });
    $(document).on('click', '.edit-finalizar', function () {

        let chamado = $(this).attr("dat");

        if (!chamado) {
            toastr.error("Chamado inválido");
            return;
        }

        // 🔒 trava botão (evita duplo clique)
        let btn = $(this);
        btn.prop('disabled', true);

        $.post(Paths.controller + 'back_rme_relatorio_efetivar.php', { chamado: chamado }, function (retorno) {

            try {
                let res = JSON.parse(retorno);

                if (res.status === "success") {
                    toastr.success(res.message);

                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }

            } catch (e) {
                console.error(retorno);
                toastr.error("Erro inesperado");
                btn.prop('disabled', false);
            }

        }).fail(function () {
            toastr.error("Erro na requisição");
            btn.prop('disabled', false);
        });

    });
    $(document).on('click','.fecharmeitens',function() {
        Recebimento();
    });

    function dropup() {
        Dropzone.autoDiscover = false;

        // Template base
        const previewNode = document.querySelector("#template");
        previewNode.id = "";
        const previewTemplate = previewNode.parentNode.innerHTML;
        previewNode.parentNode.removeChild(previewNode);

        const myDropzone = new Dropzone(document.body, {
            url: Paths.controller + "back_upload.php",
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            maxFiles: 1,
            acceptedFiles: ".csv,.xls,.xlsx,.txt",
            uploadMultiple: false,
            previewTemplate: previewTemplate,
            autoQueue: false, // só envia quando clicar no botão
            previewsContainer: "#previews",
            clickable: ".fileinput-button",
            renameFile: function (file) {
                const pega = $(".pega").attr("dat");
                const extensoes = {
                    "RECEBIMENTO"       : "xlsx"
                };

                return pega in extensoes ? `${pega}.${extensoes[pega]}` : file.name;
            }
        });

        // Quando o arquivo for adicionado
        myDropzone.on("addedfile", function(file) {
            const startBtn = file.previewElement.querySelector(".start");

            // Event listener exclusivo para este arquivo
            startBtn.onclick = async function() {
                const pega = $(".pega").attr("dat");
                const data = $("#datarme").val();
                const chamado = $("#numchamando").val();

                if(!data){
                    toastr.warning('INFORME A DATA DO RECEBIMENTO!!!');
                    $("#datarme").focus();
                    return;
                }

                if(!chamado){
                    toastr.warning('INFORME NÚMERO DO CHAMANDO!!!');
                    $("#numchamando").focus();
                    return;
                }
                
                // $("#progress-text").append(
                //     "<div style='background: #f86302; border-radius: 8px 8px 0 0; color: #ffffffff; font-weight:bold; padding: 2px 6px 2px 6px; '>" +
                //     "🚀 Enviando arquivo..." +
                //     "</div>"
                // );
                $("#progress-text").append(`
                    <div class="alert alert-orange py-2 mb-0" style=" border-radius: 8px 8px 0 0;" >
                        <i class="fas fa-rocket"></i> Enviando arquivo...
                    </div>
                `);

                try {
                    // Enfileira e processa o arquivo
                    myDropzone.enqueueFile(file);

                    await new Promise((resolve, reject) => {
                        myDropzone.on("success", () => resolve());
                        myDropzone.on("error", (f, err) => reject(err));
                        myDropzone.processFile(file);
                    });

                    // ✅ Mensagem inicial
                    $("#progress-text").append(`
                        <div class="alert alert-orange py-0 mb-0 rounded-0 ">
                            <i class="fas fa-check-circle"></i> Upload concluído.
                        </div>
                    `);

                    // 🔹 Cria barra de progresso no estilo AdminLTE
                    $("#progress-text").append(`
                        <div class="card bg-orange mb-2 shadow-none" style="border-radius: 0 0 8px 8px ;">
                            <div class="card-body p-2" >
                                <div class="progress progress-xs" style="height: 20px; border-radius: 4px;">
                                    <div id="progress-bar" class="progress-bar bg-success progress-bar-striped" style="width: 0%; transition: width 0.3s;"></div>
                                </div>
                                <div id="progress-label" class="text-center font-weight-bold text-white mt-2" >
                                    <i class="fas fa-sync"></i> Iniciando...
                                </div>
                            </div>
                        </div>
                    `);

                    const evtSource = new EventSource(
                        Paths.controller + "process_dados_sse.php?nome=" + pega + "&data=" + data + "&chamado=" + chamado
                    );

                    // 🔹 Evento padrão (mensagens gerais)
                    evtSource.onmessage = function(event) {

                        if(!event.data) return;

                        $("#progress-text").append(`
                            <div class="alert alert-warning py-1 mb-1">
                                <i class="fas fa-info-circle"></i> ${event.data}
                            </div>
                        `);

                    };
                    // evtSource.onmessage = function(event) {
                    //     $("#progress-text").append(`
                    //         <div class="alert alert-warning py-1 mb-1" style="border-radius: 0 0 8px 8px ;">
                    //             <i class="fas fa-info-circle"></i> ${event.data}
                    //         </div>
                    //     `);
                    //     $("#progress-text").scrollTop($("#progress-text")[0].scrollHeight);
                    // };

                    // 🔹 Antes de iniciar o processo icone de carregando
                    $("#progress-label").html(`<i class="fa fa-spinner fa-spin fa-fw"></i> Processando: 0%`);

                    // 🔹 Evento de progresso
                    evtSource.addEventListener("progress", function(event) {

                        const data = JSON.parse(event.data);
                        let percent = parseInt(data.percent || 0);

                        $("#progress-bar").css("width", percent + "%");

                        $("#progress-label").contents().last()[0].textContent =
                            ` Processando: ${percent}%`;

                    });
                    // evtSource.addEventListener("progress", function(event) {
                    //     const percent = parseInt(event.data);
                    //     $("#progress-bar").css("width", percent + "%");
                    //     // Atualiza apenas o texto depois do ícone
                    //     $("#progress-label").contents().last()[0].textContent = ` Processando: ${percent}%`;
                    // });

                    evtSource.addEventListener("recebimento", function(event){

                        const r = JSON.parse(event.data);

                        $("#progress-text").append(`
                            <div class="alert alert-info py-1 mb-1">
                                <i class="fas fa-file-invoice"></i>
                                Processando Pedido <b>${r.pedido}</b> / Nota <b>${r.nota}</b>
                            </div>
                        `);

                    });
                    // 🔹 Evento de erro
                    evtSource.addEventListener("error", function(event) {
                        $("#progress-text").append(`
                            <div class="alert alert-danger py-1 mb-2">
                                <i class="fas fa-times-circle"></i> ${event.data || "Erro no processamento."}
                            </div>
                        `);
                        $("#progress-label").html(`<i class="fas fa-times-circle"></i> Erro no processamento`);
                        $("#progress-bar").removeClass("bg-success").addClass("bg-danger").css("width", "100%");
                        evtSource.close();
                    });
                    // 🔹 Evento de aviso (warning)
                    evtSource.addEventListener("warning", function(event) {
                        $("#progress-text").append(`
                            <div class="alert alert-warning py-1 mb-2">
                                <i class="fas fa-exclamation-triangle"></i> ${event.data || "Aviso: verifique o processamento."}
                            </div>
                        `);
                        $("#progress-label").html(`<i class="fas fa-exclamation-triangle"></i> Aviso no processamento`);
                        $("#progress-bar")
                            .removeClass("bg-success bg-danger")
                            .addClass("bg-warning")
                            .css("width", "100%");
                        evtSource.close();
                    });

                    evtSource.addEventListener("success", function(event){

                        const data = JSON.parse(event.data);

                        $("#progress-label").html(`<i class="fas fa-check-circle"></i> Processamento concluído!`);

                        $("#progress-bar")
                            .removeClass("bg-warning bg-danger")
                            .addClass("bg-success")
                            .css("width","100%");

                        $("#progress-text").append(`
                            <div class="alert alert-success py-1 mb-2">
                                <i class="fas fa-check-circle"></i> ${data.message}
                            </div>
                        `);

                    });
                    // 🔹 Evento de encerramento
                    evtSource.addEventListener("close", function(event) {
                        // $("#progress-text").append(`
                        //     <div class="alert alert-orange py-0 mb-0" >
                        //         ${event.data}
                        //     </div>
                        // `);
                        $("#progress-label").html(`<i class="fas fa-check-circle"></i> Processamento concluído!`);
                        // $("#progress-bar").css({ width: "100%", background: "#28a745" });
                        evtSource.close();
                    });

                } catch(err) {
                    $("#progress-text").append("❌ Erro no upload: " + err + "<br>");
                }
            };
            const deleteBtn = file.previewElement.querySelector(".delete");
            deleteBtn.onclick = async function() {
                $("#progress-text").empty();
            };
        });
        $('#modal-update-relatorio').on('hidden.bs.modal', function() {

            // limpa logs de progresso
            $("#progress-text").empty();

            // limpa barra de progresso
            $("#progress-bar").css("width","0%");

            // limpa label
            $("#progress-label").html("");

            // remove arquivos do dropzone
            if (myDropzone) {
                myDropzone.removeAllFiles(true);
            }
        });


    }
    dropup();
    
    
}); 