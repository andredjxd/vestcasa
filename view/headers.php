<?php
require_once __DIR__ . '/../assets/_php/auth_session.php';
exigirLoginPagina('login.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VESTCASA PMW</title>

    <link rel="icon" type="image/png" href="../assets/img/logo_mega_loja.png">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <!-- IonIcons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="../assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
     <!-- Theme style -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <!-- daterange picker -->
    <link rel="stylesheet" href="../assets/plugins/daterangepicker/daterangepicker.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="../assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="../assets/plugins/toastr/toastr.min.css">
    <!-- dropzonejs -->
    <link rel="stylesheet" href="../assets/plugins/dropzone/dropzone.css">
    
    <link rel="stylesheet" href="../assets/plugins/jquery-ui/jquery-ui.css">
    <!-- <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"> -->
    <!-- DataTables -->
    <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="../assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- CodeMirror -->
    <link rel="stylesheet" href="../assets/plugins/codemirror/codemirror.css">
    <link rel="stylesheet" href="../assets/plugins/codemirror/theme/monokai.css">

<script>
(function () {
    const isDark = localStorage.getItem('dark-mode') === 'true';
    const root = document.documentElement;

    if (isDark) {
        root.classList.add('dark-mode');

        // 🔥 força fundo imediato (usa SUA cor do CSS)
        root.style.backgroundColor = '#121212';
    } else {
        root.style.backgroundColor = '#f4f6f9';
    }
})();
</script>
    <!-- Custon -->
    <link rel="stylesheet" href="../assets/css/style.css">
</script>
</head>
<body class="layout-top-nav">
  
  <div class="modal fade" id="modal-sync-db">
    <div class="modal-dialog modal-lg">
      <div class="modal-content bg-secondary" >
        <div class="modal-header">
          <h4 class="modal-title">🧩 Sincronizar Banco de Dados!</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body ">
          <div class="row p-0">
            <div class="card-body p-0 col-10" >
              <h4 class="p-2">Processo de verificação e atualização do banco de dados</h4>
            </div>
            <div class="card-body p-0 col-2 " >
              <button id="btnMigrate" type="button" class="btn btn-primary">🚀 Executar </button>
            </div>
          </div>
          <div class="card-body " id="msmsync" ></div>
        </div>
        <div class="modal-footer right-content-between"   id="addbtnExcluirISVRel">
          <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
          <!-- <button id="btn-salva-detalhe" type="button" class="btn btn-primary">Excluir</button> -->
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <div class="modal fade" id="modal-fila-execucao">
    <div class="modal-dialog modal-xl">
      <div class="modal-content " >
        <div class="modal-header">
            <h4 class="modal-title">🧩 ACOMPANHAMENTO DE ATUALIZAÇÃO</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="fechafila">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body p-2">

            <div class="card card-orange card-tabs m-0">

                <!-- ================= TABS HEADER ================= -->
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active"
                              data-toggle="pill"
                              href="#tab-fila"
                              role="tab">
                                FILA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                              data-toggle="pill"
                              href="#tab-agendadas"
                              role="tab">
                                Agendadas
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- ================= TAB CONTENT ================= -->
                <div class="card-body p-2">
                    <div class="tab-content">

                        <!-- ===================================================== -->
                        <!-- ===================== ABA FILA ====================== -->
                        <!-- ===================================================== -->
                        <div class="tab-pane fade show active" id="tab-fila" role="tabpanel">

                            <!-- Cabeçalho da fila -->
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <h5 class="m-0">📊 Fila de Execução</h5>

                                <div>
                                  <button class="btn btn-success btn-sm modalvendas me-2">
                                     <i class="fas fa-hand-holding-usd"></i> Atualizar Vendas
                                  </button>

                                  <button class="btn btn-primary btn-sm executar-precos me-2">
                                      <i class="fas fa-dollar-sign"></i> Atualizar Preços
                                  </button>

                                  <button class="btn btn-instagram btn-sm atualizarinstagram">
                                      <i class="fab fa-instagram"></i> Atualizar Instagram
                                  </button>
                                </div>
                            </div>

                            <!-- Tabela -->
                            <div class="table-responsive">
                                <table id="tabelaFila" class="table table-bordered table-striped table-sm mb-0" style="font-size: 0.9rem;">
                                    <thead class="text-center">
                                        <tr>
                                            <th>#</th>
                                            <th>Data</th>
                                            <th class="text-left">Processo</th>
                                            <th>Parâmetros</th>
                                            <th>Total</th>
                                            <th>Concluido</th>
                                            <th>Progresso</th>
                                            <th>Status</th>
                                            <th>Tempo</th>
                                            <th>Log</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyFila"></tbody>
                                </table>
                            </div>

                        </div>

                        <!-- ===================================================== -->
                        <!-- ================== ABA AGENDADAS ==================== -->
                        <!-- ===================================================== -->
                        <div class="tab-pane fade" id="tab-agendadas" role="tabpanel">

                            <!-- Cabeçalho das agendadas -->
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <h5 class="m-0">📅 Execuções Agendadas</h5>

                                <div class="btn-group">
                                    <!-- <button class="btn btn-success btn-sm executar-vendas-ag">
                                        Executar Vendas
                                    </button> -->
                                    <button class="btn btn-primary btn-sm executar-precos-ag">
                                        <i class="fas fa-calendar-alt"></i> Criar Agendamento
                                    </button>
                                </div>
                            </div>

                            <!-- Tabela -->
                            <div class="table-responsive">
                                <table id="tabelaAgendadas" class="table table-bordered table-striped table-sm mb-0" style="font-size: 0.9rem;">
                                    <thead class="text-center">
                                        <tr>
                                            <th>#</th>
                                            <th>Criado</th>
                                            <th class="text-left">Processo</th>
                                            <th>Parâmetros</th>
                                            <th>Total</th>
                                            <th>Concluido</th>
                                            <th>Progresso</th>
                                            <th>Status</th>
                                            <th>Data do Agendamento</th>
                                            <th>Tempo</th>
                                            <th>Log</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyAgendadas"></tbody>
                                </table>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </div>
        <div class="modal-footer right-content-between"   id="addbtnExcluirISVRel">

        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <div class="modal fade" id="modal-atualiza-venda">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content bg-info" >
        <div class="modal-header">
          <h4 class="modal-title">ATUALIZAR VENDAS</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="fechaISV">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">
            <h6>Escolha a data de atualização.</h6>
            <div class="col-12 p-0">
              <!-- <label>Data</label> -->
              <div class="input-group date" id="reservationdateatualizarvenda" data-target-input="nearest">
                  <input type="text" class="form-control form-control-sm datetimepicker-input" id="dataatualizavendas" data-target="#reservationdateatualizarvenda"/>
                  <div class="input-group-append" data-target="#reservationdateatualizarvenda" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer right-content-between"   id="addbtnDesativar">
          <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
          <button type="button" class="btn btn-primary btn-atualizarvendas">Adicionar</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <div class="modal fade" id="modal-visualizar-log">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">VISUALIZAÇÃO DE LOG</h4>
          <button type="button" class="close fecham" data-dismiss="modal" aria-label="Close" id="fechasrt03">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- style="max-height: 450px; overflow-y: auto; overflow-x: hidden;" -->
          <div class="card-body"  > 
            
              <textarea id="codeMirrorDemo" class="p-3 "></textarea>
            
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
  <div class="modal fade" id="modal-update-relatorio">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Atualiza Relatórios no DB</h4>
          <button type="button" class="close fechaatualizar" data-dismiss="modal" aria-label="Close" >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="card-body">
            <div id="actions" class="row ">
              <div class="col-lg-3 ">
                <div class="form-group">
                  <label>Num. do Chamando</label>
                    <div class="input-group" >
                        <input type="text" id="numchamando" class="form-control" autocomplete="off">
                        <div class="input-group-append">
                          <span class="input-group-text"><i class="fas fa-headset"></i></span>
                        </div>
                    </div>
                </div>
              </div>
              <div class="col-lg-3 ">
                <div class="form-group">
                  <label>Data do Recebimento</label>
                    <div class="input-group date" id="reservationdatarme" data-target-input="nearest">
                        <input type="text" id="datarme" class="form-control datetimepicker-input" data-target="#reservationdatarme"/>
                        <div class="input-group-append" data-target="#reservationdatarme" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="col-lg-6 ">
                <label>&nbsp;</label>
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
              <div id="previews"></div>
              <div class="p-0 m-0" id="progress-text" style="max-height:250px;overflow:auto;"></div>
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
<!-- ═══════════════ MODAL SYNC VESTCASA ═══════════════ -->
<style>
  #sv-log {
    background:#1e1e1e; color:#d4d4d4;
    font-family:'Consolas','Courier New',monospace; font-size:.75rem;
    height:420px; overflow-y:auto;
    padding:6px 10px; border-radius:4px;
  }
  #sv-log .sv-line {
    padding:5px 2px;
    border-bottom:1px solid rgba(255,255,255,.06);
    line-height:1.6;
  }
  #sv-log .sv-line:last-child { border-bottom:none; }
  #sv-log .sv-ts   { color:#6a9955; }
  #sv-log .sv-level{ font-weight:bold; margin-left:4px; }
  #sv-log .sv-dir  {
    font-weight:bold; margin-left:6px; padding:0 6px;
    border-radius:3px; background:rgba(255,255,255,.08);
  }
  #sv-log .sv-ops  { color:#dcdcaa; margin-left:6px; font-weight:bold; }
  #sv-log .sv-chips{
    display:flex; flex-wrap:wrap; gap:4px; margin-top:5px;
  }
  #sv-log .sv-chip {
    background:#2d2d2d; border:1px solid #3c3c3c; border-radius:3px;
    padding:1px 6px; font-size:.7rem; color:#cfcfcf; white-space:nowrap;
  }
  #sv-log .sv-chip b { color:#4ec9b0; margin-left:4px; }
  #sv-log .sv-error { color:#f48771; }
  #sv-log .sv-msgtxt{ margin-left:6px; word-break:break-word; white-space:normal; }
