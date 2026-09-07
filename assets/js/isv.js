// Pasta Raiz do Controller
//const controllerter = '../controller/';
function isNumeric(str){
    var er = /^[0-9]+$/;
    return (er.test(str));
}
function real(a){
    let res = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(a);
    return res;
}
function valorPorcetagem(valor){
    let resul = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
    return resul;
}
function formatarData(dataString) {
    if (!dataString) return '';
    
    // Converte para objeto Date (caso necessário)
    let data = new Date(dataString + 'T12:00:00'); // adicione "T12:00:00" para garantir que a conversão não afete o dia devido ao fuso horário:
    
    // Verifica se a conversão resultou em uma data válida
    if (isNaN(data.getTime())) return '';

    // Formata para DD/MM/AAAA considerando o fuso horário brasileiro
    return data.toLocaleDateString('pt-BR', { timeZone: 'America/Araguaina' });
}
function formatarMoeda(valor) {
    let numero = parseFloat(valor);
    return isNaN(numero) ? 'R$ 0,00' : numero.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}


// Torne os widgets do painel classificáveis ​​usando jquery UI
$('.connectedSortable').sortable({
placeholder: 'sort-highlight',
connectWith: '.connectedSortable',
handle: '.card-header, .nav-tabs',
forcePlaceholderSize: true,
zIndex: 999999
})
$('.connectedSortable .card-header').css('cursor', 'move')
// jQuery UI classificável para a lista de tarefas
$('.todo-list').sortable({
placeholder: 'sort-highlight',
handle: '.handle',
forcePlaceholderSize: true,
zIndex: 999999
})

