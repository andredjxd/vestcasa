<?php
include_once 'headers.php'; 
?>
<!-- Inicio Modal da Pagina -->

<div class="modal fade" id="modal-create-relatorio">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Criar Relatório de Depósito</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Loja</label>
          <select id="nomeloja" class="form-control " style="width: 100%;">
            <option value="" selected="selected">Selecione a LOJA</option>
            <option value="MEGA PALMAS">MEGA PALMAS</option>
          </select>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-6">
              <label>Data Inicial</label>
              <div class="input-group date" id="reservationdateinicial" data-target-input="nearest">
                  <input type="text" class="form-control datetimepicker-input datainicial" data-target="#reservationdateinicial"/>
                  <div class="input-group-append" data-target="#reservationdateinicial" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
            <div class="col-6">
              <label>Data Final</label>
              <div class="input-group date" id="reservationdatefinal" data-target-input="nearest">
                  <input type="text"  class="form-control datetimepicker-input datafinal" data-target="#reservationdatefinal"/>
                  <div class="input-group-append" data-target="#reservationdatefinal" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
          </div>
        </div>
        <!-- <div class="form-group">
          <label>Seu Nome</label>
          <input id="nome-create" type="text" class="form-control" placeholder="Digite seu nome!">
        </div> -->
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button type="button" class="btn btn-primary create-relat-deposito">Criar</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-edit-relatorio-deposito">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">ALTERAR RELATÓRIOS <b>#</b><span id="IDReleditDep"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Loja</label>
          <select id="nomelojaedit" class="form-control " style="width: 100%;">
            <option value="0" selected="selected">Selecione a LOJA</option>
            <option value="1">MEGA PALMAS</option>
          </select>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-6">
              <label>Data Inicial</label>
              <div class="input-group date" id="reservationdateinicialedit" data-target-input="nearest">
                  <input type="text" class="form-control datetimepicker-input datainicialedit" data-target="#reservationdateinicialedit"/>
                  <div class="input-group-append" data-target="#reservationdateinicialedit" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
            <div class="col-6">
              <label>Data Final</label>
              <div class="input-group date" id="reservationdatefinaledit" data-target-input="nearest">
                  <input type="text"  class="form-control datetimepicker-input datafinaledit" data-target="#reservationdatefinaledit"/>
                  <div class="input-group-append" data-target="#reservationdatefinaledit" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-4">
              <label>Valor</label>
              <input id="valoredit" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>Status</label>
              <input id="statusedit" type="text" class="form-control" disabled>
            </div>
            <div class="col-5">
              <label>Crição</label>
              <input id="criacaoedit" type="text" class="form-control" disabled>
            </div>
          </div>
        </div>
        <!-- <div class="form-group">
          <label>Seu Nome</label>
          <input id="nome-create" type="text" class="form-control" placeholder="Digite seu nome!">
        </div> -->
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button type="button" class="btn btn-primary alterar-relat-deposito">Alterar</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-edit-retirada">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">FINALIZAR RETIRADA <b>#</b><span id="IDRelRetFinal"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div class="row">
            <div class="col-6">
              <label>Valor</label>
              <input id="valorretirada" type="text" class="form-control form-control-sm" disabled>
            </div>
            <div class="col-6">
              <label>QTD Bananinha</label>
              <input id="qtdetirada" type="text" class="form-control form-control-sm" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label>Empresa</label>
              <input id="empresaretirada" type="text" class="form-control form-control-sm">
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <label>Data da Retirada</label>
              <div class="input-group date" id="reservationretirada" data-target-input="nearest">
                  <input type="text" class="form-control form-control-sm datetimepicker-input" id="dataretirada" data-target="#reservationretirada"/>
                  <div class="input-group-append" data-target="#reservationretirada" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
            <div class="col-6">
              <label>GVT</label>
              <input id="gvtretirada" type="text" class="form-control form-control-sm">
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label>Observação</label>
              <textarea id="observretirada" class="form-control form-control-sm" rows="3"></textarea>
            </div>
            
          </div>
        </div>
        <!-- <div class="form-group">
          <label>Seu Nome</label>
          <input id="nome-create" type="text" class="form-control" placeholder="Digite seu nome!">
        </div> -->
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button type="button" class="btn btn-primary finalizar-relat-deposito">Finalizar</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-view-retirada">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">VISUALIZAÇÃO DE RETIRADA REALIZADA</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div class="row">
            <div class="col-6">
              <label>Valor</label>
              <input id="valorretiradaview" type="text" class="form-control form-control-sm" disabled>
            </div>
            <div class="col-6">
              <label>QTD Bananinha</label>
              <input id="qtdetiradaview" type="text" class="form-control form-control-sm" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label>Empresa</label>
              <input id="empresaretiradaview" type="text" class="form-control form-control-sm" disabled> 
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <label>Data da Retirada</label>
              <div class="input-group date" id="reservationretirada" data-target-input="nearest">
                  <input type="text" class="form-control form-control-sm datetimepicker-input" id="dataretiradaview" data-target="#reservationretirada" disabled/>
                  <div class="input-group-append" data-target="#reservationretirada" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
            <div class="col-6">
              <label>GVT</label>
              <input id="gvtretiradaview" type="text" class="form-control form-control-sm" disabled>
            </div>
          </div>
           <div class="row">
            <div class="col-6">
              <label>Status</label>
              <input id="statusdoretiradaview" type="text" class="form-control form-control-sm" disabled>
            </div>
            <div class="col-6">
              <label>Finalizado</label>
              <input id="finalizadoretiradaview" type="text" class="form-control form-control-sm" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label>Observação</label>
              <textarea id="observretiradaview" class="form-control form-control-sm" rows="3" disabled></textarea>
            </div>
            
          </div>
        </div>
        <!-- <div class="form-group">
          <label>Seu Nome</label>
          <input id="nome-create" type="text" class="form-control" placeholder="Digite seu nome!">
        </div> -->
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-relatorio-itens">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >Edição de Controlde de Validade do Número <b>#</b><span id="numero"></span> Comprador: <span id="comprador"></span></h5>
        <div class="card-tools ml-auto">
          <button type="button" class="btn btn-tool " id="updaterelatorio">
            <i class='fas fa-sync-alt'><span class="brand-text font-weight"> Atualiza</span></i>
          </button>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-7 form-group">
            <label>Codigo e Descrição</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
              </div>
              <input id="codigopesq" type="text" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label>Data de Vencimento</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
              </div>
              <input id="datapesq" type="text" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
            </div>
            <!-- /.input group -->
          </div>
          <div class="col-1">
              <label>CXA:</label>
              <input id="cxapesq" type="text" class="form-control">
          </div>
          <div class="col-1">
              <label>UND:</label>
              <input id="undpesq" type="text" class="form-control">
          </div>
        </div>
        <div class="row" id="infoadd" style="text-align: center;">
        </div>
        <div class="row row-centered">
          <div class="card-body p-1">
            <table id="relvenci" class="table table-sm table-bordered table-striped table-hover " style="font-size: 0.85em;" >
              <tbody id="codtabvenci">
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-create-relat" type="button" class="btn btn-primary">Criar</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-updatedb-relatorio">
  <div class="modal-dialog modal-lg mt-5 pt-5">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Processo de atualização do Relatório <b>#</b><span id="numeroup"></span></h5>
        <button type="button " class="close closeup" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- <span id="updateprocess"></span> -->
        <div class="d-flex justify-content-center m-1" id="updateprocess" >
        </div>

      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button id="btn-update-relat" type="button" class="btn btn-primary">Iniciar</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-edit-deposito">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">ADICIONAR SANGRIAS <b>#</b><span id="IDRel"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-1 pb-1">
        <div class="form-group">
          <div class="row">
            <div class="col-3">
              <label>Data Inicial</label>
              <div class="input-group date" id="reservationdatesangria" data-target-input="nearest">
                  <input type="text" class="form-control form-control-sm datetimepicker-input datasangria" data-target="#reservationdatesangria"/>
                  <div class="input-group-append" data-target="#reservationdatesangria" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
            <div class="col-3">
              <label>PDV</label>
              <select id="numpdv" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione o PDV</option>
                <option value="1">01</option>
                <option value="2">02</option>
                <option value="3">03</option>
                <option value="4">04</option>
                <option value="5">05</option>
                <option value="6">06</option>
                <option value="7">07</option>
                <option value="8">08</option>
                <option value="9">09</option>
                <option value="10">10</option>

              </select>
            </div>
            <div class="col-6">
              <!-- <label>Operador</label>
              <input id="nomeoperador" type="text" class="form-control"> -->
              <label>Selecione o Operador</label>
              <select id="selectoperador" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione....</option>
              </select>
            </div>
            
          </div>
          <div class="row">
            
          </div>
          <div class="row">
            <div class="col-6">
              <label>Liderança</label>
              <input id="nomelideraca" type="text" class="form-control form-control-sm">
            </div>
            <div class="col-3">
              <label>Valor</label>
              <input id="valordeposito" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-3">
              <label>Número Bananinha</label>
              <input id="numerobananinha" type="text" class="form-control form-control-sm" >
            </div>
          </div>
        </div>
      </div>
      <div class="card-body pt-1 pb-1">
        <table id="relDepositoSangria" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.0em; width:100%" >
          <tbody id="codtabSangria">
          </tbody>
        </table>
      </div>
      <div class="modal-footer right-content-between" id="addbtnsalva">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-salva-detalhe" type="button" class="btn btn-primary">Salvar</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-view-deposito">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">CONTROLE DE SANGRIA PARA BOCA DE LOBO <b>#</b><span id="IDRel"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="card-body pt-1 pb-1">
        <table id="relDepositoSangriaFinalizado" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.0em; width:100%" >
          <tbody id="codtabSangriaFinalizado">
          </tbody>
        </table>
      </div>
      <div class="modal-footer right-content-between" id="addbtnsalva">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-salva-detalhe" type="button" class="btn btn-primary">Salvar</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-operadores">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header ">
        <h4 class="modal-title">CADASTRO DE COLABORADORES <b></b><span id="IDRel"></span></h4>
        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        
      </div>
      <div class="card-body p-1 align-items-center">
        <div class="card-tools ml-auto" id="btn-operador-cad"></div>
        <div class="card-tools ml-auto" id="btn-funcao-cad"></div>
        <div class="card-tools ml-auto" id="btn-horario-cad"></div>
      </div>
      <div class="card-body p-1">
        <div class="overlay-wrapper" >
          <div class="overlay purple p-2" id="overlay-colaboradores">
            <div class="overlay-content text-center">
              <i class="fas fa-5x fa-sync-alt fa-spin"></i>
              <div class="text-bold mt-3" style="font-size: 1rem;">
                Carregando...
              </div>
            </div>
          </div>
          <div class="card-body px-2 pt-1 pb-1" style="min-height: 220px;">
            <table id="relOperadores" class="table table-smm table-bordered table-striped table-hover" style="font-size: 0.9em; width:100%" >
              <tbody id="codtabOperadores">
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer right-content-between" id="addbtnsalva">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-salva-detalhe" type="button" class="btn btn-primary">Salvar</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-funcao">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">FUNÇÕES</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-1">
        <div class="card-body p-1">
          <div class="input-group">
            <input type="text" name="message" id="nomefuncao" placeholder="NOME DA FUNÇÃO" class="form-control">
            <span class="input-group-append">
              <button type="submit" id="btnfuncao" class="btn btn-primary">ADICIONAR</button>
            </span>
          </div>
        </div>
        <div class="card-body p-1">
          <div class="overlay-wrapper" >
            <div class="overlay purple p-2" id="overlay-funcao">
              <div class="overlay-content text-center">
                <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                <div class="text-bold mt-3" style="font-size: 1rem;">
                  Carregando...
                </div>
              </div>
            </div>
            <div class="card-body p-0" style="min-height: 220px;">
              <table id="relFuncao" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.1em; width:100%" >
                <tbody id="codtabFuncao">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-horario">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">LISTA DE HORÁRIO</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-1">
        <div class="card-body p-1">
          <!-- <div class="input-group">
            <input type="text" name="message" id="nomehorario" placeholder="HORÁRIO" class="form-control">
            <span class="input-group-append">
              <button type="submit" id="btnhorario" class="btn btn-primary">ADICIONAR</button>
            </span>
          </div> -->
          <div class="row ">
            <div class="col-sm-3">
              <label>Hora Inicial</label>
              <div class="input-group date" id="timepickerInicial" data-target-input="nearest">
                <input type="text" class="form-control datetimepicker-input" id="horainicial" data-target="#timepickerInicial">
                <div class="input-group-append" data-target="#timepickerInicial" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                </div>
              </div>
            </div>
            <div class="col-sm-3">
              <label>Hora Final</label>
              <div class="input-group date" id="timepickerFinal" data-target-input="nearest">
                <input type="text" class="form-control datetimepicker-input" id="horafinal" data-target="#timepickerFinal">
                <div class="input-group-append" data-target="#timepickerFinal" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                </div>
              </div>
            </div>
            <div class="col-sm-3">
              <label>Loja</label>
              <select id="selectintervalo" class="form-control " style="width: 100%;">
                <option value="0" selected="selected">Intervalo</option>
                <option value="1">01H</option>
                <option value="2">02H</option>
              </select>
            </div>
            <div class="col-sm-3">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-primary" id="btnhorario">ADICIONAR</button>
            </div>
          </div>
        </div>
        <div class="card-body p-1">
          <div class="overlay-wrapper" >
            <div class="overlay purple p-2" id="overlay-horario">
              <div class="overlay-content text-center">
                <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                <div class="text-bold mt-3" style="font-size: 1rem;">
                  Carregando...
                </div>
              </div>
            </div>
            <div class="card-body px-0 pt-1 pb-1" style="min-height: 220px;">
              <table id="relHorario" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.1em; width:100%" >
                <tbody id="codtabHorario">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-cad-colaborador">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">CADASTRAR COLABORADOR</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-1">
        <div class="card-body p-1">
          <div class="row ">
            <div class="col-sm-2">
              <label>Código</label>
              <input id="codigocolaborador" type="text" class="form-control form-control-sm" placeholder="CODIGO">
            </div>
            <div class="col-sm-7">
              <label>Nome</label>
              <input id="nomecolaborador" type="text" class="form-control form-control-sm"  placeholder="NOME COMPLETO">
            </div>
            <div class="col-3">
              <label>Data Admissão</label>
              <div class="input-group date" id="reservationdateadmissao" data-target-input="nearest">
                  <input type="text" class="form-control form-control-sm datetimepicker-input" id="dataadmissao" data-target="#reservationdateadmissao"/>
                  <div class="input-group-append" data-target="#reservationdateadmissao" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
          </div>
          <div class="row ">
            <div class="col-sm-2">
              <label>CPF</label>
              <input id="cpfcolaborador" type="text" class="form-control form-control-sm" placeholder="CPF">
            </div>
            <div class="col-sm-4">
              <label>Selecione a função</label>
              <select id="selectfuncao" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione....</option>
              </select>
            </div>
            <div class="col-sm-4">
              <label>Selecione o horário</label>
              <select id="selecthorario" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione....</option>
              </select>
            </div>
            <div class="col-sm-2 d-flex align-items-end">
              <button type="button" class="btn btn-primary w-100" id="addcolaborador">
                  ADICIONAR
              </button>
            </div>
          </div>
        </div>
        <!-- <div class="card-body p-1">
          <div class="overlay-wrapper" >
            <div class="overlay purple p-2" id="overlay-horario">
              <div class="overlay-content text-center">
                <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                <div class="text-bold mt-3" style="font-size: 1rem;">
                  Carregando...
                </div>
              </div>
            </div>
            <div class="card-body px-0 pt-1 pb-1" style="min-height: 220px;">
              <table id="relHorario" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.1em; width:100%" >
                <tbody id="codtabHorario">
                </tbody>
              </table>
            </div>
          </div>
        </div> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-desativar-colab">
  <div class="modal-dialog modal-sm">
    <div class="modal-content bg-danger" >
      <div class="modal-header">
        <h4 class="modal-title">ATENÇÃO!!!</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="fechaISV">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="card-body p-3">
          <h4>Deseja realmente desativar esse colaborador # <span id="iddesativar">re</span>?</h4>
        </div>
      </div>
      <div class="modal-footer right-content-between"   id="addbtnDesativar">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-salva-detalhe" type="button" class="btn btn-primary">Excluir</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- Fim Modal da Pagina -->
      <!-- /.modal -->
<div class="content-wrapper">
  <div class="content-header">
  </div>
  <div class="container">
    <div class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title">RELATÓRIOS DE DEPOSITOS (SANGRIAS)</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool createRel" dat='' ><i class="fas fa-plus"></i></button>
                <button type='button' class='btn btn-tool syncRelat' dat='config'><i class='fas fa-sync'></i></button>
              </div>
              <div class="card-tools" id="btn-operador"></div>
              <!-- <div class="card-tools">
                <span id="datagora" class="mr-3"></span>
                </div> -->
            </div>
            <!-- /.card-header -->
            <div class="card-body ">
              <div class="overlay-wrapper" >
                <div class="overlay purple" id="overlay-isv">
                  <div class="overlay-content text-center">
                    <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                    <div class="text-bold mt-3" style="font-size: 1rem;">
                      Carregando...
                    </div>
                  </div>
                </div>
                <div class="card-body p-0" style="min-height: 220px;">
                  <table id="relDepositoDetalhado" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.1em; width:100%" >
                    <tbody id="codtabDeposito">
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!-- /.card-body -->
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
include_once 'footer.php'; 
?>