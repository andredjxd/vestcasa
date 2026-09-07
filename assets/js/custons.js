// Pasta Raiz do Controller
// alert("Eu sou um alert!");
const controller = '../controller/';
function isNumeric(str){
    var er = /^[0-9]+$/;
    return (er.test(str));
}
function relatoriaVenciItens(id){
    // alert("Eu sou um alert!");
    $.post(controller + 'back_select_relatorios_itens.php', { id: id }, function(retorno) {
        // Limpa a tabela existente
        $('#codtabvenci').empty();
        //console.log(retorno);
        // Inicializa um array para armazenar os dados formatados
        let lis = [];
        
        // Itera sobre os dados retornados e os formata para a tabela
        $.each(JSON.parse(retorno), function(index, val) {
            // Concatena os IDs para identificação do item
            var ids = val.id + ';' + val.idrel;
            
            // Formata o valor monetário com a cultura brasileira
            let resul = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.resu);
            
            // Cria um link de exclusão e edição para cada linha
            var del = "<td><a href='#' class='text-muted itemdelete' dat=" + ids + "><i class='fas fa-trash-alt'></i></a></td>" +
                      "<td><a href='#' class='text-muted edit-quant' dat=" + ids + "><i class='fas fa-edit'></i></a></td>" +
                      "<td><a href='#' class='text-muted edit-quants' dat=" + ids + "><i class='fas fa-calendar-alt'></i></a></td>";
            
            // Adiciona os dados na lista para preencher a tabela
            lis.push([
                val.codigo,val.sub, val.desc, val.emba, val.comp, val.data, val.dias, 
                val.rentrea, val.vndreal, val.rentau, val.vnd30, val.estoque, 
                val.cxa, val.und, resul, del
            ]);
        });
    
        // Inicializa o DataTable
        $('#relvenci').DataTable({
            // Configurações para a tabela
            "lengthChange": false,
            "autoWidth": false,
            "pageLength": 7,
            
            // Configurações de formatação e estilo das colunas
            "columnDefs": [
                {
                    targets: [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                    className: 'dt-body-center' // Centraliza as colunas selecionadas
                },
                {
                    targets: [1,4],
                    visible: false,
                    searchable: false, // Oculta a coluna "Comprador"
                }
            ],
    
            // Configurações dos botões de exportação
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'CONTROLE DE VALIDADE',
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 11, 12, 8, 4, 5, 6]
                    },
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var styles = xlsx.xl['styles.xml'];
                    
                        // --- ADICIONA NOVAS CORES ---
                        var fills = $('fills', styles);
                        fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FFF7A016"/><bgColor indexed="64"/></patternFill></fill>');
                        fills.append('<fill><patternFill patternType="solid"><fgColor rgb="FF4C83E7"/><bgColor indexed="64"/></patternFill></fill>');
                        fills.attr('count', parseInt(fills.attr('count'), 10) + 2);
                    
                        // --- ADICIONA BORDA ---
                        var borders = $('borders', styles);
                        borders.append('\
                            <border>\
                                <left style="thin"><color auto="1"/></left>\
                                <right style="thin"><color auto="1"/></right>\
                                <top style="thin"><color auto="1"/></top>\
                                <bottom style="thin"><color auto="1"/></bottom>\
                            </border>');
                        borders.attr('count', parseInt(borders.attr('count'), 10) + 1);
                    
                        var fillsCount = $('fills fill', styles).length;
                        var bordersCount = $('borders border', styles).length;
                    
                        var cellXfs = $('cellXfs', styles);
                    
                        // Estilo 1: Laranja + Borda
                        cellXfs.append('<xf xfId="0" applyFill="1" fillId="' + (fillsCount - 2) + '" applyBorder="1" borderId="' + (bordersCount - 1) + '"/>');
                        var cellXfsCount1 = parseInt(cellXfs.attr('count'), 10);
                        cellXfs.attr('count', cellXfsCount1 + 1);
                    
                        // Estilo 2: Azul + Borda
                        cellXfs.append('<xf xfId="0" applyFill="1" fillId="' + (fillsCount - 1) + '" applyBorder="1" borderId="' + (bordersCount - 1) + '"/>');
                        var cellXfsCount2 = cellXfsCount1 + 1;
                        cellXfs.attr('count', cellXfsCount2 + 1);
                    
                        // Estilo 3: Só Borda (fundo branco normal)
                        cellXfs.append('<xf xfId="0" applyBorder="1" borderId="' + (bordersCount - 1) + '"/>');
                        var cellXfsCount3 = cellXfsCount2 + 1;
                        cellXfs.attr('count', cellXfsCount3 + 1);
                    
                        // --- APLICA ESTILOS NAS CÉLULAS ESPECÍFICAS ---
                        $('c[r="A2"]', sheet).attr('s', cellXfsCount2); // Azul
                        $('c[r="B2"]', sheet).attr('s', cellXfsCount1); // Laranja
                        $('c[r="C2"]', sheet).attr('s', cellXfsCount1); // Laranja
                        $('c[r="D2"]', sheet).attr('s', cellXfsCount2); // Azul
                        $('c[r="E2"]', sheet).attr('s', cellXfsCount2); // Azul
                        $('c[r="F2"]', sheet).attr('s', cellXfsCount1); // Laranja
                        $('c[r="G2"]', sheet).attr('s', cellXfsCount2); // Azul
                        $('c[r="H2"]', sheet).attr('s', cellXfsCount1); // Laranja
                    
                        // --- APLICA BORDA PARA TODAS AS OUTRAS CÉLULAS ---
                        $('row', sheet).each(function () {
                            $('c', this).each(function () {
                                var cell = $(this);
                                var address = cell.attr('r');
                                
                                // Evitar sobrescrever as células especiais
                                if ([
                                    'A2', 'B2', 'C2', 'D2', 'E2', 'F2', 'G2', 'H2'
                                ].indexOf(address) === -1) {
                                    cell.attr('s', cellXfsCount3); // aplica estilo só de borda
                                }
                            });
                        });
                    }
                    
                },
                {
                    extend: 'pdf',
                    orientation: 'landscape',
                    customize: function(doc) {
                        // Remove o título padrão do DataTables
                        doc.content.splice(0, 1);
    
                        // Define a data de geração do relatório
                        var now = new Date();
                        var jsDate = now.getDate() + '-' + (now.getMonth() + 1) + '-' + now.getFullYear();
    
                        // Configura as margens e o estilo do documento PDF
                        doc.pageMargins = [25, 90, 25, 30];
                        doc.defaultStyle.fontSize = 8.5;
                        doc.styles.tableHeader.fontSize = 8;
    
                        // Configura o cabeçalho do PDF
                        doc['header'] = function() {
                            return {
                                columns: [
                                    { alignment: 'right', image: xoperdas, width: 135, height: 60 },
                                    { alignment: 'center', margin: [0, 15, 0, 0], italics: true, bold: true, text: 'CONTROLE DE VALIDADE', fontSize: 20 },
                                    { alignment: 'left', image: atacadaonew, width: 135, height: 60 }
                                ],
                                margin: 20
                            };
                        };
    
                        // Configura o rodapé do PDF
                        doc['footer'] = function(page, pages) {
                            return {
                                columns: [
                                    { alignment: 'left', text: ['Criado em: ', { text: jsDate.toString() }] },
                                    { alignment: 'right', text: ['Página ', { text: page.toString() }, ' de ', { text: pages.toString() }] }
                                ],
                                margin: [20, 10]
                            };
                        };
    
                        // Estilo da linha e coluna no PDF
                        var objLayout = {
                            hLineWidth: function(i) { return .5; },
                            vLineWidth: function(i) { return .5; },
                            hLineColor: function(i) { return '#aaa'; },
                            vLineColor: function(i) { return '#aaa'; },
                            paddingLeft: function(i) { return 4; },
                            paddingRight: function(i) { return 4; }
                        };
                        doc.content[0].layout = objLayout;
    
                        // Configuração das larguras das colunas no PDF
                        doc.content[0].table.widths = [30, 'auto', 'auto', 'auto', 40, 20, 25, 'auto', 25, 'auto', 'auto', 'auto', 'auto', 'auto', '*'];
                    }
                }
            ],
    
            // Recria a tabela ao atualizar os dados
            "destroy": true,
            
            // Ordenação padrão das linhas
            "order": [[5, 'asc']],
    
            // Dados para a tabela
            "data": lis,
    
            // Definição das colunas
            "columns": [
                { title: 'Cod.' },
                { title: 'Sub' },
                { title: 'Descrição' },
                { title: 'Embal.' },
                { title: 'Comprador' },
                { title: 'Vencim.' },
                { title: 'Dia' },
                { title: 'RentR' },
                { title: 'Venda' },
                { title: 'RentA' },
                { title: 'V30D' },
                { title: 'Estoque' },
                { title: 'CX' },
                { title: 'UN' },
                { title: 'Venda' },
                { title: '' }
            ]
        }).buttons().container().appendTo('#relvenci_wrapper .col-md-6:eq(0)');
    });
    
}


