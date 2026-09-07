<?php
include_once 'headers.php'; 
?>
<div class="modal fade" id="modal-update-relatorio-data">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Atualiza Relatórios no DB</h4>
        <button type="button" class="close fecham" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="card-body">
          <div id="actions" class="row ">
              <div class="col-lg-3 ">
                <div class="form-group">
                  <!-- <label>Date:</label> -->
                    <div class="input-group date" id="reservationdatesrt03" data-target-input="nearest">
                        <input type="text" id="datasrt03" class="form-control datetimepicker-input" data-target="#reservationdatesrt03"/>
                        <div class="input-group-append" data-target="#reservationdatesrt03" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
              </div>
            <div class="col-lg-9 ">
              <div class="btn-group w-100 " >
                <span class="btn btn-success col fileinput-button " id="addfile">
                  <!-- <i class="fas fa-plus"></i>
                  <span>Adicionar Arquivo</span> -->
                </span>
              </div>
            </div>
           
            <!-- <div class="col-lg-6 d-flex align-items-center">
              <div class="fileupload-process w-100">
                <div id="total-progress" class="progress progress-striped deactive" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                  <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                </div>
              </div>
            </div> -->
          </div>
          <div class="table table-striped files" id="previews">
            <div id="template" class="row mt-5 ">
              <div class="col-auto">
                  <span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
              </div>
              <div class="col d-flex align-items-center">
                  <p class="mb-0">
                    <span class="lead" data-dz-name></span>
                    (<span data-dz-size></span>)
                  </p>
                  <strong class="error text-danger" data-dz-errormessage></strong>
              </div>
              <div class="col-4 d-flex align-items-center">
                  <div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                  </div>
              </div>
              <div class="col-auto d-flex align-items-center">
                <div class="btn-group">
                  <button class="btn btn-primary start">
                    <i class="fas fa-upload"></i>
                    <!-- <span>Start</span> -->
                  </button>
                  <!-- <button data-dz-remove class="btn btn-warning cancel">
                    <i class="fas fa-times-circle"></i>
                    <span>Cancel</span> 
                  </button> -->
                  <button data-dz-remove class="btn btn-danger delete" id="deletefile">
                    <i class="fas fa-trash"></i>
                    <!-- <span>Delete</span> -->
                  </button>
                </div>
              </div>
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
        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
        <!-- <button id="btn-create-relat" type="button" class="btn btn-primary">Criar</button> -->
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
    </div>
    <div class="container">
      <div class="content">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0" style="color: #fff">Painel de Controle</h1>
          </div>
        </div>
        <div class="content">
          <div class="row">
            <div class="col-lg-8">
              <div class="card">
                <div class="card-header border-transparent">
                  <h3 class="card-title">Grafico de Vendas</h3>
                  <div class="card-tools">
                    <button id="btnsyncdb" type="button" class="btn btn-tool" >
                      <i class="fas fa-sync-alt"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body p-1">
                  <!-- <canvas id="graficoVendas"></canvas> -->
                  <div id="visitors-chart"></div>
                </div> 
              </div>
              <div class="card">
                  <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                      <h3 class="card-title">Grafico de ROR</h3>
                      <!-- <a href="javascript:void(0);">View Report</a> -->
                    </div>
                  </div>
                  <div class="card-body p-1">
                    <!-- <div id="visitors-charts"></div> -->
                  </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card">
                <div class="card-header border-transparent">
                  <h3 class="card-title">Status DB</h3>
                  <div class="card-tools">
                    <button id="btnsyncdb" type="button" class="btn btn-tool" >
                      <i class="fas fa-sync-alt"></i>
                    </button>
                  </div>
                </div>
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
                </div>
              </div>
          </div>
        </div>
            <!-- <div class="row">
              <div class="col-lg-8">
                
                <div class="card">
                  <div class="card-header border-0">
                    <h3 class="card-title">Products</h3>
                    <div class="card-tools">
                      <a href="#" class="btn btn-tool btn-sm">
                        <i class="fas fa-download"></i>
                      </a>
                      <a href="#" class="btn btn-tool btn-sm">
                        <i class="fas fa-bars"></i>
                      </a>
                    </div>
                  </div>
                  <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                      <thead>
                      <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Sales</th>
                        <th>More</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr>
                        <td>
                          <img src="../assets/dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
                          Some Product
                        </td>
                        <td>$13 USD</td>
                        <td>
                          <small class="text-success mr-1">
                            <i class="fas fa-arrow-up"></i>
                            12%
                          </small>
                          12,000 Sold
                        </td>
                        <td>
                          <a href="#" class="text-muted">
                            <i class="fas fa-search"></i>
                          </a>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <img src="../assets/dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
                          Another Product
                        </td>
                        <td>$29 USD</td>
                        <td>
                          <small class="text-warning mr-1">
                            <i class="fas fa-arrow-down"></i>
                            0.5%
                          </small>
                          123,234 Sold
                        </td>
                        <td>
                          <a href="#" class="text-muted">
                            <i class="fas fa-search"></i>
                          </a>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <img src="../assets/dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
                          Amazing Product
                        </td>
                        <td>$1,230 USD</td>
                        <td>
                          <small class="text-danger mr-1">
                            <i class="fas fa-arrow-down"></i>
                            3%
                          </small>
                          198 Sold
                        </td>
                        <td>
                          <a href="#" class="text-muted">
                            <i class="fas fa-search"></i>
                          </a>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <img src="../assets/dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
                          Perfect Item
                          <span class="badge bg-danger">NEW</span>
                        </td>
                        <td>$199 USD</td>
                        <td>
                          <small class="text-success mr-1">
                            <i class="fas fa-arrow-up"></i>
                            63%
                          </small>
                          87 Sold
                        </td>
                        <td>
                          <a href="#" class="text-muted">
                            <i class="fas fa-search"></i>
                          </a>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                
              </div> -->
              
              <!-- <div class="col-lg-6">
                <div class="card">
                  <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                      <h3 class="card-title">Sales</h3>
                      <a href="javascript:void(0);">View Report</a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="d-flex">
                      <p class="d-flex flex-column">
                        <span class="text-bold text-lg">$18,230.00</span>
                        <span>Sales Over Time</span>
                      </p>
                      <p class="ml-auto d-flex flex-column text-right">
                        <span class="text-success">
                          <i class="fas fa-arrow-up"></i> 33.1%
                        </span>
                        <span class="text-muted">Since last month</span>
                      </p>
                    </div>
                    

                    <div class="position-relative mb-4">
                      <canvas id="sales-chart" height="200"></canvas>
                    </div>

                    <div class="d-flex flex-row justify-content-end">
                      <span class="mr-2">
                        <i class="fas fa-square text-primary"></i> This year
                      </span>

                      <span>
                        <i class="fas fa-square text-gray"></i> Last year
                      </span>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header border-0">
                    <h3 class="card-title">Online Store Overview</h3>
                    <div class="card-tools">
                      <a href="#" class="btn btn-sm btn-tool">
                        <i class="fas fa-download"></i>
                      </a>
                      <a href="#" class="btn btn-sm btn-tool">
                        <i class="fas fa-bars"></i>
                      </a>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                      <p class="text-success text-xl">
                        <i class="ion ion-ios-refresh-empty"></i>
                      </p>
                      <p class="d-flex flex-column text-right">
                        <span class="font-weight-bold">
                          <i class="ion ion-android-arrow-up text-success"></i> 12%
                        </span>
                        <span class="text-muted">CONVERSION RATE</span>
                      </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                      <p class="text-warning text-xl">
                        <i class="ion ion-ios-cart-outline"></i>
                      </p>
                      <p class="d-flex flex-column text-right">
                        <span class="font-weight-bold">
                          <i class="ion ion-android-arrow-up text-warning"></i> 0.8%
                        </span>
                        <span class="text-muted">SALES RATE</span>
                      </p>
                    </div>
    
                    <div class="d-flex justify-content-between align-items-center mb-0">
                      <p class="text-danger text-xl">
                        <i class="ion ion-ios-people-outline"></i>
                      </p>
                      <p class="d-flex flex-column text-right">
                        <span class="font-weight-bold">
                          <i class="ion ion-android-arrow-down text-danger"></i> 1%
                        </span>
                        <span class="text-muted">REGISTRATION RATE</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div> -->
            </div>
          </div>
        </div>
      </div>
    </div>
<?php
include_once 'footer.php'; 
?>