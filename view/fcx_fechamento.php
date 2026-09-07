<?php
include_once 'headers.php'; 
?>
<!-- Inicio Modal da Pagina -->

<div class="modal fade" id="modal-create-relatorio">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">ADICIONAR FECHAMENTO</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="card-body p-1">
          <div class="row ">
            <div class="col-sm-3">
              <label>Data Fechamento</label>
              <div class="input-group date" id="reservationdatefechamento" data-target-input="nearest">
                  <input type="text" class="form-control form-control-sm datetimepicker-input" id="dataafechamento" data-target="#reservationdatefechamento"/>
                  <div class="input-group-append" data-target="#reservationdatefechamento" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
            <div class="col-sm-6">
              <label>Selecione o Operador</label>
              <select id="selectoperador" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione....</option>
              </select>
            </div>
            <div class="col-sm-3">
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
          </div>
          <div class="row ">
            <div class="col-sm-3">
              <label>Saldo Inicial</label>
              <input id="saldoinicial" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-3">
              <label>Marq. de Cartão (POS)</label>
              <input id="saldopos" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-3">
              <label>Número POS</label>
              <select id="numpos" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione o POS</option>
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
            <div class="col-sm-3">
              <label>Saldo Final (Dinheiro)</label>
              <input id="saldofinal" type="text" class="form-control form-control-sm" >
            </div>
          </div>
          <div class="row ">
            <div class="col-sm-3">
              <label>Depósito</label>
              <input id="fechadeposito" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-3">
              <label>Sangria</label>
              <input id="fechasangria" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-2">
              <label>Quebra</label>
              <input id="fechaquebra" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-4">
              <label>Troca Fundo(Valor Correto)</label>
              <input id="fechafundo" type="text" class="form-control form-control-sm" >
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label>Observação</label>
              <textarea id="fechaobvserv" class="form-control form-control-sm" rows="3"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button type="button" class="btn btn-primary create-fechamento">Adicionar</button>
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
<div class="modal fade" id="modal-depositos-dia">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header ">
        <h4 class="modal-title">FECHAMENTO DO DIA <b></b><span id="IDRel"></span></h4>
        
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
          <div class="overlay purple p-2" id="overlay-fechamento-dia">
            <div class="overlay-content text-center">
              <i class="fas fa-5x fa-sync-alt fa-spin"></i>
              <div class="text-bold mt-3" style="font-size: 1rem;">
                Carregando...
              </div>
            </div>
          </div>
          <div class="card-body px-2 pt-1 pb-1" style="min-height: 220px;">
            <table id="relFechamentoDia" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.0em; width:100%" >
              <tbody id="codtabFechamentoDia">
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
<div class="modal fade" id="modal-altera-relatorio">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">ALTERAÇÃO DE FECHAMENTO <b>#</b><span id="idfcx"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="card-body p-1">
          <div class="row ">
            <div class="col-sm-3">
              <label>Data Fechamento</label>
              <div class="input-group date" id="reservationdatefechamentodia" data-target-input="nearest">
                  <input type="text" class="form-control form-control-sm datetimepicker-input" id="datafechamentodia" data-target="#reservationdatefechamentodia"/>
                  <div class="input-group-append" data-target="#reservationdatefechamentodia" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
            <div class="col-sm-6">
              <label>Selecione o Operador</label>
              <select id="selectoperadors" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione....</option>
              </select>
            </div>
            <div class="col-sm-3">
              <label>PDV</label>
              <select id="numpdvdia" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione o PDV</option>
                <option value="1">PDV 01</option>
                <option value="2">PDV 02</option>
                <option value="3">PDV 03</option>
                <option value="4">PDV 04</option>
                <option value="5">PDV 05</option>
                <option value="6">PDV 06</option>
                <option value="7">PDV 07</option>
                <option value="8">PDV 08</option>
                <option value="9">PDV 09</option>
                <option value="10">PDV 10</option>

              </select>
            </div>
          </div>
          <div class="row ">
            <div class="col-sm-3">
              <label>Saldo Inicial</label>
              <input id="saldoinicialdia" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-3">
              <label>Marq. de Cartão (POS)</label>
              <input id="saldoposdia" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-3">
              <label>Número POS</label>
              <select id="numposdia" class="form-control form-control-sm" style="width: 100%;">
                <option value="0" selected="selected">Selecione o POS</option>
                <option value="1">POS 01</option>
                <option value="2">POS 02</option>
                <option value="3">POS 03</option>
                <option value="4">POS 04</option>
                <option value="5">POS 05</option>
                <option value="6">POS 06</option>
                <option value="7">POS 07</option>
                <option value="8">POS 08</option>
                <option value="9">POS 09</option>
                <option value="10">POS 10</option>

              </select>
            </div>
            <div class="col-sm-3">
              <label>Saldo Final (Dinheiro)</label>
              <input id="saldofinaldia" type="text" class="form-control form-control-sm" >
            </div>
          </div>
          <div class="row ">
            <div class="col-sm-3">
              <label>Depósito</label>
              <input id="fechadepositodia" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-3">
              <label>Sangria</label>
              <input id="fechasangriadia" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-2">
              <label>Quebra</label>
              <input id="fechaquebradia" type="text" class="form-control form-control-sm" >
            </div>
            <div class="col-sm-4">
              <label>Troca Fundo(Valor Correto)</label>
              <input id="fechafundodia" type="text" class="form-control form-control-sm" >
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label>Observação</label>
              <textarea id="fechaobvservdia" class="form-control form-control-sm" rows="3"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button type="button" class="btn btn-primary update-fechamento">Alterar</button>
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
<div class="modal fade" id="modal-whatsapp">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h4 class="modal-title">Relatório para WhatsApp</h4>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <textarea 
          id="textoCopiar"
          class="form-control"
          rows="14"
          
        ></textarea>

      </div>

      <div class="modal-footer jjustify-content-end">
        <button class="btn btn-success" id="btnCopiarWhatsapp">
          📋 Copiar mensagem
        </button>
      </div>

    </div>
  </div>
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
              <h3 class="card-title">RELATÓRIOS DE FECHAMENTO</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool createClosed" dat='' ><i class="fas fa-plus"></i></button>
                <button type='button' class='btn btn-tool syncRelatFecha' dat='config'><i class='fas fa-sync'></i></button>
              </div>
              <div class="card-tools" id="btn-operador"></div>
              <!-- <div class="card-tools">
                <span id="datagora" class="mr-3"></span>
                </div> -->
            </div>
            <!-- /.card-header -->
            <div class="card-body ">
              <div class="overlay-wrapper" >
                <div class="overlay purple" id="overlay-fechamento">
                  <div class="overlay-content text-center">
                    <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                    <div class="text-bold mt-3" style="font-size: 1rem;">
                      Carregando...
                    </div>
                  </div>
                </div>
                <div class="card-body p-0" style="min-height: 220px;">
                  <table id="relDepositoFechamento" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.1em; width:100%" >
                    <tbody id="codtabFechamento">
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