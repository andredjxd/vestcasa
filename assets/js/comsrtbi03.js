// Pasta Raiz do Controller
const controllersrt03 = '../controller/';
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
    var srtbi03 = function (){
        $.post(controllersrt03+'back_com_srtbi03.php', function(retorno){
            //console.log(retorno);
            $('#srtbi03').empty();
            $.each(JSON.parse(retorno), function(i,val){
                //console.log(val.comprador);
                let resul = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.resultado);
                let venda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.valvnd);
                let varia = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.variacao);
                let resll = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.resulll);
                let codig = val.codigo+"-"+val.sub;
                var edit = "<a class='text-muted edit-itens' dat="+val.codigo+" id='edit-itens'><i class='fas fa-edit'></i></a>";
                var linha = "<tr><td>"+codig+"</td><td>"+val.descricao+"</td><td>"+val.emb+"</td><td>"+val.emb1+"</td><td>"+val.emb9+"</td><td>"+venda+"</td><td>"+val.rent+"</td><td>"+val.ajuste+"</td><td>"+val.avaria+"</td><td>"+resul+"</td><td>"+resll+"</td></tr>";
                
                $('#srtbi03').append(linha);
            });
    
    
        });
    }
    srtbi03();
    //fechasrt03
    
    var svdbia2 = function (){
        $.post(controllersrt03+'back_com_svdbia2.php', function(retorno){
            //console.log(retorno);
            $('#svdbia2').empty();
            $.each(JSON.parse(retorno), function(i,val){
                // console.log(getData());
                let vlrverba = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.vlrverba);
                let vlrmovim = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.vlrmovim);
                let vlrund = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val.vlrund);
                let qtdcxa = new Intl.NumberFormat('pt-BR').format(val.qtdcxa);//qtdverba
                let qtdmovim = new Intl.NumberFormat('pt-BR').format(val.qtdmovim)
                let resmovim = new Intl.NumberFormat('pt-BR').format(val.resmovim)
                let qtdverba = new Intl.NumberFormat('pt-BR').format(val.qtdverba)
                let codig = val.codigo+"-"+val.sub;
                let emb = val.mult1+"X"+val.mult2;
                var edit = "<a class='text-muted edit-itens' dat="+val.codigo+" id='edit-itens'><i class='fas fa-edit'></i></a>";
                var linha = "<tr><td>"+codig+"</td><td>"+val.descricao+"</td><td>"+emb+"</td><td>"+val.emb1+"</td><td>"+val.emb2+"</td><td>"+val.dataini+"</td><td>"+val.datafim+"</td><td>"+qtdverba+"</td><td>"+vlrverba+"</td><td>"+vlrund+"</td><td>"+qtdmovim+"</td><td>"+resmovim+"</td><td>"+qtdcxa+"</td><td>"+vlrmovim+"</td></tr>";
                
                $('#svdbia2').append(linha);
            });
    
    
        });
    }
    svdbia2();
    //fechasvda2

    const cafeteria = function () {
        const d = new Date();
        const year = d.getFullYear();
        const lastYear = d.getFullYear() - 1;
        const afterYear = d.getFullYear() + 1;
    
        // Exibe o modal de carregamento
        $('#modal-loading').modal('show');
    
        // Limpa os elementos antes de preencher
        $('#custom-tabs-two-tab').empty().append(`<li class='pt-2 px-3'><h3 class='card-title'>${year}</h3></li>`);
        // $('#custom-tabs-two-tab').empty().append(`<li class='pt-2 px-3'><h3 class='card-title'>
        //     <select>
        //         <option>2024</option>
        //         <option>2025</option>
        //         <option>2025</option>
        //     </select>
        // </h3></li>`);
        $('#custom-tabs-two-tabContent').empty();
    
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
    
            $.post(`${controller}back_select_cafeteria_mes.php`, { ini: dataini, fim: datafim }, function (dados) {
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
                           
                            <td>${val.dataatual || ''}</td>
                            <td>${val.dia  || ''}</td>
                            <td>${val.vendadiapassado ? valorReal(val.vendadiapassado) : ''}</td>
                            <td class='justify-celula'>${val.vendadiaatual ? valorReal(val.vendadiaatual) +' '+ evolucao : ''}</td>
                            <td >${val.rentdiapassado ? valorPorcetagem(val.rentdiapassado) : ''}</td>
                            <td class='justify-celula'>${val.rentdiaatual ? valorPorcetagem(val.rentdiaatual) +' '+ evolucaoRent : ''}</td>
                            <td>${resulDiaAnoPassado ? valorReal(resulDiaAnoPassado) : ''}</td>
                            <td class='justify-celula'>${resulDiaAnoAtual ? valorReal(resulDiaAnoAtual) +' '+ evolucaoResult : ''}</td> 
                        </tr>
                    `;
    
                    totalValorMes += parseFloat(val.vendadiaatual ? val.vendadiaatual : 0);
                    totalValorMesAnterior += parseFloat(val.vendadiapassado ? val.vendadiapassado : 0);
                    totalPerdaMes += parseFloat(val.avaria ? val.avaria : 0);
                    totalResulMes += parseFloat(resulDiaAnoAtual);
                    totalResulMesAnterior += parseFloat(resulDiaAnoPassado);
                    contadorAnterior++;
                    if ((val.vendadiaatual ? val.vendadiaatual : 0) != 0) {
                        contador++;
                    }
                });
                // var rentabilidade = (totalResulMes / totalValorMes)*100;
                var rentabilidade = totalValorMes > 0 ? (totalResulMes / totalValorMes) * 100 : 0;
                var rentabilidadeAndterior = (totalResulMesAnterior / totalValorMesAnterior)*100;
    
                $('#infos-' + month).html(tableRows);
                $('#mediadiaria-' + month).html(`<tr><td>${anoanterior}</td><td>${contadorAnterior}</td><td>${valorReal(totalValorMesAnterior / contadorAnterior)}</td></tr>
                                                <tr><td>${anoatual}</td><td>${contador ? contador : ''}</td><td>${contador ? valorReal(totalValorMes / contador) : ''}</td></tr`);
    
                completedRequests++;
                if (completedRequests >= totalRequests) {
                    $('#modal-loading').modal('hide');
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
                const resultPorc = metas.metavenda > 0 ? (totalValorMes / metas.metavenda) * 100 : 0;

                // const resultPorc = (totalValorMes / metas.metavenda) * 100;
                // const resultPorcRent = (rentabilidade / metas.metarent) * 100;
                const resultPorcRent = metas.metarent > 0 ? (rentabilidade / metas.metarent) * 100 : 0;
                // const pontoEquilibrio = (ebit / custosDistrib) * 100;
                const pontoEquilibrio = custosDistrib > 0 ? (ebit / custosDistrib) * 100 : 0;
                // const pontoEquilibrioAnterior = (ebitAnterior / custosDistrib) * 100;
                const pontoEquilibrioAnterior = custosDistrib > 0 ? (ebitAnterior / custosDistrib) * 100 : 0;

    
                $('#resul-' + month).html(`
                    <tr><td>Meta Venda</td><td>${valorReal(metas.metavenda)}</td></tr>
                    <tr><td>${anoatual} Venda</td><td>${valorReal(totalValorMes)}</td></tr>
                    <tr><td>${anoanterior} Venda</td><td>${valorReal(totalValorMesAnterior)}</td></tr>
                    <tr><td colspan='2'><div class='progress'>
                    <div class='progress-bar bg-orange' role='progressbar' aria-valuenow='40' aria-valuemin='0' aria-valuemax='100' style='width: ${parseInt(resultPorc)}%;'>${parseInt(resultPorc)}%
                    <span class='sr-only'>40% Complete (success)</span></div></div></td></tr>
                    <tr><td>Meta Rent</td><td>${metas.metarent}%</td></tr>
                    <tr><td>${anoatual} Rent</td><td>${valorPorcetagem(rentabilidade)}%</td></tr>
                    <tr><td>${anoanterior} Rent</td><td>${valorPorcetagem(rentabilidadeAndterior)}%</td></tr>
                    <tr><td colspan='2'><div class='progress'>
                    <div class='progress-bar bg-orange' role='progressbar' aria-valuenow='40' aria-valuemin='0' aria-valuemax='100' style='width: ${parseInt(resultPorcRent)}%;'>${parseInt(resultPorcRent)}%
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
                    $('#modal-loading').modal('hide');
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
                                            <th colspan='2' class='text-center'>Venda</th>
                                            <th colspan='2' class='text-center'>Rent%</th>
                                            <th colspan='2' class='text-center'>Resultado</th>
                                            
                                        </tr>   
                                    </thead>
                                <tbody id='infos-${month}'></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class='col-md-3'><div class='card'><div class='card-header'><h3 class='card-title'>Diarização</h3></div>
                    <table class='table table-striped table-sm'><thead><tr><th>Ano</th><th>Dias</th><th>Média</th></tr></thead><tbody id='mediadiaria-${month}'></tbody></table></div>
                    
                    <div class="sticky-top mb-3"><div class='card'><div class='card-header'><h3 class='card-title'>Resultado do Mês</h3>${bottonEdit}</div>
                    <table class='table table-striped table-sm'><tbody id='resul-${month}'></tbody></table></div></div></div>
                </div>
            `;
    
            const activeClass = i === d.getMonth() ? "active" : "";
            $('#custom-tabs-two-tab').append(`<li class='nav-item'><a class='nav-link ${activeClass}' data-toggle='pill' href='#custom-tabs-two-home-${month}'>${month}</a></li>`);
            $('#custom-tabs-two-tabContent').append(`<div class='tab-pane fade ${activeClass ? 'show active' : ''}' id='custom-tabs-two-home-${month}'>${tabcompleta}</div>`);

            $('.edit-meta-caf-'+months[i]).on('click', function() {
                var pega = $(this).attr("dat");
                $('#titulo-edit-meta-caf').empty();
                $('#titulo-edit-meta-caf').append("<h3 class='card-title' dat="+pega+"'>Alterar Meta do Mês de "+months[i]+"</h3>");
                $.post(controller+'back_select_cafeteria_meta.php', {ini:pega}, function(retorno){
                    // console.log(retorno);
                    $.each(JSON.parse(retorno), function(index,val){
                        // console.log(val.taxa);
                        $("#edit-taxa-adm").val(val.taxa);
                        $("#edit-des-pessoal").val(val.despessoal);
                        $("#edit-des-geral").val(val.desgeral);
                        $("#edit-data-meta").val(val.datames);
                        $("#edit-venda-meta").val(val.metavenda);
                        $("#edit-rent-meta").val(val.metarent);
                    });
                });
                $("#modal-registra-meta-caf").modal({backdrop: 'static', keyboard: false});
                
                // var setor = $("#select-setor").val();
                // var comprador = $("#selectcomprador").val();
                // var nome = $("#nome-create").val();
                
                
            });
        });
    }  
    cafeteria();
    //fechasvda2
    $('#fechasrt03').on('click', function() { 
        srtbi03();
        svdbia2();
        $('#resultado-mensal').empty();
        cafeteria();
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
    $('#btn-edit-meta-caf').on('click', function() {
        var datapes = $("#edit-data-meta").val();
        var taxa = $("#edit-taxa-adm").val();
        var dpessoal = $("#edit-des-pessoal").val();
        var dgeral = $("#edit-des-geral").val();
        var metavenda = $("#edit-venda-meta").val();
        var metarent = $("#edit-rent-meta").val();
        if(datapes == 0){
            toastr.error('Digite a Data do Mês!!!');  
        }else if(metavenda == 0){
            toastr.error('Digite a Meta de Venda!!!');
        }else if(metarent == 0){
            toastr.error('Digite a Meta da Rent!!!');
        }else if(taxa == 0){
            toastr.error('Digite a Despesa com Pessoal!!!');
        }else if(dpessoal == ''){
            toastr.error('Digite a Despesa Geral!!!');
        }else if(dgeral == ''){
            toastr.error('Digite a Taxa ADM!!!');
        }else{
            var dados = {
                data: '01/'+datapes,
                taxa: taxa,
                dpessoal: dpessoal,
                dgeral: dgeral,
                metavenda: metavenda,
                metarent: metarent
            };
            console.log(dados);
            $.post(controller+'back_cafeteria_meta_update.php', dados, function(retorno){
                console.log(retorno);
                if(retorno == 100){
                    toastr.error('ERRO em atualizar - Verificar Dados!!');
                }else{
                    toastr.success(retorno, 'Criado com Sucesso!');
                    // $("#modal-create-relatorio").modal('hide');
                    // cafeteria();
                    
                }
                
            });
        //     // $("#select-setor").val('0');
        //     // $('#selectcomprador').val('0').trigger('change');
        //     // $("#nome-create").val('');
        //     // $('#nome-create').inputmask({casing:'upper'});
            

        }
        $("#edit-taxa-adm").val("");
        $("#edit-des-pessoal").val("");
        $("#edit-des-geral").val("");
        $("#edit-data-meta").val("");
        $("#edit-venda-meta").val("");
        $("#edit-rent-meta").val("");
    });
    $('#fechar-alterar-meta').on('click', function() {
        $("#edit-taxa-adm").val("");
        $("#edit-des-pessoal").val("");
        $("#edit-des-geral").val("");
        $("#edit-data-meta").val("");
        $('#resultado-mensal').empty();
        cafeteria();
    });
    
}); 