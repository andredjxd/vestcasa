<?php
include_once 'headers.php'; 
?>

<div class="modal fade" id="modal-avarias-edit">
  <div class="modal-dialog modal-xxl modal-avarias-mobile">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">RELATORIO DE AVARIAS - ITENS </br><span id="idavaria"></span></h4>
        
        <button type="button" class="close fecharmeitens" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        
      </div>
      <div class="card-body p-1 modal-avarias-toolbar">
          <div class="d-flex justify-content-end align-items-center mx-2" id="btn-add-avaria"></div>
      </div>
      <div class="modal-body p-1 modal-avarias-body">
        <div class="overlay-wrapper" >
          <div class="overlay purple p-2" id="overlay-avaria-item">
            <div class="overlay-content text-center">
              <i class="fas fa-5x fa-sync-alt fa-spin"></i>
              <div class="text-bold mt-3" style="font-size: 1rem;">
                Carregando...
              </div>
            </div>
          </div>
          <div class="card-body px-2 pt-1 pb-1" style="min-height: 220px;">
          <!-- <div class="px-2 pt-1 pb-1 modal-avarias-table-scroll"> -->
            <table id="relAvariaItem" class="table table-smm table-bordered table-striped table-hover" style="font-size: 0.85em; width:100%" >
              <tbody id="codtabAvariaItem">
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer right-content-between p-1">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-salvar-conferencia" type="button" class="btn btn-primary">Salvar Conferência</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-add-avaria">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">ADICIONAR AVARIA</h4>
        <button type="button" class="close fecham" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form-add-avaria" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="card-body">
            <div id="actions" class="row">
              <input type="hidden" name="codigo_avaria" id="codigo_avaria">

              <!-- CÓDIGO DE BARRAS -->
              <div class="form-group col-12">
                <label for="codigo_barras">Código de Barras</label>

                <div class="input-group mb-3">
                  <input 
                    type="text" 
                    name="codigo_barras" 
                    id="codigo_barras" 
                    class="form-control" 
                    placeholder="CODIGO DE BARRAS"
                  >

                  <div class="input-group-append">
                    <button 
                      type="button" 
                      class="btn btn-primary" 
                      id="btn-toggle-reader"
                    >
                      <i class="fal fa-barcode-read"></i>
                    </button>
                  </div>
                </div>

                <div id="box-reader" style="display:none;">
                  <div id="reader" style="width: 100%;"></div>
                </div>
              </div>
              <!-- TIPO DE AVARIA -->
              <div class="form-group col-12">
                <select id="tipoavaria" name="tipoavaria" class="form-control form-control-sm" style="width: 100%;">
                  <option value="0" selected="selected">TIPO AVARIA</option>
                </select>
              </div>

              <!-- QUANTIDADE -->
              <div class="form-group col-12">
                <select id="quantidade" name="quantidade" class="form-control" style="width: 100%;">
                  <option value="" selected>QUANTIDADE</option>
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
                  <option value="11">11</option>
                  <option value="12">12</option>
                  <option value="13">13</option>
                  <option value="14">14</option>
                  <option value="15">15</option>
                  <option value="16">16</option>
                  <option value="17">17</option>
                  <option value="18">18</option>
                  <option value="19">19</option>
                  <option value="20">20</option>
                </select>
              </div>

              <!-- OBSERVAÇÕES -->
              <div class="form-group col-12">
                <label for="observacao">Observações</label>

                <textarea
                  name="observacao"
                  id="observacao"
                  class="form-control text-uppercase"
                  rows="3"
                  placeholder="DIGITE AS OBSERVAÇÕES DO ITEM"
                ></textarea>
              </div>

              <!-- UPLOAD DE FOTOS -->
              <div class="form-group col-12">
                <!-- <label for="fotos_avaria">Fotos do item</label> -->

                <div class="input-group mb-2">

                  <input 
                    type="file" 
                    class="d-none" 
                    id="fotos_avaria" 
                    name="fotos_avaria[]" 
                    accept="image/*"
                    capture="environment"
                    multiple
                  >

                  <input 
                    type="text" 
                    class="form-control" 
                    id="label_fotos_avaria"
                    value="Tirar foto do produto"
                    readonly
                  >

                  <div class="input-group-append">
                    <label 
                      for="fotos_avaria" 
                      class="btn btn-primary mb-0"
                      title="Abrir câmera"
                    >
                      <i class="fas fa-camera"></i>
                    </label>
                  </div>

                </div>

                <small class="form-text text-muted mb-2">
                  Tire uma ou mais fotos do produto. Você pode excluir antes de salvar.
                </small>

                <!-- LISTA DE FOTOS -->
                <div id="lista-fotos-avaria" class="row"></div>
              </div>

            </div>
            <div class="d-flex justify-content-center m-1" id="load" >
              <!-- <div class="spinner-border" role="status" >
                <span class="visually-hidden"></span>
              </div> -->
            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="modal-footer right-content-between">
          <button id="btn-add-salvar-avaria" type="button" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal-upload-avaria">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">PROCESSO DE ATUALIZAÇÃO DO PORTAL DE AVARIAS - RELATÓRIO <span id="idupava"></span></h4>
        <button type="button" class="close fecham" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form-add-avaria" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="card-body">
            <div id="actions" class="row">
              <input type="hidden" name="codigo_avaria" id="codigo_avaria">

              <!-- CÓDIGO DE BARRAS -->
              <div class="form-group col-12">
                <p>Após clicar no botão atualizar o processo de atualização entrara na fila para
                  execução, emquanto esse processo não termina o relatório ficara travado para 
                  adição, alteração ou exclusão, após o processo terminar
                </p>
              </div>
              

            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="modal-footer right-content-between">
          <button id="btn-add-fila-avaria" type="button" class="btn btn-primary">Atualizar</button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-fila-avaria-relatorio">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">ATUALIZAR RELATÓRIO DE AVARIAS</h4>
        <button type="button" class="close fecham" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="card-body">
          <p>
            Essa ação irá adicionar à fila de execução do worker um processo que
            consulta o portal de avarias e atualiza a lista de relatórios localmente.
          </p>
          <p class="mb-0">Deseja continuar?</p>
        </div>
      </div>
      <div class="modal-footer right-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button id="btn-confirmar-fila-avaria-relatorio" type="button" class="btn btn-primary">Atualizar</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="modal-edit-avaria">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">
          EDITAR AVARIA <span id="idavariaedit"></span>
        </h4>

        <button type="button" class="close fecham" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="form-edit-avaria" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="card-body">
            <div id="actions" class="row">

              <input type="hidden" name="id_item" id="editid_item">
              <input type="hidden" name="codigo_avaria" id="editcodigo_avaria">

              <!-- CÓDIGO DE BARRAS -->
              <div class="form-group col-12">
                <label for="editcodigo_barras">Código de Barras</label>

                <div class="input-group mb-3">
                  <input 
                    type="text" 
                    name="codigo_barras" 
                    id="editcodigo_barras" 
                    class="form-control" 
                    placeholder="CODIGO DE BARRAS"
                  >

                  <div class="input-group-append">
                    <button 
                      type="button" 
                      class="btn btn-primary" 
                      id="edit-btn-toggle-reader"
                    >
                      <i class="fal fa-barcode-read"></i>
                    </button>
                  </div>
                </div>

                <div id="edit-box-reader" style="display:none;">
                  <div id="edit-reader" style="width: 100%;"></div>
                </div>
              </div>

              <!-- TIPO DE AVARIA -->
              <div class="form-group col-12">
                <select id="edittipoavaria" name="tipoavaria" class="form-control form-control-sm" style="width: 100%;">
                  <!-- <option value="0" selected="selected">TIPO AVARIA</option> -->
                </select>
              </div>

              <!-- QUANTIDADE -->
              <div class="form-group col-12">
                <label for="editquantidade">Quantidade</label>
                <select id="editquantidade" name="quantidade" class="form-control" style="width: 100%;">
                  <option value="">QUANTIDADE</option>
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
                  <option value="11">11</option>
                  <option value="12">12</option>
                  <option value="13">13</option>
                  <option value="14">14</option>
                  <option value="15">15</option>
                  <option value="16">16</option>
                  <option value="17">17</option>
                  <option value="18">18</option>
                  <option value="19">19</option>
                  <option value="20">20</option>
                </select>
              </div>

              <!-- OBSERVAÇÕES -->
              <div class="form-group col-12">
                <label for="editobservacao">Observações</label>

                <textarea
                  name="observacao"
                  id="editobservacao"
                  class="form-control text-uppercase"
                  rows="3"
                  placeholder="DIGITE AS OBSERVAÇÕES DO ITEM"
                ></textarea>
              </div>

              <!-- FOTOS EXISTENTES -->
              <div class="form-group col-12">
                <label>Fotos atuais</label>
                <div id="editlista-fotos-existentes" class="fotos-preview-grid"></div>
              </div>

              <!-- UPLOAD DE NOVAS FOTOS -->
              <div class="form-group col-12">
                <label>Adicionar novas fotos</label>

                <div class="input-group mb-2">

                  <input 
                    type="file" 
                    class="d-none" 
                    id="editfotos_avaria" 
                    name="fotos_avaria[]" 
                    accept="image/*"
                    capture="environment"
                    multiple
                  >

                  <input 
                    type="text" 
                    class="form-control" 
                    id="editlabel_fotos_avaria"
                    value="Tirar foto do produto"
                    readonly
                  >

                  <div class="input-group-append">
                    <label 
                      for="editfotos_avaria" 
                      class="btn btn-primary mb-0"
                      title="Abrir câmera"
                    >
                      <i class="fas fa-camera"></i>
                    </label>
                  </div>

                </div>

                <small class="form-text text-muted mb-2">
                  Tire uma ou mais fotos do produto. Você pode excluir antes de salvar.
                </small>

                <div id="editlista-fotos-avaria" class="fotos-preview-grid"></div>
              </div>

            </div>

            <div class="d-flex justify-content-center m-1" id="editload"></div>
          </div>
        </div>

        <div class="modal-footer right-content-between">
          <button id="btn-edit-salvar-avaria" type="button" class="btn btn-primary">
            Salvar Alterações
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
<div class="modal fade" id="modal-view-foto-avaria" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content bg-dark">

            <div class="modal-header border-0">
                <h5 class="modal-title text-white">
                    Visualizar Foto
                </h5>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body text-center p-2">
                <img 
                    id="view-foto-avaria-img" 
                    src="" 
                    alt="Foto da avaria" 
                    style="max-width: 100%; max-height: 75vh; border-radius: 8px;"
                >
            </div>

        </div>
    </div>
