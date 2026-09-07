<?php
require_once __DIR__ . '/../assets/_php/auth_session.php';
exigirLoginPagina('login.php');
exigirAdminPagina('index.php');
include_once 'headers.php';
?>
<link rel="stylesheet" href="../assets/plugins/sweetalert2/sweetalert2.min.css">
<div class="content-wrapper">
  <div class="content-header"></div>

  <div class="content-fluid px-5">
    <div class="card card-orange card-tabs">
      <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#tab-usuarios" role="tab">Usuarios</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#tab-perfis" role="tab">Perfis</a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab-usuarios" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <h5 class="m-0">Usuarios</h5>
              <button class="btn btn-success btn-sm" id="btnNovoUsuario"><i class="fas fa-plus"></i> Novo Usuario</button>
            </div>
            <div class="table-responsive mt-2">
              <table class="table table-bordered table-striped table-sm" style="font-size:0.9rem;">
                <thead>
                  <tr><th>E-mail</th><th>Perfil</th><th>Status</th><th>Criado em</th><th></th></tr>
                </thead>
                <tbody id="tbodyUsuarios"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-perfis" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
              <h5 class="m-0">Perfis</h5>
              <button class="btn btn-success btn-sm" id="btnNovoPerfil"><i class="fas fa-plus"></i> Novo Perfil</button>
            </div>
            <div class="table-responsive mt-2">
              <table class="table table-bordered table-striped table-sm" style="font-size:0.9rem;">
                <thead>
                  <tr><th>Nome</th><th>Descricao</th><th>Web</th><th>App</th><th>Admin</th><th></th></tr>
                </thead>
                <tbody id="tbodyPerfis"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="modalUsuario">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Usuario</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="usuarioId">
        <div class="form-group">
          <label>E-mail</label>
          <input type="email" id="usuarioEmail" class="form-control">
        </div>
        <div class="form-group">
          <label>Senha <small class="text-muted">(deixe em branco para nao alterar)</small></label>
          <input type="password" id="usuarioSenha" class="form-control" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label>Perfil</label>
          <select id="usuarioRole" class="form-control">
            <option value="user">Usuario</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select id="usuarioStatus" class="form-control">
            <option value="0">Ativo</option>
            <option value="1">Inativo</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSalvarUsuario">Salvar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Perfil -->
<div class="modal fade" id="modalPerfil">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Perfil</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="perfilId">
        <div class="form-group">
          <label>Nome</label>
          <input type="text" id="perfilNome" class="form-control">
        </div>
        <div class="form-group">
          <label>Descricao</label>
          <input type="text" id="perfilDescricao" class="form-control">
        </div>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="perfilAcessoWeb">
          <label class="form-check-label">Acesso Web</label>
        </div>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="perfilAcessoApp">
          <label class="form-check-label">Acesso App</label>
        </div>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="perfilIsAdmin">
          <label class="form-check-label">Administrador</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSalvarPerfil">Salvar</button>
      </div>
    </div>
  </div>
</div>

<script src="../assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<?php
include_once 'footer.php';
?>
