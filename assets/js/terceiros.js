// Pasta Raiz do Controller
const controllerter = '../controller/';
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

    var dashboardter = function (){
        $.post(controller+'back_dash_terceiros.php', function(retorno){
            // console.log(retorno);
            for (var i = 1; i <= 20; i++) {
                // console.log(i);
                $('#infobox'+i).empty();
            }
            var pos = 0;
            $.each(JSON.parse(retorno), function(i,val){
                pos++;
                // console.log(retorno);
                var infobox1 ="<div class='info-box'><span class='info-box-icon bg-info'>"
                +"<i class='fas fa-user'></i></span><div class='info-box-content'>"
                +"<span class='info-box-text'>"+val.dataterhj+" - "+val.dataterhjsem+"</span>"
                +"<span class='info-box-number'>"+val.qtdterhj+"</span></div></div>";
                var infobox2 ="<div class='info-box'><span class='info-box-icon bg-primary'>"
                +"<i class='fas fa-user-alt'></i></span><div class='info-box-content'>"
                +"<span class='info-box-text'>"+val.dataontem+" - "+val.dataontemsem+"</span>"
                +"<span class='info-box-number'>"+val.qtdterontem+"</span></div></div>";
                var resultporc = ((parseFloat(val.qtdterhj)/parseFloat(val.qtdterontem))*100)-100;
                var quant = val.qtdterhj - val.qtdterontem;
                var infobox3 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(102, 51, 255, 0.8)'>"
                +"<i class='fas fa-sort-numeric-up' style='color: #ffffff;'></i></span><div class='info-box-content'>"
                +"<span class='info-box-text'>Quantidade</span>"
                +"<span class='info-box-number'>"+quant+"</span></div></div>";
                if(val.qtdterhj > val.qtdterontem){
                    var infobox4 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(255, 153, 51)'>"
                    +"<i class='far fa-smile' style='color: #ffffff;'></i></span><div class='info-box-content'>"
                    +"<span class='info-box-text'>Evolução<b style='font-size: 10px;'></b></span>"
                    +"<span class='info-box-number'>"+valorPorcetagem(resultporc)+"%</span></div></div>";
                }else{
                    var infobox4 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(255, 153, 51)'>"
                    +"<i class='far fa-flushed' style='color: #ffffff;'></i></span><div class='info-box-content'>"
                    +"<span class='info-box-text'>Evolução<b style='font-size: 10px;'></b></span>"
                    +"<span class='info-box-number'>"+valorPorcetagem(resultporc)+"%</span></div></div>";
                }
                
                
                // var infobox5 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(210, 45, 45, 0.8)'>"
                // +"<img src='_img/entra_mercad_w.png' style='width: 90%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 7 <b>Entrada </b><b style='font-size: 10px;'>"+val.datasrm+"</b></span>"
                // +"<span class='info-box-number'>"+real(val.isv7diasentra)+"</span></div></div>";
                // var infobox6 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(102, 51, 255, 0.8)'>"
                // +"<img src='_img/vend_entra.png' style='width: 90%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 7 Entrada Vendido</span>"
                // +"<span class='info-box-number'>"+real(val.isv7diasentravnd)+"</span></div></div>";
                // var infobox7 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(116, 88, 75)'>"
                // +"<img src='_img/bora_vender_entra.png' style='width: 85%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 7 dias Entrada<b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isvtotalvendEntra)+"</span></div></div>";

                // var infobox9 ="<div class='info-box'><span class='info-box-icon bg-info'>"
                // +"<img src='_img/track_white.png' style='width: 70%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 6 dias </span>"
                // +"<span class='info-box-number'>"+real(val.isv6dias)+"</span></div></div>";
                // var infobox10 ="<div class='info-box'><span class='info-box-icon bg-primary'>"
                // +"<img src='_img/carinho_venda_fundo_w.png' style='width: 70%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 6 dias Vendido<b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isv6diasvend)+"</span></div></div>";
                // var infobox11 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(255, 153, 51)'>"
                // +"<img src='_img/bora_vender.png' style='width: 85%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 6 dias <b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isv6diasrest)+"</span></div></div>";
                // var infobox12 ="<div class='info-box'><span class='info-box-icon bg-success'>"
                // +"<img src='_img/grafico_disco.svg' style='width: 75%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 7 Amanhã <b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isvamanha)+"</span></div></div>";
                
                // var infobox13 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(210, 45, 45, 0.8)'>"
                // +"<img src='_img/entra_mercad_w.png' style='width: 90%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 6 <b>Entrada </b><b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isv6diasentra)+"</span></div></div>";
                // var infobox14 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(102, 51, 255, 0.8)'>"
                // +"<img src='_img/vend_entra.png' style='width: 90%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 6 Entrada Vendido</span>"
                // +"<span class='info-box-number'>"+real(val.isv6diasentravnd)+"</span></div></div>";
                // var infobox15 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(116, 88, 75)'>"
                // +"<img src='_img/bora_vender_entra.png' style='width: 85%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 6 dias Entrada<b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isvtotalvendEntra6)+"</span></div></div>";

                // var infobox17 ="<div class='info-box'><span class='info-box-icon bg-info'>"
                // +"<img src='_img/track_white.png' style='width: 70%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 5 dias <b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isv5dias)+"</span></div></div>";
                // var infobox18 ="<div class='info-box'><span class='info-box-icon bg-primary'>"
                // +"<img src='_img/carinho_venda_fundo_w.png' style='width: 70%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 5 dias Vendido<b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isv5diasvend)+"</span></div></div>";
                // var infobox19 ="<div class='info-box'><span class='info-box-icon' style='background: rgba(255, 153, 51)'>"
                // +"<img src='_img/bora_vender.png' style='width: 85%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>ISV 5 dias <b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isv5diasrest)+"</span></div></div>";
                // var infobox20 ="<div class='info-box'><span class='info-box-icon bg-success'>"
                // +"<img src='_img/conquista.png' style='width: 75%;'></i></span><div class='info-box-content'>"
                // +"<span class='info-box-text'>Total ISV Vendido <b style='font-size: 10px;'></b></span>"
                // +"<span class='info-box-number'>"+real(val.isvtotalvend)+"</span></div></div>";

                $('#infobox1').append(infobox1);
                $('#infobox2').append(infobox2);
                $('#infobox3').append(infobox3);
                $('#infobox4').append(infobox4);

                
                // if (val.isv7diasentra != 0){
                //     $('#infobox5').append(infobox5);
                //     $('#infobox6').append(infobox6);
                //     $('#infobox7').append(infobox7);
                // }
                
            

                // $('#infobox9').append(infobox9);
                // $('#infobox10').append(infobox10);
                // $('#infobox11').append(infobox11);
                // $('#infobox12').append(infobox12);

                // if (val.isv6diasentra != 0){
                //     $('#infobox13').append(infobox13);
                //     $('#infobox14').append(infobox14);
                //     $('#infobox15').append(infobox15);
                // }

                // $('#infobox17').append(infobox17);
                // $('#infobox18').append(infobox18);
                // $('#infobox19').append(infobox19);
                // $('#infobox20').append(infobox20);
            });
        });
        
    }
    dashboardter();
    var statusdbter = function (){
        $.post(controllerter+'back_dash_terceiros_relatorio.php', function(retorno){
            // console.log(retorno);
            $('#statusbaseter').empty();           
            $.each(JSON.parse(retorno), function(index,val){
                var linha = "<tr><td>"+val.datater+"</td>"
                +"<td style='text-align: center;'>"+val.dia+"</td>"
                +"<td style='text-align: center;'>"+val.zeladoria+"</td>"
                +"<td style='text-align: center;'>"+val.visitante+"</td>"
                +"<td style='text-align: center;'>"+val.vigilante+"</td>"
                +"<td style='text-align: center;'>"+val.reciclagem+"</td>"
                +"<td style='text-align: center;'>"+val.promotor+"</td>"
                +"<td style='text-align: center;'>"+val.porteiro+"</td>"
                +"<td style='text-align: center;'>"+val.manutencao+"</td>"
                +"<td style='text-align: center;'>"+val.inventariante+"</td>"
                // +"<td><a class='text-muted uploadbase' dat='SMGOI13' ><i class='fas fa-upload'></i></a></td>"
                +"</tr>";
            //     if(val.smg13 == getData()){
            //         var linha = "<tr><td>SMGOI13</td><td><span class='badge badge-success'>"+val.smg13+"</span></td><td><a class='text-muted uploadbase' dat='SMGOI13' ><i class='fas fa-upload'></i></a></td></tr>";
            //     }else{
            //         var linha = "<tr><td>SMGOI13</td><td><span class='badge badge-danger'>"+val.smg13+"</span></td><td><a class='text-muted uploadbase' dat='SMGOI13'><i class='fas fa-upload'></i></a></td></tr>";
            //     }
                $('#statusbaseter').append(linha);
            
            //     if(val.srt03 == getData()){
            //         var linha2 = "<tr><td>SRTBI03</td><td><span class='badge badge-success'>"+val.srt03+"</span></td><td><a class='text-muted uploadbase' dat='SRTBI03'><i class='fas fa-upload'></i></a></td></tr>";
            //         var srt03status = "<button type='button' class='btn btn-tool uploadbase' dat='SRTBI03'><span class='badge badge-success'>"+val.srt03+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }else{
            //         var linha2 = "<tr><td>SRTBI03</td><td><span class='badge badge-danger'>"+val.srt03+"</span></td><td><a class='text-muted uploadbase' dat='SRTBI03'><i class='fas fa-upload'></i></a></td></tr>";
            //         var srt03status = "<button type='button' class='btn btn-tool uploadbase' dat='SRTBI03'><span class='badge badge-danger'>"+val.srt03+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }
            //     $('#statusbaseter').append(linha2);
            //     $('#statussrt03').empty();
            //     $('#statussrt03').append(srt03status);
            //     // console.log(getDataMenosUm());
                
            //     if(val.svdbi62 == getData()){
            //         var linha3 = "<tr><td>SVDBI62</td><td><span class='badge badge-success'>"+val.svdbi62+"</span></td><td><a class='text-muted uploadbase' dat='SVDBI62'><i class='fas fa-upload'></i></a></td></tr>";
            //         var statussvd62 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBI62'><span class='badge badge-success'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }else{
            //         var linha3 = "<tr><td>SVDBI62</td><td><span class='badge badge-danger'>"+val.svdbi62+"</span></td><td><a class='text-muted uploadbase' dat='SVDBI62'><i class='fas fa-upload'></i></a></td></tr>";
            //         var statussvd62 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBI62'><span class='badge badge-danger'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }
            //     $('#statusbaseter').append(linha3);
            //     $('#statussvd62').empty();
            //     $('#statussvd62').append(statussvd62);

            //     if(val.svdbia2 == getData()){
            //         var linha4 = "<tr><td>SVDBIA2</td><td><span class='badge badge-success'>"+val.svdbia2+"</span></td><td><a class='text-muted uploadbase' dat='SVDBIA2'><i class='fas fa-upload'></i></a></td></tr>";
            //         var statussvda2 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBIA2'><span class='badge badge-success'>"+val.svdbia2+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }else{
            //         var linha4 = "<tr><td>SVDBIA2</td><td><span class='badge badge-danger'>"+val.svdbia2+"</span></td><td><a class='text-muted uploadbase' dat='SVDBIA2'><i class='fas fa-upload'></i></a></td></tr>";
            //         var statussvda2 = "<button type='button' class='btn btn-tool uploadbase' dat='SVDBIA2'><span class='badge badge-danger'>"+val.svdbia2+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }
            //     $('#statusbaseter').append(linha4);
            //     $('#statussvda2').empty();
            //     $('#statussvda2').append(statussvda2);

            //     if(val.svdbi62 == getDataMenosUm()){
            //         var linha5 = "<tr><td>SAEBI51 DIARIO</td><td><span class='badge badge-success'>"+val.svdbi62+"</span></td><td><a class='text-muted uploadbase' dat='SAEBI51DIARIO'><i class='fas fa-upload'></i></a></td></tr>";
            //         var statussvd62 = "<button type='button' class='btn btn-tool uploadbase' dat='SAEBI51-DIARIO'><span class='badge badge-success'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }else{
            //         var linha5 = "<tr><td>SAEBI51 DIARIO</td><td><span class='badge badge-danger'>"+val.svdbi62+"</span></td><td><a class='text-muted uploadbase' dat='SAEBI51DIARIO'><i class='fas fa-upload'></i></a></td></tr>";
            //         var statussvd62 = "<button type='button' class='btn btn-tool uploadbase' dat='SAEBI51-DIARIO'><span class='badge badge-danger'>"+val.svdbi62+"</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i></button>";
            //     }
            //     $('#statusbaseter').append(linha5);
            //     $('#statussaebi51diario').empty();
            //     $('#statussaebi51diario').append(statussvd62);
                
                
            //     // if(val.subsidio == getData()){
            //     //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-success'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";
            //     // }else{
            //     //     var linha3 = "<tr><td>SUBSIDIO</td><td><span class='badge badge-danger'>"+val.subsidio+"</span></td><td><a class='text-muted uploadbase' dat='SUBSIDIO'><i class='fas fa-upload'></i></a></td></tr>";;
            //     // }
            //     // $('#statusbase').append(linha3);
                
            });
        });
    }
    statusdbter();

    $('#btnsyncter').on('click', function() {
        dashboardter();
        statusdbter();
    });   

}); 