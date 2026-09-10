
$(document).ready(function(){
    var BtnOperador = "<button type='button' class='btn btn-tool listaoperador'><span class='badge badge-success' style='font-size: 1.1em;'>COLABORADORES <i class='fas fa-user-circle'></i></span>&nbsp;&nbsp;&nbsp;</button>";
    $('#btn-operador').empty();
    $('#btn-operador').append(BtnOperador);
    $('#nomeoperador, #nomelideraca, #nomefuncao, #nomecolaborador').inputmask({casing:'upper'});

    $('#valordeposito, #valoredit').inputmask('currency', Utils.maskCurrencyBR);
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
    function ConsultaProdutos(){
        $('#overlay-isv').show();
        $.post(Paths.controller + 'back_ope_consulta_produtos.php', function(retorno) {
            setTimeout(() => {
                // Limpa a tabela existente
                $('#codtabConsulta').empty();
                // Inicializa um array para armazenar os dados formatados
                let lis = [];
                // console.log(retorno);
                
                // Itera sobre os dados retornados e os formata para a tabela
                $.each(retorno, function(index, val) {           
                    // Adiciona os dados na lista para preencher a tabela
                    lis.push([
                        val.codigo, val.descricao, val.precoclube, val.limiteclube,
                        val.precomax, val.limitemax,
                        val.precovarejo, val.limitevarejo, val.dataconsulta, ''
                    ]);
                });
                // Inicializa a DataTable
                let table = $('#relConsultaProdutos').DataTable({
                    // Tradução
                    language: {
                        url: Paths.language,
                    },
                    // Centralizar Titulos da tabela
                    initComplete: function() {

                        let api = this.api();

                        // Centraliza títulos
                        $('#relConsultaProdutos thead th').css('text-align', 'center');

                        // Limpa filtros antigos
                        $('#filtrosConsultaProdutos').empty();

                        let linhaFiltros = $('<div class="row"></div>');

                       linhaFiltros.append(`
                            <div class="mb-2">
                                <input type="text" 
                                    class="form-control form-control-sm" 
                                    style="width: 152px;"
                                    placeholder="Filtrar Código"
                                    data-col="0">
                            </div>
                        `);

                        // 🔹 FILTRO COLUNA 1 (Descrição)
                        linhaFiltros.append(`
                            <div class="col-md-5 mb-2">
                                <input type="text" 
                                    class="form-control form-control-sm"
                                    style="width: 600px;" 
                                    placeholder="Filtrar Descrição"
                                    data-col="1">
                            </div>
                        `);

                        $('#filtrosConsultaProdutos').append(linhaFiltros);

                        // Evento de filtro
                        $('#filtrosConsultaProdutos input').on('keyup change', function () {

                            let coluna = $(this).data('col');
                            api.column(coluna).search(this.value).draw();

                        });

                    },
                    searching: true,   // precisa ficar TRUE
                    dom: 'rtip',       // remove o campo de pesquisa visual
                    "responsive": true,  // Tabela responsiva
                    "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                    "autoWidth": false, // Mantém largura das colunas fixa
                    "pageLength": 12, // Exibe 10 registros por página
                    "columnDefs": [
                        {
                            targets: [0, 2, 3, 4, 5, 6, 7, 9],
                            className: 'dt-body-center'
                        }
                    ],
                    "destroy": true, // Garante recriação da tabela ao atualizar dados
                    "order": [[1, 'asc']], // Ordena pela data de quebra de forma crescente
                    "data": lis, // Insere os dados na tabela
                    columns: [
                        { title: 'Codigo',       width: '100px' },
                        { title: 'Descrição',    width: '600px' },
                        { title: 'Preço Clube',  width: '80px' },
                        { title: 'Lim. Clube',   width: '40px' },
                        { title: 'Preço Max',    width: '80px' },
                        { title: 'Lim. Max',     width: '40px' },
                        { title: 'Preço Varejo', width: '80px' },
                        { title: 'Lim. Varejo',  width: '40px' },
                        { title: 'Consulta',     width: '160px' },
                        { title: '',             width: '40px' }
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


                        // Formata datas
                       
                        
                        $('td', row).eq(2).html(Utils.formatarMoeda(data[2]));
                        $('td', row).eq(4).html(Utils.formatarMoeda(data[4]));
                        $('td', row).eq(6).html(Utils.formatarMoeda(data[6]));
                        $('td', row).eq(8).html(Utils.separaDataHora(data[8]));
                        
                        

                    },

                });
                table.buttons().container().appendTo('#relConsultaProdutos_wrapper .col-md-6:eq(0)');
                $('#overlay-isv').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
        
    }
    if (document.getElementById('codtabConsulta')) {
        ConsultaProdutos();
    }
    // Ações para load
    $(document).on('click','.syncRelat',function() {
        ConsultaProdutos();
    });
    
    
}); 