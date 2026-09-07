<?php
require_once __DIR__ . '/../assets/_php/auth_session.php';

$_SESSION = [];
session_destroy();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
