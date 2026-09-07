
$(document).ready(function(){
    var BtnAvaria = `
        <a href="javascript:void(0)"
        class="btn btn-app bg-success addavaria  mr-3"
        dat="ADDAVARIA">
            <i class="fad fa-plus-circle"></i>
            Adicionar
        </a>
        <a href="javascript:void(0)"
        class="btn btn-app bg-primary btn-atualiza-avaria  mr-3"
        dat="UPLAVARIA">
            <i class="fad fa-arrow-alt-circle-up"></i>
            Atualizar
        </a>
    `;

    $('#btn-add-avaria').empty().append(BtnAvaria);

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


    let html5QrCode = null;
    let leitorAtivo = false;

    $(document).on('click', '#btn-toggle-reader', function () {

        if (leitorAtivo) {
            pararLeitorCodigo();
        } else {
            abrirLeitorCodigo();
        }

    });

    function abrirLeitorCodigo() {
        const boxReader = document.getElementById("box-reader");
        const reader = document.getElementById("reader");

        if (!boxReader || !reader) {
            toastr.warning("Elemento do leitor não encontrado.");
            return;
        }

        boxReader.style.display = "block";

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 150
                }
            },
            function (decodedText) {
                $("#codigo_barras").val(decodedText);
                pararLeitorCodigo();
            },
            function () {
                // ignora erros enquanto escaneia
            }
        ).then(function () {
            leitorAtivo = true;

            $('#btn-toggle-reader')
                .removeClass('btn-primary')
                .addClass('btn-danger')
                .html('<i class="fas fa-times"></i>');
        }).catch(function (err) {
            leitorAtivo = false;
            boxReader.style.display = "none";

            $('#btn-toggle-reader')
                .removeClass('btn-danger')
                .addClass('btn-primary')
                .html('<i class="fas fa-camera"></i>');

            if (!window.isSecureContext) {
                toastr.error("A câmera só funciona em HTTPS.");
            } else {
                toastr.error("Erro ao abrir câmera: " + err);
            }
        });
    }

    function pararLeitorCodigo() {
        const boxReader = document.getElementById("box-reader");

        if (!html5QrCode || !leitorAtivo) {
            leitorAtivo = false;

            if (boxReader) {
                boxReader.style.display = "none";
            }

            $('#btn-toggle-reader')
                .removeClass('btn-danger')
                .addClass('btn-primary')
                .html('<i class="fas fa-camera"></i>');

            return;
        }

        html5QrCode.stop().then(function () {
            leitorAtivo = false;

            if (boxReader) {
                boxReader.style.display = "none";
            }

            $('#btn-toggle-reader')
                .removeClass('btn-danger')
                .addClass('btn-primary')
                .html('<i class="fas fa-camera"></i>');

        }).catch(function (err) {
            leitorAtivo = false;

            if (boxReader) {
                boxReader.style.display = "none";
            }

            $('#btn-toggle-reader')
                .removeClass('btn-danger')
                .addClass('btn-primary')
                .html('<i class="fas fa-camera"></i>');

            console.log("Erro ao parar leitor:", err);
        });
    }

    let arquivosFotosAvaria = [];
    let dataTransferFotos = new DataTransfer();

    $(document).on('change', '#fotos_avaria', function (event) {
        const novosArquivos = Array.from(event.target.files);

        if (novosArquivos.length === 0) {
            return;
        }

        novosArquivos.forEach(function (file) {
            if (!file.type.startsWith('image/')) {
                toastr.warning('Arquivo ignorado: selecione apenas imagens.');
                return;
            }

            arquivosFotosAvaria.push(file);
        });

        // limpa antes de reconstruir o input com DataTransfer
        $('#fotos_avaria').val('');

        atualizarInputFotos();
        renderizarListaFotos();

    });

    function atualizarInputFotos() {
        dataTransferFotos = new DataTransfer();

        arquivosFotosAvaria.forEach(function (file) {
            dataTransferFotos.items.add(file);
        });

        document.getElementById('fotos_avaria').files = dataTransferFotos.files;

        let total = arquivosFotosAvaria.length;

        if (total === 0) {
            $('#label_fotos_avaria').val('Tirar foto do produto');
        } else if (total === 1) {
            $('#label_fotos_avaria').val('1 foto adicionada');
        } else {
            $('#label_fotos_avaria').val(total + ' fotos adicionadas');
        }
    }

    function renderizarListaFotos() {
        const lista = $('#lista-fotos-avaria');
        lista.empty();

        arquivosFotosAvaria.forEach(function (file, index) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const html = `
                    <div class="col-6 col-md-3 foto-preview-card" data-index="${index}">
                        <button 
                            type="button" 
                            class="btn btn-danger btn-sm btn-remover-foto remover-foto-avaria" 
                            data-index="${index}"
                            title="Excluir foto"
                        >
                            <i class="fas fa-times"></i>
                        </button>

                        <div class="foto-preview-box">
                            <img src="${e.target.result}" alt="Foto ${index + 1}">
                        </div>

                        <small class="d-block text-center mt-1">
                            Foto ${index + 1}
                        </small>
                    </div>
                `;

                lista.append(html);
            };

            reader.readAsDataURL(file);
        });
    }

    $(document).on('click', '.remover-foto-avaria', function () {
        const index = parseInt($(this).attr('data-index'));

        arquivosFotosAvaria.splice(index, 1);

        atualizarInputFotos();
        renderizarListaFotos();
    });
    

    function infobox(aguardando,conferido,finalizado){
        var infobox = 
            "<div class='info-box mb-2 bg-warning'>"+
                "<span class='info-box-icon'><i class='far fa-hourglass-start'></i></span>"+
                "<div class='info-box-content'>"+
                    "<span class='info-box-text'>Aguar. Conferência</span>"+
                    "<span class='info-box-number'><h4>"+aguardando+"</h4></span>"+
                "</div>"+
            "</div>"+
            "<div class='info-box mb-2 bg-danger'>"+
                "<span class='info-box-icon'><i class='fas fa-check-square'></i></span>"+
                "<div class='info-box-content'>"+
                    "<span class='info-box-text'>Conferido</span>"+
                    "<span class='info-box-number'>"+conferido+"</span>"+
                "</div>"+
            "</div>"+
            "<div class='info-box mb-2 bg-info'>"+
                "<span class='info-box-icon'><i class='fas fa-box-check'></i></span>"+
                "<div class='info-box-content'>"+
                    "<span class='info-box-text'>Finalizado</span>"+
                    "<span class='info-box-number'>"+finalizado+"</span>"+
                "</div>"+
            "</div>"
          ;
        $('#boxavaria').empty();
        $('#boxavaria').append(infobox);
    }

    function montarCaminhoFoto(caminho) {
        caminho = caminho || '';

        // remove barras iniciais
        caminho = caminho.replace(/^\/+/, '');

        // se já for URL completa, mantém
        if (caminho.startsWith('http://') || caminho.startsWith('https://')) {
            return caminho;
        }

        // projeto rodando em /vestcasa/
        return '/vestcasa/' + caminho;
    }

    function renderizarFotosExistentes(fotos) {
        const lista = $('#editlista-fotos-existentes');
        lista.empty();

        if (!fotos || fotos.length === 0) {
            lista.html('<div class="col-12 text-muted">Nenhuma foto cadastrada.</div>');
            return;
        }

        fotos.forEach(function (foto) {

            let fotoOriginal = montarCaminhoFoto(foto.url_arquivo || foto.caminho_arquivo || '');
            let fotoPreview  = montarCaminhoFoto(foto.thumb_arquivo || foto.url_arquivo || foto.caminho_arquivo || '');

            const html = `
                <div class="foto-preview-card foto-existente-card" data-id="${foto.id}">
                    <button 
                        type="button" 
                        class="btn btn-danger btn-sm btn-remover-foto remover-foto-existente-avaria" 
                        data-id="${foto.id}"
                        title="Excluir foto"
                    >
                        <i class="fas fa-times"></i>
                    </button>

                    <div class="foto-preview-box">
                        <img 
                            src="${fotoPreview}" 
                            alt="${foto.nome_arquivo || 'Foto'}"
                            class="abrir-view-foto-avaria"
                            data-full="${fotoOriginal}"
                            loading="lazy"
                        >
                    </div>

                    <small class="d-block text-center mt-1">
                        Foto
                    </small>
                </div>
            `;

            lista.append(html);
        });
    }

    let arquivosFotosAvariaEdit = [];
    let dataTransferFotosEdit = new DataTransfer();

    $(document).on('change', '#editfotos_avaria', function (event) {
        const novosArquivos = Array.from(event.target.files);

        if (novosArquivos.length === 0) {
            return;
        }

        novosArquivos.forEach(function (file) {
            if (!file.type.startsWith('image/')) {
                toastr.warning('Arquivo ignorado: selecione apenas imagens.');
                return;
            }

            arquivosFotosAvariaEdit.push(file);
        });

        $('#editfotos_avaria').val('');

        atualizarInputFotosEdit();
        renderizarListaFotosEdit();
    });

    function atualizarInputFotosEdit() {
        dataTransferFotosEdit = new DataTransfer();

        arquivosFotosAvariaEdit.forEach(function (file) {
            dataTransferFotosEdit.items.add(file);
        });

        document.getElementById('editfotos_avaria').files = dataTransferFotosEdit.files;

        let total = arquivosFotosAvariaEdit.length;

        if (total === 0) {
            $('#editlabel_fotos_avaria').val('Tirar foto do produto');
        } else if (total === 1) {
            $('#editlabel_fotos_avaria').val('1 foto adicionada');
        } else {
            $('#editlabel_fotos_avaria').val(total + ' fotos adicionadas');
        }
    }

    function renderizarListaFotosEdit() {
        const lista = $('#editlista-fotos-avaria');
        lista.empty();

        arquivosFotosAvariaEdit.forEach(function (file, index) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const html = `
                    <div class="foto-preview-card" data-index="${index}">
                        <button 
                            type="button" 
                            class="btn btn-danger btn-sm btn-remover-foto remover-foto-avaria-edit" 
                            data-index="${index}"
                            title="Excluir foto"
                        >
                            <i class="fas fa-times"></i>
                        </button>

                        <div class="foto-preview-box">
                            <img 
                                src="${e.target.result}" 
                                alt="Foto ${index + 1}"
                                class="abrir-view-foto-avaria"
                                data-src="${e.target.result}"
                            >
                        </div>

                        <small class="d-block text-center mt-1">
                            Nova ${index + 1}
                        </small>
                    </div>
                `;

                lista.append(html);
            };

            reader.readAsDataURL(file);
        });
    }

    $(document).on('click', '.remover-foto-avaria-edit', function () {
        const index = parseInt($(this).attr('data-index'));

        arquivosFotosAvariaEdit.splice(index, 1);

        atualizarInputFotosEdit();
        renderizarListaFotosEdit();
    });

    $(document).on('click', '.remover-foto-existente-avaria', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let fotoId = $(this).data('id');
        let card = $(this).closest('.foto-existente-card');

        if (!confirm('Deseja remover esta foto?')) {
            return;
        }

        $.ajax({
            url: Paths.controller + 'back_avarias_relatorio_foto_remover.php',
            type: 'POST',
            dataType: 'json',
            data: {
                id_foto: fotoId
            },
            success: function (retorno) {
                if (retorno.error === 101) {
                    toastr.success(retorno.message || 'Foto removida.');

                    card.remove();

                    if ($('#editlista-fotos-existentes .foto-existente-card').length === 0) {
                        $('#editlista-fotos-existentes').html(
                            '<div class="col-12 text-muted">Nenhuma foto cadastrada.</div>'
                        );
                    }

                } else {
                    toastr.error(retorno.message || 'Erro ao remover foto.');
                }
            },
            error: function () {
                toastr.error('Erro de comunicação ao remover foto.');
            }
        });
    });
    
    // Relatorio dos depositos
    function Recebimento(){
        $('#overlay-recebimento').show();
        $.post(Paths.controller + 'back_avarias_relatorio.php', function(retorno) {
            setTimeout(() => {
                // Limpa a tabela existente
                $('#codtabAvarias').empty();
                // Inicializa um array para armazenar os dados formatados
                let lis = [];
                // console.log(retorno);
                
                // Itera sobre os dados retornados e os formata para a tabela
                $.each(retorno, function(index, val) {       
                    // Concatena os IDs para identificação do item
                    var avaria = val.codigoavaria;
                    var relatorio = val.descricao;
                    
                    // Cria um link de exclusão e edição para cada linha
                    var del = "&nbsp;&nbsp;&nbsp;"+
                                "<td><a href='#' class='text-muted edit-avarias' dat='" + avaria + "|" + relatorio + "'><i class='fas fa-file-edit'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                            "&nbsp;&nbsp;&nbsp;";    
                    // Adiciona os dados na lista para preencher a tabela
                    lis.push([
                        val.empresa, val.codigoavaria, val.descricao, val.situacao,
                        val.quantidade, val.totalvalor, val.datacriacao, del
                    ]);
                });
                // Inicializa a DataTable
                let table = $('#relAvarias').DataTable({
                    // Tradução
                    "language": {
                        url: Paths.language,
                    },
                    // Centralizar Titulos da tabela
                    initComplete: function() {
                         $('#relAvarias thead th').css('text-align', 'center');
                    },
                    "searching": true,   // precisa ficar TRUE
                    "dom": 'rtip',       // remove o campo de pesquisa visual
                    "responsive": true,  // Tabela responsiva
                    "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                    "autoWidth": false, // Mantém largura das colunas fixa
                    "pageLength": 12, // Exibe 10 registros por página
                    "columnDefs": [
                        // Centraliza as colunas
                        {
                            targets: [0, 1, 2, 3, 4, 5, 6, 7],
                            className: 'dt-body-center'
                        },

                        // Prioridade no mobile
                        {
                            targets: 1, // AVARIA
                            responsivePriority: 1
                        },
                        {
                            targets: 2, // DESCRIÇÃO
                            responsivePriority: 2
                        },
                        {
                            targets: 4, // QUANT. ITENS
                            responsivePriority: 3
                        },
                        {
                            targets: 5, // TOTAL
                            responsivePriority: 4
                        },
                        {
                            targets: 7, // AÇÕES
                            responsivePriority: 5
                        },

                        // Colunas que podem sumir primeiro
                        {
                            targets: 6, // DATA CRIAÇÃO
                            responsivePriority: 100
                        },
                        {
                            targets: 3, // SITUAÇÃO
                            responsivePriority: 101
                        },
                        {
                            targets: 0, // LOJA
                            responsivePriority: 102
                        }
                    ],
                    "destroy": true, // Garante recriação da tabela ao atualizar dados
                    "order": [[1, 'desc']], // Ordena pela data de quebra de forma crescente
                    "data": lis, // Insere os dados na tabela
                    "columns": [
                        { title: 'LOJA'},
                        { title: 'AVARIA'},
                        { title: 'DESCRIÇÃO'},
                        { title: 'SITUAÇÃO'},
                        { title: 'QUANT. ITENS'},
                        { title: 'TOTAL'},
                        { title: 'DATA CRIAÇÃO'},
                        { title: '', width: '100px' }
                    ],
                    createdRow: function (row, data) {
                        $('td', row).eq(5).html(Utils.formatarMoeda(data[5]));
                    },

                });
                table.buttons().container().appendTo('#relAvarias_wrapper .col-md-6:eq(0)');
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
    Recebimento();

    function ViewAvariaItem(avaria){
        // toastr.success(data, 'Teste !');
        let avariaS = avaria;

        $('#overlay-avaria-item').show();
        $.post(Paths.controller + 'back_avaria_relatorio_itens.php',{avaria:avariaS}, function(retorno) {
            setTimeout(() => {
            // Limpa a tabela existente
            $('#codtabAvariaItem').empty();
               
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
               // Identificação do item
                var ids = val.idava || '';

                var codigoAvaria = val.codavaria || '';
                var codigoBarras = val.codbarras || '';
                var tipoAvaria   = val.tipoavaria || '';
                var quantidade   = val.quantidade || '';
                var observacao   = val.observacao || '';

                var descricaoCompleta = (val.descricao || '').trim();

                var SKU = '';
                var descricao = '';

                if (descricaoCompleta !== '') {

                    var separaDescricao = descricaoCompleta.split(" - ");

                    if (separaDescricao.length >= 2) {
                        SKU = separaDescricao[0] || '';
                        descricao = separaDescricao.slice(1).join(" - ") || '';
                    } else {
                        SKU = '';
                        descricao = descricaoCompleta;
                    }

                } else {
                    SKU = '';
                    descricao = codigoBarras;
                }

                // Cria um link de exclusão e edição para cada linha
                var del = `
                    <a href="#"
                    class="text-muted editAvaria"
                    data-id="${ids}"
                    data-avaria="${codigoAvaria}">
                        <i class="fad fa-pencil-alt"></i>
                    </a>

                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                    <a href="#"
                    class="text-muted delAvaria"
                    data-id="${ids}"
                    data-avaria="${codigoAvaria}">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                `;
         
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                   val.codavaria, val.codbarras, SKU, descricao,  
                   val.tipoavaria, val.quantidade, val.valor, val.observacao, del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relAvariaItem').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relAvariaItem thead th').css('text-align', 'center');
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
                // "dom": 'rtip',
                "data": lis, // Insere os dados na tabela
                 columns: [
                    { title: 'AVARIA', className: 'not-mobile text-center' },
                    { title: 'CODIGO BARRAS', className: 'not-mobile text-center' },
                    { title: 'SKU', className: 'not-mobile text-center' },
                    { title: 'DESCRIÇÃO', className: 'all text-left' },
                    { title: 'TIPO AVARIA', className: 'not-mobile text-center' },
                    { title: 'QUANTIDADE', className: 'not-mobile text-center' },
                    { title: 'VALOR', className: 'not-mobile text-center' },
                    { title: 'OBSERVAÇÃO', className: 'not-mobile text-left', width: '100px' },
                    { title: '', className: 'not-mobile text-center' }
                ],

                columnDefs: [
                    {
                        targets: 3,
                        responsivePriority: 1
                    },
                    {
                        targets: 8,
                        orderable: false,
                        searchable: false
                    }
                ],
                createdRow: function (row, data) {
                    
                    // $('td', row).eq(1).html(Utils.formatarCNPJ(data[1]));
                    // $('td', row).eq(4).html(Utils.formatarData(data[4]));
                    // $('td', row).eq(4).html(formatarMoeda(data[4]));
                    // $('td', row).eq(5).html(formatarMoeda(data[5]));
                    // $('td', row).eq(6).html(formatarMoeda(data[6]));
                    // $('td', row).eq(7).html(formatarMoeda(data[7]));
                    // $('td', row).eq(8).html(formatarMoeda(data[8]));

                },
                
            });
            table.buttons().container().appendTo('#relAvariaItem_wrapper .col-md-6:eq(0)');

            setTimeout(function () {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#relAvariaItem')) {
                    $('#relAvariaItem').DataTable().columns.adjust().responsive.recalc();
                }
                restaurarScrollModalAvarias();
            }, 100);
            
            $('#overlay-avaria-item').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
    }

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
    $(document).on('click','.addavaria',function() {
        var pega = $(this).attr("dat");
        // toastr.success(pega);

        Utils.selectTipoAvaria();
        
        $("#modal-add-avaria").modal({backdrop: 'static', keyboard: false});

        
        // $('#addfile').append("<i class='fas fa-plus pega' dat="+pega+"></i><span> </span><span>Adicionar "+pega+"</span>");
        // $("#modal-update-relatorio").modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.edit-avarias',function() {
        var pega = $(this).attr("dat");
        sep = pega.split('|')
        var avaria = sep[0];
        var nome = sep[1];
        
        $('#codigo_avaria').val(avaria);
        
        // toastr.success(pega, 'Teste !');
        $('#idavaria').empty();
        $('#idavaria').append('<b># '+avaria+'</b> '+nome);

        $("#modal-avarias-edit")
            .off('shown.bs.modal.avarias')
            .one('shown.bs.modal.avarias', function () {
                ViewAvariaItem(avaria);
            })
            .modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click', '#btn-edit-salvar-avaria', function () {

        let formData = new FormData();

        formData.append('id_item', $('#editid_item').val());
        formData.append('codigo_avaria', $('#editcodigo_avaria').val());
        formData.append('codigo_barras', $('#editcodigo_barras').val());
        formData.append('tipoavaria', $('#edittipoavaria').val());
        formData.append('quantidade', $('#editquantidade').val());
        formData.append('observacao', $('#editobservacao').val());

        arquivosFotosAvariaEdit.forEach(function (file) {
            formData.append('fotos_avaria[]', file);
        });

        $.ajax({
            url: Paths.controller + 'back_avarias_relatorio_item_editar.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#btn-edit-salvar-avaria').prop('disabled', true).html('Salvando...');
            },
            success: function (retorno) {

                $('#btn-edit-salvar-avaria').prop('disabled', false).html('Salvar Alterações');

                if (retorno.error === 101) {
                    toastr.success(retorno.message || 'Item atualizado com sucesso.');

                    arquivosFotosAvariaEdit = [];
                    dataTransferFotosEdit = new DataTransfer();

                    $('#editlista-fotos-avaria').empty();
                    $('#editlabel_fotos_avaria').val('Tirar foto do produto');

                    atualizarInputFotosEdit();

                    $('#modal-edit-avaria').modal('hide');

                    let codigoAvariaAtual = $('#editcodigo_avaria').val();

                    if (typeof ViewAvariaItem === 'function') {
                        ViewAvariaItem(codigoAvariaAtual);
                    }

                } else {
                    toastr.error(retorno.message || 'Erro ao atualizar item.');
                }
            },
            error: function () {
                $('#btn-edit-salvar-avaria').prop('disabled', false).html('Salvar Alterações');
                toastr.error('Erro de comunicação ao salvar edição.');
            }
        });
    });

    $(document).on('click', '.editAvaria', function (e) {
        e.preventDefault();

        let itemId = $(this).data('id');
        let codigoAvaria = $(this).data('avaria');

        if (!itemId) {
            toastr.error('ID do item não encontrado.');
            return;
        }

        $('#form-edit-avaria')[0].reset();

        $('#editlista-fotos-avaria').empty();
        $('#editlista-fotos-existentes').empty();

        arquivosFotosAvariaEdit = [];
        dataTransferFotosEdit = new DataTransfer();

        atualizarInputFotosEdit();

        $('#editid_item').val(itemId);
        $('#editcodigo_avaria').val(codigoAvaria);

        $('#idavariaedit').html('<b>' + codigoAvaria + '</b>');

        $.ajax({
            url: Paths.controller + 'back_avarias_relatorio_item_buscar.php',
            type: 'POST',
            dataType: 'json',
            data: {
                id_item: itemId
            },
            beforeSend: function () {
                $('#editload').html('<div class="spinner-border" role="status"></div>');
            },
            success: function (retorno) {

                $('#editload').html('');

                if (retorno.error !== 101) {
                    toastr.error(retorno.message || 'Erro ao buscar item.');
                    return;
                }

                let item = retorno.item;

                $('#editid_item').val(item.id);
                $('#editcodigo_avaria').val(item.codigo_avaria);
                $('#editcodigo_barras').val(item.codigo_barras);
                // $('#edittipoavaria').val(item.tipo_avaria);
                Utils.selectTipoAvariaEdit(item.tipo_avaria);
                $('#editquantidade').val(parseInt(item.quantidade));
                $('#editobservacao').val(item.observacao);

                renderizarFotosExistentes(retorno.fotos);

                $("#modal-edit-avaria").modal({
                    backdrop: 'static',
                    keyboard: false
                });
            },
            error: function () {
                $('#editload').html('');
                toastr.error('Erro de comunicação ao buscar item.');
            }
        });
    });

    function restaurarScrollModalAvarias() {
        setTimeout(function () {
            if ($('#modal-avarias-edit').hasClass('show') || $('#modal-avarias-edit').is(':visible')) {
                $('body').addClass('modal-open');

                $('#modal-avarias-edit').css({
                    'overflow': 'hidden'
                });

                $('#modal-avarias-edit .modal-avarias-body').css({
                    'overflow': 'hidden'
                });

                $('#modal-avarias-edit .modal-avarias-table-scroll').css({
                    'overflow-y': 'auto',
                    'overflow-x': 'hidden'
                });

                if ($('.modal-backdrop').length > 1) {
                    $('.modal-backdrop').not(':last').remove();
                }
            }
        }, 150);
    }

    $(document).on('hidden.bs.modal', '#modal-edit-avaria', function () {
        restaurarScrollModalAvarias();
    });

    $(document).on('hidden.bs.modal', '#modal-add-avaria, #modal-upload-avaria, #modal-view-foto-avaria', function () {
        restaurarScrollModalAvarias();
    });

    $(document).on('shown.bs.modal', '#modal-avarias-edit', function () {
        restaurarScrollModalAvarias();

        setTimeout(function () {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#relAvariaItem')) {
                $('#relAvariaItem').DataTable().columns.adjust().responsive.recalc();
            }
        }, 250);
    });

    $(document).on('click', '#btn-add-salvar-avaria', function () {

        let codigoAvaria = $('#codigo_avaria').val();
        let codigoBarras = $('#codigo_barras').val().trim();
        let tipoAvaria   = $('#tipoavaria').val();
        let quantidade   = $('#quantidade').val();
        let observacao   = $('#observacao').val().trim();
        let fotos        = document.getElementById('fotos_avaria').files;

        if (!codigoAvaria) {
            toastr.error('Código da avaria não encontrado!');
            return;
        }

        if (!codigoBarras) {
            toastr.error('Informe o código de barras!');
            $('#codigo_barras').focus();
            return;
        }

        if (!tipoAvaria) {
            toastr.error('Selecione o tipo de avaria!');
            $('#tipoavaria').focus();
            return;
        }

        if (!quantidade) {
            toastr.error('Selecione a quantidade!');
            $('#quantidade').focus();
            return;
        }

        if (fotos.length === 0) {
            toastr.error('Adicione pelo menos uma foto do produto!');
            return;
        }

        let formData = new FormData($('#form-add-avaria')[0]);

        $.ajax({
            url: Paths.controller + 'back_avarias_relatorio_item_insert.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,

            beforeSend: function () {
                $('#btn-add-salvar-avaria')
                    .prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

                $('#load').html(`
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Salvando...</span>
                    </div>
                `);
            },

            success: function (retorno) {
                $('#load').empty();

                if (retorno.error == 101) {
                    toastr.success(retorno.message);

                    $('#form-add-avaria')[0].reset();

                    // limpa fotos acumuladas se você estiver usando array/DataTransfer
                    if (typeof arquivosFotosAvaria !== 'undefined') {
                        arquivosFotosAvaria = [];
                    }

                    if (typeof dataTransferFotos !== 'undefined') {
                        dataTransferFotos = new DataTransfer();
                    }

                    $('#lista-fotos-avaria').empty();
                    $('#label_fotos_avaria').val('Tirar foto do produto');

                    $('#modal-add-avaria').modal('hide');

                    // se quiser recarregar os itens da avaria
                    if (typeof ViewAvariaItem === 'function') {
                        ViewAvariaItem(codigoAvaria);
                    }

                } else {
                    toastr.error(retorno.message);
                }
            },

            error: function (xhr) {
                $('#load').empty();

                console.log(xhr.responseText);
                toastr.error('Erro na requisição ao salvar avaria.');
            },

            complete: function () {
                $('#btn-add-salvar-avaria')
                    .prop('disabled', false)
                    .html('Salvar');
            }
        });
    });

    // ======================================================
    // DELETAR ITEM DA AVARIA
    // ======================================================
    $(document).on('click', '.delAvaria', function (e) {
        e.preventDefault();

        let itemId = $(this).data('id');
        let codigoAvaria = $(this).data('avaria');

        if (!itemId) {
            toastr.error('ID do item não encontrado.');
            return;
        }

        if (!confirm('Deseja realmente excluir este item e todas as fotos vinculadas?')) {
            return;
        }

        $.ajax({
            url: Paths.controller + 'back_avarias_relatorio_item_delete.php',
            type: 'POST',
            dataType: 'json',
            data: {
                id_item: itemId
            },
            beforeSend: function () {
                toastr.info('Excluindo item...');
            },
            success: function (retorno) {

                if (retorno.error === 101) {
                    toastr.success(retorno.message || 'Item excluído com sucesso.');

                    if (typeof ViewAvariaItem === 'function') {
                        ViewAvariaItem(codigoAvaria);
                    }

                } else {
                    toastr.error(retorno.message || 'Erro ao excluir item.');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                toastr.error('Erro de comunicação ao excluir item.');
            }
        });
    });

    $(document).on('click', '.abrir-view-foto-avaria', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let src = $(this).data('full') || $(this).data('src') || $(this).attr('src');

        if (!src) {
            toastr.error('Imagem não encontrada.');
            return;
        }

        $('#view-foto-avaria-img').attr('src', '');

        $('#modal-view-foto-avaria').modal({
            backdrop: true,
            keyboard: true
        });

        $('#view-foto-avaria-img').attr('src', src);
    });

    // ======================================================
    // CORRIGE SCROLL AO FECHAR MODAL DE VISUALIZAÇÃO
    // QUANDO EXISTE MODAL DE EDIÇÃO ABERTO POR BAIXO
    // ======================================================
    $(document).on('hidden.bs.modal', '#modal-view-foto-avaria', function () {

        if ($('#modal-edit-avaria').is(':visible')) {

            $('body').addClass('modal-open');

            $('#modal-edit-avaria').css({
                'overflow-y': 'auto'
            });
        }

    });

    $(document).on('click','.btn-atualiza-avaria',function() {
        var pega = $(this).attr("dat");
        
        // toastr.success(pega);
        var idupava = $('#idavaria').text();
        // toastr.success(idupava);
        $('#idupava').empty();
        $('#idupava').append('<b> '+idupava+'</b>');
        
        $("#modal-upload-avaria").modal({backdrop: 'static', keyboard: false});

        
        // $('#addfile').append("<i class='fas fa-plus pega' dat="+pega+"></i><span> </span><span>Adicionar "+pega+"</span>");
        // $("#modal-update-relatorio").modal({backdrop: 'static', keyboard: false});
    });

    // ======================================================
    // ATUALIZAR AVARIAS
    // ======================================================
    $(document).on('click', '#btn-add-fila-avaria', function() {

        const processoAva = "atualizaAvarias.py";
        var pega = $('#idavaria').text().trim();

        // Pega somente o número
        const data = pega.split(' ')[1];


        // toastr.warning(processoAva);

        // toastr.success(data);

        $.post(
            Paths.controller + 'back_fila_criar_execucao.php',
            {
                processo: processoAva,
                data: data
            },
            function (retorno) {

                if (!retorno.success) {

                    if (retorno.message.includes("JÁ ESTÁ")) {
                        toastr.warning(retorno.message);
                    } else {
                        toastr.error(retorno.message);
                    }

                    return;
                }

                toastr.success(retorno.message);

            },
            'json' // 👈 IMPORTANTE garantir que trate como JSON
        );
        $('#modal-upload-avaria').modal('hide');
    });

    // ======================================================
    // ATUALIZAR RELATÓRIO DE AVARIAS (FILA)
    // ======================================================
    $(document).on('click', '.btn-fila-avaria-relatorio', function() {
        $('#modal-fila-avaria-relatorio').modal({backdrop: 'static', keyboard: false});
    });

    $(document).on('click', '#btn-confirmar-fila-avaria-relatorio', function() {

        const processo = "atualizaAvariaRelatorio.py";
        const $btn = $(this);

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Atualizando...');

        $.post(
            Paths.controller + 'back_fila_criar_execucao.php',
            {
                processo: processo
            },
            function (retorno) {

                if (!retorno.success) {

                    if (retorno.message.includes("JÁ ESTÁ")) {
                        toastr.warning(retorno.message);
                    } else {
                        toastr.error(retorno.message);
                    }

                    return;
                }

                toastr.success(retorno.message);

            },
            'json' // 👈 IMPORTANTE garantir que trate como JSON
        ).always(function () {
            $btn.prop('disabled', false).html('Atualizar');
            $('#modal-fila-avaria-relatorio').modal('hide');
        });
    });

});
