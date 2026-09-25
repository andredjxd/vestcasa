<?php
include '../assets/_db/db.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["error" => true, "message" => "ID do tipo não informado."]);
    exit;
}

try {
    $stmt = $cnx->prepare("DELETE FROM vest_avaria_tipo_avaria WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        throw new Exception("Tipo de avaria não encontrado.");
    }

    echo json_encode(["error" => false, "message" => "Tipo de avaria excluído!"]);

} catch (Throwable $e) {
    echo json_encode(["error" => true, "message" => $e->getMessage()]);
}
