<?php
include_once 'headers.php';
?>
<div class="content-wrapper">
  <div class="content-header">
  </div>
  <div class="container">
    <div class="content-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header border-transparent">
              <h3 class="card-title"></h3>GERADOR DE ETIQUETAS
            </div>
            <!-- /.card-header -->
            <div class="card-body">

              <div class="row mb-3">
                <div class="col-md-4">
                  <div class="input-group">
                    <input type="text" id="input-codigo-etiqueta" class="form-control" placeholder="Bipar ou digitar o código de barras" autofocus>
                    <div class="input-group-append">
                      <button id="btn-add-codigo-etiqueta" type="button" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Adicionar
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-8 text-right">
                  <button id="btn-atualizar-tabela-etiqueta" type="button" class="btn btn-info" title="Atualizar tabela">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                  <button id="btn-limpar-codigos-etiqueta" type="button" class="btn btn-default">
                    <i class="fas fa-trash"></i> Limpar lista
                  </button>
                  <button id="btn-gerar-pdf-etiqueta" type="button" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Gerar PDF
                  </button>
                </div>
              </div>

              <style>
                #tabela-codigos-etiqueta td,
                #tabela-codigos-etiqueta th {
                  padding: 2px 6px;
                  vertical-align: middle;
                }
              </style>

              <table id="tabela-codigos-etiqueta" class="table table-sm table-bordered table-striped" style="font-size: 0.8em;">
                <thead>
                  <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 140px;">Código de Barra</th>
                    <th>Descrição</th>
                    <th style="width: 90px;" class="text-center">Status</th>
                    <th style="width: 50px;" class="text-center">Ação</th>
                  </tr>
                </thead>
                <tbody id="corpo-tabela-codigos-etiqueta">
                </tbody>
              </table>

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
