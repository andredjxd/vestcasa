const controllersmg12 = '../controller/';
function isNumeric(str){
    var er = /^[0-9]+$/;
    return (er.test(str));
}
function valorReal(valor){
    let resul = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
    return resul;
}
function valorPorcetagem(valor){
    let resul = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
    return resul;

}
function getEvolucao(valorAtual, valorPassado) {
    if (valorAtual > valorPassado) {
        return "<span class='description-percentage text-success'><i class='fas fa-caret-up'></i></span>";
    } else {
        return "<span class='description-percentage text-danger'><i class='fas fa-caret-down'></i></span>";
    }
}


$(document).ready(function(){
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

    function reltorioSMGOI12() {

        /* 1. Abre o modal --------------------------------------------- */
        $('#modal-loading-smg').modal('show');

        $.post(controllersmg12 + 'back_select_smgoi12.php', function (retorno) {

            /* 2. Limpa instância anterior ------------------------------- */
            if ($.fn.DataTable.isDataTable('#relsmg')) {
                $('#relsmg').DataTable().clear().destroy();
            }
            $('#tabsmg').empty();

            /* 3. Converte JSON em array -------------------------------- */
            const lis = JSON.parse(retorno).map(v => [
                v.codigo,        // 0
                v.sub_codigo,    // 1
                v.descricao,     // 2
                v.emb,           // 3
                v.nome_comprador,// 4
                v.qtd_ped_comp,  // 5
                v.ped_filial,    // 6
                v.ped_nf_at,     // 7
                valorPorcetagem(v.cm),            // 8
                valorPorcetagem(v.cmout),         // 8
                v.cxa,           // 9
                v.outracxa,      // 10
                valorPorcetagem(v.valest),
                valorPorcetagem(v.valestout),
                v.vnd30,         // 11
                v.vnd30out,       // 12
                valorPorcetagem(v.valvnd),         // 11
                valorPorcetagem(v.valvndout),        // 11
                valorPorcetagem(v.aut),        // 11
                valorPorcetagem(v.autout)        // 11
            ]);

            /* 4. Cria o DataTable (sincrônico) -------------------------- */
            const table = $('#relsmg').DataTable({
                destroy      : true,
                order        : [[2, 'asc']],
                lengthChange : false,
                pageLength   : 10,
                autoWidth    : false,
                dom          : 'Bfrtip',  // Botões + filtro + tabela + paginação
                data         : lis,
                columns: [
                    { title: 'Cod.'      }, // 0
                    { title: 'Sub'       }, // 1
                    { title: 'Descrição' }, // 2
                    { title: 'Embal.'    }, // 3
                    { title: 'Comprador' }, // 4
                    { title: 'PCo 702' }, // 5
                    { title: 'PFi 702' }, // 6
                    { title: 'PNF 702' }, // 7
                    { title: 'CM 702'    }, // 8
                    { title: 'CM 671'    }, // 8
                    { title: 'CXA 702'   }, // 9
                    { title: 'CXA 671'   }, // 10
                    { title: 'EST 702'   }, // 9
                    { title: 'EST 671'   }, // 9
                    { title: 'VND30 702' }, // 11
                    { title: 'VND30 671' }, // 12
                    { title: 'VENDA 702' }, // 12
                    { title: 'VENDA 671' },  // 12
                    { title: 'AUT 702' },  // 12
                    { title: 'AUT 671' }  // 12
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text  : 'Excel',
                        title : 'COMPARAR SMGOI12',
                        footer: true
                        // exportOptions: {
                        //     columns: [0, 1, 2, 3, 4, 5, 6,7,8,9,10]  // ajuste se quiser
                        // }
                    }
                ]
            });

            /* 5. Move botões para o local desejado ---------------------- */
            table.buttons().container()
                .appendTo('#relsmg_wrapper .col-md-6:eq(0)');

            /* 6. Fecha o modal (tudo já está renderizado) --------------- */
            $('#modal-loading-smg').modal('hide');

        }).fail(function () {
            alert('Erro ao carregar os dados.');
            $('#modal-loading-smg').modal('hide');
        });
    }
    reltorioSMGOI12();
    $('#fechasmg12').on('click', function() { 
        reltorioSMGOI12();

    });
}); 