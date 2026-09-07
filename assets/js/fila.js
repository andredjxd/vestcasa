    $(document).ready(function(){

        /* ======================================================
        VARIÁVEIS GLOBAIS SSE
        ====================================================== */
        let evtSourceFila = null;
        let evtSourceAgendadas = null;

        function formatarParametro(parametro) {
            if (
                parametro === null ||
                parametro === undefined ||
                String(parametro).trim() === ''
            ) {
                return 0;
            }

            const valor = String(parametro).trim();

            // número inteiro
            if (/^-?\d+$/.test(valor)) {
                return valor;
            }

            // data
            return Utils.formatarData(valor);
        }
        
        function atualizarTabelaFila(dados) {

            const tbody = document.getElementById("tbodyFila");
            if (!tbody) return;

            let html = "";

            dados.forEach(proc => {

                const progresso = Number(proc.progresso) || 0;

                let statusBadge = `<span class="badge bg-secondary">Fila</span>`;
                if (proc.status == 1) statusBadge = `<span class="badge bg-warning">Executando</span>`;
                if (proc.status == 2) statusBadge = `<span class="badge bg-success">Concluído</span>`;
                if (proc.status == 3) statusBadge = `<span class="badge bg-danger">Erro</span>`;

                const barraAnimada = proc.status == 1
                    ? "progress-bar bg-orange progress-bar-striped progress-bar-animated"
                    : "progress-bar bg-orange";

                let logs = '';

                if (proc.log !== null && proc.log !== undefined && proc.log !== '') {
                    logs = `<a href="#"
                                class="text-muted visualizarlog"
                                data-id="${proc.id}">
                                <i class="fas fa-tasks-alt"></i>
                            </a>`;
                }

                html += `
                    <tr>
                        <td>${proc.id ?? ''}</td>
                        <td class="text-center">${Utils.separaDataHora(proc.created) ?? ''}</td>
                        <td>${Utils.removerExtensao(proc.nome_processo) ?? ''}</td>
                        <td class="text-center">${formatarParametro(proc.parametros)}</td>
                        <td class="text-center">${proc.total ?? 0}</td>
                        <td class="text-center">${proc.processados ?? 0}</td>
                        <td style="min-width:180px;">
                            <div class="progress" style="height:18px;">
                                <div class="${barraAnimada}"
                                    style="width:${progresso}%">
                                    ${progresso.toFixed(1)}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${proc.tempo_execucao ?? '0'}</td>
                        <td class="text-center">${logs}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function atualizarTabelaAgendadas(dados) {

            const tbody = document.getElementById("tbodyAgendadas");
            if (!tbody) return;

            let html = "";

            dados.forEach(proc => {

                const progresso = Number(proc.progresso) || 0;

                let statusBadge = `<span class="badge bg-secondary">Fila</span>`;
                if (proc.status == 1) statusBadge = `<span class="badge bg-warning">Executando</span>`;
                if (proc.status == 2) statusBadge = `<span class="badge bg-success">Concluído</span>`;
                if (proc.status == 3) statusBadge = `<span class="badge bg-danger">Erro</span>`;
                if (proc.status == 9) statusBadge = `<span class="badge bg-info">Agendada</span>`;

                const barraAnimada = proc.status == 1
                    ? "progress-bar bg-orange progress-bar-striped progress-bar-animated"
                    : "progress-bar bg-orange";

                let logs = '';

                if (proc.log !== null && proc.log !== undefined && proc.log !== '') {
                    logs = `<a href="#"
                                class="text-muted visualizarlog"
                                data-id="${proc.id}">
                                <i class="fas fa-tasks-alt"></i>
                            </a>`;
                }

                html += `
                    <tr>
                        <td>${proc.id ?? ''}</td>
                        <td class="text-center">${Utils.separaDataHora(proc.created) ?? ''}</td>
                        <td>${Utils.removerExtensao(proc.nome_processo) ?? ''}</td>
                        <td class="text-center">${Utils.formatarData(proc.parametros) ?? 0}</td>
                        <td class="text-center">${proc.total ?? 0}</td>
                        <td class="text-center">${proc.processados ?? 0}</td>
                        <td style="min-width:180px;">
                            <div class="progress" style="height:18px;">
                                <div class="${barraAnimada}"
                                    style="width:${progresso}%">
                                    ${progresso.toFixed(1)}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">${Utils.separaDataHora(proc.data_agendada) ?? ''}</td>
                        <td class="text-center">${proc.tempo_execucao ?? ''}</td>
                        <td class="text-center">${logs}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        /* ======================================================
        SSE FILA
        ====================================================== */
        function iniciarFila() {

            if (evtSourceFila) {
                evtSourceFila.close();
            }

            evtSourceFila = new EventSource("../controller/back_fila_execucao_sse.php?tipo=fila");

           evtSourceFila.onmessage = function(event) {
                atualizarTabelaFila(JSON.parse(event.data));
            };

            evtSourceFila.onerror = function () {
                console.log("SSE FILA desconectado");
            };
        }

        function pararFila() {
            if (evtSourceFila) {
                evtSourceFila.close();
                evtSourceFila = null;
            }
        }


        /* ======================================================
        SSE AGENDADAS
        ====================================================== */
        function iniciarAgendadas() {

            if (evtSourceAgendadas) {
                evtSourceAgendadas.close();
            }

            evtSourceAgendadas = new EventSource("../controller/back_fila_execucao_sse.php?tipo=agendadas");

            evtSourceAgendadas.onmessage = function(event) {
                atualizarTabelaAgendadas(JSON.parse(event.data));
            };

            evtSourceAgendadas.onerror = function () {
                console.log("SSE AGENDADAS desconectado");
            };
        }

        function pararAgendadas() {
            if (evtSourceAgendadas) {
                evtSourceAgendadas.close();
                evtSourceAgendadas = null;
            }
        }

        /* ======================================================
        CONTROLE POR ABA ATIVA (NÍVEL PROFISSIONAL)
        ====================================================== */
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {

            const alvo = $(e.target).attr("href");

            if (alvo === "#tab-fila") {
                iniciarFila();
                pararAgendadas();
            }

            if (alvo === "#tab-agendadas") {
                iniciarAgendadas();
                pararFila();
            }
        });


        /* ======================================================
        ABRIR MODAL + INICIAR PRIMEIRA ABA
        ====================================================== */
        $('.filaexe').on('click', function() {

            $('#modal-fila-execucao').modal({
                backdrop: 'static',
                keyboard: false
            });

            // Sempre começa pela aba FILA
            iniciarFila();
        });

        $('#modal-fila-execucao').on('shown.bs.modal', function () {
            $('body').addClass('modal-open').css('overflow', 'hidden');
        });
        /* ======================================================
        FECHAR MODAL = ENCERRAR TODAS CONEXÕES
        ====================================================== */
        $('#modal-fila-execucao').on('hidden.bs.modal', function () {
            $('body').removeClass('modal-open').css('overflow', '');
            pararFila();
            pararAgendadas();
        });


        /* ======================================================
        EXECUTAR PREÇOS
        ====================================================== */
        $(document).on('click', '.executar-precos', function() {

            const processo = "consultaPreco.py";

            $.post(Paths.controller + 'back_fila_criar_execucao.php',
                { processo: processo },
                function(retorno) {

                    if (retorno.error == 100) {
                        toastr.error('ERRO em atualizar - Verificar Dados!!');
                    }
                    else if (retorno.error == 103) {
                        toastr.warning(retorno.message);
                    }
                    else {
                        toastr.success(retorno.message);
                    }
                }
            );
        });

        /* ======================================================
        EXECUTAR INSTAGRAM
        ====================================================== */
        $(document).on('click', '.atualizarinstagram', function() {

            const processo = "atualizaInstagram.py";

            $.post(Paths.controller + 'back_fila_criar_execucao.php',
                { processo: processo },
                function(retorno) {

                    if (retorno.error == 100) {
                        toastr.error('ERRO em atualizar - Verificar Dados!!');
                    }
                    else if (retorno.error == 103) {
                        toastr.warning(retorno.message);
                    }
                    else {
                        toastr.success(retorno.message);
                    }
                }
            );
        });

        /* ======================================================
            ATUALIZAR VENDA
        ====================================================== */
        $(document).on('click', '.modalvendas', function() {

            $('#modal-atualiza-venda').modal({
                backdrop: 'static',
                keyboard: false
            });
        });

        /* ======================================================
            ATUALIZAR VENDAS
        ====================================================== */
        $(document).on('click', '.btn-atualizarvendas', function() {

            const processo = "atualizaVenda.py";
            const data = $('#dataatualizavendas').val();

            // console.log(data);
            // 

            $.post(
                Paths.controller + 'back_fila_criar_execucao.php',
                {
                    processo: processo,
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
        });
        
        $(document).on('click', '.visualizarlog', function(e) {
            e.preventDefault();

            const id = $(this).data('id');

            // Limpa antes de carregar
            editor.setValue("Carregando log...");

            $('#modal-visualizar-log').modal({
                backdrop: 'static',
                keyboard: false
            });

            $.post(Paths.controller + 'back_fila_buscar_log.php', { id: id }, function(retorno) {
                // console.log(typeof retorno.data);
                if (retorno.error == 0) {

                    let logTexto = retorno.data?.log ?? "";

                    editor.setValue(String(logTexto));

                    setTimeout(() => {

                        let totalLines = editor.lineCount();

                        if (totalLines > 0) {
                            let ultimaLinha = totalLines - 1;
                            editor.setCursor(ultimaLinha, 0);
                            editor.scrollIntoView({ line: ultimaLinha, ch: 0 });
                        }

                    }, 100);

                    } else {
                        editor.setValue("Erro ao carregar log.");
                    }

                }, 'json');

            });
        

        $('#modal-visualizar-log').on('shown.bs.modal', function () {
            editor.refresh();
            
        });

        // =============================
        // INICIALIZA CODEMIRROR
        // =============================
        var editor = CodeMirror.fromTextArea(
            document.getElementById("codeMirrorDemo"), 
            {
                mode: "htmlmixed",
                theme: "monokai",
                lineNumbers: true,
                lineWrapping: true,
                autoCloseTags: true,
                autoCloseBrackets: true,
                readOnly: true // 👈 recomendado para log
            }
        );

        // editor.setSize("100%", 350);
        editor.setSize("100%", window.innerHeight * 0.7);

    });

    