</div>

<!-- Fim Modal da Pagina -->
      <!-- /.modal -->
<div class="content-wrapper">
  <div class="content-header">
  </div>
  <!-- <div class="container"> -->
    <div class="content-fluid px-5">
      <div class="row">
        <div class="col-md-10">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title" >RELATÓRIO AVARIAS</h3> 
              <div class="card-tools">
                <!-- <button type="button" class="btn btn-tool " id="cadastra-cnpj">
                  <i class='fas fa-plus'><span class="brand-text font-weight"> Adicionar</span></i>
                </button> -->
                <!-- <button type="button" class="btn btn-tool createRel" dat='' ><i class="fas fa-plus"></i></button> -->
                <button type='button' class='btn btn-tool syncRelat' dat='config' title="Atualizar tabela"><i class='fas fa-sync'></i></button>
                <button type='button' class='btn btn-tool btn-fila-avaria-relatorio' title="Atualizar Relatório no Portal (Fila)"><i class='fas fa-cloud-download-alt'></i></button>
              </div>
              
            </div>
            <!-- /.card-header -->
            <div class="card-body p-2">
              <div class="overlay-wrapper" >
                <div class="overlay purple" id="overlay-recebimento">
                  <div class="overlay-content text-center">
                    <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                    <div class="text-bold mt-3" style="font-size: 1rem;">
                      Carregando...
                    </div>
                  </div>
                </div>
                <div class="card-body p-0" style="min-height: 220px;">
                  
                  <table id="relAvarias" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.0em; width:100%" >
                    <div id="filtrosConsultaProdutos" class=" px-2"></div>
                    <tbody id="codtabAvarias">
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!-- /.card-body -->
          </div>
        </div> 

        <div class="col-md-2" id="boxavaria"></div>    
      </div>
    </div>
  <!-- </div> -->
</div>

<?php
include_once 'footer.php'; 
?>
