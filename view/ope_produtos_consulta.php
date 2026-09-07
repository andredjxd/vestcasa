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
  <div class="modal-dialog modal-xl">
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

                      <th colspan="4"></th>
                      <th colspan="3" class="text-center">Preço no Clube</th>
                      <th colspan="2" class="text-center">Limite Clube</th>
                      <th colspan="3" class="text-center">Preço Clube Max</th>
                      <th colspan="2" class="text-center">Limite Max</th>
                      <th colspan="3" class="text-center">Preço no Varejo</th>
                      <th colspan="2" class="text-center">Limite Varejo</th>
                  </tr>
                  <tr>
                      <th>#</th>
                      <th>Data</th>
                      <th>Código Barra</th>
                      <th>Descrição</th>
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

                      <th>ANT</th>
                      <th>ATU</th>
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
  <!-- <div class="container"> -->
    <div class="content-fluid px-5">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title">TELA DE CONSULTA DE PRODUTOS</h3>
              <div class="card-tools">
                <!-- <button type="button" class="btn btn-tool createRel" dat='' ><i class="fas fa-plus"></i></button> -->
                <button type='button' class='btn btn-tool syncRelat' dat='config'><i class='fas fa-sync'></i></button>
              </div>
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
                  
                  <table id="relConsultaProdutos" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.1em; width:100%" >
                    <div id="filtrosConsultaProdutos" class=" px-2"></div>
                    <tbody id="codtabConsulta">
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
  <!-- </div> -->
</div>

<?php
include_once 'footer.php'; 
?>