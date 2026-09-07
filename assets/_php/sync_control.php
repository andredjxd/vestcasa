<?php
header('Content-Type: application/json; charset=utf-8');

define('SYNC_DIR', '/opt/vestcasa-sync');
define('PID_FILE', SYNC_DIR . '/sync_vestcasa.pid');
define('SCRIPT',   SYNC_DIR . '/sync_vestcasa.py');
define('LOG_FILE', SYNC_DIR . '/sync_vestcasa.log');
define('LOG_LINES', 25);

function getPid(): int {
    if (!file_exists(PID_FILE)) return 0;
    return (int) trim(file_get_contents(PID_FILE));
}

function isRunning(int $pid): bool {
    return $pid > 0 && file_exists("/proc/$pid");
}

function decodeUniEscapes(string $s): string {
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($m) {
        return mb_chr(hexdec($m[1]), 'UTF-8');
    }, $s) ?? $s;
}

function getLogTail(): array {
    if (!file_exists(LOG_FILE)) return [];
    $lines = file(LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return [];
    $tail = array_slice($lines, -LOG_LINES);
    return array_map('decodeUniEscapes', $tail);
}

$action = $_GET['action'] ?? 'status';

match ($action) {

    'status' => (function () {
        $pid     = getPid();
        $running = isRunning($pid);
        echo json_encode([
            'running' => $running,
            'pid'     => $running ? $pid : null,
            'log'     => getLogTail(),
        ]);
    })(),

    'start' => (function () {
        $pid = getPid();
        if (isRunning($pid)) {
            echo json_encode(['ok' => false, 'msg' => "Já está rodando (PID $pid)"]);
            return;
        }
        $script = SCRIPT;
        $log    = LOG_FILE;
        exec("nohup python3 \"$script\" > /dev/null 2>&1 &");
        sleep(2);
        $newPid  = getPid();
        $running = isRunning($newPid);
        echo json_encode([
            'ok'  => $running,
            'msg' => $running ? "Iniciado (PID $newPid)" : 'Falha ao iniciar — verifique o log',
        ]);
    })(),

    'stop' => (function () {
        $pid = getPid();
        if (!isRunning($pid)) {
            if (file_exists(PID_FILE)) unlink(PID_FILE);
            echo json_encode(['ok' => true, 'msg' => 'Processo já estava parado']);
            return;
        }
        exec("kill $pid 2>/dev/null");
        sleep(1);
        if (!isRunning($pid)) {
            if (file_exists(PID_FILE)) unlink(PID_FILE);
            echo json_encode(['ok' => true, 'msg' => "Processo $pid encerrado"]);
        } else {
            exec("kill -9 $pid 2>/dev/null");
            sleep(1);
            echo json_encode([
                'ok'  => !isRunning($pid),
                'msg' => isRunning($pid) ? "Não foi possível encerrar PID $pid" : "Processo $pid encerrado (SIGKILL)",
            ]);
        }
    })(),

    default => (function () {
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida']);
    })(),
};