</style>
<div class="modal fade" id="modal-sync-vestcasa">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white py-2">
        <h5 class="modal-title mb-0">
          <i class="fad fa-sync-alt mr-2"></i> Sync Vestcasa
          <span id="sv-badge" class="badge ml-2" style="font-size:.75rem">—</span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body pb-2">

        <!-- Status row -->
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div>
            <span style="font-size:.85rem" class="text-muted">PID:</span>
            <strong id="sv-pid" class="ml-1">—</strong>
          </div>
          <div>
            <button id="sv-btn-start" class="btn btn-success btn-sm mr-2" disabled>
              <i class="fas fa-play mr-1"></i>Iniciar
            </button>
            <button id="sv-btn-stop" class="btn btn-danger btn-sm" disabled>
              <i class="fas fa-stop mr-1"></i>Parar
            </button>
            <button id="sv-btn-refresh" class="btn btn-secondary btn-sm ml-2" title="Atualizar">
              <i class="fas fa-redo"></i>
            </button>
          </div>
        </div>

        <!-- Log -->
        <div id="sv-log"></div>

        <div id="sv-msg" class="mt-2" style="font-size:.8rem; min-height:18px;"></div>
      </div>

      <div class="modal-footer py-1">
        <small class="text-muted mr-auto">Atualiza a cada 5s enquanto aberto</small>
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════ FIM MODAL SYNC ═══════════════ -->