$(document).ready(function(){

    var dashboardISV = function (){
        $('#overlay-isv').show();
        $.post(controller+'back_dash_isv.php',{cqu: 100}, function(retorno){
            // console.log(retorno);
            setTimeout(() => {
            if(retorno.error == 101 || retorno.error == 102){
                // console.log("vazio");
                $('#msmVazio').empty();
                var msmVazio ="<div class='info-box'>"
                +"<span class='info-box-icon' style='background: rgb(248, 99, 2); margin-right: 10px;'><i class='fas fa-exclamation' style='color: white;'></i></span>"
                // +"<span class='info-box-icon' style='background: rgb(248, 99, 2); margin-right: 10px;'><i class='fas fa-exclamation' style='color: white;'></i></span>"
                +"<div class='info-box-content'>"
                +"<span class='info-box-text' style='font-size: 2em;'>"+retorno.message+"</span>"
                +"<span class='info-box-number'></span></div></div>";
                $('#msmVazio').append(msmVazio);
            }else{
                // console.log("ok");
                $('#msmVazio').empty();
                $('#msmVazio').append("<div class='row'>"
                  +"<div class='col-md-3 col-sm-6 col-12' id='infoboxISV1'></div>"
                  +"<div class='col-md-3 col-sm-6 col-12' id='infoboxISV2'></div>"
                  +"<div class='col-md-3 col-sm-6 col-12' id='infoboxISV3'></div>"
                  +"<div class='col-md-3 col-sm-6 col-12' id='infoboxISV4'></div>"
                  +"</div>");
                for (var i = 1; i <= 30; i++) {
                    //console.log(i);
                    $('#infoboxISV'+i).empty();
                }

                // Pegar apenas o campo dia do último objeto
                const ultimaDaLista = retorno.at(-1).dia;
                const TamanhoLista = Array.isArray(retorno) ? retorno.length : 0;

                var pos = 0;
                let totalVendido = 0;
                $.each(retorno, function(i,val){
                    pos++;
                    // console.log(retorno);
                    totalVendido = totalVendido + parseFloat(val.total_estoqueVND); // garantir que seja número

                    var infobox1 ="<div class='info-box'><span class='info-box-icon' style='background: rgb(248, 99, 2)'>"
                    +"<img src='../assets/img/track_white.png' style='width: 70%;'></span><div class='info-box-content'>"
                    +"<span class='info-box-text'>ISV "+val.dia+" Dias </span>"
                    +"<span class='info-box-number'>"+real(val.total_estoque)+"</span></div></div>";

                    var infobox2 ="<div class='info-box'><span class='info-box-icon' style='background: rgb(0, 158, 97)'>"
                    +"<img src='../assets/img/carinho_venda_fundo_w.png' style='width: 70%;'></span><div class='info-box-content'>"
                    +"<span class='info-box-text'>ISV "+val.dia+" dias Vendido </span>"
                    +"<span class='info-box-number'>"+real(val.total_estoqueVND)+"</span></div></div>";

                    var infobox3 ="<div class='info-box'><span class='info-box-icon' style='background: rgb(248, 99, 2)'>"
                    +"<img src='../assets/img/bora_vender.png' style='width: 85%;'></i></span><div class='info-box-content'>"
                    +"<span class='info-box-text'>ISV "+val.dia+" Dias Restante</span>"
                    +"<span class='info-box-number'>"+real(val.total_estoqueRest)+"</span></div></div>";

                    if(ultimaDaLista == val.dia && TamanhoLista > 1){
                        var infobox4 ="<div class='info-box'><span class='info-box-icon' style='background: rgb(0, 158, 97)'>"
                        +"<img src='../assets/img/conquista.png' style='width: 85%;'></i></span><div class='info-box-content'>"
                        +"<span class='info-box-text'>Total ISV Vendido</span>"
                        +"<span class='info-box-number'>"+real(totalVendido)+"</span></div></div>";
                    }else{
                        var infobox4 ="<div class='info-box'><span class='info-box-icon' style='background: rgb(0, 158, 97)'>"
                        +"<img src='../assets/img/grafico_disco.svg' style='width: 85%;'></i></span><div class='info-box-content'>"
                        +"<span class='info-box-text'>ISV "+val.dia+" Amanhã</span>"
                        +"<span class='info-box-number'>"+real(val.total_isv_amanha)+"</span></div></div>";
                    }
                    

                    $('#infoboxISV1').append(infobox1);
                    $('#infoboxISV2').append(infobox2);
                    $('#infoboxISV3').append(infobox3);
                    $('#infoboxISV4').append(infobox4);
                    
                });
            }
            $('#overlay-isv').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
    }
    dashboardISV();
    function parametrosISV(){
        // alert("Eu sou um alert!");
        
        $.post(controller + 'back_select_dashboard.php', function(retorno) {
            // Limpa a tabela existente
            $('#codtabISV').empty();
            //console.log(retorno);
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {
                // Concatena os IDs para identificação do item
                var ids = val.id;
                
                // Formata o valor monetário com a cultura brasileira
                let resul = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.resu);
                
                // Cria um link de exclusão e edição para cada linha
                var del = "<td><a href='#' class='text-muted edit-isv' dat='"+ids+";100'><i class='fas fa-edit'></i></a></td>&nbsp;&nbsp;&nbsp;"
                        +  "<td><a href='#' class='text-muted del-isv' dat='"+ids+";100'><i class='fas fa-trash-alt'></i></a></td> " 
                        // +"<td><a href='#' class='text-muted edit-quants' dat=" + ids + "><i class='fas fa-calendar-alt'></i></a></td>";
                
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.pos, val.ope01, val.dias01, val.ope02, val.dias02, val.setores, del
                ]);
            });
        
            // Inicializa o DataTable
            $('#relISV').DataTable({
                language: {
                    url: '../../../gestaolider/assets/plugins/datatables/pt-BR.json',
                },
                // Configurações para a tabela
                // Centralizar Titulos da tabela
                initComplete: function() {
                    $('#relISV thead th').css('text-align', 'center');
                },
                
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 7,
                searching: false, // 🔹 Desativa o campo de pesquisa
                
                // Configurações de formatação e estilo das colunas
                "columnDefs": [
                    {
                        targets: [0, 1, 2, 3, 4, 5, 6],
                        className: 'dt-body-center' // Centraliza as colunas selecionadas
                    }
                    ,
                    {
                        // targets: [1,4],
                        // visible: false,
                        searchable: false, // Oculta a coluna "Comprador"
                    }
                ],

                // Recria a tabela ao atualizar os dados
                "destroy": true,
                
                // Ordenação padrão das linhas
                "order": [[0, 'asc']],
        
                // Dados para a tabela
                "data": lis,
        
                // Definição das colunas
                "columns": [
                    { title: 'Posição' },
                    { title: 'ISV' },
                    { title: 'Dias ISV' },
                    { title: 'Idade' },
                    { title: 'Dias Idade' },
                    { title: 'Setores' },
                    { title: '' }
                ]
            }).buttons().container().appendTo('#relISV_wrapper .col-md-6:eq(0)');
        });
    
    }
    function RelatorioISVDetalhado(pega){
        sep = pega.split(";");

        let dataisv = '';
        let EstoqueGeral = 0;
        let ISV = 0;
        let ISVvnd = 0;
        let Restante = 0;
        let Porcent = 0;
        let dia = 0;
        $.post(controller + 'back_select_detalhe_itens_isv_cab.php',{id:sep[0],cqu:sep[1]}, function(retorno) {
            $.each(retorno, function(index, val) {
                dataisv         = val.data;
                EstoqueGeral    = val.estgeral;
                ISV             = val.isv;
                ISVvnd          = val.isvnd;
                Restante        = val.restante;
                Porcent         = val.porcent;
                dias            = val.dias;
            });
        });
        $.post(controller + 'back_select_detalhe_itens_isv.php',{id:sep[0],cqu:sep[1]}, function(retorno) {
            // Limpa a tabela existente
            $('#codtabISVRel').empty();
            // Inicializa um array para armazenar os dados formatados
            let lis = [];
            // Itera sobre os dados retornados e os formata para a tabela
            $.each(retorno, function(index, val) {                
                // Adiciona os dados na lista para preencher a tabela
                lis.push([
                    val.pos, val.slj, val.codigo+'-'+val.sub_cod, val.descricao, val.emb, val.dtutlent, val.qtultent, 
                    val.cxa, val.und, val.ida, val.isv, val.toe, val.vnd
                ]);
            });
            // Inicializa a DataTable
            let table = $('#relISVDetalhado').DataTable({
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 15, // Exibe 10 registros por página
                "columnDefs": [
                    {
                        targets: [0, 5, 6, 7, 8, 9, 10, 11, 12 ],
                        className: 'dt-body-center' // Centraliza as colunas selecionadas
                    }
                    ,
                    {
                        targets: [1],
                        visible: false,
                        searchable: false, // Oculta a coluna "Comprador"
                    }
                ],
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[0, 'asc']], // Ordena pela data de quebra de forma crescente
                "buttons": [{
                    // extend: 'pdf',
                    //     filename: function() {
                    //         let now = new Date();
                    //         let dateStr = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()}`;
                    //         let timeStr = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
                    //         return `relatorio_de_ISV_${dateStr}_${timeStr}`;
                    //         // return `relatorio_de_ISV_${new Date().toISOString().slice(0, 19).replace('T', '_')}`;
                    //     },
                    //     //"orientation": 'portrait', // Define o formato do PDF como retrato (vertical)
                    //     "orientation": 'landscape', // Define a orientação do PDF como paisagem
                    //     "className": 'btn btn-primary custom-pdf-button',
                    //     "exportOptions": { columns: [0,1,2,3,4,5,6,7,8,9,10,11,12] },
                    //     "customize": function(doc) {
                    //         doc.content.splice(0, 1); // Remove título padrão
                    //         // Formatação dos valores na exportação
                    //         doc.content[0].table.body.forEach(function(row, i) {
                            
                    //             // Lista de colunas a serem formatadas
                    //             const colunasValor = [
                    //                 { index: 11, texto: 'Total' }
                    //             ];
                                
                    //             colunasValor.forEach(coluna => {
                    //                 if (row[coluna.index].text !== coluna.texto) {
                    //                     row[coluna.index].text = formatarMoeda(row[coluna.index].text);
                    //                 }
                    //             });

                    //             // Lista de colunas a serem formatadas
                    //             const colunasData = [
                    //                 { index: 5, texto: 'Data' }
                    //             ];
                                
                    //             colunasData.forEach(coluna => {
                    //                 if (row[coluna.index].text !== coluna.texto) {
                    //                     row[coluna.index].text = formatarData(row[coluna.index].text);
                    //                 }
                    //             });
                                
                    //         });
                    //         let now = new Date();
                    //         let jsDate = now.toLocaleString();
                    //         // Configura margens e estilos do documento
                    //         doc.pageMargins = [25, 90, 25, 30];
                    //         doc.defaultStyle.fontSize = 9;
                    //         doc.styles.tableHeader.fontSize = 8;
                    //         // Configura cabeçalho do PDF
                    //         doc['header'] = function() {
                    //             return {
                    //                 columns: [
                    //                     { alignment: 'right', image: atacadaonew, width: 135, height: 60 },
                    //                     { alignment: 'center', margin: [0, 15, 0, 0], italics: true, bold: true, text: 'RELATÓRIO DETALHADO DE QUEBRA DO OPERADOR', fontSize: 20 },
                    //                     { alignment: 'left', image: atacadaonew, width: 135, height: 60 }
                    //                 ],
                    //                 margin: 20
                    //             };
                    //         };
                    //         // Configuração do rodapé do PDF
                    //         doc['footer'] = function(page, pages) {
                    //             return {
                    //                 columns: [
                    //                     { alignment: 'left', text: ['Gerado em: ', { text: jsDate }] },
                    //                     { alignment: 'center', text: ['By: Andre Alves '] },
                    //                     { alignment: 'right', text: ['Página ', { text: page.toString() }, ' de ', { text: pages.toString() }] }
                    //                 ],
                    //                 margin: [20, 10]
                    //             };
                    //         };
                    //         doc.content[0].table.widths = [20, 90, 50, '*', 100, 55, 25, 20, 20, 35, 20, 60, 10,]; // Ajusta larguras da tabela
                    //     }
                    // },
                    // {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>',
                        titleAttr: 'Imprimir Relatório',
                        className: 'btn btn-primary custom-pdf-button',

                        // Remove o título padrão
                        title: '',

                        // Adiciona novo título centralizado
                        messageTop: function () {
                            return `<h3 style="text-align:center; margin-bottom:10px;">Relatório de ISV de ${dias} dias - Valor: ${real(ISV)}</h3>
                                    <div class="row">
                                        <div class="col-3"><span>Valor de Estoque: ${real(EstoqueGeral)}</span></div>
                                        <div class="col-3"><span>Valor ISV Vendido: ${real(ISVvnd)}</span></div>
                                        <div class="col-2"><span>Resultado: ${real(Restante)}</span></div>
                                        <div class="col-2"><span>Porcentangem: ${valorPorcetagem(Porcent)}%</span></div>
                                        <div class="col-2" style="text-align:right"><span>Data de Referência: ${formatarData(dataisv)}</span></div>
                                    </div>`;
                        },


                        customize: function (win) {
                            // Diminui tamanho da fonte geral
                            $(win.document.body)
                                .css('font-size', '9pt')
                                .css('margin', '10px');
                            
                            // Diminui fonte da tabela e ajusta largura
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', '8pt')
                                .css('width', '100%');
                            // $(win.document.body).find('table thead th').css({
                            //     'text-align': 'center',
                            //     'vertical-align': 'middle'
                            // });

                            // 👉 Formata a coluna 4 (data) e coluna 12 (valor monetário)
                            $(win.document.body).find('table tbody tr').each(function () {
                                // ======== Coluna 4 (data) ========
                                const cellData = $('td', this).eq(5);
                                let dataTexto = cellData.text().trim();

                                // Verifica se o formato é AAAA-MM-DD
                                if (/^\d{4}-\d{2}-\d{2}$/.test(dataTexto)) {
                                    const partes = dataTexto.split('-'); // [AAAA, MM, DD]
                                    const dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;
                                    cellData.text(dataFormatada);
                                }

                                // ======== Coluna 12 (valor monetário) ========
                                const cellValor = $('td', this).eq(11); // índice 11 → 12ª coluna
                                let texto = cellValor.text().trim();

                                // Normaliza o valor removendo símbolos e espaços
                                texto = texto.replace(/[^\d,,-.]/g, '');

                                // Se o número estiver no formato brasileiro (com vírgula decimal)
                                if (texto.includes(',')) {
                                    // Remove pontos de milhar e troca vírgula por ponto
                                    texto = texto.replace(/\./g, '').replace(',', '.');
                                }

                                const valor = parseFloat(texto);
                                if (!isNaN(valor)) {
                                    const valorFormatado = valor.toLocaleString('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL'
                                    });
                                    cellValor.text(valorFormatado);
                                    cellValor.css('text-align', 'left');
                                }
                            });

                            // Força modo paisagem e define estilos
                            const css = `
                                @page { size: landscape; }

                                table.dataTable,
                                table.dataTable th,
                                table.dataTable td {
                                    border: 1px solid #000 !important;
                                    border-collapse: collapse !important;
                                }

                                table.dataTable th,
                                table.dataTable td {
                                    padding: 2px 2px !important;
                                }

                                h3 { font-size: 14pt; }

                                table td:nth-child(12),
                                table th:nth-child(12) {
                                    text-align: right;
                                }
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

                    }
                ],
                "data": lis, // Insere os dados na tabela
                "columns": [
                    { title: '#' },
                    { title: 'Setor' },
                    { title: 'Código' },
                    { title: 'Descrição' },
                    { title: 'Embalagem' },
                    { title: 'DtUlEn' },
                    { title: 'QtUlEn' },
                    { title: 'CXA' },
                    { title: 'UND' },
                    { title: 'Idade' },
                    { title: 'ISV' },
                    { title: 'Total' },
                    { title: 'VND' }
                ],
                createdRow: function(row, data) {
                    const dataOriginal = data[5]; // Exemplo: "2025-09-10"
                    const partes = dataOriginal.split('-'); // ["2025", "09", "10"]
                    const dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`; // "10/09/2025"
                    const valor = parseFloat(data[11]);
                    $('td', row).eq(10).html(`R$ ${valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
                    $('td', row).eq(4).html(dataFormatada);
                },
            });
            table.buttons().container().appendTo('#relISVDetalhado_wrapper .col-md-6:eq(0)');
        });
    }
    var dashboardISVRel = function (){
       $('#overlay-isv-rel').show();
        $.post(controller+'back_dash_isv.php',{cqu: 101}, function(retorno){
            setTimeout(() => {
            if(retorno.error == 101 || retorno.error == 102){
                $('#list-rel-isv').empty();
                var msmVazio ="<div class='info-box'>"
                +"<span class='info-box-icon' style='background: rgb(248, 99, 2); margin-right: 10px;'><i class='fas fa-exclamation' style='color: white;'></i></span>"
                // +"<span class='info-box-icon' style='background: rgb(248, 99, 2); margin-right: 10px;'><i class='fas fa-exclamation' style='color: white;'></i></span>"
                +"<div class='info-box-content'>"
                +"<span class='info-box-text' style='font-size: 2em;'>"+retorno.message+"</span>"
                +"<span class='info-box-number'></span></div></div>";
                $('#list-rel-isv').append(msmVazio);
            }else{
                $('#list-rel-isv').empty();
                $.each(retorno, function(i,val){
                    var listaRelatorio = "<li class='p-0'style='font-size: 0.85em;>"
                    +"<!-- drag handle -->"
                    +"<span class='handle'>"
                    +"  <i class='fas fa-ellipsis-v'></i>"
                    +"<i class='fas fa-ellipsis-v'></i>"
                    +"</span>"
                    +"<span class='text' >Relatório de ISV - "+val.setor+" de "+val.dia01+" dia(s)"+val.nome+"</span>"
                    +"<small class='badge badge-success m-1'>ISV "+real(val.totalisvrel)+"</small>"
                    +"<small class='badge badge-primary m-1'>Vendido "+real(val.totalisvvndrel)+"</small>"
                    +"<small class='badge badge-secondary m-1'>Restante "+real(val.totalisvrest)+"</small>"
                    +"<small class='badge badge-warning m-1'>Amanhã "+real(val.totoalisvamanha)+"</small>"
                    +"<div class='tools iconsrelisv'>"
                    +"  <i class='fas fa-edit alt-rel-isv' id='"+val.id+";101'></i>"
                    +"  <i class='fas fa-trash del-rel-isv' id='"+val.id+";101'></i>"
                    +"  <i class='fas fa-clipboard-list visualizarRelISV' id='"+val.id+";101'></i>"
                    +"</div>"
                    +"</li>";                      

                    $('#list-rel-isv').append(listaRelatorio);
                });
            }
            $('#overlay-isv-rel').hide();
            }, 500); // 1000 milissegundos = 1 segundos
        });
    }
    function GerarSelectPos(SelecDiv, SelecId, CQuery, Titulo, qtdpos, PosIni) {
        const $div = $('#' + SelecDiv);
        $div.empty();

        const selectHTML = `
            <label for="${SelecId}" class="form-label">${Titulo}</label>
            <select id="${SelecId}" class="form-control" required>
                <option value="" disabled ${!PosIni ? 'selected' : ''}>Selecione...</option>
            </select>
        `;
        $div.append(selectHTML);

        const gerarPosicao = (quantidade) => Array.from({ length: quantidade }, (_, i) => i + 1);

        const todasPosicoes = gerarPosicao(qtdpos);
        //const todasPosicoes = [1, 2, 3, 4, 5, 6, 7];

        $.post(controller + 'back_select_pos.php', {codquery: CQuery},function(retorno) {
            const ocupadas = Array.isArray(retorno) 
                ? retorno.map(item => parseInt(item.pos) || 0).filter(n => n > 0)
                : [];

            const $select = $('#' + SelecId);

            todasPosicoes.forEach(pos => {
                // Se a posição está ocupada e não é a posição inicial, pula
                if (ocupadas.includes(pos) && pos !== parseInt(PosIni)) return;

                // Marca como selecionado se for a posição atual
                const selected = (parseInt(PosIni) === pos) ? 'selected' : '';

                $select.append(`<option value="${pos}" ${selected}>${pos}º posição</option>`);
            });

        }, 'json')
        .fail(function() {
            console.error('Erro ao carregar posições.');
        });
    }
    function GerarSelectOperador(SelecDiv, SelecId, Titulo, OperadorAtual) {
        const $div = $('#' + SelecDiv);
        $div.empty();

        const selectHTML = `
            <label for="${SelecId}" class="form-label">${Titulo}</label>
            <select id="${SelecId}" class="form-control" required>
                <option value="" disabled ${!OperadorAtual ? 'selected' : ''}>Selecione...</option>
            </select>
        `;
        $div.append(selectHTML);

        // Lista criativa e organizada de operadores
        const operadoresSQL = {
            ">": "Maior (>)",
            ">=": "Maior ou igual (≥)",
            "<": "Menor (<)",
            "<=": "Menor ou igual (≤)",
            "=": "Igual (=)",
            "!=": "Diferente (≠)" // 🔹 opcional, mas útil
        };

        const $select = $('#' + SelecId);
        $.each(operadoresSQL, function(valor, texto) {
            const selected = (OperadorAtual === valor) ? 'selected' : '';
            $select.append(`<option value="${valor}" ${selected}>${texto}</option>`);
        });
    }
    function GerarSelectOperadorLogico(SelecDiv, SelecId, Titulo, OperadorAtual) {
        const $div = $('#' + SelecDiv);
        $div.empty();

        const selectHTML = `
            <label for="${SelecId}" class="form-label">${Titulo}</label>
            <select id="${SelecId}" class="form-control" required>
                <option value="" disabled ${!OperadorAtual ? 'selected' : ''}>Selecione...</option>
            </select>
        `;
        $div.append(selectHTML);

        // Lista criativa e organizada de operadores
        const operadoresSQL = {
            "IN": "Incluir setores (IN)",
            "NOT IN": "Excluir setores (NOT IN)"
        };

        const $select = $('#' + SelecId);
        $.each(operadoresSQL, function(valor, texto) {
            const selected = (OperadorAtual === valor) ? 'selected' : '';
            $select.append(`<option value="${valor}" ${selected}>${texto}</option>`);
        });
    }
    function GerarSelectDias(SelecDiv, SelecId, Titulo, DiaAtual, MaxDias = 11) {
        const $div = $('#' + SelecDiv);
        $div.empty();

        const selectHTML = `
            <label for="${SelecId}" class="form-label">${Titulo}</label>
            <select id="${SelecId}" class="form-control" required>
                <option value="" disabled ${!DiaAtual ? 'selected' : ''}>Selecione...</option>
            </select>
        `;
        $div.append(selectHTML);

        const $select = $('#' + SelecId);

        // gera as opções de 1 até MaxDias
        for (let i = 1; i <= MaxDias; i++) {
            const selected = (parseInt(DiaAtual) === i) ? 'selected' : '';
            const texto = i === 1 ? `${i} dia` : `${i} dias`;
            $select.append(`<option value="${i}" ${selected}>${texto}</option>`);
        }
    }
    function LoadNome(SelecDiv, SelecId, Titulo, Nome) {
        const $div = $('#' + SelecDiv);
        $div.empty(); // Limpa o conteúdo anterior

        // Garante que Nome sempre seja string
        const valorNome = (Nome ?? '').trim();

        // Cria o label e o input dinamicamente
        const inputHTML = `
            <label for="${SelecId}" class="form-label d-block mb-1">${Titulo}</label>
            <input 
                id="${SelecId}" 
                type="text" 
                class="form-control" 
                placeholder="Informe um nome para o relatório, caso julgue necessário."
                value="${valorNome}">
        `;

        // Insere o conteúdo no container alvo
        $div.append(inputHTML);

        // (Opcional) transforma automaticamente o texto em MAIÚSCULAS
        $('#' + SelecId).on('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    function GerarSelectSetores(SelecDiv, SelecId, Titulo, OperadorAtual) {
        const $div = $('#' + SelecDiv);
        $div.empty();

        const selectHTML = `
            <label for="${SelecId}" class="form-label">${Titulo}</label>
            <select id="${SelecId}" class="form-control" required>
                <option value="" disabled ${!OperadorAtual ? 'selected' : ''}>Selecione...</option>
            </select>
        `;
        $div.append(selectHTML);

        // Lista criativa e organizada de operadores
        const operadoresSQL = {
            "LOJA": "LOJA (LJ)",
            "FRIOS": "FRIOS (RF)",
            "HORTIFRUTI": "HORTIFRUTI (HF)",
            "AÇOUGUE": "AÇOUGUE (AC)",
            "PADARIA": "PADARIA (PA)",
            "FATIADO": "FATIADO (FR)",
            "CAFETERIA": "CAFETERIA (CF)"
        };

        const $select = $('#' + SelecId);
        $.each(operadoresSQL, function(valor, texto) {
            const selected = (OperadorAtual === valor) ? 'selected' : '';
            $select.append(`<option value="${valor}" ${selected}>${texto}</option>`);
        });
    }
    function GerarCheckBoxSetores(SelecDiv, InputCheckBox, setor, Titulo, SetoresSelecionados) {
        // Normaliza SetoresSelecionados
        let selecionadosArray = [];
        if (!SetoresSelecionados) {
            selecionadosArray = [];
        } else if (Array.isArray(SetoresSelecionados)) {
            selecionadosArray = SetoresSelecionados.map(String);
        } else if (typeof SetoresSelecionados === 'string') {
            selecionadosArray = SetoresSelecionados.split(',').map(s => s.trim()).filter(Boolean);
        } else {
            selecionadosArray = [String(SetoresSelecionados)];
        }

        const $div = $('#' + SelecDiv);
        $div.empty();

        // Header + input
        $div.append(`
            <label style="font-size: 1.5em;">${Titulo}</label>
            <input id="${InputCheckBox}" type="text" class="form-control" disabled>
        `);

        // Container único
        const $container = $(`<div id="${SelecDiv}_container" style="display:flex; margin:7px; flex-wrap:wrap; gap:1px; align-items:flex-start;"></div>`);
        $div.append($container);

        // Requisição backend
        $.post(controller + 'back_select_setores_smg13.php', function(retorno) {
            let setores = retorno;
            if (typeof retorno === 'string') {
                try { setores = JSON.parse(retorno); } 
                catch(e){ console.error('Erro JSON setores', e); setores = []; }
            }

            const itensPorColuna = 1;
            let coluna = $(`<div class="coluna-setores" style="flex:1; min-width:400px;"></div>`);
            $container.append(coluna);

            const disponiveisSet = new Set();

            $.each(setores, function(i, val) {
                if(i>0 && i % itensPorColuna === 0){
                    coluna = $(`<div class="coluna-setores" style="flex:1; min-width:400px;"></div>`);
                    $container.append(coluna);
                }

                const snuStr = String(val.snu);
                disponiveisSet.add(snuStr);
                const isChecked = selecionadosArray.includes(snuStr);

                const inputId = `customCheckbox_${snuStr}_${SelecDiv}`;
                const $input = $(`<input class="custom-control-input" type="checkbox" name="${setor}[]" id="${inputId}" value="${snuStr}">`);
                if(isChecked) $input.prop('checked', true);

                const $label = $(`<label class="custom-control-label" for="${inputId}">${snuStr} - ${val.sno}</label>`);
                const $wrap = $(`<div class="custom-control custom-checkbox" style="margin-bottom:1px;"></div>`);
                $wrap.append($input).append($label);
                coluna.append($wrap);
            });

            // Atualiza o input quando checkboxes mudarem (delegation no container)
            const namespace = `change.${InputCheckBox}`;
            $container.off(namespace, `input[name="${setor}[]"]`);
            $container.on(namespace, `input[name="${setor}[]"]`, function() {
                const selecionados = $container.find(`input[name="${setor}[]"]:checked`)
                    .map(function(){ return $(this).val(); })
                    .get()
                    .join(',');
                $('#' + InputCheckBox).val(selecionados);
            });

            // Inicializa input com valores existentes
            const iniciaisPresentes = selecionadosArray.filter(s => disponiveisSet.has(s));
            $('#' + InputCheckBox).val(iniciaisPresentes.join(','));
        }, 'json');
    }
    // Adicionar parametros de ISV
    $(document).on('click','#btn-adic-isv',function() {
        $('#modal-add-item-isv').modal({backdrop: 'static', keyboard: false});

        //Funções que gera os select dos formularios
        GerarSelectPos('SelectPos','addisvPos', 100,'Posição', 7, '');
        GerarSelectOperador('SelectOpeADD', 'addisvOperacao', 'ISV - Operação', '');
        GerarSelectDias('SelectDiasISVADD', 'addisvDias', 'ISV - Dias', '')
        GerarSelectOperador('SelectIdaOpeADD', 'addidaOperacao', 'Idade - Operação', '');
        GerarSelectDias('SelectIdaDiasADD', 'addidaDias', 'Idade - Dias', '')

        GerarCheckBoxSetores('addSelectSetores', 'addisvSetoresInput', 'setores','Setores que serão excluídos do indicador')
        

        $('#addbtnISV').empty();
        $('#addbtnISV').append("<a><button id='btn-add-isv-salva' type='button' class='btn btn-primary'>Salvar</button></a>");       
    });
    $(document).on('click','#btn-add-isv-salva',function() {
        pos = $('#addisvPos').val();
        op1 = $('#addisvOperacao').val();
        dt1 = $('#addisvDias').val();
        op2 = $('#addidaOperacao').val();
        dt2 = $('#addidaDias').val();
        set = $('#addisvSetoresInput').val();

        if (pos === '' || pos === null) {
            toastr.error('Selecione um Posição!');
        } 
        else if (op1 === '' || op1 === null) {
            toastr.error('Selecione a primeira operação </br> (ISV - Operação)!');
        } 
        else if (dt1 === '' || dt1 === null) {
            toastr.error('Selecione o número de dias </br> (ISV - Dias)!');
        } 
        else if (op2 === '' || op2 === null) {
            toastr.error('Selecione a segunda operação </br> (Idade - Operação)!');
        } 
        else if (dt2 === '' || dt2 === null) {
            toastr.error('Selecione o número de dias </br> (Idade - Dias)!');
        } 
        else if (!set || set.length === 0) {
            toastr.info('Selecione ao menos um setor!');
        } 
        else {
            var dados = {
                cqu: 100,
                pos: pos,
                op1: op1,
                dt1: dt1,
                op2: op2,
                dt2: dt2,
                set: set
            };
            // console.log(dados);
            $.post(controller + 'back_insert_parametro_isv.php', dados, function(retorno) {
                if (retorno.error == 101) {
                    toastr.success(retorno.message);
                    $("#modal-add-item-isv").modal('hide');
                    parametrosISV();
                } else {
                    toastr.error(retorno.message);
                }
            });
        }
        
    });
    // Editar parametros de ISV
    $(document).on('click','.edit-isv',function() {
        var pega = $(this).attr("dat");
        separa = pega.split(';');
        id = separa[0];
        cqu = separa[1];
        // toastr.success(id+qu);
        // $.post(controller+'back_select_dashboard_item.php',{id:id}, function(retorno){
        $.post(controller+'back_select_isv_rel_itens.php',{id:id,cqu:cqu}, function(retorno){
             
            $.each(retorno, function(index,val){
                // $('#delid').empty();
                // $('#delid').append("<b >"+val.id+"</b>");
                
                //Funções que gera os select dos formularios
                GerarSelectPos('SelectPosAlterar','isvPos', 100,'Posição', 7, val.pos);
                GerarSelectOperador('SelectOpeAlterar', 'isvOperacao', 'ISV - Operação', val.ope01);
                GerarSelectDias('SelectDiasISVAlterar', 'isvDias', 'ISV - Dias', val.dias01)
                GerarSelectOperador('SelectIdaOpeAlterar', 'idaOperacao', 'Idade - Operação', val.ope02);
                GerarSelectDias('SelectIdaDiasAlterar', 'idaDias', 'Idade - Dias', val.dias02)
                GerarCheckBoxSetores('SelectSetores', 'isvSetoresInput', 'setoresalt','Setores que serão excluídos do indicador', val.setores)
                
                // $('#isvSetoresInput').val(val.setores);
                
                $('#addbtnsalvaISV').empty();
                $('#addbtnsalvaISV').append("<a><button id='btn-salva-isv' type='button' class='btn btn-primary' dataid="+val.id+">Salvar</button></a>");
            });
            

        });

        $('#modal-edit-item-isv').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#btn-salva-isv',function() {
        var id = $(this).attr("dataid");
        pos = $('#isvPos').val();
        op1 = $('#isvOperacao').val();
        dt1 = $('#isvDias').val();
        op2 = $('#idaOperacao').val();
        dt2 = $('#idaDias').val();
        set = $('#isvSetoresInput').val();
        if($.isNumeric(pos) && $.isNumeric(dt1) && $.isNumeric(dt2)){
            
            var dados = {
                cqu: 100,
                id:id,
                pos:pos,
                op1:op1,
                dt1:dt1,
                op2:op2,
                dt2:dt2,
                set:set
            }
            // console.log(dados);
            $.post(controller+'back_update_dashboard_paramentros.php',dados, function(retorno){
                if(retorno == 101){
                    toastr.success("Os parâmetros foram alterados com sucesso!");
                    $("#modal-edit-item-isv").modal('hide');
                    parametrosISV();
                    
                }else{
                    toastr.error("Erro ao altera!!!");
                }
            });
        }else{
            // $('#qtd'+snota[0]).val("");
            toastr.error('Somente Números!', 'Atenção!');   
        }
        
    });
    // Excluir parametros de ISV
    $(document).on('click','.del-isv',function() {
        var pega = $(this).attr("dat");
        // console.log(pega);
        sep = pega.split(';')
        // toastr.success(sep[0]+";"+sep[1]);
        $('#id-del-isv').empty();
        $('#id-del-isv').append(sep[0]);
        $('#addbtnExcluirISV').empty();
        $('#addbtnExcluirISV').append("<a><button id='btn-excluir-isv' type='button' class='btn btn-warning' dataid="+sep[0]+";"+sep[1]+">Excluir</button></a>");
        $('#modal-delete-isv').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#btn-excluir-isv',function() {
        var pega = $(this).attr("dataid");
        // toastr.success(pega);
        sep = pega.split(';')
        $.post(controller+'back_del_dash_isv.php', {id:sep[0],cqu:sep[1]}, function(dados){
            if(dados == 102){
                toastr.success("Deletado com sucesso!!!");
                parametrosISV();
                dashboardISV();
                dashboardISVRel();
            }else if(dados == 101){
                toastr.error("Erro ao Deletar!!!");
            }
        });
           
        parametrosISV();
        $('#modal-delete-isv').modal('hide');
    });
    // Ação load aba Relatorio
    $(document).on('click','#rel-isv-tab',function() {
       dashboardISVRel();    
    });
    $(document).on('click','#dash-isv-tab',function() {
       dashboardISV();    
    });
    // Adicionar parametro Relatorio ISV
    $(document).on('click','#add-rel-isv',function() {
        $('#modal-add-item-isv-rel').modal({backdrop: 'static', keyboard: false});
        nom = $('#addRelNomeRel').val("");
        //Funções que gera os select dos formularios
        GerarSelectPos('SelectPosAddRelISV','addisvPosRel', 101,'Posição', 15, '');
        GerarSelectOperador('SelectOpeADDRelISV', 'addisvOperacaoRel', 'Rel - ISV - Operação', '');
        GerarSelectDias('SelectDiasISVADDRelISV', 'addisvDiasRel', 'Rel - ISV - Dias', '')
        GerarSelectOperador('SelectIdaOpeADDRelISV', 'addidaOperacaoRel', 'Rel - Idade - Operação', '');
        GerarSelectDias('SelectIdaDiasADDRelISV', 'addidaDiasRel', 'Rel - Idade - Dias', '')

        GerarSelectOperadorLogico('SelectIdaOpeLogADDRelISV', 'addOpeLog', 'Rel - Incluir/Excluir', '')
        GerarSelectSetores('SelectPosAddRelSetISV', 'addNomeIsv', 'Setor da Loja', '');
        LoadNome('InputNomeRelISVADD', 'addRelNomeRel', 'Nome para Relatório', '');

        GerarCheckBoxSetores('addSelectSetoresRelISV', 'addisvSetoresInputRel', 'setores','Setores que serão incluido/excluídos do indicador')
        

        $('#addbtnISVRel').empty();
        $('#addbtnISVRel').append("<a><button id='btn-add-isv-rel-salva' type='button' class='btn btn-green'>Salvar</button></a>");       
    });
    $(document).on('click','#btn-add-isv-rel-salva',function() {
        pos = $('#addisvPosRel').val();
        op1 = $('#addisvOperacaoRel').val();
        dt1 = $('#addisvDiasRel').val();
        op2 = $('#addidaOperacaoRel').val();
        dt2 = $('#addidaDiasRel').val();
        opl = $('#addOpeLog').val();
        sto = $('#addNomeIsv').val();
        nom = $('#addRelNomeRel').val();
        set = $('#addisvSetoresInputRel').val();

        if (pos === '' || pos === null) {
            toastr.error('Selecione um Posição!');
        } 
        else if (op1 === '' || op1 === null) {
            toastr.error('Selecione a primeira operação </br> (Rel - ISV - Operação)!');
        } 
        else if (dt1 === '' || dt1 === null) {
            toastr.error('Selecione o número de dias </br> (Rel - ISV - Dias)!');
        } 
        else if (op2 === '' || op2 === null) {
            toastr.error('Selecione a segunda operação </br> (Rel - Idade - Operação)!');
        } 
        else if (dt2 === '' || dt2 === null) {
            toastr.error('Selecione o número de dias </br> (Rel - Idade - Dias)!');
        } 
        else if (opl === '' || opl === null) {
            toastr.error('Selecione a operação que inclui ou exclui setores </br> (Rel - Incluir/Excluir)!');
        }
        else if (sto === '' || sto === null) {
            toastr.error('Selecione um setor da loja </br> (Setor da Loja)!');
        } 
        else if (!set || set.length === 0) {
            toastr.info('Selecione ao menos um setor!');
        } 
        else {
            var dados = {
                cqu: 101,
                pos: pos,
                op1: op1,
                dt1: dt1,
                op2: op2,
                dt2: dt2,
                opl: opl,
                sto: sto,
                nom: nom,
                set: set
            };

            $.post(controller + 'back_insert_parametro_isv.php', dados, function(retorno) {
                if (retorno.error == 101) {
                    toastr.success(retorno.message);
                    $("#modal-add-item-isv-rel").modal('hide');
                    dashboardISVRel();
                } else {
                    toastr.error(retorno.message);
                }
            });
        }
        
    });
    // Alteracao parametro Relatorio ISV
    $(document).on('click','.alt-rel-isv',function() {
        var pega = $(this).attr("id");
        separa = pega.split(';');
        id = separa[0];
        cqu = separa[1];
        // toastr.success(id+cqu);
        $.post(controller+'back_select_isv_rel_itens.php',{id:id,cqu:cqu}, function(retorno){
            // console.log(retorno);
            $.each(retorno, function(index,val){                
                // Funções que gera os select dos formularios
                GerarSelectPos('SelectPosATLRelISV','isvPosALTISV', 101,'Posição', 15, val.pos);
                GerarSelectOperador('SelectOpeALTRelISV', 'isvOperacaoALT', 'ISV - Operação', val.ope01);
                GerarSelectDias('SelectDiasISVALTRelISV', 'isvDiasALT', 'ISV - Dias', val.dias01)
                GerarSelectOperador('SelectIdaOpeALTRelISV', 'idaOperacaoALT', 'Idade - Operação', val.ope02);
                GerarSelectDias('SelectIdaDiasALTRelISV', 'idaDiasALT', 'Idade - Dias', val.dias02)
                GerarSelectOperadorLogico('SelectIdaOpeLogALTRelISV', 'addOpeLogALT', 'Rel - Incluir/Excluir', val.opelogico)
                GerarSelectSetores('SelectPosALTRelSetISV', 'SetorISVALT', 'Setor da Loja', val.setnome);
                LoadNome('InputNomeRelISVALT', 'inputNomeISV', 'Nome para Relatório', val.nome);
                GerarCheckBoxSetores('SelectSetoresRelISVALT', 'isvSetoresALT', 'setoresrelalt','Setores que serão incluido/excluídos do indicador', val.setores);
                $('#btnAltRelISV').empty();
                $('#btnAltRelISV').append("<a><button id='btn-salva-alt-rel-isv' type='button' class='btn btn-primary' dataid="+val.id+">Salvar</button></a>");
            });
        });
        $('#modal-alt-item-isv-rel').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#btn-salva-alt-rel-isv',function() {
        var id = $(this).attr("dataid");
        pos = $('#isvPosALTISV').val();
        op1 = $('#isvOperacaoALT').val();
        dt1 = $('#isvDiasALT').val();
        op2 = $('#idaOperacaoALT').val();
        dt2 = $('#idaDiasALT').val();
        opl = $('#addOpeLogALT').val();
        sto = $('#SetorISVALT').val();
        nom = $('#inputNomeISV').val();
        set = $('#isvSetoresALT').val();
        // toastr.success(id+pos+opl);
        var dados = {
            cqu: 101,
            id:id,
            pos:pos,
            op1:op1,
            dt1:dt1,
            op2:op2,
            dt2:dt2,
            opl:opl,
            sto:sto,
            nom:nom,
            set:set
        }
        // console.log(dados);
        $.post(controller+'back_update_dashboard_paramentros.php',dados, function(retorno){
            if(retorno == 101){
                toastr.success("Os parâmetros foram alterados com sucesso!");
                $("#modal-alt-item-isv-rel").modal('hide');
                dashboardISVRel();
                
            }else{
                toastr.error("Erro ao altera!!!");
            }
        });  
    });
    // Excluir parametros relatorio de ISV
    $(document).on('click','.del-rel-isv',function() {
        var pega = $(this).attr("id");
        // console.log(pega);
        sep = pega.split(';')
        // toastr.success(pega);
        $.post(controller+'back_select_isv_rel_itens.php',{id:sep[0],cqu:sep[1]}, function(retorno){
            console.log(retorno);
            $('#msmdel').empty();
            $.each(retorno, function(i,val){
                $('#msmdel').append(`<h5>Deseja realmente excluir este relatório abaixo?</h5>`);
                $('#msmdel').append(`<h5>Relatório de ISV - ${val.setnome} de ${val.dias01} dia(s) - ${val.nome}</h5>`);
                $('#addbtnExcluirISVRel').empty();
                $('#addbtnExcluirISVRel').append("<a><button id='btn-excluir-isv-rel' type='button' class='btn btn-warning' dataid="+sep[0]+";"+sep[1]+">Excluir</button></a>");
                $('#modal-delete-rel-isv').modal({backdrop: 'static', keyboard: false});
            });
        });
    });
    $(document).on('click','#btn-excluir-isv-rel',function() {
        var pega = $(this).attr("dataid");
        sep = pega.split(';')
        // toastr.success(sep[0]);
        $.post(controller+'back_del_dash_isv.php', {id:sep[0],cqu:sep[1]}, function(dados){
            if(dados == 102){
                toastr.success("Deletado com sucesso!!!");
                parametrosISV();
                dashboardISVRel();
            }else if(dados == 101){
                toastr.error("Erro ao Deletar!!!");
            }
        });
           
        parametrosISV();
        $('#modal-delete-rel-isv').modal('hide');
    });
    // Ações para load 
    $(document).on('click','.configISV',function() {
        parametrosISV();
        $('#modal-config-isv').modal({backdrop: 'static', keyboard: false});
    });
    // Load items relatorio isv
    $(document).on('click','.visualizarRelISV',function() {
        var pega = $(this).attr("id");
        // toastr.success(pega);
        RelatorioISVDetalhado(pega);
        $('#modal-visualizar-isv-rel').modal({backdrop: 'static', keyboard: false});
    });
     $(document).on('click','.atuaISV',function() {
        parametrosISV();
        dashboardISV();
        dashboardISVRel();
    });
    // Botões de Fechar
    $(document).on('click','#fechaISVRel',function() {
        dashboardISVRel();
    });
    $('#fechaISV').on('click', function() { 
        dashboardISV();
        dashboardISVRel();
    });
    $('#fechasrt03').on('click', function() { 
        dashboardISV();
        dashboardISVRel();
    });
}); 