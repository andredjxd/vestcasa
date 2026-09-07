<?php
include_once 'headers.php'; 
?>
<div class="modal fade" id="modal-recebimento-edit">
  <div class="modal-dialog modal-xxl">
    <div class="modal-content">
      <div class="modal-header ">
        <h4 class="modal-title">CONFERÊNCIA DE ITENS <b>#</b><span id="idrme"></span></h4>
        
        <button type="button" class="close fecharmeitens" data-dismiss="modal" aria-label="Close">
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
          <div class="overlay purple p-2" id="overlay-recebimento-item">
            <div class="overlay-content text-center">
              <i class="fas fa-5x fa-sync-alt fa-spin"></i>
              <div class="text-bold mt-3" style="font-size: 1rem;">
                Carregando...
              </div>
            </div>
          </div>
          <div class="card-body px-2 pt-1 pb-1" style="min-height: 220px;">
            <table id="relRecebimentoItem" class="table table-smm table-bordered table-striped table-hover" style="font-size: 0.85em; width:100%" >
              <tbody id="codtabRecebimentoItem">
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer right-content-between p-1">
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <button id="btn-salvar-conferencia" type="button" class="btn btn-primary">Salvar Conferência</button>
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
        <div class="col-md-9">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title" >RELATÓRIO DE RECEBIMENTO DE MERCADORIAS</h3> 
              <div class="card-tools">
                <!-- <button type="button" class="btn btn-tool " id="cadastra-cnpj">
                  <i class='fas fa-plus'><span class="brand-text font-weight"> Adicionar</span></i>
                </button> -->
                <!-- <button type="button" class="btn btn-tool createRel" dat='' ><i class="fas fa-plus"></i></button> -->
                <button type='button' class='btn btn-tool syncRelat' dat='config'><i class='fas fa-sync'></i></button>
              </div>
              <div class="card-tools" id="btn-recebimento"></div>
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
                  
                  <table id="relRecebimento" class="table table-smm table-bordered table-striped table-hover" style="font-size: 1.0em; width:100%" >
                    <div id="filtrosConsultaProdutos" class=" px-2"></div>
                    <tbody id="codtabRecebimento">
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!-- /.card-body -->
          </div>
        </div> 

        <div class="col-md-3" id="boxrme"></div>    
      </div>
    </div>
  <!-- </div> -->
</div>

<?php
include_once 'footer.php'; 
?>