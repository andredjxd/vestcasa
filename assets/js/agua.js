// Pasta Raiz do Controller
const controlleragua = '../controller/';
function isNumeric(str){
    var er = /^[0-9]+$/;
    return (er.test(str));
}

$(document).ready(function(){
    $('#nome-create-agua').inputmask({casing:'upper'});
    $('#leitura').inputmask({
        mask: "999999.999",  
        numericInput: true,  // Digitação da direita para a esquerda
        rightAlign: false,   // Mantém alinhado à esquerda
        placeholder: "0",    // Define "0" como preenchimento padrão
        onBeforeWrite: function(event, buffer, caretPos, opts) {
          // Se o usuário digitar poucos números, preenche automaticamente com zeros à esquerda
          let value = buffer.join("").replace(/_/g, "0"); // Substitui "_" por "0"
          return { value };
        }
    });
    ///000000.000S
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
    const registroAgua = function () {
        const d = new Date();
        const year = d.getFullYear();
        const lastYear = d.getFullYear() - 1;
        const afterYear = d.getFullYear() + 1;
    
        // Exibe o modal de carregamento
        $('#modal-loading-agua').modal('show');
    
        // Limpa os elementos antes de preencher
        $('#custom-tabs-two-tab-agua').empty().append(`<li class='pt-2 px-3'><h3 class='card-title'>${year}</h3></li>`);
        $('#custom-tabs-two-tabContent-agua').empty();
    
        const months = ["JAN", "FEV", "MAR", "ABR", "MAI", "JUN", "JUL", "AGO", "SET", "OUT", "NOV", "DEZ"];
        const mesesnum        = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
        const mesesnumpassado = ["02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "01"];
        
        let totalRequests = months.length * 2; // 2 requisições por mês
        let completedRequests = 0;
    
        months.forEach((month, i) => {
            const dataini = `${year}${mesesnum[i]}01`;
            let datafim; 
            let anoanterior = lastYear;
            let anoatual = year;

            datafim = `${mesesnumpassado[i] === "01" ? afterYear : year}${mesesnumpassado[i]}01`;

            let totalValorMes = 0;
            let totalValorMesAnterior = 0;
            let totalPerdaMes = 0;
            let totalResulMes = 0;
            let totalResulMesAnterior = 0;
            let contador = 0;
            let contadorAnterior = 0;

            let consumoTotalDia = 0;
            let consumoTotalNoi = 0;
            let totalConsumo = 0;
    
            $.post(`${controller}back_agua_registro.php`, { ini: dataini, fim: datafim }, function (dados) {
                //console.log(dados);
                const parsedData = JSON.parse(dados);
                let tableRows = "";

                parsedData.forEach((val) => {

                    const resulDiaAnoPassado = (val.vendadiapassado * val.rentdiapassado) / 100;
                    const resulDiaAnoAtual = (val.vendadiaatual * val.rentdiaatual) / 100;
                    let evolucao = getEvolucao(val.vendadiaatual, val.vendadiapassado);
                    let evolucaoRent = getEvolucao(val.rentdiaatual, val.rentdiapassado);
                    let evolucaoResult = getEvolucao(resulDiaAnoAtual, resulDiaAnoPassado);
                    // <td>${val.dataapassado || ''}</td>
                    tableRows += `
                        <tr>
                           
                            <td>${val.data || ''}</td>
                            <td>${val.dia || ''}</td>
                            <td>${val.leitura06  || ''}</td>
                            <td>${val.nome1 || ''}</td>
                            <td>${val.leitura23 || ''}</td>
                            <td>${val.nome2 || ''}</td>
                            <td>${val.consumodia || ''}</td>
                            <td>${val.consumonoite || ''}</td>
                        </tr>
                    `;
    
                    consumoTotalDia += parseFloat(val.consumodia ? val.consumodia : 0);
                    consumoTotalNoi += parseFloat(val.consumonoite ? val.consumonoite : 0);

                    totalValorMes += parseFloat(val.vendadiaatual ? val.vendadiaatual : 0);
                    totalValorMesAnterior += parseFloat(val.vendadiapassado ? val.vendadiapassado : 0);
                    totalPerdaMes += parseFloat(val.avaria ? val.avaria : 0);
                    totalResulMes += parseFloat(resulDiaAnoAtual);
                    totalResulMesAnterior += parseFloat(resulDiaAnoPassado);
                    contadorAnterior++;
                    contador++;
                });
                var rentabilidade = (totalResulMes / totalValorMes)*100;
                var rentabilidadeAndterior = (totalResulMesAnterior / totalValorMesAnterior)*100;
    
                $('#infos-agua-' + month).html(tableRows);
                $('#mediadiaria-agua-' + month).html(`<tr><td>${contador ? contador : ''}</td><td>${parseFloat(consumoTotalDia).toFixed(3)}</td><td>${parseFloat(consumoTotalNoi).toFixed(3)}</td></tr>
                                                <tr><td colspan='2'>Consumo Total</td><td>${parseFloat(consumoTotalDia+consumoTotalNoi).toFixed(3)}</td></tr`);
    
                completedRequests++;
                if (completedRequests >= totalRequests) {
                    $('#modal-loading-agua').modal('hide');
                }
                
            //});
               

            $.post(`${controller}back_select_cafeteria_meta.php`, { ini: dataini }, function (metaDados) {
                const metas = JSON.parse(metaDados)[0] || { taxa: 0, despessoal: 0, desgeral: 0, metavenda: 0, metarent: 0 };
                const taxa = metas.taxa || 0; // Se estiver vazio, define como 0
                const valorTaxa = (totalValorMes * taxa) / 100;
                const valorTaxaAnterior = (totalValorMesAnterior * taxa) / 100;
                const lucroBruto = totalResulMes + valorTaxa + totalPerdaMes;
                const lucroBrutoAnterior = totalResulMesAnterior + valorTaxa + totalPerdaMes;
                const custosDistrib = parseFloat(metas.despessoal) + parseFloat(metas.desgeral);
                const ebit = lucroBruto - custosDistrib;
                const ebitAnterior = lucroBrutoAnterior - custosDistrib;
                const resultPorc = (totalValorMes / metas.metavenda) * 100;
                const resultPorcRent = (rentabilidade / metas.metarent) * 100;
                const pontoEquilibrio = (ebit / custosDistrib) * 100;
                const pontoEquilibrioAnterior = (ebitAnterior / custosDistrib) * 100;
    
                $('#resul-' + month).html(`
                    <tr><td>Meta Venda</td><td>${valorReal(metas.metavenda)}</td></tr>
                    <tr><td>${anoatual} Venda</td><td>${valorReal(totalValorMes)}</td></tr>
                    <tr><td>${anoanterior} Venda</td><td>${valorReal(totalValorMesAnterior)}</td></tr>
                    <tr><td colspan='2'><div class='progress'>
                    <div class='progress-bar bg-primary' role='progressbar' aria-valuenow='40' aria-valuemin='0' aria-valuemax='100' style='width: ${parseInt(resultPorc)}%'>${parseInt(resultPorc)}%
                    <span class='sr-only'>40% Complete (success)</span></div></div></td></tr>
                    <tr><td>Meta Rent</td><td>${metas.metarent}%</td></tr>
                    <tr><td>${anoatual} Rent</td><td>${valorPorcetagem(rentabilidade)}%</td></tr>
                    <tr><td>${anoanterior} Rent</td><td>${valorPorcetagem(rentabilidadeAndterior)}%</td></tr>
                    <tr><td colspan='2'><div class='progress'>
                    <div class='progress-bar bg-primary' role='progressbar' aria-valuenow='40' aria-valuemin='0' aria-valuemax='100' style='width: ${parseInt(resultPorcRent)}%'>${parseInt(resultPorcRent)}%
                    <span class='sr-only'>40% Complete (success)</span></div></div></td></tr>
                    <tr><td>${anoatual} M. Bruta</td><td>${valorReal(totalResulMes)}</td></tr>
                    <tr><td>${anoanterior} M. Bruta</td><td>${valorReal(totalResulMesAnterior)}</td></tr>
                    <tr><td>Taxa ADM</td><td>${taxa}%</td></tr>
                    <tr><td>${anoatual} Valor Taxa</td><td>${valorReal(valorTaxa)}</td></tr>
                    <tr><td>${anoanterior} Valor Taxa</td><td>${valorReal(valorTaxaAnterior)}</td></tr>
                    <tr><td>${anoatual} L.Bruta</td><td>${valorReal(lucroBruto)}</td></tr>
                    <tr><td>${anoanterior} L.Bruta</td><td>${valorReal(lucroBrutoAnterior)}</td></tr>
                    <tr><td>Desp.Pessoal</td><td>${valorReal(metas.despessoal)}</td></tr>
                    <tr><td>Desp.Geral</td><td>${valorReal(metas.desgeral)}</td></tr>
                    <tr><td>C.Distribuição</td><td>${valorReal(custosDistrib)}</td></tr>
                    <tr><td>${anoatual} EBIT</td><td>${valorReal(ebit)}</td></tr>
                    <tr><td>${anoanterior} EBIT</td><td>${valorReal(ebitAnterior)}</td></tr>
                    <tr><td>${anoatual} P.Equilíbrio</td><td>${valorPorcetagem(pontoEquilibrio)}%</td></tr>
                    <tr><td>${anoanterior} P.Equilíbrio</td><td>${valorPorcetagem(pontoEquilibrioAnterior)}%</td></tr>
                `);
    
                completedRequests++;
                if (completedRequests >= totalRequests) {
                    $('#modal-loading-agua').modal('hide');
                }
            });
            });
            var bottonEdit = `<div class='card-tools'><button type='button' class='btn btn-tool edit-meta-caf-${months[i]}' dat='${dataini}'><i class='fas fa-calendar-alt'></i></button> </div>`;
            const tabcompleta = `
                <div class='row'>
                    <div class='col-md-9'>
                        <div class='card'>
                            <div class='card-header'>
                                <h3 class='card-title'>POR DIA</h3>
                            </div>
                            <div class='card-body p-0'>
                                <table class='table table-bordered table-striped table-sm'>
                                    <thead>
                                        <tr>
                                            <th colspan='2'></th>
                                            <th class='text-center'>${anoanterior}</th>
                                            <th class='text-center'>${anoatual}</th>
                                            <th class='text-center'>${anoanterior}</th>
                                            <th class='text-center'>${anoatual}</th>
                                            <th class='text-center'>${anoanterior}</th>
                                            <th class='text-center'>${anoatual}</th>
                                            
                                        </tr>   
                                        <tr>
                                            <th>Data</th>
                                            <th>Dia</th>
                                            <th>06H</th>
                                            <th>Nome</th>
                                            <th>23H</th>
                                            <th>Nome</th>
                                            <th>Dia</th>
                                            <th>Noite</th>
                                            
                                            
                                        </tr>   
                                    </thead>
                                <tbody id='infos-agua-${month}'></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class='col-md-3'><div class="sticky-top mb-3"><div class='card'>
                    <div class='card-header'><h3 class='card-title'>Diarização</h3></div>
                    <table class='table table-striped table-sm'>
                    <thead><tr><th>Dias</th><th>Dia</th><th>Noite</th></tr></thead>
                    <tbody id='mediadiaria-agua-${month}'></tbody></table></div></div>
                    
                    
                </div>
            `;
            // <div class="sticky-top mb-3"><div class='card'><div class='card-header'><h3 class='card-title'>Resultado do Mês</h3>${bottonEdit}</div>
            //         <table class='table table-striped table-sm'><tbody id='resul-${month}'></tbody></table></div></div></div>
    
            const activeClass = i === d.getMonth() ? "active" : "";
            $('#custom-tabs-two-tab-agua').append(`<li class='nav-item'><a class='nav-link ${activeClass}' data-toggle='pill' href='#custom-tabs-two-home-${month}'>${month}</a></li>`);
            $('#custom-tabs-two-tabContent-agua').append(`<div class='tab-pane fade ${activeClass ? 'show active' : ''}' id='custom-tabs-two-home-${month}'>${tabcompleta}</div>`);

            // $('.edit-meta-caf-'+months[i]).on('click', function() {
            //     var pega = $(this).attr("dat");
            //     $('#titulo-edit-meta-caf').empty();
            //     $('#titulo-edit-meta-caf').append("<h3 class='card-title' dat="+pega+"'>Alterar Meta do Mês de "+months[i]+"</h3>");
            //     $.post(controller+'back_select_cafeteria_meta.php', {ini:pega}, function(retorno){
            //         // console.log(retorno);
            //         $.each(JSON.parse(retorno), function(index,val){
            //             // console.log(val.taxa);
            //             $("#edit-taxa-adm").val(val.taxa);
            //             $("#edit-des-pessoal").val(val.despessoal);
            //             $("#edit-des-geral").val(val.desgeral);
            //             $("#edit-data-meta").val(val.datames);
            //             $("#edit-venda-meta").val(val.metavenda);
            //             $("#edit-rent-meta").val(val.metarent);
            //         });
            //     });
            //     $("#modal-registra-meta-caf").modal({backdrop: 'static', keyboard: false});
                
            //     // var setor = $("#select-setor").val();
            //     // var comprador = $("#selectcomprador").val();
            //     // var nome = $("#nome-create").val();
                
                
            // });
        });
    }  
    registroAgua();

    $('#btnaddagua').on('click', function() { 
        $("#select-setor-agua").val('0');
        $('#dataagua').val('');
        $("#leitura").val('');
        $("#nome-create-agua").val('');
        $('#nome-create-agua').inputmask({casing:'upper'});
    });

    $('#btn-registar-agua').on('click', function() {
        var horario     = $("#select-setor-agua").val();
        var dataagua    = $("#dataagua").val();
        var leitura     = $("#leitura").val();
        var nome        = $("#nome-create-agua").val();
        if(horario == 0){
            toastr.error('Selecione um Horário!!!');  
        }else if(dataagua == ''){
            toastr.error('Selecione a Data!!!');
        }else if(leitura == ''){
            toastr.error('Digite a Leitura!!!');
        }else if(nome == ''){
            toastr.error('Digite o seu Nome!!!');
        }else{
            var dados = {
                horario: horario,
                dataagua: dataagua,
                leitura: leitura,
                nome: nome
            };
            console.log(dados);
            $.post(controller+'back_agua_add.php', dados, function(retorno){
                //console.log(retorno);
                if(retorno == 100){
                    toastr.error('ERRO ao criar o relatório - Verificar Banco de Dados!!');
                }else if(retorno == 101){
                    toastr.error('Dados já inseridos!!');
                }else{
                    toastr.success(retorno, 'Adicionado com Sucesso!');
                    $("#modal-registra-agua").modal('hide');
                    registroAgua();
                    
                }
                
            });
            // $("#select-setor").val('0');
            // $('#selectcomprador').val('0').trigger('change');
            // $("#nome-create").val('');
            
            $('#nome-create-agua').inputmask({casing:'upper'});

        }
        
    });
    $('#reservationdateagua').datetimepicker({
        format: 'L'
    });
}); 