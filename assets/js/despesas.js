// Pasta Raiz do Controller
const controllerdespesa = '../controller/';
function isNumeric(str){
    var er = /^[0-9]+$/;
    return (er.test(str));
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
    function relatoriocnpj(){
        $.post(controllerdespesa+'back_select_relatorios_cnpj.php', function(retorno){
            $('#codtabcnpj').empty();
            let liscnpj = []
            // console.log(retorno);
            $.each(JSON.parse(retorno), function(index,val){
                var ids = val.id;
                var del = "<td ><a href='#' class='text-muted edit-quantdesp' dat="+ids+"><i class='fas fa-edit'></i></a></td >";
                liscnpj.push([val.id,val.cnpj,val.razao,del]);
                $('#relcnpj').DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": false,
                    "pageLength": 15,
                    "destroy": true,
                    //searching: false,
                    "order": [[2, 'asc']],
                    // scrollY: 200,
                    // paging: false,
                    "data": liscnpj,
                    "columns": [
                        { title: 'Cod.' },
                        { title: 'Descrição' },
                        { title: 'Embal.' },
                        { title: '' },          
                    ],
                }).buttons().container().appendTo('#relcnpj_wrapper .col-md-6:eq(0)');
            });
        });
    }
    var relatoriodespesa = function (){
        const d = new Date();
        $('#custom-tabs-two-tab-desp').empty();
        $('#custom-tabs-two-tabContent-desp').empty();
        $('#custom-tabs-two-tab-desp').append("<li class='pt-2 px-3'><h3 class='card-title'>"+d.getFullYear()+"</h3></li>");
        const months = ["JAN","FEV","MAR","ABR","MAI","JUN","JUL","AGO","SET","OUT","NOV","DEZ"];
        const mesesnum = ["01","02","03","04","05","06","07","08","09","10","11","12"];
        const mesesnums = ["02","03","04","05","06","07","08","09","10","11","12","01",];
        for(let i = 0;i < 12; i++){
            var dataini = d.getFullYear()+""+(mesesnum[i])+"01"
            if (i < 10) {
                mesesnu = "0" + (mesesnum[i]+1);
            }else{
                mesesnu = (mesesnum[i]+1);
            }
            var datafim = d.getFullYear()+""+(mesesnum[i])+"31"

            
            $('#custom-tabs-two-tab-desp').append(meses);
            // $('#custom-tabs-two-tabContent-desp').append(tab);
        }
    }
    
    var totalrol = 0.00;
    $('#add-despesa').on('click', function() {
        $("#cnpj-despesa").val('');
        $("#razaosocial-despesa").val('');
        $("#nfe-despesa").val('');
        $("#nfe-serie-despesa").val('');
        $("#valor-total-despesa").val(parseFloat(totalrol));
        $("#descricao-despesa").val('');
        $('#pesqcnpj').val("");
        $('#relatorios-rol-add').empty(); //
        $('#modal-registra-despesa').modal({backdrop: 'static', keyboard: false});
        
    });
    $('#btn-add-despesa').on('click', function() {
        var cnpjdesp = $("#cnpj-despesa").val();
        var razsocial = $("#razaosocial-despesa").val();
        var nfedesp = $("#nfe-despesa").val();
        var nfeserie = $("#nfe-serie-despesa").val();
        var descdesp = $("#descricao-despesa").val();
        if(cnpjdesp == 0){
            toastr.error('Selecione um CNPJ!!!');  
        }else if(razsocial == 0){
            toastr.error('Selecione uma Razão Social!!!');
        }else if(nfedesp == ''){
            toastr.error('Digite o Número da NF-e!!!');
        }else if(nfeserie == ''){
            toastr.error('Digite o Série da NF-e!!!');
        }else if(descdesp == ''){
            toastr.error('Digite a Descrição!!!');
        }else{
            var dados = {
                cnpjdesp: cnpjdesp,
                razsocial: razsocial,
                nfedesp: nfedesp,
                nfeserie: nfeserie,
                descdesp: descdesp
            };
            //    console.log(dados);
           $.post(controller+'back_despesa_create.php',dados, function(retorno){
                console.log(retorno);
                if(retorno == 100){
                    toastr.error("Erro ao altera!!!");
                }else{
                    $('#modal-registra-despesa').modal('hide');
                    toastr.success('Adicionando com sucesso!!!');
                }
           });
           
           
        }
    });
    $('#add-cnpj').on('click', function() {
        relatoriocnpj();
        $('#modal-relatorio-cnpj').modal({backdrop: 'static', keyboard: false});
        
    });
    $('#cadastra-cnpj').on('click', function() {
        $("#cadastra-cnpj-despesa").val('');
        $("#cadastra-razaosocial-despesa").val('');
        $('#modal-cadastra-cnpj').modal({backdrop: 'static', keyboard: false});

    });
    $('#btn-cadastra-despesa').on('click', function() {
        var cnpjcadas = $("#cadastra-cnpj-despesa").val();
        var cnpjrazao = $("#cadastra-razaosocial-despesa").val();
        if(cnpjcadas == 0){
            toastr.error('Adicione o CNPJ');  
        }else if(cnpjrazao == ''){
            toastr.error('Adicione a Razão Social!!!');
        }else{
            var dados = {cnpjcadas: cnpjcadas, cnpjrazao: cnpjrazao};
            $.post(controller+'back_insert_cnpj.php',dados, function(retorno){
                console.log(retorno);
                if(retorno != 100){
                    toastr.success("Quantidade alterada com sucesso!!!");
                    $("#modal-cadastra-cnpj").modal('hide');
                    relatoriocnpj();
                }else{
                    toastr.error("Erro ao altera!!!");
                }
            });
            // var teste =cnpjcadas+"-"+cnpjrazao
            // toastr.success(teste);
        }
    });
    $(document).on('click','.edit-quantdesp',function() {
        var id = $(this).attr("dat");
        // toastr.success(id);
        $.post(controllerdespesa+'back_select_cnpj.php',{id:id}, function(retorno){
            $.each(JSON.parse(retorno), function(index,val){
                $("#alterar-cnpj-despesa").val(val.cnpj);
                $("#alterar-razaosocial-despesa").val(val.razao);
            });
        });
        // $("#cadastra-cnpj-despesa").val('');
        // $("#cadastra-razaosocial-despesa").val('');
        $('#modal-altera-cnpj').modal({backdrop: 'static', keyboard: false});

    });
    var dadoscnpj = "";
    $('#pesqcnpj').autocomplete({
        source: function( request, response ) {
            $.ajax( {
              url: controllerdespesa+"back_pesquisa_cnpj.php",
              type: 'post',
              dataType: "json",
              data: {
                name: request.term
              },
              success: function( datas ) {
                response(datas );
                // console.log(datas);
              }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            $('#pesqcnpj').val("");
            $('#cnpj-despesa').val(ui.item.cnpj);
            $('#razaosocial-despesa').val(ui.item.razao);
            $('#nfe-despesa').focus();
            
            dadoscnpj=ui.item.id;
        }
    });
    $('#pesqcnpj').on('click',function() {
        $('#pesqcnpj').val("");
    });
    $('#add-rol-detalhe').on('click', function() {
        $("#cadastra-rol-add").val("");
        $("#cadastra-valor-add").val("");
        $("#cadastra-descri-add").val("");
        $('#modal-add-rol').modal({backdrop: 'static', keyboard: false});
        $('#cadastra-rol-add').focus();
    });
    //let resul = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.resu);
    $('#btn-add-rol').on('click', function() {
        var rol = $("#cadastra-rol-add").val();
        var rolvalor = $("#cadastra-valor-add").val().replace(",",".");
        var roldesc = $("#cadastra-descri-add").val();
        let rrolvalor = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(rolvalor);
        var linha = "<tr><td>"+rol+"</td><td>"+roldesc+"</td><td>"+rrolvalor+"</td></tr>";
        $('#relatorios-rol-add').append(linha);
        totalrol = totalrol + parseFloat(rolvalor);
        let resul = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(totalrol);
        $("#valor-total-despesa").val(resul);
        $('#modal-add-rol').modal('hide');
    });

}); 