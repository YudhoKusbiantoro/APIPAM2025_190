<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$lab_id = $data['lab_id'] ?? null;

if (!$id || !$lab_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID dan lab_id wajib diisi']);
    exit;
}

// Pastikan user yang dihapus ada di lab yang sama
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND lab_id = ?");
$stmt->execute([$id, $lab_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$id])) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus user']);
}
?>