function carregarUsuarios() {
  $.post('../controller/back_usuarios_listar.php', function (dados) {
    const $tb = $('#tbodyUsuarios').empty();
    dados.forEach(function (u) {
      $tb.append(
        '<tr>' +
        '<td>' + u.email + '</td>' +
        '<td>' + (u.role === 'admin' ? 'Administrador' : 'Usuario') + '</td>' +
        '<td>' + (u.status == 0 ? 'Ativo' : 'Inativo') + '</td>' +
        '<td>' + (u.created_at || '') + '</td>' +
        '<td>' +
        '<button class="btn btn-primary btn-xs btn-editar-usuario" data-id="' + u.id + '" data-email="' + u.email + '" data-role="' + u.role + '" data-status="' + u.status + '"><i class="fas fa-edit"></i></button> ' +
        '<button class="btn btn-danger btn-xs btn-excluir-usuario" data-id="' + u.id + '"><i class="fas fa-trash"></i></button>' +
        '</td>' +
        '</tr>'
      );
    });
  });
}

function carregarPerfis() {
  $.post('../controller/back_perfis_listar.php', function (dados) {
    const $tb = $('#tbodyPerfis').empty();
    dados.forEach(function (p) {
      $tb.append(
        '<tr>' +
        '<td>' + p.nome + '</td>' +
        '<td>' + (p.descricao || '') + '</td>' +
        '<td>' + (p.acesso_web == 1 ? 'Sim' : 'Nao') + '</td>' +
        '<td>' + (p.acesso_app == 1 ? 'Sim' : 'Nao') + '</td>' +
        '<td>' + (p.is_admin == 1 ? 'Sim' : 'Nao') + '</td>' +
        '<td>' +
        '<button class="btn btn-primary btn-xs btn-editar-perfil" data-id="' + p.id + '" data-nome="' + p.nome + '" data-descricao="' + (p.descricao || '') + '" data-web="' + p.acesso_web + '" data-app="' + p.acesso_app + '" data-admin="' + p.is_admin + '"><i class="fas fa-edit"></i></button> ' +
        '<button class="btn btn-danger btn-xs btn-excluir-perfil" data-id="' + p.id + '"><i class="fas fa-trash"></i></button>' +
        '</td>' +
        '</tr>'
      );
    });
  });
}

$(function () {
  if (!$('#tbodyUsuarios').length) return;

  carregarUsuarios();
  carregarPerfis();

  $('#btnNovoUsuario').on('click', function () {
    $('#usuarioId, #usuarioEmail, #usuarioSenha').val('');
    $('#usuarioRole').val('user');
    $('#usuarioStatus').val('0');
    $('#modalUsuario').modal('show');
  });

  $(document).on('click', '.btn-editar-usuario', function () {
    const d = $(this).data();
    $('#usuarioId').val(d.id);
    $('#usuarioEmail').val(d.email);
    $('#usuarioSenha').val('');
    $('#usuarioRole').val(d.role);
    $('#usuarioStatus').val(d.status);
    $('#modalUsuario').modal('show');
  });

  $('#btnSalvarUsuario').on('click', function () {
    const id = $('#usuarioId').val();
    const url = id ? '../controller/back_usuarios_update.php' : '../controller/back_usuarios_insert.php';
    $.post(url, {
      id: id,
      email: $('#usuarioEmail').val(),
      senha: $('#usuarioSenha').val(),
      role: $('#usuarioRole').val(),
      status: $('#usuarioStatus').val()
    }).done(function (resp) {
      if (resp.ok) {
        $('#modalUsuario').modal('hide');
        carregarUsuarios();
      } else {
        Swal.fire('Erro', resp.msg, 'error');
      }
    }).fail(function (xhr) {
      Swal.fire('Erro', (xhr.responseJSON && xhr.responseJSON.msg) || 'Falha ao salvar.', 'error');
    });
  });

  $(document).on('click', '.btn-excluir-usuario', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Excluir usuario?', icon: 'warning', showCancelButton: true }).then(function (r) {
      if (!r.isConfirmed) return;
      $.post('../controller/back_usuarios_delete.php', { id: id }).done(function (resp) {
        if (resp.ok) carregarUsuarios();
        else Swal.fire('Erro', resp.msg, 'error');
      }).fail(function (xhr) {
        Swal.fire('Erro', (xhr.responseJSON && xhr.responseJSON.msg) || 'Falha ao excluir.', 'error');
      });
    });
  });

  $('#btnNovoPerfil').on('click', function () {
    $('#perfilId, #perfilNome, #perfilDescricao').val('');
    $('#perfilAcessoWeb, #perfilAcessoApp, #perfilIsAdmin').prop('checked', false);
    $('#modalPerfil').modal('show');
  });

  $(document).on('click', '.btn-editar-perfil', function () {
    const d = $(this).data();
    $('#perfilId').val(d.id);
    $('#perfilNome').val(d.nome);
    $('#perfilDescricao').val(d.descricao);
    $('#perfilAcessoWeb').prop('checked', d.web == 1);
    $('#perfilAcessoApp').prop('checked', d.app == 1);
    $('#perfilIsAdmin').prop('checked', d.admin == 1);
    $('#modalPerfil').modal('show');
  });

  $('#btnSalvarPerfil').on('click', function () {
    const id = $('#perfilId').val();
    const url = id ? '../controller/back_perfis_update.php' : '../controller/back_perfis_insert.php';
    $.post(url, {
      id: id,
      nome: $('#perfilNome').val(),
      descricao: $('#perfilDescricao').val(),
      acesso_web: $('#perfilAcessoWeb').is(':checked') ? 1 : 0,
      acesso_app: $('#perfilAcessoApp').is(':checked') ? 1 : 0,
      is_admin: $('#perfilIsAdmin').is(':checked') ? 1 : 0
    }).done(function (resp) {
      if (resp.ok) {
        $('#modalPerfil').modal('hide');
        carregarPerfis();
      } else {
        Swal.fire('Erro', resp.msg, 'error');
      }
    }).fail(function (xhr) {
      Swal.fire('Erro', (xhr.responseJSON && xhr.responseJSON.msg) || 'Falha ao salvar.', 'error');
    });
  });

  $(document).on('click', '.btn-excluir-perfil', function () {
    const id = $(this).data('id');
    Swal.fire({ title: 'Excluir perfil?', icon: 'warning', showCancelButton: true }).then(function (r) {
      if (!r.isConfirmed) return;
      $.post('../controller/back_perfis_delete.php', { id: id }).done(function (resp) {
        if (resp.ok) carregarPerfis();
        else Swal.fire('Erro', resp.msg, 'error');
      }).fail(function (xhr) {
        Swal.fire('Erro', (xhr.responseJSON && xhr.responseJSON.msg) || 'Falha ao excluir.', 'error');
      });
    });
  });
});