$.ajaxSetup({
    cache: false
});
$(document).ready(function(){
    var statusdb = function (){
        $.post(controller+'back_status_db.php', function(retorno){
            // console.log(retorno);
            $('#statusbase').empty();           
            $.each(JSON.parse(retorno), function(index,val){
                if(val.smg13 == getData()){
                    var linha = "<tr><td>SMGOI13</td><td><span class='badge badge-success'>"+val.smg13+"</span></td><td><a class='text-muted uploadbase' dat='SMGOI13' ><i class='fas fa-upload'></i></a></td></tr>";
                }else{
                    var linha = "<tr><td>SMGOI13</td><td><span class='badge badge-danger'>"+val.smg13+"</span></td><td><a class='text-muted uploadbase' dat='SMGOI13'><i class='fas fa-upload'></i></a></td></tr>";
                }
                $('#statusbase').append(linha);
            
                // if(val.srt03 == getData()){
                //     var linha2 = "<tr><td>SRTBI03</td><td><span class='badge badge-success'>"+val.srt03+"</span></td><td><a class='text-muted uploadbase' dat='SRTBI03'><i class='fas fa-upload'></i></a></td></tr>";
                //     var srt03status = "<button type='button' class='btn btn-tool uploadbase' dat='SRTBI03'><span class='badge badge-success'>"+val.srt03+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                // }else{
                //     var linha2 = "<tr><td>SRTBI03</td><td><span class='badge badge-danger'>"+val.srt03+"</span></td><td><a class='text-muted uploadbase' dat='SRTBI03'><i class='fas fa-upload'></i></a></td></tr>";
                //     var srt03status = "<button type='button' class='btn btn-tool uploadbase' dat='SRTBI03'><span class='badge badge-danger'>"+val.srt03+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                // }
                // $('#statusbase').append(linha2);
                // $('#statussrt03').empty();
                // $('#statussrt03').append(srt03status);
                // console.log(getDataMenosUm());
                
                if(val.svdbi62 == getData()){
                    var linha3 = "<tr><td>SVDBI62</td><td><span class='badge badge-success'>"+val.svdbi62+"</span></td><td><a class='text-muted uploadbase' dat='SVDBI62'><i class='fas fa-upload'></i></a></td></tr>";
                    var statussvd62 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBI62'><span class='badge badge-success'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                }else{
                    var linha3 = "<tr><td>SVDBI62</td><td><span class='badge badge-danger'>"+val.svdbi62+"</span></td><td><a class='text-muted uploadbase' dat='SVDBI62'><i class='fas fa-upload'></i></a></td></tr>";
                    var statussvd62 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBI62'><span class='badge badge-danger'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                }
                $('#statusbase').append(linha3);
                $('#statussvd62').empty();
                $('#statussvd62').append(statussvd62);

                // if(val.svdbia2 == getData()){
                //     var linha4 = "<tr><td>SVDBIA2</td><td><span class='badge badge-success'>"+val.svdbia2+"</span></td><td><a class='text-muted uploadbase' dat='SVDBIA2'><i class='fas fa-upload'></i></a></td></tr>";
                //     var statussvda2 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBIA2'><span class='badge badge-success'>"+val.svdbia2+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                // }else{
                //     var linha4 = "<tr><td>SVDBIA2</td><td><span class='badge badge-danger'>"+val.svdbia2+"</span></td><td><a class='text-muted uploadbase' dat='SVDBIA2'><i class='fas fa-upload'></i></a></td></tr>";
                //     var statussvda2 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBIA2'><span class='badge badge-danger'>"+val.svdbia2+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                // }
                // $('#statusbase').append(linha4);
                // $('#statussvda2').empty();
                // $('#statussvda2').append(statussvda2);

                if(val.saebidiario == getDataMenosUm()){
                    var linha5 = "<tr><td>SAEBI51 DIARIO</td><td><span class='badge badge-success'>"+val.saebidiario+"</span></td><td><a class='text-muted uploadbase' dat='SAEBI51DIARIO'><i class='fas fa-upload'></i></a></td></tr>";
                    var statussae51 = "<button type='button' class='btn btn-tool uploadbase' dat='SAEBI51-DIARIO'><span class='badge badge-success'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                }else{
                    var linha5 = "<tr><td>SAEBI51 DIARIO</td><td><span class='badge badge-danger'>"+val.saebidiario+"</span></td><td><a class='text-muted uploadbase' dat='SAEBI51DIARIO'><i class='fas fa-upload'></i></a></td></tr>";
                    var statussae51 = "<button type='button' class='btn btn-tool uploadbase' dat='SAEBI51-DIARIO'><span class='badge badge-danger'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                }
                $('#statusbase').append(linha5);
                $('#statussaebi51diario').empty();
                $('#statussaebi51diario').append(statussae51);

                
                var linha6 = "<tr><td>SMGOI12</td><td><span class='badge badge-success'>Filial 702 Data:"+val.smgoi12+" Filial 671 Data:"+val.smgoi12out+"</span></td><td><a class='text-muted uploadbase' dat='SMGOI12'><i class='fas fa-upload'></i></a></td></tr>";
                var statussmg12 = "<button type='button' class='btn btn-tool uploadbase' dat='SMGOI12'><span class='badge badge-success'>Filial 702 Data:"+val.smgoi12+"</br> Filial 671 Data:"+val.smgoi12out+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                
                $('#statussmgoi12').empty();
                $('#statussmgoi12').append(statussmg12);

                //var linha7 = "<tr><td>SMGOI12</td><td><span class='badge badge-success'>Filial 702 Data:"+val.smgoi12+" Filial 671 Data:"+val.smgoi12out+"</span></td><td><a class='text-muted uploadbase' dat='SMGOI12'><i class='fas fa-upload'></i></a></td></tr>";
                var statussmg13 = "<button type='button' class='btn btn-tool uploadbase' dat='SMGOI13'><span class='badge badge-success'>SMGOI13 "+val.smg13+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                var statusVNDRUB = "<button type='button' class='btn btn-tool uploadbase' dat='VENDA-RUB'><span class='badge badge-success'>VENDA-RUB "+val.vndrub+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
                var configISV = "<button type='button' class='btn btn-tool configISV' dat='config'></span><i class='fas fa-cog'></i></button>";
                var atualizaISV = "<button type='button' class='btn btn-tool atuaISV' dat='atuISV'></span><i class='fas fa-sync-alt'></i></button>";
                
                $('#statussmgoi13').empty();
                $('#statussmgoi13').append(statussmg13+statusVNDRUB+configISV+atualizaISV);
                
                
                // if(val.subsidio == getData()){
                //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-success'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";
                // }else{
                //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-danger'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";;
                // }
                // $('#statusbase').append(linha3);
                
            });
        });
    }
    statusdb();
    
    // function verificaRel(nome,data){
    //     $('#load').empty();
    //     $('#load').append('<button class="btn btn-primary btn-lg" type="button" disabled><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>&nbsp;&nbsp;Atualizando relatórios no DB AGUARDE...</button>');
    //     $.post(controller+'back_upload_db.php', {nome: nome,data: data} ,function(retorno){
    //         if(retorno == 101){
    //             $('#load').empty();
    //             $('#load').append('<button class="btn btn-success btn-lg" type="button" disabled>Atualização realizado com sucesso!!!</button>');
    //         }else if(retorno == 102){
    //             $('#load').empty();
    //             $('#load').append('<button class="btn btn-danger btn-lg" type="button" disabled>Arquivo não encontrado!!!</button>');
    //             toastr.error("Arquivo não encontrado!");
    //         }else if(retorno == 103){
    //             $('#load').empty();
    //             $('#load').append('<button class="btn btn-danger btn-lg" type="button" disabled>Arquivo não encontrado no diretorio!!!</button>');
    //             //toastr.error("Arquivo não encontrado no diretorio!!!");
    //         }else if(retorno == 104){
    //             $('#load').empty();
    //             $('#load').append('<button class="btn btn-danger btn-lg" type="button" disabled>Arquivo não é CSV!!!</button>');
    //             //toastr.error("Arquivo não encontrado no diretorio!!!");
    //         }else if(retorno == 'Arquivo não é XLS'){
    //             $('#load').empty();
    //             $('#load').append('<button class="btn btn-danger btn-lg" type="button" disabled>Arquivo não é XLS!!!</button>');
    //         }else if(retorno == 105){
    //             $('#load').empty();
    //             $('#load').append('<button class="btn btn-danger btn-lg" type="button" disabled>Erro na atualizao da tabela</button>');
    //         }else{
    //             //toastr.info(retorno);
    //             //console.log(retorno);
    //             $('#load').empty();
    //             $('#load').append("<button class='btn btn-danger btn-lg' type='button' disabled>"+retorno+"</button>");
    //         }
    //         $('.select2').select2({
    //             theme: 'bootstrap4'
    //         });
    //         selectComprador();
    //     });
    
    // }
    function verificaRel(nome,data){
        $('#load').empty();
        
        // $('#load').append('<button class="btn btn-primary btn-lg" type="button" disabled><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>&nbsp;&nbsp;Atualizando relatórios no DB AGUARDE...</button>');
        $.post(controller+'back_upload_db.php', {nome: nome,data: data} ,function(retorno){

            //toastr.info(retorno);
            //console.log(retorno);
            $('#load').empty();
            $('#load').append("<button class='btn btn-danger btn-lg' type='button' disabled>"+retorno+"</button>");

        });
    
    }
    function getData() {
        var d = new Date();
        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        if (day < 10) {
            day = "0" + day;
        }
        if (month < 10) {
            month = "0" + month;
        }
        var date = day + "/" + month + "/" + year;
    
        return date;
    }
    function getDataMenosUm() {
        var d = new Date();
        var day = d.getDate() - 5;
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        if (day < 10) {
            day = "0" + day;
        }
        if (month < 10) {
            month = "0" + month;
        }
        var date = day + "/" + month + "/" + year;
    
        return date;
    }
    var datetime = null, date = null;
    var update = function () {
        date = moment(new Date())
        datetime.html(date.format('dddd, D MMMM YYYY, HH:mm:ss '));
    };
    //Date picker
    $('#reservationdatesrt03').datetimepicker({
        format: 'L'
    });

    // Configurações do toastr responsavel pelas notificacões!!!
    toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-left"
    };
    toastr.options.closeButton = true;

    // Função para carregar os compradores no select
    function selectComprador() {
        // Envia uma requisição POST para obter os dados dos compradores
        $.post(controller + 'back_select_comprador.php', function(dados) {
            // Converte os dados recebidos para um objeto JSON
            $.each(JSON.parse(dados), function(index, val) {
                // Adiciona as opções ao select de comprador
                $('#selectcomprador').append('<option value="'+val.comprador+'">'+val.comprador+'</option>');
                // Adiciona as opções ao select de ID de comprador
                $('#selectcomprador-id').append('<option value="'+val.comprador+'">'+val.comprador+'</option>');
            });
        });
    }

    // Inicializa o select2 para os selects com classe 'select2'
    $('.select2').select2({
        theme: 'bootstrap4'  // Aplica o tema Bootstrap 4
    });

    // Chama a função para preencher os selects com os compradores
    selectComprador();

    // Mascaras Input DATA
    $('#datapesq').inputmask('dd/mm/aaaa', { 'placeholder': 'dd/mm/aaaa' });
    //Data on-line
    datetime = $('#datagora')
    update();
    setInterval(update, 1000);

    const dadosTabela = [];
    var dadosConsul = "";
    
    var relatoriosVencimentos = function (){
        $.post(controller+'back_select_relatorios.php', function(retorno){
            //console.log(retorno);
            $('#relatoriosvencimentos').empty();
            $.each(JSON.parse(retorno), function(i,val){
                //console.log(val.comprador);
                if (val.data == getData()){
                    var stadt = "<span class='badge badge-success'>"+val.data+"</span>";
                }else{
                    var stadt = "<span class='badge badge-danger'>"+val.data+"</span>";
                }
                var comp = val.comprador.replace(' ','-');
                var cabreal = val.id+';'+comp
                var edit = "<a class='text-muted edit-itens' dat="+cabreal+" id='edit-itens'><i class='fas fa-edit'></i></a>";
                var editrel = "<a class='text-muted edit-rel' dat="+cabreal+" id='edit-itens'><i class='fas fa-pen'></i></a>";
                var arqu = "<a class='text-muted archive 'dat="+val.id+"><i class='fas fa-archive'></i></a>";
                var linha = "<tr><td>"+val.id+"</td><td>"+editrel+"</td><td>"+val.setor+"</td><td>"+val.comprador+"</td><td>"+val.nome+"</td><td>"+stadt+"</td><td>"+val.itens+"</td><td>"+edit+"</td><td>"+arqu+"</td></tr>";
                
                $('#relatoriosvencimentos').append(linha);
            });
    
    
        });
    }
    relatoriosVencimentos();
    // Configuração do autocomplete para o campo 'codigopesq'
    $('#codigopesq').autocomplete({
        // Função para buscar dados via AJAX
        source: function(request, response) {
            $.ajax({
                url: controller + "back_pesquisa.php", // URL do arquivo de back-end para pesquisa
                type: 'post', // Método de envio
                dataType: "json", // Espera uma resposta no formato JSON
                data: {
                    name: request.term // Envia o termo pesquisado no campo
                },
                success: function(datas) {
                    response(datas); // Passa os dados recebidos para o autocomplete
                    console.log(datas); // Exibe os dados recebidos no console (opcional para debug)
                }
            });
        },
        minLength: 2, // Define o número mínimo de caracteres para iniciar a busca
        select: function(event, ui) {
            // Limpa os campos relacionados ao resultado da pesquisa
            $('#undpesq').val("");
            $('#cxapesq').val("");
            
            // Foca no campo de data
            $('#datapesq').focus();
            
            // Armazena os dados selecionados em uma variável para uso posterior
            dadosConsul = ui.item.emba + ';' + ui.item.vnda + ';' + ui.item.comp + ';' + ui.item.arent + 
                        ';' + ui.item.venda + ';' + ui.item.rrent + ';' + ui.item.resu + ';' + ui.item.estoq+
                        ';' + ui.item.sub;
        }
    });

    $('#codigopesq').on('click',function() {
        $('#codigopesq').val("");
        $('#datapesq').val("");
        $('#cxapesq').val("");
        $('#undpesq').val("");
    });
    $('#datapesq').on('keypress',function(e) {
        if(e.which == 13) {
            $('#cxapesq').focus();
        }
    });
    $('#cxapesq').on('keypress',function(e) {
        if(e.which == 13) {
            $('#undpesq').focus();
        }
    });
    $('#undpesq').on('keypress',function(e) {
       
        if(e.which == 13) {
            idRel   = $('#numero').text();
            codigo  = $('#codigopesq').val();
            data    = $('#datapesq').val();
            cxa     = $('#cxapesq').val();
            und     = $('#undpesq').val();
            cod     = codigo.split('|');
            res     = dadosConsul.split(';');
            emb     = res[0];
            vnd30   = res[1];
            comprad = res[2];
            rentAtu = res[3];
            vndReal = res[4];
            rentRea = res[5];
            resulta = res[6];
            estoque = res[7];
            sub     = res[8];
            // console.log(idRel);
            dado=cod[0]+';'+cod[1]+';'+sub+';'+emb+';'+comprad+';'+data+';'+rentRea+';'+vndReal+';'+rentAtu+';'+vnd30+';'+estoque+';'+cxa+';'+und+';'+resulta;
            dadosTabela.push(dado);
            var dados = {
                idre: idRel,
                codig: cod[0],
                sub: sub,
                descricao: cod[1],
                emb: emb,
                comprado: comprad,
                data: data,
                rentr: rentRea,
                vndReal: vndReal,
                renta: rentAtu,
                vnd30: vnd30,
                estoque: estoque,
                cxa: cxa,
                und: und,
                resultado: resulta 
            };
            $.post(controller+'back_add_itens.php', dados, function(dados){
                
                $.each(JSON.parse(dados), function(index,val){
                    console.log(val.codigo);
                    $('#infoadd').empty();
                    var addrec = val.codigo+' - '+val.descricao+' - '+val.comprado+' - '+val.emb+' - '+val.datavc+' - CXA:'+val.cxa+' - UND:'+val.und;
                    $('#infoadd').append("<div class='col'><span class='badge badge-warning'>ADICIONADO: "+addrec+"</span></div>");
                    relatoriaVenciItens(idRel);
                    relatoriosVencimentos();
                });
                toastr.success('Adicionado com sucesso!');
                
                
                $('#codigopesq').val("");
                $('#datapesq').val("");
                $('#cxapesq').val("");
                $('#undpesq').val("");
                $('#codigopesq').focus();
            });
            //console.log(dadosTabela);
            //relatorioVenci(dadosTabela);
            
            
                 
        }
        //relatoriaVenciItens(idRel);
        
    });
    $(document).on('click','.itemdelete',function() {
        var pega = $(this).attr("dat");
        separa = pega.split(';')
        // toastr.success(separa[0]);
        $.post(controller+'back_del_itens.php', {id:pega}, function(dados){
            if(dados == 102){
                toastr.success("Deletado com sucesso!!!");
                relatoriaVenciItens(separa[1]);
                relatoriosVencimentos();
            }else if(dados == 101){
                toastr.error("Erro ao Deletar!!!");
            }
        });
           
        // relatorioVenci(dadosTabela);
    });
    $('#btnadd').on('click', function() { 
        $("#select-setor").val('0');
        $('#selectcomprador').val('0').trigger('change');
        $("#nome-create").val('');
        $('#nome-create').inputmask({casing:'upper'});
    });
    $('#btn-create-relat').on('click', function() {
        var setor = $("#select-setor").val();
        var comprador = $("#selectcomprador").val();
        var nome = $("#nome-create").val();
        if(setor == 0){
            toastr.error('Selecione um Setor!!!');  
        }else if(comprador == 0){
            toastr.error('Selecione um Comprador!!!');
        }else if(nome == ''){
            toastr.error('Digite seu Nome!!!');
        }else{
            var dados = {
                setor: setor,
                comprador: comprador,
                nome: nome
            };
            //console.log(dados);
            $.post(controller+'back_create_relatorio.php', dados, function(retorno){
                //console.log(retorno);
                if(retorno == 100){
                    toastr.error('ERRO ao criar o relatório - Verificar Banco de Dados!!');
                }else{
                    toastr.success(retorno, 'Criado com Sucesso!');
                    $("#modal-create-relatorio").modal('hide');
                    relatoriosVencimentos();
                }
            });
            $("#select-setor").val('0');
            $('#selectcomprador').val('0').trigger('change');
            $("#nome-create").val('');
            $('#nome-create').inputmask({casing:'upper'});
        }
    });
    $('#btnsyncdb').on('click', function() {
        statusdb();
        relatoriosVencimentos();
    });
    $(document).on('click','.uploadbase',function() {
        var pega = $(this).attr("dat");
        // toastr.success(pega);
        $('#addfile').empty();
        $('#load').empty();
       
        $('#addfile').append("<i class='fas fa-plus pega' dat="+pega+"></i><span> </span><span>Adicionar "+pega+"</span>");
        $("#modal-update-relatorio").modal({backdrop: 'static', keyboard: false});

        
        // $('#addfile').append("<i class='fas fa-plus pega' dat="+pega+"></i><span> </span><span>Adicionar "+pega+"</span>");
        // $("#modal-update-relatorio").modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.uploadbasedata',function() {
        var pega = $(this).attr("dat");
        // toastr.success(pega);
        $('#addfile').empty();
        $('#load').empty();
        $('#addfile').append("<i class='fas fa-plus pega' dat="+pega+"></i><span> </span><span>Adicionar "+pega+"</span>");
        $("#modal-update-relatorio-data").modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.edit-itens',function() {
        var pega = $(this).attr("dat");
        separa = pega.split(';');
        separan = separa[1].split('-');
        //toastr.success(pega);
        $('#numero').empty();
        $('#numero').append('<b>'+separa[0]+'</b>');
        $('#comprador').empty();
        $('#comprador').append(separan[0]+' '+separan[1]);
        $('#infoadd').empty();
        relatoriaVenciItens(separa[0]);
        $('#modal-relatorio-itens').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.edit-quant',function() {
        var pega = $(this).attr("dat");
        separa = pega.split(';');
        id = separa[0];
        // toastr.success(id);
        $.post(controller+'back_select_detalhe_itens.php',{id:id}, function(retorno){
            $.each(JSON.parse(retorno), function(index,val){
                $('#delid').empty();
                $('#delid').append("<b >"+val.idrel+"</b>");
                let resul = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.resu);
                $('#dcodigo').val(val.codigo);
                $('#dcomprador').val(val.comp);
                $('#ddescricao').val(val.desc);
                $('#demb').val(val.emba);
                $('#destoque').val(val.estoque);
                $('#dv30d').val(val.vnd30);
                $('#drentr').val(val.rentrea);
                $('#dvenda').val(val.vndreal);
                $('#drenta').val(val.rentau);
                $('#dresult').val(resul);
                $('#dvencimento').val(val.data);
                $('#ddias').val(val.dias);
                $('#dcxa').val(val.cxa);
                $('#dund').val(val.und);
                $('#addbtnsalva').empty();
                $('#addbtnsalva').append("<a><button id='btn-salva-detalhe' type='button' class='btn btn-primary' dataid="+val.id+">Salvar</button></a>");
            });
            

        });

        // $('#numero').empty();
        // $('#numero').append('<b>'+separa[0]+'</b>');
        // $('#comprador').empty();
        // $('#comprador').append(separan[0]+' '+separan[1]);
        // $('#infoadd').empty();
        //relatoriaVenciItens(separa[0]);
        $('#modal-edit-item').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','.edit-rel',function() {
        $('#btn-altera-relat').remove();
        var pega = $(this).attr("dat");
        separa = pega.split(';');
        separan = separa[1].split('-');
        //toastr.success(separa[0]);
        $('#idrel').empty();
        $('#idrel').append('<b>'+separa[0]+'</b>');
        $('#ncomprador').empty();
        $('#ncomprador').append(separan[0]+' '+separan[1]);
        //back_select_relatorios_alterar
        $.post(controller+'back_select_relatorios_alterar.php', {id:separa[0]}, function(retorno){
            $.each(JSON.parse(retorno), function(i,val){
                $("#select-setor-id").val(val.setor);
                var newOption = $("<option selected='selected'></option>").val(val.comprador).text(val.comprador);
                $('#selectcomprador-id').append(newOption).trigger('change');
                $("#nome-create-id").val(val.nome);
                $('.btn-altera-relat').append("<button id='btn-altera-relat' dat="+val.id+" type='button' class='btn btn-primary'>Alterar</button>");   
            });
        });
        $('#nome-create-id').inputmask({casing:'upper'});
        $('#modal-edit-relatorio').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click','#btn-altera-relat',function() {
        var id = $(this).attr("dat");
        var setor       = $("#select-setor-id").val();
        var comprador   = $("#selectcomprador-id").val();
        var nome        = $("#nome-create-id").val();
        var dados = {
            id: id,
            setor: setor,
            comprador: comprador,
            nome: nome
        };

        // console.log(dados);
        // toastr.success(comprador);
        $.post(controller+'back_update_relatorios.php', dados, function(retorno){
            if(retorno == 102){
                toastr.error('ERRO em altera o relatório - Verificar Banco de Dados!!');
            }else{
                toastr.success('Alterado com Sucesso!');
                $('#modal-edit-relatorio').modal('hide');
                relatoriosVencimentos();
                
            }
        });
        $('#btn-altera-relat').remove();
    });
    $(document).on('click','#btn-salva-detalhe',function() {
        idRel = $('#delid').text();
        var id = $(this).attr("dataid");
        cxa = $('#dcxa').val();
        und = $('#dund').val();
        if($.isNumeric(cxa) && $.isNumeric(und)){
            
            var dados = {
                id:id,
                cxa:cxa,
                und:und
            }
            $.post(controller+'back_update_relatorios_qtda.php',dados, function(retorno){
                if(retorno == 101){
                    toastr.success("Quantidade alterada com sucesso!!!");
                    $("#modal-edit-item").modal('hide');
                    relatoriaVenciItens(idRel)
                }else{
                    toastr.error("Erro ao altera!!!");
                }
            });
        }else{
            // $('#qtd'+snota[0]).val("");
            toastr.error('Somente Números!', 'Atenção!');   
        }
    });
    $(document).on('click','.archive',function() {
        var pega = $(this).attr("dat");
        toastr.success(pega);
    });
    $('#addfile').on('click', function() {
        $('#load').empty();
    });
    $('#deletefile').on('click', function() {
        $('#load').empty();
    });
    $(document).on('click','#updaterelatorio',function() {
        $("#modal-updatedb-relatorio").modal({backdrop: 'static', keyboard: false});
        idRel = $('#numero').text();
        $('#btn-update-relat').prop('disabled', false);
        $('#numeroup').empty();
        $('#numeroup').append('<b>'+idRel+'</b>');
        $('#updateprocess').append('<button class="btn btn-success btn-lg" type="button" disabled>Pronto inicia o processo!!!</button>');
        $(document).on('click','.closeup',function() {
            relatoriaVenciItens(idRel);
            $('#updateprocess').empty();
            
        });
    });
    $(document).on('click','#btn-update-relat',function() {
        idRel = $('#numero').text();
        $('#updateprocess').empty();
        $('#updateprocess').append('<button class="btn btn-primary btn-lg" type="button" disabled><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">&nbsp;&nbsp;</span>Atualizando relatórios no DB AGUARDE...</button>');
        $('#btn-update-relat').prop('disabled', true);
        
        $.post(controller+'back_select_relatorios_itens.php',{id:idRel}, function(retorno){
            // console.log(retorno);
            let countrel = 0;
            output = {};
            var obj = JSON.parse(retorno);
            for (var i = 0; i < obj.length; i++) {
                output["Test "+ i] = JSON.stringify(obj[i]);
             }
            var countreltt = output.totalCount = obj.length;
            // console.log(countreltt);
            
            $.each(JSON.parse(retorno), function(i,val){                
                $.post(controller+'back_update_relatorios_itens.php',{id:val.idrel,cod:val.codigo}, function(rto){});
                countrel++;
            });
            function upbtn(){ 
                $('#updateprocess').empty();
                $('#updateprocess').append('<button class="btn btn-success btn-lg" type="button" disabled>Atualização realizado com sucesso!!!</button>');
                relatoriosVencimentos();
            }
            // console.log(countrel);
            if(countreltt == countrel){
                // toastr.success(countrel+' - '+countreltt);
                setTimeout(upbtn, 1000);
                
                
            }
        });
        
    });
    $('#fechasrt03').on('click', function() { 
        statusdb();
        $('#progress-text').empty();
        
    });
    var copyright = function(){
        $('#copyright').empty();
        var d = new Date();
        var year = d.getFullYear();
        var linha = "Copyright &copy; 2023 - "+year+" <a href='#'>Andre Alves</a>";
        $('#copyright').append(linha);
    }
    copyright();


    function dropup() {
        Dropzone.autoDiscover = false;

        // Template base
        const previewNode = document.querySelector("#template");
        previewNode.id = "";
        const previewTemplate = previewNode.parentNode.innerHTML;
        previewNode.parentNode.removeChild(previewNode);

        const myDropzone = new Dropzone(document.body, {
            url: controller + "back_upload.php",
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
                    "SMGOI13"       : "xlsx",
                    "SAEBI51-DIARIO": "xlsx",
                    "SCDOU121"      : "xlsx",
                    "SRTBI03"       : "csv",
                    "SVDBI62"       : "csv",
                    "SVDBIA2"       : "csv",
                    "TERCEIROS"     : "csv",
                    "QUEBRAS"       : "xls",
                    "BOLETOS"       : "xls",
                    "BUDGET"        : "xlsx", // ok
                    "RAZAO"         : "xls",
                    "ALMOX"         : "xls",
                    "REFEITORIO"    : "xls",
                    "SMGOI12"       : "csv",
                    "VENDA-RUB"     : "xlsx"
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
                const data = $("#datasrt03").val();
                
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
                        controller + "process_dados_sse.php?nome=" + pega + "&data=" + data
                    );

                    // 🔹 Evento padrão (mensagens gerais)
                    evtSource.onmessage = function(event) {
                        $("#progress-text").append(`
                            <div class="alert alert-warning py-1 mb-1" style="border-radius: 0 0 8px 8px ;">
                                <i class="fas fa-info-circle"></i> ${event.data}
                            </div>
                        `);
                        $("#progress-text").scrollTop($("#progress-text")[0].scrollHeight);
                    };

                    // 🔹 Antes de iniciar o processo icone de carregando
                    $("#progress-label").html(`<i class="fa fa-spinner fa-spin fa-fw"></i> Processando: 0%`);

                    // 🔹 Evento de progresso
                    evtSource.addEventListener("progress", function(event) {
                        const percent = parseInt(event.data);
                        $("#progress-bar").css("width", percent + "%");
                        // Atualiza apenas o texto depois do ícone
                        $("#progress-label").contents().last()[0].textContent = ` Processando: ${percent}%`;
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
            if (myDropzone) myDropzone.removeAllFiles(true);
        });


    }

    dropup();
});