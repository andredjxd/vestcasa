// Pasta Raiz do Controller
//const controllerque = '../controller/';

function isNumeric(str){
    var er = /^[0-9]+$/;
    return (er.test(str));
}
function real(a){
    let res = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(a);
    // let res = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', useGrouping: false }).format(a);
    return res;
}
function valorPorcetagem(valor){
    let resul = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
    return resul;
}
function ValorMonet(valor) {
    if (valor == null || isNaN(valor)) {  // Verifica se o valor é nulo, indefinido ou não é um número
        return 0.00;  // Retorna valor padrão se não for válido
    }
    // Remove qualquer símbolo de moeda (exemplo: "R$") e vírgulas
    return parseFloat(valor.replace(/[^\d.-]/g, '').replace(',', '.'));
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


$(document).ready(function(){
    // $("#valor-total-despesa").maskMoney({symbol:'R$ ', thousands:'.', decimal:',', symbolStay: true});
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
    function getDataDB(dia) {
        var d = new Date();
        // var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        if (dia < 10) {
            dia = "0" + dia;
        }
        if (month < 10) {
            month = "0" + month;
        }
        var date = year + "" + month + "" + dia;
    
        return date;
    }
    function getDataDBFim(data) {
        var d = new Date(data);
        var day = d.getDate();
        var month = d.getMonth()+1;
        var year = d.getFullYear();
        
        var y = new Date();
        var yearv = y.getFullYear()-1;

        if (day < 10) {
            day = "0" + day;
        }
        if (month < 10) {
            month = "0" + month;
        }
        if(year == yearv){
            year = d.getFullYear()+1;
        }
        var date = year + "" + month + "" + day;
        return date;
    }
    var StatusDBQuebra = function (){
        $.post(controller+'back_quebra_status_db.php', function(retorno){
            // console.log(retorno);
            $('#statusbasequebra').empty();    
            $.each(JSON.parse(retorno), function(index,val){
                var linha = "<tr><td>QUEBRAS Portal ADM</td><td class='align-middle'><span class='badge badge-primary'>"+val.dtquebra+"</span></td><td rowspan='2' class='align-middle'><a class='text-muted uploadbase' dat='QUEBRAS'><i class='fas fa-upload'></i></a></td></tr>";
                $('#statusbasequebra').append(linha);
                var linha3 = "<tr><td>Última Atualização</td><td class='align-middle'><span class='badge badge-primary'>"+val.dtcreate+"</span></td></tr>";
                $('#statusbasequebra').append(linha3);
            
                if(val.dtoperador == getData()){
                    var linha2 = "<tr><td>SCDOU121 CPD Gera</td><td class='align-middle'><span class='badge badge-success'>"+val.dtoperador+"</span></td><td class='align-middle'><a class='text-muted uploadbase' dat='SCDOU121'><i class='fas fa-upload'></i></a></td></tr>";
                }else{
                    var linha2 = "<tr><td>SCDOU121 CPD Gera</td><td class='align-middle'><span class='badge badge-danger'>"+val.dtoperador+"</span></td><td class='align-middle'><a class='text-muted uploadbase' dat='SCDOU121'><i class='fas fa-upload'></i></a></td></tr>";
                }
                $('#statusbasequebra').append(linha2);
                
                

                // if(val.subsidio == getData()){
                //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-success'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";
                // }else{
                //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-danger'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";;
                // }
                // $('#statusbase').append(linha3);
                
            });
        });
    }
    StatusDBQuebra();
    var StatusDBIndicadores = function (){
        $.post(controller+'back_quebra_indicadores.php', function(retorno){
            // console.log(retorno);
            $('#statusbaseindicador').empty();
            let totalQuebra = 0; // Inicializa o total corretamente  
            $.each(JSON.parse(retorno), function(index,val){
                var linha = "<tr><td>"+val.mesano+"</td><<td>"+ real(val.totalrestapg)+"</td>";//<td><a class='text-muted uploadbase' dat='QUEBRAS'><i class='fas fa-upload'></i></a></td></tr>";
                $('#statusbaseindicador').append(linha);
                totalQuebra += parseFloat(val.totalrestapg); // Soma corretamente os valores numéricos
            });
            
            var Totais = "<tr><td><strong>Total</strong></td><td><strong>"+ real(totalQuebra)+"</strong></td>";
            $('#statusbaseindicador').append(Totais);
        });
    }
    StatusDBIndicadores();

    var RelatorioQuebra = function (){
        // Faz uma requisição POST para obter os dados do relatório
        $.post(controllerdespesa + 'back_quebra_relatorio.php', function(retorno) {
            $('#tabquebra').empty(); // Limpa a tabela antes de adicionar novos dados
            
            let liscnpj = []; // Lista para armazenar os dados processados

            // Processa cada item do retorno JSON
            $.each(JSON.parse(retorno), function(index, val) {
                let ids = val.operador;
                let nome = val.nome;
                let det = `<td style='text-align: center;'><a href='#' class='text-muted detalheopera' dat="${ids}"><i class='fas fa-search'></i></a></td>`;
                
                liscnpj.push([val.contador, val.login, val.operador, nome, val.dtquebra, ValorMonet(val.quebra), ValorMonet(val.quebrapg), ValorMonet(val.totalresta), det]);
            });

            // Inicializa o DataTable com os dados processados
            $('#relquebra').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 10,
                // "columnDefs": [
                //     { targets: [4, 5], className: 'dt-body-center' }
                // ],
                "buttons": [{   
                    "extend": 'pdf',
                    "filename": function() {
                        let now = new Date();
                        let dateStr = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()}`;
                        let timeStr = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
                        return `relatorio_quebras_${dateStr}_${timeStr}`;
                    },
                    // "orientation": 'portrait',
                    "orientation": 'landscape',
                    "className": 'btn btn-primary custom-pdf-button',
                    "exportOptions": { columns: [0, 2, 3, 4, 5, 6, 7] },
                    "customize": function(doc) {
                        doc.content.splice(0, 1); // Remove título padrão
                        
                         // Formatação dos valores na exportação
                         doc.content[0].table.body.forEach(function(row, i) {
                           
                            // Lista de colunas a serem formatadas
                            const colunas = [
                                { index: 4, texto: 'Quebra' },
                                { index: 5, texto: 'Pago' },
                                { index: 6, texto: 'Restante' }
                            ];
                            
                            colunas.forEach(coluna => {
                                if (row[coluna.index].text !== coluna.texto) {
                                    row[coluna.index].text = formatarMoeda(row[coluna.index].text);
                                }
                            });
                            
                        });

                        let now = new Date();
                        let jsDate = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getFullYear()} às ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
                        
                        // Configura margens e estilos do documento
                        doc.pageMargins = [25, 90, 25, 30];
                        doc.defaultStyle.fontSize = 9;
                        doc.styles.tableHeader.fontSize = 10;

                        // Cabeçalho do PDF
                        doc['header'] = function() {
                            return {
                                columns: [
                                    { alignment: 'right', image: atacadaonew, width: 135, height: 60 },
                                    { alignment: 'center', margin: [0, 15, 0, 0], italics: true, bold: true, text: 'RELATÓRIO DE SOBRAS E QUEBRAS', fontSize: 20 },
                                    { alignment: 'left', image: atacadaonew, width: 135, height: 60 }
                                ],
                                margin: 20
                            };
                        };

                        // Rodapé do PDF
                        doc['footer'] = function(page, pages) {
                            return {
                                columns: [
                                    { alignment: 'left', text: [`Gerado em: ${jsDate}`] },
                                    { alignment: 'center', text: ['By: Andre Alves '] },
                                    { alignment: 'right', text: [`Página ${page} de ${pages}`] }
                                ],
                                margin: [20, 10]
                            };
                        };
                        
                        doc.content[0].table.widths = [20, 30, '*', 50 , 50, 50, 50]; // Ajusta larguras da tabela
                    }
                }],
                "destroy": true, // Evita conflito ao recriar a tabela
                "order": [[3, 'asc']], // Ordena pela coluna de valor
                "data": liscnpj,
                "columns": [
                    { title: '#' },
                    { title: 'Login' },
                    { title: 'Ope' },
                    { title: 'Nome' },
                    { title: 'Data' },
                    { title: 'Quebra'},
                    { title: 'Pago'},
                    { title: 'Restante'},
                    { title: '' }
                ],
                "createdRow": function(row, data) {
                    // Formata o valor monetário para exibição
                    $('td', row).eq(5).html(`R$ ${data[5].toFixed(2).replace('.', ',')}`);
                    $('td', row).eq(6).html(`R$ ${data[6].toFixed(2).replace('.', ',')}`);
                    $('td', row).eq(7).html(`R$ ${data[7].toFixed(2).replace('.', ',')}`);
                },
                
            }).buttons().container().appendTo('#relquebra_wrapper .col-md-6:eq(0)');
        });

    }
    RelatorioQuebra();

    function relatorioQuebraDetalhado(ope){
        // Faz uma requisição POST para obter os dados detalhados do relatório de quebras
        $.post(controllerdespesa + 'back_quebra_relatorio_detalhado.php', { ope: ope }, function(retorno) {
            
            // Limpa o conteúdo da tabela antes de inserir novos dados
            $('#codtabdetalhado').empty();
            let listquebraope = [];
            
            // Processa os dados retornados e os formata para a DataTable
            $.each(JSON.parse(retorno), function(index, val) {
                listquebraope.push([
                    val.contador,
                    val.operador,
                    val.nome,
                    formatarData(val.dt_quebra), // Formata a data de quebra
                    ValorMonet(val.valor_qb),   // Formata valores monetários
                    ValorMonet(val.valor_pg),
                    ValorMonet(val.resta_pg)
                ]);
            });

            // Inicializa a DataTable
            let table = $('#relquebradetalhado').DataTable({
                "responsive": true,  // Tabela responsiva
                "lengthChange": false, // Remove a opção de alterar o número de linhas exibidas
                "autoWidth": false, // Mantém largura das colunas fixa
                "pageLength": 10, // Exibe 10 registros por página
                "destroy": true, // Garante recriação da tabela ao atualizar dados
                "order": [[2, 'asc']], // Ordena pela data de quebra de forma crescente
                "buttons": [{
                    extend: 'pdf',
                    filename: function() {
                        return `relatorio_quebras_${new Date().toISOString().slice(0, 19).replace('T', '_')}`;
                    },
                    //"orientation": 'portrait', // Define o formato do PDF como retrato (vertical)
                    "orientation": 'landscape', // Define a orientação do PDF como paisagem
                    "className": 'btn btn-primary custom-pdf-button',
                    "exportOptions": { columns: [0,1,2,3,4,5,6] },
                    "customize": function(doc) {
                        let now = new Date();
                        let jsDate = now.toLocaleString();

                        // Configura margens e estilos do documento
                        doc.pageMargins = [25, 90, 25, 30];
                        doc.defaultStyle.fontSize = 12;
                        doc.styles.tableHeader.fontSize = 8;

                        // Configura cabeçalho do PDF
                        doc['header'] = function() {
                            return {
                                columns: [
                                    { alignment: 'right', image: atacadaonew, width: 135, height: 60 },
                                    { alignment: 'center', margin: [0, 15, 0, 0], italics: true, bold: true, text: 'RELATÓRIO DETALHADO DE QUEBRA DO OPERADOR', fontSize: 20 },
                                    { alignment: 'left', image: atacadaonew, width: 135, height: 60 }
                                ],
                                margin: 20
                            };
                        };

                        // Configuração do rodapé do PDF
                        doc['footer'] = function(page, pages) {
                            return {
                                columns: [
                                    { alignment: 'left', text: ['Gerado em: ', { text: jsDate }] },
                                    { alignment: 'center', text: ['By: Andre Alves '] },
                                    { alignment: 'right', text: ['Página ', { text: page.toString() }, ' de ', { text: pages.toString() }] }
                                ],
                                margin: [20, 10]
                            };
                        };

                        // 📌 **Correção: Garante que a tabela existe antes de definir os `widths`**
                        if (doc.content && doc.content.length > 0 && doc.content[0].table) {
                            doc.content[0].table.widths = [20, 40, '*', '*', '*', '*', 100];
                        }
                    }
                }],
                "data": listquebraope, // Insere os dados na tabela
                "columns": [
                    { title: '#' },
                    { title: 'Operador' },
                    { title: 'Nome' },
                    { title: 'Data Quebra' },
                    { title: 'Valor Quebra' },
                    { title: 'Valor Pago' },
                    { title: 'Valor a Pagar' }
                ],
                createdRow: function(row, data) {
                    $('td', row).eq(4).html(`R$ ${data[4]}`);
                    $('td', row).eq(5).html(`R$ ${data[5]}`);
                    $('td', row).eq(6).html(`R$ ${data[6]}`);
                    // $('td', row).eq(6).html(`R$ ${data[6]}`);
                    // $('td', row).eq(7).html(`R$ ${data[7]}`);
                }
            });

            // Adiciona os botões de exportação ao layout da tabela
            table.buttons().container().appendTo('#relquebradetalhado_wrapper .col-md-6:eq(0)');
        });

    }
    $('#btnsyncdbquebra').on('click', function() {
        StatusDBQuebra();
        RelatorioQuebra();
        StatusDBIndicadores();
    });
    $(document).on('click','.detalheopera',function() {
        var op = $(this).attr("dat");
        relatorioQuebraDetalhado(op);
        $('#modal-relatorio-quebra-detalhe').modal({backdrop: 'static', keyboard: false});
        
    });
}); 