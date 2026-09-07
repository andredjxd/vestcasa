<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioLogado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

// Usada nas paginas (view/*.php via headers.php): redireciona para o login.
function exigirLoginPagina(string $loginUrl): void
{
    if (!usuarioLogado()) {
        header('Location: ' . $loginUrl);
        exit;
    }
}

function usuarioEhAdmin(): bool
{
    return ($_SESSION['usuario_role'] ?? '') === 'admin';
}

// Usada em telas/controllers restritos a administrador.
function exigirAdminPagina(string $redirectUrl): void
{
    if (!usuarioEhAdmin()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

function exigirAdminApi(): void
{
    if (!usuarioEhAdmin()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'msg' => 'Acesso restrito a administradores.']);
        exit;
    }
}

// Usada nos controllers (back_*.php via db.php): responde 401 em JSON.
function exigirLoginApi(): void
{
    if (!usuarioLogado()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'msg' => 'Sessao expirada, faca login novamente.']);
        exit;
    }
    // Libera o lock do arquivo de sessao assim que confirma o login, para
    // nao travar outras chamadas AJAX enquanto endpoints SSE ficam com a
    // conexao aberta (fila de execucao, etc).
    session_write_close();
}
