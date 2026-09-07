<?php
include_once 'headers.php'; 
?>
<!-- Inicio Modal da Pagina -->
<div class="modal fade" id="modal-consulta-produto">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >CONSULTA - NEW</h5>
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
            <div class="row">
              <div class="col-md-9">
                <div class="card card-primary shadow-lg">
                  <div class="card-header">
                    <h3 class="card-title" id="produto"></h3>

                    <div class="card-tools">
                      <button type="button" class="btn btn-tool consultaextrato" ><i class="fas fa-file-alt"></i></button>
                    </div>
                  </div>
                  <div class="card-body" id="produto-detalhe"></div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card card-primary shadow-lg">
                  <!-- <div class="card-header">
                    <h3 class="card-title" id="produto">Shadow - Large</h3>

                    <div class="card-tools">
                      <button type="button" class="btn btn-tool" ></button>
                    </div>
                  </div> -->
                  <div class="bg-light card-body" id="produto-detalhe-img"></div>
                </div>
              </div>
              <!-- /.col -->
            </div>
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
<div class="modal fade" id="modal-consulta-extrato">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >CONSULTA - NEW</h5>
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
        <div class="row">
          <div class="col-md-12">
            <div class="timeline timelineextrato">
            </div>
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
<div class="modal fade" id="modal-view-desvinculados">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">PRODUTOS SEM VINCULO COM CODIGO DE BARRA</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="card-body pt-1 pb-1">
        <table id="relDesvinculados" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.0em; width:100%" >
          <tbody id="codtabDesvinculado">
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
            <div class="card-header border-transparent pb-1">
              <h3 class="card-title">TELA DE CONSULTA DE PRODUTOS -  NEW</h3>
              <div class="card-tools">
                
                <!-- <button type="button" class="btn btn-tool createRel" dat='' ><i class="fas fa-plus"></i></button> -->
                <button type='button' class='btn btn-tool syncRelat' dat='config'><i class='fas fa-sync'></i></button>
              </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body pt-1">
              <div class="overlay-wrapper" >
                <div class="overlay purple" id="overlay-consulta-produtos">
                  <div class="overlay-content text-center">
                    <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                    <div class="text-bold mt-3" style="font-size: 1rem;">
                      Carregando...
                    </div>
                  </div>
                </div>
                <div class="card-body p-0" style="min-height: 220px;">
                  
                  <table id="relConsultaProdutosNew" class="table table-smm table-bordered table-striped table-hover" style="font-size: 0.9em; width:100%" >
                    <div id="filtrosConsultaProdutosNew"></div>
                    <tbody id="codtabConsultaNew">
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