<div class="wrapper">
  <!-- Navbar -->
  <div class="container" >
    
    <nav class=" navbar navbar-expand navbar-green navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <!-- <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars" style="color: #fff"></i></a>
        </li> -->
        <a href="index.php" class="navbar-brand">
          <img src="../assets/img/logovestcasa_bold.png" alt="logo" class="brand-image" style="height:25px;opacity: .8">
        </a>
        <!-- <li class="nav-item d-none d-sm-inline-block">
          <a href="index.php" class="nav-link" style="color: #fff">Início </a>
        </li> -->
        <!-- <li class="nav-item d-none d-sm-inline-block">
          <a href="vencimento.php" class="nav-link">Vencimento</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="agua.php" class="nav-link">Água</a>
        </li> -->
      
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle" style="color: #fff">COM</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="com_vendas.php" class="dropdown-item-green">COM VENDAS - DIA </a></li>
            <!-- <li><a href="com_srtbi03.php" class="dropdown-item-green">SRTBI03 </a></li>
            <li><a href="com_svdbia2.php" class="dropdown-item-green">SVDBIA2 </a></li> -->
            <!-- <li><a href="#" class="dropdown-item-green">Some other action</a></li> -->

            <!-- <li class="dropdown-divider"></li> -->

            <!-- Level two dropdown-->
            <!-- <li class="dropdown-submenu dropdown-hover">
              <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Hover for action</a>
              <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                  <a tabindex="-1" href="#" class="dropdown-item">level 2</a>
                </li> -->

                <!-- Level three dropdown-->
                <!-- <li class="dropdown-submenu">
                  <a id="dropdownSubMenu3" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">level 2</a>
                  <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                    <li><a href="#" class="dropdown-item">3rd level</a></li>
                    <li><a href="#" class="dropdown-item">3rd level</a></li>
                  </ul>
                </li> -->
                <!-- End Level three -->

                <!-- <li><a href="#" class="dropdown-item">level 2</a></li>
                <li><a href="#" class="dropdown-item">level 2</a></li> -->
              <!-- </ul> -->
            <!-- </li> -->
                <!-- End Level two -->
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle"  style="color: #fff">ADM</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <?php if (usuarioEhAdmin()): ?>
            <li><a href="usuarios.php" class="dropdown-item-green">USUARIOS</a></li>
            <?php endif; ?>
            <!-- DESATIVADO: NÃO ACESSAR -->
            <!-- <li><a href="fcx_deposito.php" class="dropdown-item-green" >FCX DEPOSITO </a></li> -->
            <!-- <li><a href="fcx_fechamento.php" class="dropdown-item-green" >FCX FECHAMENTO </a></li> -->
            <!-- <li><a href="adm_cafeteria.php" class="dropdown-item-green" >CAFETERIA </a></li>
            <li><a href="adm_razao.php" class="dropdown-item-green" >BUDGET / RAZAO (dev)</a></li>
            <li><a href="adm_quebras.php" class="dropdown-item-green" >FCX QUEBRAS </a></li>
            <li><a href="adm_boleto.php" class="dropdown-item-green" >FCX BOLETOS </a></li>
            
            <li><a href="agua.php" class="dropdown-item-green">ÁGUA </a></li> -->
            
            <!-- <li class="dropdown-submenu dropdown-hover">
              <a  id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Despesas </a>
              <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                  <a tabindex="-1" href="adm_despesas.php" class="dropdown-item">Lancar Notas</a>
                  <a tabindex="-1" href="#" class="dropdown-item">Cadastro CNPJ</a>
                  <a tabindex="-1" href="#" class="dropdown-item">Cadastro ROL</a>
                </li> 
              </ul
            </li> -->
            <!-- <li><a href="#" class="dropdown-item">Some other action</a></li> -->

            <!-- <li class="dropdown-divider"></li> -->

            <!-- Level two dropdown-->
            <!-- <li class="dropdown-submenu dropdown-hover">
              <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Hover for action</a>
              <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                  <a tabindex="-1" href="#" class="dropdown-item">level 2</a>
                </li> -->

                <!-- Level three dropdown-->
                <!-- <li class="dropdown-submenu">
                  <a id="dropdownSubMenu3" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">level 2</a>
                  <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                    <li><a href="#" class="dropdown-item">3rd level</a></li>
                    <li><a href="#" class="dropdown-item">3rd level</a></li>
                  </ul>
                </li> -->
                <!-- End Level three -->

                <!-- <li><a href="#" class="dropdown-item">level 2</a></li>
                <li><a href="#" class="dropdown-item">level 2</a></li> -->
              <!-- </ul>
            </li> -->
                <!-- End Level two -->
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle" style="color: #fff">OPE</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="ope_precos_alterados.php" class="dropdown-item-green">OPE PREÇOS ALTERADOS </a></li>
            <li><a href="ope_gerador_etiquetas.php" class="dropdown-item-green">OPE GERADOR ETIQUETAS </a></li>
            <li><a href="ope_produtos_consulta_new.php" class="dropdown-item-green">OPE CONSULTAR PRODUTOS </a></li>
            <li><a href="mercadoria_ajuste.php" class="dropdown-item-green">OPE AJUSTE DE MERCADORIA</a></li>
            <li><a href="rme_relatorios.php" class="dropdown-item-green">RME RELATÓRIOS DE RECEBIMENTO</a></li>
            <li><a href="avarias_relatorios.php" class="dropdown-item-green">OPE RELATÓRIOS DE AVARIAS</a></li>
            <!-- <li><a href="ope_produtos_consulta_new.php" class="dropdown-item-green">OPE CONSULTAR PRODUTOS - NEW </a></li> -->
            <!-- <li><a href="ope_isv.php" class="dropdown-item-green">ISV </a></li> -->
            <!-- <li><a href="#" class="dropdown-item">Some other action</a></li> -->

            <!-- <li class="dropdown-divider"></li> -->

            <!-- Level two dropdown-->
            <!-- <li class="dropdown-submenu dropdown-hover">
              <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Hover for action</a>
              <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                  <a tabindex="-1" href="#" class="dropdown-item">level 2</a>
                </li> -->

                <!-- Level three dropdown-->
                <!-- <li class="dropdown-submenu">
                  <a id="dropdownSubMenu3" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">level 2</a>
                  <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                    <li><a href="#" class="dropdown-item">3rd level</a></li>
                    <li><a href="#" class="dropdown-item">3rd level</a></li>
                  </ul>
                </li> -->
                <!-- End Level three -->

                <!-- <li><a href="#" class="dropdown-item">level 2</a></li>
                <li><a href="#" class="dropdown-item">level 2</a></li> -->
              <!-- </ul> -->
            <!-- </li> -->
                <!-- End Level two -->
          </ul>
        </li>
      </ul>
      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <!-- <i class="far fa-bell"></i> -->
            <span id="datagora" class="mr-3" style="color: #fff"></span>
          </a>
        <!-- <span id="datagora" class="mr-3"></span> -->
        <!-- <span id="sycnmigrations" class="mr-3"></span> -->
        </li>
        
        <!-- <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span id="sycnmigrations" class="mr-3"></span>
          </a>        
        </li> -->
        <!-- Navbar Search -->
        <!-- <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                  </button>
                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li> -->

        <!-- Messages Dropdown Menu -->
        <!-- <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-comments"></i>
            <span class="badge badge-danger navbar-badge">3</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <a href="#" class="dropdown-item">
             
              <div class="media">
                <img src="../assets/dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                <div class="media-body">
                  <h3 class="dropdown-item-title">
                    Brad Diesel
                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                  </h3>
                  <p class="text-sm">Call me whenever you can...</p>
                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                </div>
              </div>
              
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              
              <div class="media">
                <img src="../assets/dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                <div class="media-body">
                  <h3 class="dropdown-item-title">
                    John Pierce
                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                  </h3>
                  <p class="text-sm">I got your message bro</p>
                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                </div>
              </div>
              
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              
              <div class="media">
                <img src="../assets/dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                <div class="media-body">
                  <h3 class="dropdown-item-title">
                    Nora Silvester
                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                  </h3>
                  <p class="text-sm">The subject goes here</p>
                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                </div>
              </div>
              
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
          </div>
        </li> -->
        <!-- Notifications Dropdown Menu -->
         <li class="nav-item">
          <a class="nav-link filaexe" href="#" role="button" style="color: #fff">
            <i class="fad fa-stream"></i>
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#" style="color: #fff">
            <i class="fad fa-server"></i>
            <!-- <span class="badge badge-warning navbar-badge">15</span> -->
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notifications">
            <!-- <span class="dropdown-item dropdown-header">15 Notifications</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item" >
              <i class="fas fa-envelope mr-2"></i> 4 new messages
              <span class="float-right text-muted text-sm" >3 mins</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-users mr-2"></i> 8 friend requests
              <span class="float-right text-muted text-sm">12 hours</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
              <i class="fas fa-file mr-2"></i> 3 new reports
              <span class="float-right text-muted text-sm">2 days</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a> -->
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="toggleDarkMode()" style="color:#fff">
            <i id="iconDark" class="fas fa-moon"></i>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button" style="color: #fff">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" id="btnLogout" title="Sair" style="color: #fff">
            <i class="fas fa-sign-out-alt"></i>
          </a>
        </li>
        <script>
          document.getElementById('btnLogout').addEventListener('click', function (e) {
            e.preventDefault();
            fetch('../controller/back_logout.php', { method: 'POST' })
              .finally(function () { window.location.href = 'login.php'; });
          });
        </script>
        <!-- <li class="nav-item">
          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
            <i class="fas fa-th-large"></i>
          </a>
        </li> -->
      </ul>
    </nav>
    <!-- /.navbar -->
  </div>
  <!-- MODAL GLOBAL -->

  

