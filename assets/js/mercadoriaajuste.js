$(document).ready(function () {
    if (!$('#relAjusteMercadoria').length) {
        return;
    }

    toastr.options = {
        progressBar: true,
        positionClass: 'toast-top-left',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000
    };

    function limparFormulario(manterCodigo) {
        if (!manterCodigo) {
            $('#ajuste-codigo-barras').val('');
        }

        $('#ajuste-sku').val('');
        $('#ajuste-codigo-confirmado').val('');
        $('#ajuste-produto').text('');
        $('#ajuste-sku-label').text('');
        $('#ajuste-marca').text('');
        $('#ajuste-estoque-atual').text('0');
        $('#ajuste-quantidade').val('');
        $('#ajuste-observacao').val('');
        $('#box-produto-ajuste').addClass('d-none');
    }

    function formatarStatus(status) {
        status = parseInt(status, 10);

        if (status === 2) {
            return '<span class="badge badge-success">AJUSTADO</span>';
        }

        if (status === 3) {
            return '<span class="badge badge-secondary">CANCELADO</span>';
        }

        return '<span class="badge badge-warning">PENDENTE</span>';
    }

    function carregarPendentes() {
        $('#overlay-ajuste-mercadoria').show();

        $.post(Paths.controller + 'back_mercadoria_ajuste_listar.php', { status: 'pendente' }, function (retorno) {
            let lis = [];
            let totalItens = 0;
            let saldo = 0;

            $.each(retorno, function (index, val) {
                totalItens++;
                saldo += parseInt(val.quantidade_ajuste, 10) || 0;

                let del = "<a href='#' class='text-danger delete-ajuste' dat='" + val.id + "'><i class='fas fa-trash'></i></a>";

                lis.push([
                    val.id,
                    val.codigo_barras,
                    val.sku,
                    val.produto,
                    val.marca || '',
                    val.estoque_anterior,
                    val.quantidade_ajuste,
                    val.observacao || '',
                    del
                ]);
            });

            $('#ajuste-total-pendente').text(totalItens);
            $('#ajuste-saldo-pendente').text(saldo);
            $('#codtabAjusteMercadoria').empty();

            let table = $('#relAjusteMercadoria').DataTable({
                language: {
                    url: Paths.language
                },
                searching: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        titleAttr: 'Imprimir Relatorio',
                        className: 'btn btn-primary',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] },
                        title: 'RELATORIO DE AJUSTE DE MERCADORIA - PENDENTE'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i>',
                        titleAttr: 'Exportar Excel',
                        className: 'btn btn-success',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] },
                        title: 'RELATORIO DE AJUSTE DE MERCADORIA - PENDENTE'
                    }
                ],
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 8,
                destroy: true,
                order: [[0, 'desc']],
                data: lis,
                columnDefs: [
                    { targets: [0, 1, 2, 5, 6, 8], className: 'dt-body-center' }
                ],
                columns: [
                    { title: '#' },
                    { title: 'Codigo Barras' },
                    { title: 'SKU' },
                    { title: 'Produto' },
                    { title: 'Marca' },
                    { title: 'Estoque Atual' },
                    { title: 'Qtd. Ajuste' },
                    { title: 'Observacao' },
                    { title: '' }
                ]
            });

            table.buttons().container().appendTo('#relAjusteMercadoria_wrapper .col-md-6:eq(0)');
            $('#overlay-ajuste-mercadoria').hide();
        }, 'json').fail(function () {
            $('#overlay-ajuste-mercadoria').hide();
            toastr.error('Erro ao listar itens pendentes');
        });
    }

    function carregarRelatorio() {
        $('#overlay-ajuste-relatorio').show();

        $.post(Paths.controller + 'back_mercadoria_ajuste_listar.php', { status: 'relatorio' }, function (retorno) {
            let lis = [];

            $.each(retorno, function (index, val) {
                lis.push([
                    val.id,
                    val.codigo_barras,
                    val.sku,
                    val.produto,
                    val.estoque_anterior,
                    val.quantidade_ajuste,
                    val.estoque_posterior,
                    formatarStatus(val.status),
                    val.usuario_ajuste || val.usuario_criacao || '',
                    val.data_ajuste || val.created_at
                ]);
            });

            $('#codtabAjusteMercadoriaHistorico').empty();

            let table = $('#relAjusteMercadoriaHistorico').DataTable({
                language: {
                    url: Paths.language
                },
                searching: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        titleAttr: 'Imprimir Relatorio',
                        className: 'btn btn-primary',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] },
                        title: 'RELATORIO DE AJUSTES DE MERCADORIA'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i>',
                        titleAttr: 'Exportar Excel',
                        className: 'btn btn-success',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] },
                        title: 'RELATORIO DE AJUSTES DE MERCADORIA'
                    }
                ],
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 8,
                destroy: true,
                order: [[0, 'desc']],
                data: lis,
                columnDefs: [
                    { targets: [0, 1, 2, 4, 5, 6, 7, 9], className: 'dt-body-center' }
                ],
                columns: [
                    { title: '#' },
                    { title: 'Codigo Barras' },
                    { title: 'SKU' },
                    { title: 'Produto' },
                    { title: 'Est. Ant.' },
                    { title: 'Ajuste' },
                    { title: 'Est. Final' },
                    { title: 'Status' },
                    { title: 'Usuario' },
                    { title: 'Data' }
                ]
            });

            table.buttons().container().appendTo('#relAjusteMercadoriaHistorico_wrapper .col-md-6:eq(0)');
            $('#overlay-ajuste-relatorio').hide();
        }, 'json').fail(function () {
            $('#overlay-ajuste-relatorio').hide();
            toastr.error('Erro ao carregar relatorio');
        });
    }

    function buscarMercadoria() {
        let codigo = $('#ajuste-codigo-barras').val().replace(/\D/g, '');

        if (!codigo) {
            toastr.warning('Informe o codigo de barras');
            $('#ajuste-codigo-barras').focus();
            return;
        }

        $.post(Paths.controller + 'back_mercadoria_ajuste_buscar.php', { codigo_barras: codigo }, function (retorno) {
            if (retorno.error) {
                limparFormulario(true);
                toastr.warning(retorno.message || 'Mercadoria nao encontrada');
                return;
            }

            $('#ajuste-sku').val(retorno.sku);
            $('#ajuste-codigo-confirmado').val(retorno.codigo_barras);
            $('#ajuste-produto').text(retorno.produto);
            $('#ajuste-sku-label').text(retorno.sku);
            $('#ajuste-marca').text(retorno.marca || '');
            $('#ajuste-estoque-atual').text(retorno.estoque);
            $('#box-produto-ajuste').removeClass('d-none');
            $('#ajuste-quantidade').focus();
        }, 'json').fail(function () {
            toastr.error('Erro ao consultar mercadoria');
        });
    }

    function adicionarItem() {
        let codigo = $('#ajuste-codigo-confirmado').val();
        let quantidade = parseInt($('#ajuste-quantidade').val(), 10);

        if (!codigo || !$('#ajuste-sku').val()) {
            toastr.warning('Consulte uma mercadoria antes de adicionar');
            $('#ajuste-codigo-barras').focus();
            return;
        }

        if (!quantidade) {
            toastr.warning('Informe uma quantidade de ajuste diferente de zero');
            $('#ajuste-quantidade').focus();
            return;
        }

        $.post(Paths.controller + 'back_mercadoria_ajuste_insert.php', {
            codigo_barras: codigo,
            quantidade_ajuste: quantidade,
            observacao: $('#ajuste-observacao').val()
        }, function (retorno) {
            if (retorno.status === 'success') {
                toastr.success(retorno.message);
                limparFormulario(false);
                $('#ajuste-codigo-barras').focus();
                carregarPendentes();
                carregarRelatorio();
            } else {
                toastr.error(retorno.message || 'Erro ao adicionar item');
            }
        }, 'json').fail(function () {
            toastr.error('Erro ao adicionar item');
        });
    }

    function efetivarAjuste() {
        let total = parseInt($('#ajuste-total-pendente').text(), 10) || 0;

        if (total <= 0) {
            toastr.warning('Nenhum item pendente para ajuste');
            return;
        }

        if (!confirm('Confirma realizar o ajuste no estoque dos itens pendentes?')) {
            return;
        }

        $('#btn-ajustar-estoque').prop('disabled', true);

        $.post(Paths.controller + 'back_mercadoria_ajuste_efetivar.php', function (retorno) {
            if (retorno.status === 'success') {
                toastr.success(retorno.message);
                carregarPendentes();
                carregarRelatorio();
            } else {
                toastr.error(retorno.message || 'Erro ao realizar ajuste');
            }
        }, 'json').fail(function () {
            toastr.error('Erro ao realizar ajuste');
        }).always(function () {
            $('#btn-ajustar-estoque').prop('disabled', false);
        });
    }

    $(document).on('click', '#btn-buscar-mercadoria', function () {
        buscarMercadoria();
    });

    $(document).on('keydown', '#ajuste-codigo-barras', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarMercadoria();
        }
    });

    $(document).on('keydown', '#ajuste-quantidade', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            adicionarItem();
        }
    });

    $(document).on('input', '#ajuste-codigo-barras', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    $(document).on('click', '#btn-add-ajuste', function () {
        adicionarItem();
    });

    $(document).on('click', '#btn-refresh-ajuste', function () {
        carregarPendentes();
    });

    $(document).on('click', '#btn-refresh-relatorio-ajuste', function () {
        carregarRelatorio();
    });

    $(document).on('click', '#btn-limpar-ajuste', function () {
        limparFormulario(false);
        $('#ajuste-codigo-barras').focus();
    });

    $(document).on('click', '.delete-ajuste', function (e) {
        e.preventDefault();

        let id = $(this).attr('dat');

        $.post(Paths.controller + 'back_mercadoria_ajuste_delete.php', { id: id }, function (retorno) {
            if (retorno.status === 'success') {
                toastr.success(retorno.message);
                carregarPendentes();
                carregarRelatorio();
            } else {
                toastr.error(retorno.message || 'Erro ao remover item');
            }
        }, 'json').fail(function () {
            toastr.error('Erro ao remover item');
        });
    });

    $(document).on('click', '#btn-ajustar-estoque', function () {
        efetivarAjuste();
    });

    limparFormulario(false);
    carregarPendentes();
    carregarRelatorio();
});
