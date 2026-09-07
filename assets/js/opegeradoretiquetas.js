$(document).ready(function () {

    let codigosEtiqueta = [];

    function badgeStatus(status) {

        if (status === 'encontrado') {
            return `<span class="badge badge-success">Encontrado</span>`;
        }

        if (status === 'nao_encontrado') {
            return `<span class="badge badge-danger">Não encontrado</span>`;
        }

        return `<span class="badge badge-secondary">Pendente</span>`;
    }

    function renderTabelaCodigos() {

        let linhas = '';

        codigosEtiqueta.forEach((item, idx) => {
            linhas += `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.codigo}</td>
                    <td>${item.descricao || ''}</td>
                    <td class="text-center">${badgeStatus(item.status)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm py-0 px-1 btn-remover-codigo-etiqueta" data-id="${item.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#corpo-tabela-codigos-etiqueta').html(linhas);
    }

    function carregarListaSalva() {

        $.post(Paths.controller + 'back_ope_gerador_etiquetas_listar.php', {}, function (retorno) {

            if (!retorno || retorno.erro) {
                return;
            }

            codigosEtiqueta = retorno;
            renderTabelaCodigos();
        });
    }

    function adicionarCodigoEtiqueta() {

        let codigo = $('#input-codigo-etiqueta').val().trim();

        if (!codigo) {
            return;
        }

        $.post(Paths.controller + 'back_ope_gerador_etiquetas_adicionar.php', { codigo: codigo }, function (retorno) {

            if (!retorno || retorno.erro) {
                toastr.error((retorno && retorno.erro) || 'Erro ao salvar o código.', 'Gerador de Etiquetas');
                return;
            }

            let novoItem = { id: retorno.id, codigo: retorno.codigo, descricao: '', status: 'pendente' };

            // ultimo adicionado sempre no topo da lista
            codigosEtiqueta.unshift(novoItem);
            renderTabelaCodigos();

            // verifica na hora se o codigo existe no cadastro
            $.post(Paths.controller + 'back_ope_gerador_etiquetas.php', { codigos: JSON.stringify([retorno.codigo]) }, function (info) {

                if (!info || info.erro || !info[0]) {
                    return;
                }

                let resultado = info[0];

                novoItem.status = resultado.encontrado ? 'encontrado' : 'nao_encontrado';
                novoItem.descricao = resultado.descricao || '';

                if (!resultado.encontrado) {
                    toastr.warning(`Código ${retorno.codigo} não encontrado no cadastro.`, 'Gerador de Etiquetas');
                }

                renderTabelaCodigos();
            });
        });

        $('#input-codigo-etiqueta').val('').focus();
    }

    function consultarCodigos(callback) {

        if (!codigosEtiqueta.length) {
            toastr.warning('Adicione pelo menos um código de barra.', 'Gerador de Etiquetas');
            return;
        }

        let listaCodigos = codigosEtiqueta.map(item => item.codigo);

        $.post(Paths.controller + 'back_ope_gerador_etiquetas.php', { codigos: JSON.stringify(listaCodigos) }, function (retorno) {

            if (!retorno || retorno.erro) {
                toastr.error((retorno && retorno.erro) || 'Erro ao consultar os códigos informados.', 'Gerador de Etiquetas');
                return;
            }

            retorno.forEach((info, idx) => {

                if (!codigosEtiqueta[idx]) {
                    return;
                }

                codigosEtiqueta[idx].status = info.encontrado ? 'encontrado' : 'nao_encontrado';
                codigosEtiqueta[idx].descricao = info.descricao || '';
            });

            renderTabelaCodigos();

            if (typeof callback === 'function') {
                callback(retorno);
            }
        });
    }

    $('#btn-add-codigo-etiqueta').on('click', function () {
        adicionarCodigoEtiqueta();
    });

    $('#input-codigo-etiqueta').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            adicionarCodigoEtiqueta();
        }
    });

    $(document).on('click', '.btn-remover-codigo-etiqueta', function () {

        let id = parseInt($(this).attr('data-id'));

        $.post(Paths.controller + 'back_ope_gerador_etiquetas_remover.php', { id: id }, function () {
            codigosEtiqueta = codigosEtiqueta.filter(item => item.id !== id);
            renderTabelaCodigos();
        });
    });

    $('#btn-limpar-codigos-etiqueta').on('click', function () {

        $.post(Paths.controller + 'back_ope_gerador_etiquetas_limpar.php', {}, function () {
            codigosEtiqueta = [];
            renderTabelaCodigos();
            toastr.info('Lista de códigos limpa.', 'Gerador de Etiquetas');
        });
    });

    $('#btn-atualizar-tabela-etiqueta').on('click', function () {
        carregarListaSalva();
    });

    $('#btn-gerar-pdf-etiqueta').on('click', function () {

        consultarCodigos(function (retorno) {

            let encontrados = retorno.filter(item => item.encontrado);
            let naoEncontrados = retorno.filter(item => !item.encontrado);

            if (naoEncontrados.length) {
                toastr.warning(
                    'Código(s) não encontrado(s): ' + naoEncontrados.map(item => item.codigo_digitado).join(', '),
                    'Gerador de Etiquetas'
                );
            }

            if (!encontrados.length) {
                toastr.error('Nenhum dos códigos informados foi encontrado no cadastro.', 'Gerador de Etiquetas');
                return;
            }

            gerarEtiquetasPDF(encontrados);
        });
    });

    carregarListaSalva();
});
