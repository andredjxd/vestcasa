<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VESTCASA PMW - Login</title>
    <link rel="icon" type="image/png" href="../assets/img/logo_mega_loja.png">
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/plugins/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <img src="../assets/img/logovestcasa_bold.png" alt="logo" style="height:40px;opacity:.85">
    </div>
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">Acesse o sistema</p>

        <form id="formLogin">
          <div class="input-group mb-3">
            <input type="email" id="email" class="form-control" placeholder="E-mail" autocomplete="username" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" id="senha" class="form-control" placeholder="Senha" autocomplete="current-password" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../assets/plugins/jquery/jquery.min.js"></script>
  <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/plugins/sweetalert2/sweetalert2.min.js"></script>
  <script>
    $('#formLogin').on('submit', function (e) {
      e.preventDefault();
      const $btn = $(this).find('button[type=submit]').prop('disabled', true);
      $.post('../controller/back_login.php', {
        email: $('#email').val(),
        senha: $('#senha').val()
      }).done(function (resp) {
        if (resp && resp.ok) {
          window.location.href = 'index.php';
        } else {
          Swal.fire('Erro', (resp && resp.msg) || 'Nao foi possivel entrar.', 'error');
          $btn.prop('disabled', false);
        }
      }).fail(function (xhr) {
        const resp = xhr.responseJSON;
        Swal.fire('Erro', (resp && resp.msg) || 'Credenciais invalidas.', 'error');
        $btn.prop('disabled', false);
      });
    });
  </script>
</body>
</html>
