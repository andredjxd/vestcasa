


// ===============================
// CAMINHOS GLOBAIS DO SISTEMA
// ===============================

window.Paths = {
    controller: '../controller/',
    assets: '../assets/',
    api: '../controller/api/',
    language: '../../../vestcasa/assets/plugins/datatables/pt-BR.json'
};

// =====================================
// FUNÇÕES UTILITÁRIAS GLOBAIS
// =====================================

window.Utils = {

    formatarData(data) {
        if (!data) return '';
        const [ano, mes, dia] = data.split('-');
        return `${dia}/${mes}/${ano}`;
    },
    removerExtensao(nomeArquivo) {
        if (!nomeArquivo) return '';

        const partes = nomeArquivo.split('.');
        partes.pop(); // remove a última parte (extensão)

        return partes.join('.');
    },

    isNumeric(str) {
        return /^[0-9]+$/.test(str);
    },

    real(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    },

    valorPorcentagem(valor) {
        return new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(valor);
    },

    valorMonet(valor) {
        if (valor == null || isNaN(valor)) return 0;
        return parseFloat(valor.toString().replace(/[^\d.-]/g, '').replace(',', '.'));
    },

    formatarMoeda(valor) {
        const numero = parseFloat(valor);
        return isNaN(numero)
            ? 'R$ 0,00'
            : numero.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },

    moedaParaNumero(valor) {
        if (!valor) return 0;

        valor = valor.toString().trim();
        valor = valor.replace(/[^\d.,-]/g, '');

        if (valor.includes(',')) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        }

        return Number(valor) || 0;
    },

    inverterSinal(numero) {
        return numero ? numero * -1 : 0;
    },

    validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;

        let soma = 0, resto;

        for (let i = 1; i <= 9; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }

        resto = (soma * 10) % 11;
        if (resto >= 10) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;

        soma = 0;
        for (let i = 1; i <= 10; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }

        resto = (soma * 10) % 11;
        if (resto >= 10) resto = 0;

        return resto === parseInt(cpf.substring(10, 11));
    },

    formatarCPF(valor) {
        if (valor == null) return '';

        valor = String(valor).replace(/\D/g, '').padStart(11, '0');

        return valor.replace(
            /(\d{3})(\d{3})(\d{3})(\d{2})/,
            '$1.$2.$3-$4'
        );
    },
    formatarCNPJ(valor) {

        if (valor == null) return '';

        valor = String(valor)
            .replace(/\D/g, '')
            .padStart(14, '0');

        return valor.replace(
            /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/,
            '$1.$2.$3/$4-$5'
        );
    },

    doisDigitos(valor) {
        return String(valor).padStart(2, '0');
    },

    quatroDigitos(valor) {
        return String(valor).padStart(4, '0');
    },

    separaDataHora(dataHora) {
        if (!dataHora) return '';

        const [data, hora] = dataHora.split(' ');
        const [ano, mes, dia] = data.split('-');

        return `${dia}/${mes}/${ano} ${hora}`;
    },
    valorPorcetagem(valor){
        let resul = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
        return resul+'%';
    },
        // Função para carregar no select
    selectFuncao() {
        // Envia uma requisição POST para obter os dados 
        $.post(Paths.controller + 'back_fcx_select_funcao.php', function(dados) {
            // Converte os dados recebidos para um objeto JSON
            $.each(dados, function(index, val) {
                // Adiciona as opções ao select de comprador
                $('#selectfuncao').append('<option value="'+val.id+'">'+val.funcao+'</option>');
            });
        });
    },
    selectHorario() {
        // Envia uma requisição POST para obter os dados 
        $.post(Paths.controller + 'back_fcx_select_horario.php', function(dados) {
            // Converte os dados recebidos para um objeto JSON
            $.each(dados, function(index, val) {
                // Adiciona as opções ao select de comprador
                $('#selecthorario').append('<option value="'+val.id+'">'+val.horario+'</option>');
            });
        });
    },
    selectTipoAvaria() {
        $.post(
            Paths.controller + 'back_avaria_select_tipo.php',
            function(dados) {

                $('#tipoavaria').empty();
                $('#tipoavaria').append('<option value="">TIPO DE AVARIA</option>');

                $.each(dados, function(index, val) {
                    $('#tipoavaria').append(
                        '<option value="' + val.tipoava + '">' + val.tipoava + '</option>'
                    );
                });

            },
            'json'
        );
    },
    selectTipoAvariaEdit(tipo) {
        $.post(
            Paths.controller + 'back_avaria_select_tipo_edit.php',
            function(dados) {

                $('#edittipoavaria').empty();

                $('#edittipoavaria').append(
                    '<option value="">TIPO AVARIA</option>'
                );

                $.each(dados, function(index, val) {

                    let selected = '';

                    if (val.tipoava == tipo) {
                        selected = 'selected';
                    }

                    $('#edittipoavaria').append(
                        '<option value="' + val.tipoava + '" ' + selected + '>' + val.tipoava + '</option>'
                    );
                });

            },
            'json'
        );
    },
    selectOperadores(id) {
        // Envia uma requisição POST para obter os dados 
        $.post(Paths.controller + 'back_fcx_select_colaborador.php', function(dados) {
            // Converte os dados recebidos para um objeto JSON
            $.each(dados, function(index, val) {
                // Adiciona as opções ao select de comprador
                $('#'+id).append('<option value="'+val.id+'">'+val.nome+'</option>');
            });
        });
    },
    // ============================
    // 💰 MÁSCARA MONETÁRIA BR
    // ============================
    maskCurrencyBR: {
        prefix: 'R$ ',
        groupSeparator: '.',
        radixPoint: ',',
        digits: 2,
        digitsOptional: false,
        autoGroup: true,
        rightAlign: false,
        allowMinus: false,
        placeholder: '0',
        unmaskAsNumber: true
    },
    formatarCodigosBarra(valor) {

        if (!valor) return '';

        // 🔥 se vier array
        if (Array.isArray(valor)) {
            return valor.join('<br>');
        }

        // 🔥 se vier string
        if (typeof valor === 'string') {
            return valor.replace(/,/g, '<br>');
        }

        // 🔥 fallback
        return '';
    }

    
};


