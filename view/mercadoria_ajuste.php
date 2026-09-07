<?php
include_once 'headers.php';
?>
<div class="content-wrapper">
  <div class="content-header"></div>

  <div class="content-fluid px-5">
    <div class="row">
      <div class="col-md-4">
        <div class="card">
          <div class="card-header border-transparent">
            <h3 class="card-title">AJUSTE DE MERCADORIA</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" id="btn-limpar-ajuste">
                <i class="fas fa-eraser"></i>
              </button>
            </div>
          </div>

          <div class="card-body">
            <div class="form-group">
              <label>Codigo de Barras</label>
              <div class="input-group">
                <input type="text" id="ajuste-codigo-barras" class="form-control" autocomplete="off" autofocus>
                <div class="input-group-append">
                  <button class="btn btn-primary" id="btn-buscar-mercadoria" type="button">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>

            <div id="box-produto-ajuste" class="callout callout-info py-2 d-none">
              <input type="hidden" id="ajuste-sku">
              <input type="hidden" id="ajuste-codigo-confirmado">
              <h6 id="ajuste-produto" class="font-weight-bold mb-1"></h6>
              <div class="text-muted small">
                SKU: <span id="ajuste-sku-label"></span>
              </div>
              <div class="text-muted small">
                Marca: <span id="ajuste-marca"></span>
              </div>
              <div class="mt-2">
                Estoque atual:
                <span class="badge badge-secondary" id="ajuste-estoque-atual">0</span>
              </div>
            </div>

            <div class="form-group">
              <label>Quantidade do Ajuste</label>
              <input type="number" id="ajuste-quantidade" class="form-control" step="1">
            </div>

            <div class="form-group">
              <label>Observacao</label>
              <textarea id="ajuste-observacao" class="form-control" rows="2" maxlength="255"></textarea>
            </div>

            <button type="button" id="btn-add-ajuste" class="btn btn-success btn-block">
              <i class="fas fa-plus"></i> Adicionar Item
            </button>
          </div>
        </div>

        <div class="info-box mb-3">
          <span class="info-box-icon bg-warning">
            <i class="fas fa-boxes"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Itens Pendentes</span>
            <span class="info-box-number" id="ajuste-total-pendente">0</span>
          </div>
        </div>

        <div class="info-box mb-3">
          <span class="info-box-icon bg-info">
            <i class="fas fa-balance-scale"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Saldo do Ajuste</span>
            <span class="info-box-number" id="ajuste-saldo-pendente">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card">
          <div class="card-header border-transparent">
            <h3 class="card-title">ITENS PARA AJUSTAR</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" id="btn-refresh-ajuste">
                <i class="fas fa-sync"></i>
              </button>
              <button type="button" class="btn btn-tool" id="btn-ajustar-estoque">
                <span class="badge badge-success" style="font-size: 1.0em;">
                  <i class="fas fa-check"></i> Ajuste
                </span>
              </button>
            </div>
          </div>

          <div class="card-body p-2">
            <div class="overlay-wrapper">
              <div class="overlay purple" id="overlay-ajuste-mercadoria">
                <div class="overlay-content text-center">
                  <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                  <div class="text-bold mt-3" style="font-size: 1rem;">
                    Carregando...
                  </div>
                </div>
              </div>
              <div class="card-body p-0" style="min-height: 220px;">
                <table id="relAjusteMercadoria" class="table table-smm table-bordered table-striped table-hover" style="font-size: 0.9em; width:100%">
                  <tbody id="codtabAjusteMercadoria"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header border-transparent">
            <h3 class="card-title">RELATORIO DE AJUSTES</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" id="btn-refresh-relatorio-ajuste">
                <i class="fas fa-sync"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-2">
            <div class="overlay-wrapper">
              <div class="overlay purple" id="overlay-ajuste-relatorio">
                <div class="overlay-content text-center">
                  <i class="fas fa-5x fa-sync-alt fa-spin"></i>
                  <div class="text-bold mt-3" style="font-size: 1rem;">
                    Carregando...
                  </div>
                </div>
              </div>
              <div class="card-body p-0" style="min-height: 220px;">
                <table id="relAjusteMercadoriaHistorico" class="table table-smm table-bordered table-striped table-hover" style="font-size: 0.85em; width:100%">
                  <tbody id="codtabAjusteMercadoriaHistorico"></tbody>
                </table>
              </div>
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
