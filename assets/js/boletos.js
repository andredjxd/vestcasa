// Pasta Raiz do Controller
const controllerBoleto = '../controller/';

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
    if (!dataString || dataString === '0000-00-00') return 'Em Aberto';
    
    // Converte para objeto Date (caso necessário)
    let data = new Date(dataString + 'T12:00:00'); // adicione "T12:00:00" para garantir que a conversão não afete o dia devido ao fuso horário:
    
    // Verifica se a conversão resultou em uma data válida
    if (isNaN(data.getTime())) return '';
    
    // Formata para DD/MM/AAAA considerando o fuso horário brasileiro
    return data.toLocaleDateString('pt-BR', { timeZone: 'America/Araguaina' });
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

    var StatusDBboleto = function (){
        $.post(controllerBoleto+'back_boleto_status_db.php', function(retorno){
            // console.log(retorno);
            $('#statusbaseboleto').empty();    
            $.each(JSON.parse(retorno), function(index,val){
                var linha = "<tr><td>Última Data de Boleto Gerado</td><td><span class='badge badge-primary'>"+val.dtboleto+"</span></td><td><a class='text-muted uploadbase' dat='BOLETOS'><i class='fas fa-upload'></i></a></td></tr>";
                $('#statusbaseboleto').append(linha);
                var linha2 = "<tr><td>Última Atualização</td><td><span class='badge badge-primary'>"+val.upaqruivo+"</span></td><td><a class='text-muted uploadbase' dat='BOLETOS'><i class='fas fa-upload'></i></a></td></tr>";
                $('#statusbaseboleto').append(linha2);
            
                // if(val.dtoperador == getData()){
                //     var linha2 = "<tr><td>SCDOU121</td><td>CPD Gera</td><td><span class='badge badge-success'>"+val.dtoperador+"</span></td><td><a class='text-muted uploadbase' dat='SCDOU121'><i class='fas fa-upload'></i></a></td></tr>";
                // }else{
                //     var linha2 = "<tr><td>SCDOU121</td><td>CPD Gera</td><td><span class='badge badge-danger'>"+val.dtoperador+"</span></td><td><a class='text-muted uploadbase' dat='SCDOU121'><i class='fas fa-upload'></i></a></td></tr>";
                // }
                // $('#statusbaseboleto').append(linha2);
                
                

                // if(val.subsidio == getData()){
                //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-success'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";
                // }else{
                //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-danger'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";;
                // }
                // $('#statusbase').append(linha3);
                
            });
        });
    }
    StatusDBboleto();

    var StatusDBIndicadores = function (){
        $.post(controller+'back_boleto_indicadores.php', function(retorno){
            // console.log(retorno);
            $('#statusindicadorboleto').empty();
            $.each(JSON.parse(retorno), function(index,val){
                var linha = "<tr><td>Titulos Abertos</td><td>"+ real(val.titulopen)+"</td>";//<td><a class='text-muted uploadbase' dat='QUEBRAS'><i class='fas fa-upload'></i></a></td></tr>";
                $('#statusindicadorboleto').append(linha);
                var linha2 = "<tr><td>Titulos Vencidos</td><td>"+ real(val.titulovenci)+"</td>";//<td><a class='text-muted uploadbase' dat='QUEBRAS'><i class='fas fa-upload'></i></a></td></tr>";
                $('#statusindicadorboleto').append(linha2);
            });
        });
    }
    StatusDBIndicadores();

    var StatusDBIndicadoresMes = function (){
        $.post(controller+'back_boleto_indicadores_mes.php', function(retorno){
            // console.log(retorno);
            $('#statusindicadorboletomes').empty();
            $.each(JSON.parse(retorno), function(index,val){
                var linha = "<tr><td>"+val.mesano+"</td><td>"+ real(val.total)+"</td>";//<td><a class='text-muted uploadbase' dat='QUEBRAS'><i class='fas fa-upload'></i></a></td></tr>";
                $('#statusindicadorboletomes').append(linha);
                
            });
        });
        
    }
    StatusDBIndicadoresMes();

    var RelatorioBoletos = function () {
        // Faz uma requisição POST para obter os dados do relatório de boletos
        $.post(controllerdespesa + 'back_boleto_relatorio.php', function(retorno) {
            
            // Limpa o conteúdo da tabela antes de inserir novos dados
            $('#tabboleto').empty();
            
            // Array para armazenar os dados dos boletos
            let liscnpj = [];
    
            // Itera sobre os dados retornados e formata-os para serem usados na tabela
            $.each(JSON.parse(retorno), function(index, val) {

                let dataVencimento = new Date(val.vencimento);
                let dataAtual = new Date();
                let diasAtraso = Math.floor((dataAtual - dataVencimento) / (1000 * 60 * 60 * 24));
                //console.log(diasAtraso);
                liscnpj.push([
                    val.titulo,   
                    val.razao,      // Razão social
                    val.cnpj,       // CNPJ/CPF
                    val.datebase,
                    val.vencimento, // Mantemos o formato original para ordenação correta
                    
                    val.pagamento,
                    diasAtraso,
                    ValorMonet(val.valor) // Formata o valor monetário
                ]);
            });
    
            // Inicializa a DataTable com as configurações desejadas
            let table = $('#relboleto').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 13,
                "columnDefs": [{
                    "targets": [4, 5], // Define o alinhamento central para as colunas de vencimento e pagamento
                    "className": 'dt-body-center'
                }],
                "destroy": true,
                "order": [[4, 'asc']], // Ordena a coluna de vencimento corretamente
                "data": liscnpj, 
                
                "columns": [
                    { title: 'Título' },
                    { title: 'Razão Social' },
                    { title: 'CNPJ/CPF' },
                    {
                        title: 'Data Base',
                        render: function(data, type, row) {
                            if (!data) return '';
                            let date = moment(data, ['YYYY-MM-DD', 'DD/MM/YYYY']); // Suporte a diferentes formatos
                            if (!date.isValid()) return data;
    
                            return type === 'display' || type === 'filter' 
                                ? date.format('DD/MM/YYYY') // Exibe no formato brasileiro
                                : date.format('YYYY-MM-DD'); // Usa formato ISO para ordenação correta
                        }
                    },
                    {
                        title: 'Vencimento',
                        render: function(data, type, row) {
                            if (!data) return '';
                            let date = moment(data, ['YYYY-MM-DD', 'DD/MM/YYYY']); // Suporte a diferentes formatos
                            if (!date.isValid()) return data;
    
                            return type === 'display' || type === 'filter' 
                                ? date.format('DD/MM/YYYY') // Exibe no formato brasileiro
                                : date.format('YYYY-MM-DD'); // Usa formato ISO para ordenação correta
                        }
                    },
                    {
                        title: 'Pagamento',
                        render: function(data, type, row) {
                            // Se a data for vazia ou for "0000-00-00", exibir "Em Aberto"
                            if (!data || data === "0000-00-00") return "Em Aberto";
                    
                            let date = moment(data, ['YYYY-MM-DD', 'DD/MM/YYYY']);
                            if (!date.isValid()) return data;
                    
                            return type === 'display' || type === 'filter' 
                                ? date.format('DD/MM/YYYY')  // Exibe no formato brasileiro
                                : date.format('YYYY-MM-DD'); // Usa formato ISO para ordenação correta
                        }
                    },
                    { title: 'Dias' },               
                    { 
                        title: 'Valor',
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(data);
                            }
                            return data;
                        }
                    }
                ],
                "createdRow": function(row, data, dataIndex) {
                    let vencimento  = data[4]; // Índice 3 é a coluna de Vencimento
                    let pagamento   = data[5]; // Índice 4 é a coluna de Pagamento
                    console.log(vencimento);
                    console.log(pagamento);

                    // Converter vencimento para objeto de data
                    let dataVencimento = new Date(vencimento);
                    let dataAtual = new Date();
                    let diferencaDias = Math.floor((dataAtual - dataVencimento) / (1000 * 60 * 60 * 24));
                    console.log(diferencaDias);

                    // Adiciona a classe 'linha-em-aberto' se o pagamento estiver "Em Aberto"
                    if (pagamento === "0000-00-00") {
                        $(row).addClass('linha-em-aberto');
                    }

                    // Adiciona a classe 'amarelo' se o vencimento for maior que 7 dias e pagamento for "0000-00-00"
                    if (pagamento === "0000-00-00" && diferencaDias > 7) {
                        $(row).addClass('amarelo');
                    }
                }
                
            });
    
            // Adiciona os botões de exportação no layout da tabela
            table.buttons().container().appendTo('#relboleto_wrapper .col-md-6:eq(0)');
        });
    };
    // Chama a função para carregar os boletos
    RelatorioBoletos();
    
    
    $('#btnsyncdbboleto').on('click', function() {
        StatusDBboleto();
        StatusDBIndicadores();
        StatusDBIndicadoresMes();
        RelatorioBoletos();
    });
    
    
}); 