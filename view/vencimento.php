<?php
include_once 'headers.php'; 
?>
<!-- Inicio Modal da Pagina -->

<div class="modal fade" id="modal-create-relatorio">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Criar Relatório</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Setor</label>
          <select id="select-setor" class="form-control " style="width: 100%;">
            <option value="0" selected="selected">Selecione um Setor</option>
            <option>LOJA</option>
            <option>FRIOS</option>
            <option>HORTI-FRUTI</option>
            <option>CAFETERIA</option>
          </select>
        </div>
        <div class="form-group">
          <label>Comprador</label>
          <select  id="selectcomprador" class="form-control select2" style="width: 100%;">
            <option value="0" selected="selected">Selecione o Comprador</option>
          </select>
        </div>
        <div class="form-group">
          <label>Seu Nome</label>
          <input id="nome-create" type="text" class="form-control" placeholder="Digite seu nome!">
        </div>
      </div>
      <div class="modal-footer right-content-between">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button id="btn-create-relat" type="button" class="btn btn-primary">Criar</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-edit-relatorio">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Alterar Relatório </h4></br>
        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-header">
      <h5><b>#</b><span id="idrel"></span> Comprador: <span id="ncomprador"></span></h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Setor</label>
          <select id="select-setor-id" class="form-control " style="width: 100%;">
            <option value="0" selected="selected">Selecione um Setor</option>
            <option>LOJA</option>
            <option>FRIOS</option>
            <option>HORTI-FRUTI</option>
            <option>CAFETERIA</option>
          </select>
        </div>
        <div class="form-group">
          <label>Comprador</label>
          <select  id="selectcomprador-id" class="form-control select2" style="width: 100%;">
            <!-- <option id="comprad-id" value="0" selected="selected"></option> -->
          </select>
        </div>
        <div class="form-group">
          <label>Seu Nome</label>
          <input id="nome-create-id" type="text" class="form-control" placeholder="Digite seu nome!">
        </div>
      </div>
      <div class="modal-footer right-content-between btn-altera-relat">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-altera-relat" type="button" class="btn btn-primary">Alterar</button> -->
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
<div class="modal fade" id="modal-edit-item">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Detalhe de Item do relatório <b>#</b><span id="delid"></span></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div class="row">
            <div class="col-3">
              <label>Codigo</label>
              <input id="dcodigo" type="text" class="form-control" disabled>
            </div>
            <div class="col-9">
              <label>Comprador</label>
              <input id="dcomprador" type="text" class="form-control" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label>Descrição</label>
              <input id="ddescricao" type="text" class="form-control" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <label>Embalagem</label>
              <input id="demb" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>Estoque</label>
              <input id="destoque" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>V30Dias</label>
              <input id="dv30d" type="text" class="form-control" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-3">
              <label>%Realizada</label>
              <input id="drentr" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>Venda</label>
              <input id="dvenda" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>%Atual</label>
              <input id="drenta" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>Resultado</label>
              <input id="dresult" type="text" class="form-control" disabled>
            </div>
          </div>
          <div class="row">
            <div class="col-3">
              <label>Vencimento</label>
              <input id="dvencimento" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>Dias</label>
              <input id="ddias" type="text" class="form-control" disabled>
            </div>
            <div class="col-3">
              <label>CXA</label>
              <input id="dcxa" type="text" class="form-control">
            </div>
            <div class="col-3">
              <label>UND</label>
              <input id="dund" type="text" class="form-control">
            </div>
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
<!-- Fim Modal da Pagina -->
      <!-- /.modal -->
<div class="content-wrapper">
  <div class="content-header">
  </div>
  <div class="container">
    <div class="content">
      <div class="row">
        <div class="col-lg-9">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title">Controles de Vencimentos</h3>
              
              <div class="card-tools">
                <button id="btnadd" type="button" class="btn btn-tool" data-toggle="modal" data-target="#modal-create-relatorio">
                  <i class="fas fa-plus"></i>
                </button>
                <!-- <button type="button" class="btn btn-tool" data-card-widget="remove">
                  <i class="fas fa-times"></i>
                </button> -->
              </div>
              <div class="card-tools">
                <span id="datagora" class="mr-3"></span>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
              <div class="table-responsive table-sm">
                <table class="table m-0">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th></th>
                    <th>Setor</th>
                    <th>Comprador</th>
                    <th>Nome</th>
                    <th>Atualização</th>
                    <th>Itens</th>
                    <th></th>
                    <th></th>
                  </tr>
                  </thead>
                  <tbody id="relatoriosvencimentos">
                  </tbody>
                </table>
              </div>
              <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title">Status DB</h3>
              
              <div class="card-tools">
                <button id="btnsyncdb" type="button" class="btn btn-tool" >
                  <i class="fas fa-sync-alt"></i>
                </button>
                <!-- <button type="button" class="btn btn-tool" data-card-widget="remove">
                  <i class="fas fa-times"></i>
                </button> -->
              </div>
              <!-- <div class="card-tools">
                <span id="datagora" class="mr-3"></span>
              </div> -->
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
              <div class="table-responsive table-sm">
                <table class="table m-0">
                  <thead>
                  <tr>
                    <th>Relatório</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                  </thead>
                  <tbody id="statusbase">
                  </tbody>
                </table>
              </div>
              <!-- /.table-responsive -->
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