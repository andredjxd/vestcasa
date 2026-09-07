<?php
include_once 'headers.php'; 
?>
<!-- Inicio Modal da Pagina -->
<div class="modal" id="modal-loading" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5>Carregando...</h5>
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-relatorio-precos-alterados">
  <div class="modal-dialog modal-xxl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >Relatório Detalhado de Preços Alterados</h5>
        <div class="card-tools ml-auto">
          <!-- <button type="button" class="btn btn-tool " id="cadastra-cnpj">
            <i class='fas fa-plus'><span class="brand-text font-weight"> Adicionar</span></i>
          </button> -->
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row row-centered">
          <div class="card-body p-1">
            <table id="relVendaDetalhaPreco" class="table table-sm table-bordered table-striped table-hover " style="font-size: 0.65em;" >
              <thead>
                  <tr>
                      <!-- <th rowspan="2">#</th>
                      <th rowspan="2">Data</th>
                      <th rowspan="2">Código Barra</th>
                      <th rowspan="2">Descrição</th> -->

                      <th colspan="6"></th>
                      <th colspan="3" class="text-center">Preço no Clube</th>
                      <th colspan="2" class="text-center">Limite Clube</th>
                      <th colspan="3" class="text-center">Preço Clube Max</th>
                      <th colspan="2" class="text-center">Limite Max</th>
                      <th colspan="3" class="text-center">Preço no Varejo</th>
                  </tr>
                  <tr>
                      <th>#</th>
                      <th>Data</th>
                      <th>SKU</th>
                      <th>Código Barra</th>
                      <th>Descrição</th>
                      <th>EST</th>
                      <th>ANT</th>
                      <th>ATU</th>
                      <th>Status</th>

                      <th>ANT</th>
                      <th>ATU</th>

                      <th>ANT</th>
                      <th>ATU</th>
                      <th>Status</th>

                      <th>ANT</th>
                      <th>ATU</th>

                      <th>ANT</th>
                      <th>ATU</th>
                      <th>Status</th>
                  </tr>
              </thead>
              <tbody id="cobtabDetalhaPreco">
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
<div class="modal fade" id="modal-relatorio-rol-detalhado-almox">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >Relatório Detalhado</h5>
        <div class="card-tools ml-auto">
          <!-- <button type="button" class="btn btn-tool " id="cadastra-cnpj">
            <i class='fas fa-plus'><span class="brand-text font-weight"> Adicionar</span></i>
          </button> -->
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row row-centered">
          <div class="card-body p-1">
            <table id="relroldetalhadoalmox" class="table table-sm table-bordered table-striped table-hover " style="font-size: 0.85em;" >
              <tbody id="roltabdetalhadoalmox">
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

<!-- Fim Modal da Pagina -->
      <!-- /.modal -->
<div class="content-wrapper">
  <div class="content-header">
  </div>
  <div class="container">
    <div class="content-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title"></h3>ACOMPANHAMENTO DE PREÇOS ALTERADOS
              <!-- <p id="statusrel"></p> -->
              
              <div class="card-tools" >
                <!-- <button type='button' class='btn btn-tool' id="add-cnpj">
                  <span class='badge badge-success'>CNPJ's</span>
                </button>
                <button type='button' class='btn btn-tool' id="add-rol">
                  <span class='badge badge-success'>ROL's</span>
                </button>
                <button type='button' class='btn btn-tool' id="add-despesa">
                  <span class='badge badge-success'>Adicionar</span>
                </button> -->
                <!-- <button type='button' class='btn btn-tool uploadbase' dat='BUDGET'>
                  <span class='badge badge-success ' > BUDGET</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i>
                </button>
                <button type='button' class='btn btn-tool uploadbase' dat='RAZAO'>
                  <span class='badge badge-success ' > RAZÃO</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i>
                </button>
                <button type='button' class='btn btn-tool uploadbase' dat='ALMOX'>
                  <span class='badge badge-success ' > ALMOX</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i>
                </button>
                <button type='button' class='btn btn-tool uploadbase' dat='REFEITORIO'>
                  <span class='badge badge-success ' > REFEITORIO</span>&nbsp;&nbsp;&nbsp;<i class='fas fa-upload'></i>
                </button> -->
                <button id="btnsyncdbpreco" type="button" class="btn btn-tool" >
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
            <div class="card card-orange card-tabs" id="precosalterados">
            </div>
          </div>
        </div>   
      </div>
    </div>
  </div>
</div>

<?php
include_once 'footer.php'; 
?>