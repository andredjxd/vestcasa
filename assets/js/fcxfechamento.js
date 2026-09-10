
$(document).ready(function(){
    $('#saldoinicial, #saldofinal, #fechadeposito, #fechasangria, #saldopos,'
        +'#saldoinicialdia, #fechaquebra, #fechafundo, #fechaquebradia, #fechafundodia'
    ).inputmask('currency', Utils.maskCurrencyBR);
    // Date picker (inicial e final)
    $('#reservationdatefechamento, #reservationdatefechamentodia').datetimepicker({
        format: 'L'
    });
    // Quando selecionar a data → voltar foco para o input
    $('#reservationdatefechamento').on('change.datetimepicker', function () {

        setTimeout(function () {
            $('#dataafechamento').focus();
            $('#dataafechamento')[0].setSelectionRange(
                $('#dataafechamento').val().length,
                $('#dataafechamento').val().length
            );
        }, 100);

    });
    // Relatorio dos depositos
    function RelatorioFechamento(){
        $('#overlay-fechamento').show();
        $.post(Paths.controller + 'back_fcx_fechamento_select.php', function(retorno) {
            setTimeout(() => {
            // Limpa a tabela existente
            $('#codtabFechamento').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {
                // Cria um link de exclusão e edição para cada linha
                var del = "&nbsp;&nbsp;&nbsp;"+
                            "<td><a href='#' class='text-muted view-fechamento' dat=" + val.data + "><i class='fas fa-eye'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                            
                            "<td><a href='#' class='text-muted view-whatsapp' dat=" + val.data + "><i class='fab fa-whatsapp'></i></a></td>"+
                        "&nbsp;&nbsp;&nbsp;";           
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.data, val.totalpos, val.totaldeposito, val.totalsangria, val.totalgeral, del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relDepositoFechamento').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relDepositoFechamento thead th').css('text-align', 'center');
                },
                "searching": false,
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 13, // Exibe 10 registros por página
                "columnDefs": [
                    {
                        targets: [0, 1, 2, 3, 4, 5],
                        className: 'dt-body-center' // Centraliza as colunas selecionadas
                    }
                ],
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[0, 'desc']], // Ordena pela data de quebra de forma crescente
                "data": lis, // Insere os dados na tabela
                "columns": [
                    { title: 'Data' },
                    { title: 'Valor Cartão' },
                    { title: 'Valor Depósito' },
                    { title: 'Valor Sangria' },
                    { title: 'Valor Total' },
                    { title: '' }
                ],
                createdRow: function (row, data) {
                    const formatarData = (data) => {
                        if (!data) return '';
                        const [ano, mes, dia] = data.split('-');
                        return `${dia}/${mes}/${ano}`;
                    };
                    // Formata valor monetário
                    function formatarMoeda(valor) {
                        const numero = Number(valor) || 0;

                        return numero.toLocaleString('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }

                    // Formata datas
                    $('td', row).eq(0).html(formatarData(data[0]));
                    // Formata valor monetário
                    $('td', row).eq(1).html(formatarMoeda(data[1]));
                    $('td', row).eq(2).html(formatarMoeda(data[2]));
                    $('td', row).eq(3).html(formatarMoeda(data[3]));
                    $('td', row).eq(4).html(formatarMoeda(data[4]));

                },

            });
            table.buttons().container().appendTo('#relDepositoFechamento_wrapper .col-md-6:eq(0)');
            $('#overlay-fechamento').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
        
    }
    if (document.getElementById('codtabFechamento')) {
        RelatorioFechamento();
    }
        // Relatorio dos colaboradores
    function ViewFechamentoDia(data){
        // toastr.success(data, 'Teste !');
        $('#overlay-fechamento-dia').show();
        $.post(Paths.controller + 'back_fcx_fechamento_select_dia.php',{data:data}, function(retorno) {
            setTimeout(() => {
            // Limpa a tabela existente
            $('#codtabFechamentoDia').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // console.log(retorno);
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {  
                // Concatena os IDs para identificação do item
                var ids = val.idfcx;
                // Cria um link de exclusão e edição para cada linha
                var del = "&nbsp;&nbsp;&nbsp;"+
                            "<td><a href='#' class='text-muted editfechamento' dat=" + ids + "><i class='fad fa-pencil-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                            "<td><a href='#' class='text-muted delhorario' dat=" + ids + "><i class='fad fa-trash-alt'></i></a></td>&nbsp;&nbsp;&nbsp;" +
                        "&nbsp;&nbsp;&nbsp;";
         
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                   val.data, val.operador, val.pdv, val.saldoini, val.saldocartao, val.deposito, val.sangria, 
                   val.saldofinal, val.totalgeral, del
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relFechamentoDia').DataTable({
                // Tradução
                language: {
                    url: Paths.language,
                },
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relFechamentoDia thead th').css('text-align', 'center');
                },
                "searching": false,
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 10, // Exibe 10 registros por página
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[0, 'asc']], // Ordena pela data de quebra de forma crescente
                "data": lis, // Insere os dados na tabela
                "columnDefs": [
                    { targets: [0,2,3,4,5,6,7,8], className: 'text-center' }   // Ações
                ],
                "columns": [
                    { title: 'Data' },
                    { title: 'Nome' },
                    { title: 'PDV' },
                    { title: 'Inicial' },
                    { title: 'Cartão' },
                    { title: 'Depósito' },
                    { title: 'Sangria' },
                    { title: 'Final' },
                    { title: 'Total' },
                    { title: '' }
                    
                ],
                createdRow: function (row, data) {

                    const formatarData = (data) => {
                        if (!data) return '';
                        const [ano, mes, dia] = data.split('-');
                        return `${dia}/${mes}/${ano}`;
                    };
                    // Formata valor monetário
                    function formatarMoeda(valor) {
                        const numero = Number(valor) || 0;

                        return numero.toLocaleString('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                    
                    // 📅 Data
                    $('td', row).eq(0).html(formatarData(data[0]));
                    // Formata valor monetário
                    $('td', row).eq(3).html(formatarMoeda(data[3]));
                    $('td', row).eq(4).html(formatarMoeda(data[4]));
                    $('td', row).eq(5).html(formatarMoeda(data[5]));
                    $('td', row).eq(6).html(formatarMoeda(data[6]));
                    $('td', row).eq(7).html(formatarMoeda(data[7]));
                    $('td', row).eq(8).html(formatarMoeda(data[8]));

                },
                
            });
            table.buttons().container().appendTo('#relFechamentoDia_wrapper .col-md-6:eq(0)');
            $('#overlay-fechamento-dia').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        }); 
    }
    // Fechamento Dia
    $(document).on('click','.view-fechamento',function() {
        var data = $(this).attr("dat");
        ViewFechamentoDia(data)
        $('#modal-depositos-dia').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.editfechamento',function() {
        var id = $(this).attr("dat");
        // toastr.success(id, 'Teste !');
        $('#idfcx').empty();
        $('#idfcx').append('<b>'+id+'</b>');
        Utils.selectOperadores('selectoperadors');
        $.post(Paths.controller + 'back_fcx_fechamento_select_detalhado.php',{id:id}, function(retorno) {
            // console.log(retorno);
            $.each(retorno, function(index, val) {
                // console.log(val.data);
                $('#datafechamentodia').val(Utils.formatarData(val.data));
                $('#selectoperadors').val(val.operador);
                $('#numpdvdia').val(val.pdv);
                $('#saldoinicialdia').val(Utils.formatarMoeda(val.saldoini));
                $('#saldoposdia').val(Utils.formatarMoeda(val.saldocartao));
                $('#numposdia').val(val.pos);
                $('#saldofinaldia').val(Utils.formatarMoeda(val.saldofinal));
                $('#fechadepositodia').val(Utils.formatarMoeda(val.deposito));
                $('#fechasangriadia').val(Utils.formatarMoeda(val.sangria));
                $('#fechaquebradia').val(Utils.formatarMoeda(val.quebra));
                $('#fechafundodia').val(Utils.formatarMoeda(val.trocafundo));
                $('#fechaobvservdia').val(val.observ);
            });
        });
        $('#modal-altera-relatorio').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.update-fechamento',function() {
        id = $('#idfcx').text();
        toastr.success(id, 'Teste update !');
        let dadoupdate = {
            id:             $('#idfcx').text(),
            data:           $('#datafechamentodia').val(),
            operador:       $('#selectoperadors').val(),
            numpdv:         $('#numpdvdia').val(),
            saldoinicial:   $('#saldoinicialdia').inputmask('unmaskedvalue'),
            saldopos:       $('#saldoposdia').inputmask('unmaskedvalue'),
            numpos:         $('#numposdia').val(),
            saldofinal:     $('#saldofinaldia').inputmask('unmaskedvalue'),
            fechadeposito:  $('#fechadepositodia').inputmask('unmaskedvalue'),
            fechasangria:   $('#fechasangriadia').inputmask('unmaskedvalue'),
            fechaobvserv:   $('#fechaobvservdia').val(),
        };
        console.log(dadoupdate);

    });
    // Ações para load
    $(document).on('click','.syncRelatFecha',function() {
        RelatorioFechamento();
    });
    $(document).on('click','.createClosed',function() {
        Utils.selectOperadores('selectoperador');
        $('#dataafechamento').val('');
        $('#modal-create-relatorio').modal({backdrop: 'static', keyboard: false});
    });
    $('form').on('keydown', 'input, select, textarea', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            let inputs = $(this).closest('form')
                .find('input, select, textarea')
                .filter(':visible:not([readonly]):not([disabled])');

            let index = inputs.index(this);

            if (index > -1 && index + 1 < inputs.length) {
                inputs.eq(index + 1).focus();
            }
        }
    });
    // função para percorre formulario com o enter
    $(document).on('keydown', '.modal-content input, .modal-content select, .modal-content textarea', function(e) {

        if (e.key === 'Enter') {

            e.preventDefault();

            let container = $(this).closest('.modal-content');

            // Campos do formulário
            let campos = container
                .find('input, select, textarea')
                .filter(':visible:not([readonly]):not([disabled])');

            let index = campos.index(this);

            // Se não for o último campo
            if (index > -1 && index + 1 < campos.length) {

                campos.eq(index + 1).focus();

            } else {

                // Se for o último (Observação)
                container.find('.create-fechamento').focus();
            }
        }
    });
    $(document).on('click','.create-fechamento',function() {
        
        let dadofecha = {
            datafecha:      $('#dataafechamento').val(),
            operador:       $('#selectoperador').val(),
            numpdv:         $('#numpdv').val(),
            saldoinicial:   $('#saldoinicial').inputmask('unmaskedvalue'),
            saldopos:       $('#saldopos').inputmask('unmaskedvalue'),
            numpos:         $('#numpos').val(),
            saldofinal:     $('#saldofinal').inputmask('unmaskedvalue'),
            fechadeposito:  $('#fechadeposito').inputmask('unmaskedvalue'),
            fechasangria:   $('#fechasangria').inputmask('unmaskedvalue'),
            fechaquebra:    $('#fechaquebra').inputmask('unmaskedvalue'),
            fechafundo:     $('#fechafundo').inputmask('unmaskedvalue'),
            fechaobvserv:   $('#fechaobvserv').val(),
        };
        // console.log(dadofecha);
        $.post(Paths.controller+'back_fcx_fechamento_insert.php',{dadofecha}, function(retorno){
            // console.log(retorno);
            if(retorno.error == 101){
                toastr.success(retorno.message);
                RelatorioFechamento();
                // $('#dataafechamento').val('');
                $('#selectoperador').val('0').trigger('change');
                $('#numpdv').val('0').trigger('change');
                $('#saldoinicial').val('');
                $('#saldopos').val('');
                $('#numpos').val('0').trigger('change');
                $('#saldofinal').val('');
                $('#fechadeposito').val('');
                $('#fechasangria').val('');
                $('#fechaquebra').val('');
                $('#fechafundo').val('');
                $('#fechaobvserv').val('');
                 // volta foco para Data
                setTimeout(function () {
                    $('#dataafechamento').focus();
                }, 300);
                
            }else{
                toastr.error(retorno.message);
            }
        });
    });

            // datadeposito:       $('.datasangria').val(),
            // pdv:                $('#numpdv').val(),
            // nomeoperador:       $('#selectoperador').val(),
            // nomelideranca:      $('#nomelideraca').val(),
            // valodeposito:       $('#valordeposito').inputmask('unmaskedvalue'),
            // numerobananinha:    $('#numerobananinha').val()
    // Fechamento Dia
    $(document).on('click','.view-whatsapp',function(){

        var data = $(this).attr("dat");

        $.post(Paths.controller+'back_fcx_fechamento_whatsapp.php',{data}, function(retorno){
            // console.log(retorno);
            if(retorno.error == 0){
                let mensagem = `${retorno.message}`;
                $('#textoCopiar').val(mensagem);
            }else{
                toastr.error(retorno.message);
            }
        });
    // sempre resetar o botão
    $('#btnCopiarWhatsapp').text('📋 Copiar mensagem');

        $('#modal-whatsapp').modal({
            backdrop: 'static',
            keyboard: false
        });

    });

    $('#btnCopiarWhatsapp').click(function(){

        var texto = $('#textoCopiar')[0];
        texto.select();
        texto.setSelectionRange(0, 99999);

        if (navigator.clipboard) {

            navigator.clipboard.writeText(texto.value)
            .then(() => {
                $('#btnCopiarWhatsapp').text('✔ Copiado');
            })
            .catch(() => {
                document.execCommand('copy');
                $('#btnCopiarWhatsapp').text('✔ Copiado');
            });

        } else {

            document.execCommand('copy');
            $('#btnCopiarWhatsapp').text('✔ Copiado');

        }

        setTimeout(() => {
            $('#btnCopiarWhatsapp').text('📋 Copiar mensagem');
        }, 5000);

    });
    $(document).on('click','.detalherol',function() {
        var rol = $(this).attr("dat");
        separa = rol.split('|')
        toastr.success(rol, 'Teste !');
        razaoDespesaDetalhado(separa[0],separa[1],separa[2]);
        $('#modal-relatorio-rol-detalhado').modal({backdrop: 'static', keyboard: false});
        
    });
    $(document).on('click','.detalherolalmox',function() {
        var rol = $(this).attr("dat");
        separa = rol.split('|')
        toastr.success(rol, 'Teste !');
        razaoAlmoxDetalhado(separa[0],separa[1],separa[2]);
        $('#modal-relatorio-rol-detalhado-almox').modal({backdrop: 'static', keyboard: false});
        
    });
}); 