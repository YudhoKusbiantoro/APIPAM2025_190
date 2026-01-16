<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['lab_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'id dan lab_id wajib diisi']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM bahan WHERE id = ? AND lab_id = ?");
$result = $stmt->execute([$data['id'], $data['lab_id']]);

if ($result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Bahan berhasil dihapus'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus bahan']);
}
?>